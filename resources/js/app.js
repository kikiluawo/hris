import './bootstrap';
import '@fontsource-variable/plus-jakarta-sans';
import '@fontsource-variable/fraunces';
import $ from 'jquery';
import select2 from 'select2';
import Swal from 'sweetalert2';
import Alpine from 'alpinejs';
import { renderOpeningPoster, downloadOpeningPoster } from './poster';
import './assessment';

window.Swal = Swal;
select2(window, $);
window.$ = window.jQuery = $;

const systemPrefersDark = () => window.matchMedia('(prefers-color-scheme: dark)').matches;

const select2Language = {
    noResults: () => 'Tidak ada hasil',
    searching: () => 'Cari…',
};

const select2Timers = new WeakMap();

let dialogReturnFocus = null;

const refreshSelect2 = (select) => {
    const $select = $(select);

    if ($select.data('select2')) {
        $select.select2('destroy');
    }

    $select.off('.select2sync');

    $select.select2({
        width: '100%',
        placeholder: select.dataset.placeholder || undefined,
        allowClear: select.hasAttribute('data-select2-clear'),
        minimumResultsForSearch: select.options.length > 7 ? 0 : Infinity,
        language: select2Language,
    });

    $select.on('select2:select.select2sync select2:unselect.select2sync', () => {
        select.dispatchEvent(new Event('change', { bubbles: true }));
    });
};

const scheduleSelect2 = (select) => {
    clearTimeout(select2Timers.get(select));

    select2Timers.set(select, setTimeout(() => {
        select2Timers.delete(select);
        refreshSelect2(select);
    }, 0));
};

const wireSelect2 = (select) => {
    if (select.dataset.select2Wired === '1') {
        return;
    }

    select.dataset.select2Wired = '1';
    refreshSelect2(select);

    const observer = new MutationObserver(() => scheduleSelect2(select));
    observer.observe(select, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['disabled'],
    });
};

const wireAllSelect2 = () => {
    document.querySelectorAll('select[data-select2-init]').forEach((select) => {
        wireSelect2(select);
        scheduleSelect2(select);
    });
};

window.refreshSelect2Soon = (ids) => {
    ids.forEach((id) => {
        const select = document.getElementById(id);

        if (select) {
            scheduleSelect2(select);
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', wireAllSelect2);
} else {
    wireAllSelect2();
}

document.addEventListener('alpine:initialized', wireAllSelect2);

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        mode: localStorage.getItem('theme') ?? 'system',

        set(mode) {
            this.mode = mode;

            const dark = mode === 'dark' || (mode === 'system' && systemPrefersDark());
            document.documentElement.classList.toggle('dark', dark);

            if (mode === 'system') {
                localStorage.removeItem('theme');
            } else {
                localStorage.setItem('theme', mode);
            }
        },

        toggle() {
            this.set(document.documentElement.classList.contains('dark') ? 'light' : 'dark');
        },
    });

    Alpine.store('confirmBox', {
        open: false,
        title: '',
        message: '',
        confirmLabel: 'Ya, lanjutkan',
        cancelLabel: 'Batal',
        tone: 'brand',
        resolver: null,

        ask(options = {}) {
            if (this.resolver) {
                this.resolver(false);
            }

            this.title = options.title || 'Konfirmasi';
            this.message = options.message || '';
            this.confirmLabel = options.confirmLabel || 'Ya, lanjutkan';
            this.cancelLabel = options.cancelLabel || 'Batal';
            this.tone = ['brand', 'warning', 'danger'].includes(options.tone) ? options.tone : 'brand';
            dialogReturnFocus = document.activeElement;
            this.open = true;

            setTimeout(() => {
                document.querySelector('[data-confirm-accept]')?.focus();
            }, 60);

            return new Promise((resolve) => {
                this.resolver = resolve;
            });
        },

        accept() {
            this.close(true);
        },

        cancel() {
            this.close(false);
        },

        close(result) {
            this.open = false;
            const resolve = this.resolver;
            this.resolver = null;
            resolve?.(result);

            if (dialogReturnFocus instanceof HTMLElement) {
                dialogReturnFocus.focus();
            }

            dialogReturnFocus = null;
        },
    });

    Alpine.store('posterBox', {
        open: false,
        loading: false,
        image: '',
        error: '',
        job: null,
        copied: false,
        requestId: 0,

        async show(job) {
            const requestId = ++this.requestId;
            this.job = job;
            this.open = true;
            this.loading = true;
            this.image = '';
            this.error = '';
            this.copied = false;

            try {
                const image = await renderOpeningPoster(job);

                if (requestId === this.requestId) {
                    this.image = image;
                }
            } catch (error) {
                if (requestId === this.requestId) {
                    this.error = 'Gambar gagal dibuat. Silakan coba lagi.';
                }
            } finally {
                if (requestId === this.requestId) {
                    this.loading = false;
                }
            }
        },

        close() {
            this.requestId++;
            this.open = false;
            this.loading = false;
            this.image = '';
            this.error = '';
            this.job = null;
        },

        download() {
            if (this.image) {
                downloadOpeningPoster(this.image, this.job);
            }
        },

        copy() {
            const url = this.job?.url ?? '';

            if (!url) {
                return;
            }

            const markCopied = () => {
                this.copied = true;
                setTimeout(() => {
                    this.copied = false;
                }, 1600);
            };

            if (navigator.clipboard?.writeText) {
                navigator.clipboard.writeText(url).then(markCopied).catch(() => this.legacyCopy(url, markCopied));
            } else {
                this.legacyCopy(url, markCopied);
            }
        },

        legacyCopy(url, done) {
            const field = document.createElement('textarea');
            field.value = url;
            field.setAttribute('readonly', '');
            field.style.position = 'fixed';
            field.style.opacity = '0';
            document.body.appendChild(field);
            field.select();

            try {
                document.execCommand('copy');
            } catch {
                // Clipboard access can be blocked; the field stays selectable in the dialog.
            }

            field.remove();
            done();
        },
    });
});

const confirmOptions = (element) => ({
    title: element.dataset.confirmTitle || 'Konfirmasi',
    message: element.dataset.confirm || '',
    confirmLabel: element.dataset.confirmLabel || 'Ya, lanjutkan',
    cancelLabel: element.dataset.confirmCancel || 'Batal',
    tone: element.dataset.confirmTone || 'brand',
});

const confirmBox = () => Alpine.store('confirmBox');

document.addEventListener('click', (event) => {
    const button = event.target.closest('button[type="submit"][data-confirm]');

    if (!button || !button.form) {
        return;
    }

    event.preventDefault();

    confirmBox().ask(confirmOptions(button)).then((confirmed) => {
        if (!confirmed) {
            return;
        }

        if (button.form.dataset.confirm) {
            button.form.dataset.confirmOk = '1';
        }

        button.form.requestSubmit(button);
    });
}, true);

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
        return;
    }

    if (form.dataset.confirmOk === '1') {
        delete form.dataset.confirmOk;
        return;
    }

    event.preventDefault();

    const submitter = event.submitter;

    confirmBox().ask(confirmOptions(form)).then((confirmed) => {
        if (!confirmed) {
            return;
        }

        form.dataset.confirmOk = '1';
        form.requestSubmit(submitter || undefined);
    });
}, true);

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (event.defaultPrevented || !(form instanceof HTMLFormElement) || !event.submitter) {
        return;
    }

    if (form.method.toLowerCase() !== 'post') {
        return;
    }

    const submitter = event.submitter;

    setTimeout(() => {
        submitter.disabled = true;
    }, 0);
});

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
    if ((localStorage.getItem('theme') ?? 'system') === 'system') {
        document.documentElement.classList.toggle('dark', event.matches);
    }
});

window.Alpine = Alpine;

Alpine.start();
