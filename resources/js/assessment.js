const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const postJson = async (url, payload = {}) => {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(payload),
    });

    let data = {};

    try {
        data = await response.json();
    } catch (error) {
        data = {};
    }

    return { ok: response.ok, status: response.status, data };
};

const cameraErrorMessage = (error) => {
    const name = error?.name ?? '';

    if (name === 'NotAllowedError' || name === 'PermissionDeniedError' || name === 'SecurityError') {
        return 'Izin kamera ditolak. Aktifkan izin kamera untuk situs ini di pengaturan browser, lalu coba lagi.';
    }

    if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
        return 'Kamera tidak ditemukan di perangkat ini. Gunakan perangkat lain yang memiliki kamera.';
    }

    if (name === 'NotReadableError' || name === 'TrackStartError') {
        return 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi tersebut, lalu coba lagi.';
    }

    if (name === 'NotSupportedError' || !window.isSecureContext) {
        return 'Kamera hanya bisa diakses lewat koneksi aman (HTTPS). Hubungi HRD jika tautan ini tidak menggunakan HTTPS.';
    }

    return 'Kamera gagal diaktifkan. Periksa izin kamera, lalu coba lagi.';
};

window.assessmentStart = (config = {}) => ({
    agreed: false,
    starting: false,
    error: '',
    resume: Boolean(config.resume),

    get ready() {
        return this.agreed;
    },

    async begin() {
        if (!this.ready || this.starting) {
            return;
        }

        this.starting = true;
        this.error = '';

        try {
            if (!navigator.mediaDevices?.getUserMedia) {
                throw { name: !window.isSecureContext ? 'NotSupportedError' : 'NotFoundError' };
            }

            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user' },
                audio: false,
            });

            stream.getTracks().forEach((track) => track.stop());

            const { ok, data } = await postJson(config.startUrl, {});

            if (!ok) {
                this.error = data.message || 'Tes belum bisa dimulai. Coba lagi.';

                if (data.redirect) {
                    window.location.href = data.redirect;
                }

                return;
            }

            window.location.href = data.redirect;
        } catch (error) {
            this.error = cameraErrorMessage(error);
        } finally {
            this.starting = false;
        }
    },
});

const IMAGE_PLACEHOLDER = '__IMAGE__';

const uploadAssessmentImage = async (url, file) => {
    if (!file) {
        return { error: 'Pilih berkas gambar dulu.' };
    }

    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        return { error: 'Format gambar harus JPG, PNG, atau WebP.' };
    }

    if (file.size > 6 * 1024 * 1024) {
        return { error: 'Ukuran gambar maksimal 6 MB.' };
    }

    const body = new FormData();
    body.append('image', file);

    let response;
    let data = {};

    try {
        response = await fetch(url, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body,
        });

        data = await response.json().catch(() => ({}));
    } catch (error) {
        return { error: 'Gambar gagal diunggah. Periksa koneksi internet Anda, lalu coba lagi.' };
    }

    if (!response.ok || !data.name) {
        return { error: data.message || 'Gambar gagal diunggah. Coba lagi dengan berkas lain.' };
    }

    return { name: data.name };
};

const imagePreview = (root, name) => (name ? root.replace(IMAGE_PLACEHOLDER, name) : null);

let questionUid = 0;

const normalizeQuestion = (question = {}) => {
    const rawOptions = Array.isArray(question.options) ? question.options : [];
    const options = rawOptions.length >= 2
        ? rawOptions.slice(0, 6).map((option) => {
            if (option && typeof option === 'object' && 'text' in option) {
                return String(option.text ?? '');
            }
            return String(option ?? '');
        })
        : ['', ''];

    const rawImages = Array.isArray(question.option_images) ? question.option_images : [];

    let correct = question.correct_option;

    if (correct === '' || correct === undefined) {
        correct = null;
    } else {
        correct = Number(correct);
    }

    const type = ['essay', 'scale', 'disc'].includes(question.type) ? question.type : 'multiple_choice';

    return {
        _uid: question._uid || `q-${Date.now()}-${questionUid++}`,
        type,
        question: String(question.question ?? ''),
        image_path: String(question.image_path ?? ''),
        dimension: String(question.dimension ?? ''),
        options,
        option_images: options.map((_, index) => String(rawImages[index] ?? '')),
        correct_option: Number.isInteger(correct) ? correct : null,
        points: type === 'scale' || type === 'disc' ? (Number(question.points) || 0) : (Number(question.points) || 1),
    };
};

window.assessmentForm = (config = {}) => ({
    questions: (config.questions ?? []).map(normalizeQuestion),
    bank: Array.isArray(config.bank) ? config.bank : [],
    uploadUrl: config.uploadUrl ?? '',
    imageRoot: config.imageRoot ?? '',
    bankOpen: false,
    bankQuery: '',
    bankType: '',
    bankDimension: '',
    bankChecked: {},
    bankNotice: '',
    errorMessage: '',
    imageBusy: '',
    imageError: '',
    formTab: (config.questions && config.questions.length > 0) ? 'questions' : 'settings',
    activeQuestionIndex: 0,
    questionSearch: '',
    questionFilter: 'all',

    get currentQuestion() {
        if (!Array.isArray(this.questions) || this.questions.length === 0) {
            return null;
        }
        if (this.activeQuestionIndex >= this.questions.length) {
            this.activeQuestionIndex = this.questions.length - 1;
        }
        if (this.activeQuestionIndex < 0) {
            this.activeQuestionIndex = 0;
        }
        return this.questions[this.activeQuestionIndex];
    },

    get filteredQuestionsWithIndex() {
        let list = this.questions.map((q, idx) => ({ ...q, _originalIndex: idx }));

        if (this.questionFilter !== 'all') {
            if (this.questionFilter === 'disc') {
                list = list.filter((q) => q.type === 'disc');
            } else if (this.questionFilter === 'aptitude') {
                list = list.filter((q) => ['Verbal', 'Numerik', 'Logika', 'Spasial'].includes(q.dimension));
            } else if (this.questionFilter === 'accuracy') {
                list = list.filter((q) => q.dimension === 'Ketelitian');
            } else if (this.questionFilter === 'scale') {
                list = list.filter((q) => q.type === 'scale');
            } else if (this.questionFilter === 'essay') {
                list = list.filter((q) => q.type === 'essay');
            } else if (this.questionFilter === 'multiple_choice') {
                list = list.filter((q) => q.type === 'multiple_choice');
            }
        }

        if (this.questionSearch.trim() !== '') {
            const term = this.questionSearch.trim().toLowerCase();
            list = list.filter((q) => (q.question || '').toLowerCase().includes(term) || (q.dimension || '').toLowerCase().includes(term));
        }

        return list;
    },

    selectQuestion(index) {
        if (index >= 0 && index < this.questions.length) {
            this.activeQuestionIndex = index;
        }
    },

    nextQuestion() {
        if (this.activeQuestionIndex < this.questions.length - 1) {
            this.activeQuestionIndex++;
        }
    },

    prevQuestion() {
        if (this.activeQuestionIndex > 0) {
            this.activeQuestionIndex--;
        }
    },

    duplicateQuestion(index) {
        const source = this.questions[index];
        if (!source) return;

        const copy = normalizeQuestion({
            type: source.type,
            question: source.question + ' (Salinan)',
            image_path: source.image_path,
            dimension: source.dimension,
            options: [...source.options],
            option_images: [...source.option_images],
            correct_option: source.correct_option,
            points: source.points,
        });

        this.questions.splice(index + 1, 0, copy);
        this.activeQuestionIndex = index + 1;
    },

    moveQuestion(index, direction) {
        const targetIndex = index + direction;
        if (targetIndex < 0 || targetIndex >= this.questions.length) return;

        const [moved] = this.questions.splice(index, 1);
        this.questions.splice(targetIndex, 0, moved);
        this.activeQuestionIndex = targetIndex;
    },

    init() {
        if (this.questions.length === 0) {
            this.questions.push(normalizeQuestion({ type: 'multiple_choice', points: 1 }));
        }
    },

    imageUrl(name) {
        return imagePreview(this.imageRoot, name);
    },

    optionImage(question, index) {
        return question.option_images?.[index] ?? null;
    },

    optionFilled(question, index) {
        return String(question.options[index] ?? '').trim() !== '' || Boolean(this.optionImage(question, index));
    },

    async pickImage(key, files, apply) {
        const file = files?.[0];

        if (!file || this.imageBusy) {
            return;
        }

        this.imageBusy = key;
        this.imageError = '';

        const result = await uploadAssessmentImage(this.uploadUrl, file);

        this.imageBusy = '';

        if (result.error) {
            this.imageError = result.error;

            return;
        }

        apply(result.name);
    },

    clearImage(question, index = null) {
        if (index === null) {
            question.image_path = '';

            return;
        }

        question.option_images[index] = '';
    },

    get bankDimensions() {
        const dims = this.bank.map((item) => {
            if (item.type === 'disc') return 'Kepribadian DISC';
            return (item.dimension || '').trim();
        }).filter(Boolean);

        return [...new Set(dims)].sort();
    },

    get bankFiltered() {
        const term = this.bankQuery.trim().toLowerCase();

        return this.bank.filter((item) => {
            if (this.bankType && item.type !== this.bankType) {
                return false;
            }

            if (this.bankDimension) {
                const itemDim = item.type === 'disc' ? 'Kepribadian DISC' : (item.dimension || '').trim();
                if (itemDim !== this.bankDimension) {
                    return false;
                }
            }

            if (term === '') {
                return true;
            }

            return `${item.question} ${item.dimension ?? ''}`.toLowerCase().includes(term);
        });
    },

    get bankSelectedCount() {
        return Object.values(this.bankChecked).filter(Boolean).length;
    },

    bankTypeLabel(type) {
        return { multiple_choice: 'Pilihan ganda', essay: 'Esai', scale: 'Skala', disc: 'DISC' }[type] ?? 'Soal';
    },

    inCurrentTest(item) {
        const text = String(item.question).trim().toLowerCase();

        return this.questions.some((question) => String(question.question).trim().toLowerCase() === text);
    },

    openBank() {
        this.bankNotice = this.bank.length === 0
            ? 'Bank soal masih kosong. Tambahkan soal lewat menu Bank soal.'
            : '';
        this.bankOpen = true;
    },

    toggleBank(item) {
        if (this.inCurrentTest(item)) {
            return;
        }

        this.bankChecked[item.id] = !this.bankChecked[item.id];
    },

    checkAllBank() {
        this.bankFiltered.forEach((item) => this.toggleBankState(item, true));
    },

    uncheckAllBank() {
        this.bankFiltered.forEach((item) => this.toggleBankState(item, false));
    },

    toggleBankState(item, state) {
        if (!this.inCurrentTest(item)) {
            this.bankChecked[item.id] = state;
        }
    },

    addFromBank() {
        const picked = this.bank.filter((item) => this.bankChecked[item.id] && !this.inCurrentTest(item));

        if (picked.length === 0) {
            this.bankNotice = 'Centang dulu soal yang mau dipakai.';

            return;
        }

        picked.forEach((item) => {
            this.questions.push(normalizeQuestion({
                type: item.type,
                question: item.question,
                image_path: item.image_path,
                dimension: item.dimension,
                options: item.options,
                option_images: item.option_images,
                correct_option: item.correct_option,
                points: item.points,
            }));
        });

        this.bankChecked = {};
        this.bankNotice = picked.length + ' soal ditambahkan ke tes.';
        this.bankOpen = false;
        this.activeQuestionIndex = this.questions.length - 1;
        this.questionFilter = 'all';
    },

    addQuestion(type) {
        this.questions.push(normalizeQuestion({ type, points: type === 'disc' ? 0 : 1 }));
        this.activeQuestionIndex = this.questions.length - 1;
        this.questionFilter = 'all';
    },

    remove(index) {
        if (this.questions.length <= 1) {
            this.errorMessage = 'Tes harus punya minimal satu soal.';
            return;
        }

        this.questions.splice(index, 1);
        if (this.activeQuestionIndex >= this.questions.length) {
            this.activeQuestionIndex = this.questions.length - 1;
        }
    },

    addOption(question) {
        if (question.options.length < 6) {
            question.options.push('');
            question.option_images.push('');
        }
    },

    removeOption(question, index) {
        if (question.options.length <= 2) {
            return;
        }

        question.options.splice(index, 1);
        question.option_images.splice(index, 1);

        if (question.correct_option === index) {
            question.correct_option = null;
        } else if (question.correct_option > index) {
            question.correct_option -= 1;
        }
    },

    switchType(question) {
        if (question.type === 'multiple_choice') {
            if (!Array.isArray(question.options) || question.options.length < 2) {
                question.options = ['', ''];
                question.option_images = ['', ''];
            }

            question.correct_option = null;
            return;
        }

        if (question.type === 'disc') {
            if (!Array.isArray(question.options)) {
                question.options = [];
                question.option_images = [];
            }
            while (question.options.length < 4) {
                question.options.push('');
                question.option_images.push('');
            }
            if (question.options.length > 4) {
                question.options = question.options.slice(0, 4);
                question.option_images = question.option_images.slice(0, 4);
            }
            if (!question.dimension) {
                question.dimension = 'S,I,D,C';
            }
            question.points = 0;
            question.correct_option = null;
            return;
        }

        if (question.type === 'scale') {
            if (!Array.isArray(question.options) || question.options.length < 2) {
                question.options = ['', ''];
                question.option_images = ['', ''];
            }
        }

        question.correct_option = null;
    },

    totalPoints() {
        return this.questions
            .filter((question) => question.type !== 'scale' && question.type !== 'disc')
            .reduce((sum, question) => sum + (Number(question.points) || 0), 0);
    },

    validate() {
        this.errorMessage = '';
        this.imageError = '';

        if (this.imageBusy) {
            this.errorMessage = 'Gambar masih diunggah. Tunggu sampai selesai sebelum menyimpan.';
            return false;
        }

        for (let index = 0; index < this.questions.length; index += 1) {
            const question = this.questions[index];
            const number = index + 1;

            if (!String(question.question).trim()) {
                this.errorMessage = `Teks soal nomor ${number} masih kosong.`;
                return false;
            }

            if (question.type !== 'scale' && question.type !== 'disc' && (!Number.isFinite(Number(question.points)) || Number(question.points) < 1)) {
                this.errorMessage = `Poin soal nomor ${number} minimal 1.`;
                return false;
            }

            if (question.type === 'essay') {
                continue;
            }

            const minOptions = question.type === 'disc' ? 4 : 2;
            if (question.options.length < minOptions) {
                this.errorMessage = question.type === 'disc'
                    ? `Soal nomor ${number} (DISC) harus memiliki 4 pilihan pernyataan.`
                    : `Soal nomor ${number} butuh minimal dua pilihan.`;
                return false;
            }

            const blank = question.options
                .map((_, optionIndex) => optionIndex)
                .filter((optionIndex) => !this.optionFilled(question, optionIndex))
                .map((optionIndex) => String.fromCharCode(65 + optionIndex));

            if (blank.length > 0) {
                this.errorMessage = `Pilihan ${blank.join(', ')} di soal nomor ${number} belum ada teks atau gambarnya.`;
                return false;
            }

            if (question.type === 'scale' || question.type === 'disc') {
                continue;
            }

            const correct = Number(question.correct_option);

            if (question.correct_option === null || correct < 0 || correct >= question.options.length) {
                this.errorMessage = `Tandai jawaban benar untuk soal nomor ${number}.`;
                return false;
            }
        }

        return true;
    },
});

window.bankForm = (initial = {}, config = {}) => ({
    uploadUrl: config.uploadUrl ?? '',
    imageRoot: config.imageRoot ?? '',
    type: ['multiple_choice', 'essay', 'scale', 'disc'].includes(initial.type) ? initial.type : 'scale',
    question: String(initial.question ?? ''),
    image_path: String(initial.image_path ?? ''),
    dimension: String(initial.dimension ?? ''),
    options: Array.isArray(initial.options) && initial.options.length > 0
        ? initial.options.slice(0, 6).map((option) => {
            if (option && typeof option === 'object' && 'text' in option) {
                return String(option.text ?? '');
            }
            return String(option ?? '');
        })
        : ['', ''],
    option_images: [],
    imageBusy: '',
    imageError: '',
    correct: initial.correct_option === null || initial.correct_option === undefined ? '' : String(initial.correct_option),
    points: Number(initial.points) || 1,

    init() {
        const raw = Array.isArray(initial.option_images) ? initial.option_images : [];

        this.option_images = this.options.map((_, index) => String(raw[index] ?? ''));
    },

    imageUrl(name) {
        return imagePreview(this.imageRoot, name);
    },

    optionFilled(index) {
        return String(this.options[index] ?? '').trim() !== '' || Boolean(this.option_images[index]);
    },

    async pickImage(key, files, apply) {
        const file = files?.[0];

        if (!file || this.imageBusy) {
            return;
        }

        this.imageBusy = key;
        this.imageError = '';

        const result = await uploadAssessmentImage(this.uploadUrl, file);

        this.imageBusy = '';

        if (result.error) {
            this.imageError = result.error;

            return;
        }

        apply(result.name);
    },

    clearImage(kind, index = null) {
        if (kind === 'question') {
            this.image_path = '';

            return;
        }

        this.option_images[index] = '';
    },

    toType(next) {
        this.type = next;

        if (next === 'essay') {
            this.correct = '';

            return;
        }

        if (this.options.length < 2) {
            this.options = ['', ''];
            this.option_images = ['', ''];
        }

        if (next === 'scale' || next === 'disc') {
            this.correct = '';
        }

        if (next === 'disc') {
            this.points = 0;
            if (this.options.length < 4) {
                while (this.options.length < 4) {
                    this.options.push('');
                    this.option_images.push('');
                }
            }
        }
    },

    addOption() {
        if (this.options.length < 6) {
            this.options.push('');
            this.option_images.push('');
        }
    },

    removeOption(index) {
        if (this.options.length <= 2) {
            return;
        }

        this.options.splice(index, 1);
        this.option_images.splice(index, 1);

        if (this.correct === String(index)) {
            this.correct = '';
        } else if (this.correct !== '' && Number(this.correct) > index) {
            this.correct = String(Number(this.correct) - 1);
        }
    },

    likert() {
        this.options = ['Sangat tidak sesuai', 'Tidak sesuai', 'Cukup sesuai', 'Sesuai', 'Sangat sesuai'];
        this.option_images = ['', '', '', '', ''];
        this.correct = '';
    },
});

window.assessmentTest = (config = {}) => ({
    questions: config.questions ?? [],
    answers: Object.fromEntries((config.questions ?? []).map((question) => [
        question.id,
        {
            selected_option: question.selected_option ?? null,
            most_option: question.most_option ?? null,
            least_option: question.least_option ?? null,
            answer: question.answer ?? '',
        },
    ])),
    current: 0,
    total: (config.questions ?? []).length,
    remaining: Number(config.remaining ?? 0),
    deadline: 0,
    saving: false,
    saveState: 'idle',
    saveResetTimer: null,
    submitting: false,
    ended: false,
    endedMessage: '',
    warning: null,
    confirmSubmit: false,
    paletteOpen: false,
    zoomImage: null,
    cameraReady: false,
    cameraError: '',
    cameraManualStop: false,
    stream: null,
    hiddenTimer: null,
    tickHandle: null,
    visibilityHandler: null,
    beforeUnloadHandler: null,
    isFullscreen: false,
    fullscreenHandler: null,
    keyHandler: null,
    copyHandler: null,
    copyAlert: false,
    copyAlertTimer: null,
    snapshotTimer: null,

    init() {
        this.deadline = Date.now() + this.remaining * 1000;
        this.tick();
        this.tickHandle = setInterval(() => this.tick(), 1000);

        this.isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);
        this.visibilityHandler = () => this.onVisibilityChange();
        this.beforeUnloadHandler = (event) => this.onBeforeUnload(event);
        this.fullscreenHandler = () => this.onFullscreenChange();
        this.keyHandler = (event) => this.onKeyDown(event);
        this.copyHandler = (event) => this.onCopyAttempt(event);

        document.addEventListener('visibilitychange', this.visibilityHandler);
        window.addEventListener('beforeunload', this.beforeUnloadHandler);
        document.addEventListener('fullscreenchange', this.fullscreenHandler);
        document.addEventListener('webkitfullscreenchange', this.fullscreenHandler);
        window.addEventListener('keydown', this.keyHandler);
        document.addEventListener('copy', this.copyHandler);
        document.addEventListener('cut', this.copyHandler);
        document.addEventListener('paste', this.copyHandler);

        this.startCamera();
        this.snapshotTimer = setInterval(() => this.captureSnapshot('periodic'), 75000);
    },

    destroy() {
        clearInterval(this.tickHandle);
        clearInterval(this.snapshotTimer);
        clearTimeout(this.hiddenTimer);
        clearTimeout(this.saveResetTimer);
        clearTimeout(this.copyAlertTimer);
        document.removeEventListener('visibilitychange', this.visibilityHandler);
        window.removeEventListener('beforeunload', this.beforeUnloadHandler);
        document.removeEventListener('fullscreenchange', this.fullscreenHandler);
        document.removeEventListener('webkitfullscreenchange', this.fullscreenHandler);
        window.removeEventListener('keydown', this.keyHandler);
        document.removeEventListener('copy', this.copyHandler);
        document.removeEventListener('cut', this.copyHandler);
        document.removeEventListener('paste', this.copyHandler);
        this.stopCamera();
    },

    get timeLabel() {
        const seconds = Math.max(0, this.remaining);
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const rest = seconds % 60;

        if (hours > 0) {
            return `${hours}:${String(minutes).padStart(2, '0')}:${String(rest).padStart(2, '0')}`;
        }

        return `${String(minutes).padStart(2, '0')}:${String(rest).padStart(2, '0')}`;
    },

    get progress() {
        return this.total === 0 ? 0 : Math.round(((this.current + 1) / this.total) * 100);
    },

    get answeredCount() {
        return this.questions.filter((question) => this.answered(question)).length;
    },

    get unansweredCount() {
        return this.total - this.answeredCount;
    },

    get saveLabel() {
        if (this.saveState === 'saving') {
            return 'Menyimpan…';
        }

        if (this.saveState === 'error') {
            return 'Gagal menyimpan';
        }

        if (this.saveState === 'saved') {
            return 'Tersimpan';
        }

        return 'Jawaban tersimpan otomatis';
    },

    answered(question) {
        const answer = this.answers[question.id];

        if (!answer) {
            return false;
        }

        if (question.type === 'essay') {
            return String(answer.answer ?? '').trim() !== '';
        }

        if (question.type === 'disc') {
            return answer.most_option !== null && answer.most_option !== undefined
                && answer.least_option !== null && answer.least_option !== undefined;
        }

        return answer.selected_option !== null && answer.selected_option !== undefined;
    },

    selectDisc(question, role, index) {
        const ans = this.answers[question.id];
        if (!ans) return;

        if (role === 'most') {
            if (ans.least_option === index) {
                ans.least_option = null;
            }
            ans.most_option = index;
            ans.selected_option = index;
        } else if (role === 'least') {
            if (ans.most_option === index) {
                ans.most_option = null;
            }
            ans.least_option = index;
        }

        this.save(question);
    },

    optionText(question, index) {
        return String(question.options?.[index] ?? '').trim();
    },

    optionImage(question, index) {
        return question.option_images?.[index] ?? null;
    },

    zoom(url) {
        if (url) {
            this.zoomImage = url;
        }
    },

    tick() {
        if (this.ended) {
            return;
        }

        this.remaining = Math.max(0, Math.ceil((this.deadline - Date.now()) / 1000));

        if (this.remaining <= 0 && !this.submitting) {
            clearInterval(this.tickHandle);
            this.submit('time_up');
        }
    },

    syncRemaining(remaining) {
        if (typeof remaining !== 'number') {
            return;
        }

        this.remaining = remaining;
        this.deadline = Date.now() + remaining * 1000;
    },

    jump(index) {
        if (this.ended) {
            return;
        }

        this.save(this.questions[this.current]);
        this.current = Math.max(0, Math.min(index, this.total - 1));
        this.paletteOpen = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    prev() {
        this.jump(this.current - 1);
    },

    next() {
        if (this.current < this.total - 1) {
            this.jump(this.current + 1);
            return;
        }

        this.openSubmit();
    },

    openSubmit() {
        if (this.ended) {
            return;
        }

        this.save(this.questions[this.current]);
        this.confirmSubmit = true;
    },

    save(question) {
        if (this.ended || !question) {
            return Promise.resolve();
        }

        const answer = this.answers[question.id];

        if (!answer) {
            return Promise.resolve();
        }

        this.saving = true;
        this.saveState = 'saving';
        clearTimeout(this.saveResetTimer);

        return postJson(config.answerUrl, {
            question_id: question.id,
            selected_option: question.type === 'essay' ? null : (answer.selected_option ?? null),
            most_option: question.type === 'disc' ? (answer.most_option ?? null) : null,
            least_option: question.type === 'disc' ? (answer.least_option ?? null) : null,
            answer: question.type === 'essay' ? (answer.answer ?? '') : null,
            access_token: config.accessToken,
        })
            .then(({ ok, status, data }) => {
                if (status === 409) {
                    window.location.href = config.finishedUrl;
                    return;
                }
                if (data.action === 'ended') {
                    this.finish(data.message);
                    return;
                }

                if (!ok) {
                    this.saveState = 'error';

                    if (status === 419) {
                        this.finish('Sesi Anda kedaluwarsa. Muat ulang halaman untuk melanjutkan.');
                    }

                    return;
                }

                this.saveState = 'saved';
                this.syncRemaining(data.remaining);
                this.saveResetTimer = setTimeout(() => {
                    if (this.saveState === 'saved') {
                        this.saveState = 'idle';
                    }
                }, 2400);
            })
            .catch(() => {
                this.saveState = 'error';
            })
            .finally(() => {
                this.saving = false;
            });
    },

    async submit() {
        if (this.submitting || this.ended) {
            return;
        }

        this.submitting = true;
        this.confirmSubmit = false;

        try {
            await this.save(this.questions[this.current]);

            if (this.ended) {
                window.location.href = config.finishedUrl;
                return;
            }

            this.captureSnapshot('submit');
            const { data, status } = await postJson(config.submitUrl, { access_token: config.accessToken });
            if (status === 409) {
                window.location.href = config.finishedUrl;
                return;
            }
            window.location.href = data.redirect || config.finishedUrl;
        } catch (error) {
            this.submitting = false;
            this.finish('Jawaban gagal dikirim. Periksa koneksi internet Anda, lalu muat ulang halaman.');
        }
    },

    onBeforeUnload(event) {
        if (this.ended || this.submitting) {
            return;
        }

        event.preventDefault();
        event.returnValue = '';
    },

    onVisibilityChange() {
        if (this.ended) {
            return;
        }

        if (document.visibilityState === 'hidden') {
            this.reportViolation('tab_hidden');
            this.captureSnapshot('violation');

            clearTimeout(this.hiddenTimer);
            this.hiddenTimer = setTimeout(() => {
                const payload = new FormData();
                payload.append('type', 'tab_hidden_long');
                payload.append('_token', csrfToken());
                payload.append('access_token', config.accessToken);
                navigator.sendBeacon(config.violationUrl, payload);
            }, 15000);

            return;
        }

        clearTimeout(this.hiddenTimer);
        this.checkStatus();
    },

    async reportViolation(type) {
        if (this.ended) {
            return;
        }

        try {
            const { data, status } = await postJson(config.violationUrl, { type, access_token: config.accessToken });
        if (status === 409) {
            window.location.href = config.finishedUrl;
            return;
        }
            this.handleViolation(data);
        } catch (error) {
            this.saveState = 'error';
        }
    },

    handleViolation(data) {
        if (!data) {
            return;
        }

        if (data.action === 'warn') {
            this.warning = data.count ?? 1;
            this.syncRemaining(data.remaining);
            return;
        }

        if (data.action === 'ended') {
            this.finish(data.message);
        }
    },

    acknowledge() {
        this.warning = null;
        postJson(config.violationUrl, { type: 'warning_acknowledged', access_token: config.accessToken }).catch(() => {});
    },

    async checkStatus() {
        if (this.ended) {
            return;
        }

        try {
            const response = await fetch(config.statusUrl, {
                headers: { Accept: 'application/json', 'X-Access-Token': config.accessToken },
            });
            if (response.status === 409) {
                window.location.href = config.finishedUrl;
                return;
            }
            const data = await response.json();

            if (data.status && data.status !== 'in_progress') {
                if (data.status === 'ended') {
                    this.finish(data.message || 'Tes dihentikan karena pelanggaran aturan.');
                } else {
                    this.finish(data.reason === 'time_up'
                        ? 'Waktu tes sudah habis. Jawaban yang tersimpan sudah dikirim.'
                        : 'Tes sudah dikirim.');
                }

                return;
            }

            this.syncRemaining(data.remaining);
        } catch (error) {
            this.saveState = 'error';
        }
    },

    finish(message) {
        if (this.ended) {
            return;
        }

        this.ended = true;
        this.endedMessage = message || 'Tes sudah berakhir.';
        this.confirmSubmit = false;
        this.warning = null;
        this.submitting = false;
        clearInterval(this.tickHandle);
        clearTimeout(this.hiddenTimer);
        this.stopCamera();
    },

    leave() {
        window.location.href = config.finishedUrl;
    },

    async startCamera() {
        this.cameraError = '';
        this.cameraManualStop = false;

        if (!navigator.mediaDevices?.getUserMedia) {
            this.cameraError = cameraErrorMessage({ name: 'NotSupportedError' });
            return;
        }

        try {
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 } },
                audio: false,
            });

            const video = this.$refs.camera;

            if (video) {
                video.srcObject = this.stream;
                video.play().catch(() => {});
            }

            const track = this.stream.getVideoTracks()[0];

            if (track) {
                track.addEventListener('ended', () => this.onCameraLost());
            }

            this.cameraReady = true;
            setTimeout(() => this.captureSnapshot('start'), 2000);
        } catch (error) {
            this.cameraError = cameraErrorMessage(error);
        }
    },

    async requestFullscreen() {
        try {
            const el = document.documentElement;
            if (el.requestFullscreen) {
                await el.requestFullscreen();
            } else if (el.webkitRequestFullscreen) {
                await el.webkitRequestFullscreen();
            }
            this.isFullscreen = true;
        } catch (error) {
            // Safari/mobile permissions
        }
    },

    onFullscreenChange() {
        if (this.ended) {
            return;
        }

        const isNowFull = !!(document.fullscreenElement || document.webkitFullscreenElement);
        if (!isNowFull && this.isFullscreen) {
            this.isFullscreen = false;
            this.reportViolation('fullscreen_exit');
            this.captureSnapshot('violation');
        } else if (isNowFull) {
            this.isFullscreen = true;
        }
    },

    onKeyDown(event) {
        if (this.ended) {
            return;
        }

        const key = event.key?.toLowerCase();
        const isCtrlOrCmd = event.ctrlKey || event.metaKey;

        if (
            (isCtrlOrCmd && ['c', 'v', 'x', 'u', 'a', 'p', 's'].includes(key)) ||
            key === 'f12' ||
            (isCtrlOrCmd && event.shiftKey && ['i', 'j', 'c'].includes(key))
        ) {
            event.preventDefault();
            this.triggerCopyAlert();
        }
    },

    onCopyAttempt(event) {
        if (this.ended) {
            return;
        }
        event.preventDefault();
        this.triggerCopyAlert();
    },

    triggerCopyAlert() {
        this.copyAlert = true;
        clearTimeout(this.copyAlertTimer);
        this.copyAlertTimer = setTimeout(() => {
            this.copyAlert = false;
        }, 3000);
    },

    captureSnapshot(trigger = 'periodic') {
        if (this.ended || !this.cameraReady || !this.$refs.camera || !config.snapshotUrl) {
            return;
        }

        try {
            const video = this.$refs.camera;
            if (!video.videoWidth || !video.videoHeight) {
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = 320;
            canvas.height = 240;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, 320, 240);
            const dataUrl = canvas.toDataURL('image/jpeg', 0.55);

            postJson(config.snapshotUrl, {
                image: dataUrl,
                trigger: trigger,
                access_token: config.accessToken,
            }).catch(() => {});
        } catch (error) {
            // non-blocking
        }
    },

    retryCamera() {
        this.stopCamera();
        this.cameraReady = false;
        this.startCamera();
    },

    stopCamera() {
        this.cameraManualStop = true;

        if (this.stream) {
            this.stream.getTracks().forEach((track) => track.stop());
            this.stream = null;
        }

        this.cameraReady = false;
    },

    onCameraLost() {
        if (this.ended || this.cameraManualStop) {
            return;
        }

        this.cameraReady = false;
        this.cameraError = 'Kamera terputus. Aktifkan kembali kamera untuk melanjutkan tes.';
        this.reportViolation('camera_lost');
    },
});
