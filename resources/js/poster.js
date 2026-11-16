const W = 1080;
const H = 1350;
const M = 76;

const GOLD = '#d9b46a';
const SANS = '"Plus Jakarta Sans Variable", "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif';
const SERIF = '"Fraunces Variable", Fraunces, Georgia, "Times New Roman", serif';

let fontsPromise = null;

const ensureFonts = () => {
    fontsPromise ??= (async () => {
        if (!document.fonts) {
            return;
        }

        try {
            await Promise.all([
                document.fonts.load(`500 96px ${SERIF}`, 'A'),
                document.fonts.load(`400 24px ${SANS}`, 'A'),
                document.fonts.load(`600 30px ${SANS}`, 'A'),
                document.fonts.load(`700 20px ${SANS}`, 'A'),
            ]);
        } catch {
            // Font loading is best effort; fallback fonts still render.
        }

        await document.fonts.ready;
    })();

    return fontsPromise;
};

const roundRect = (ctx, x, y, width, height, radius) => {
    ctx.beginPath();

    if (typeof ctx.roundRect === 'function') {
        ctx.roundRect(x, y, width, height, radius);
        return;
    }

    ctx.moveTo(x + radius, y);
    ctx.arcTo(x + width, y, x + width, y + height, radius);
    ctx.arcTo(x + width, y + height, x, y + height, radius);
    ctx.arcTo(x, y + height, x, y, radius);
    ctx.arcTo(x, y, x + width, y, radius);
    ctx.closePath();
};

const trackedWidth = (ctx, text, spacing) => {
    let width = 0;

    for (const char of text) {
        width += ctx.measureText(char).width + spacing;
    }

    return Math.max(0, width - spacing);
};

const tracked = (ctx, text, x, y, spacing) => {
    let cursor = x;

    for (const char of text) {
        ctx.fillText(char, cursor, y);
        cursor += ctx.measureText(char).width + spacing;
    }
};

const elide = (ctx, text, maxWidth) => {
    let value = String(text ?? '');

    if (ctx.measureText(value).width <= maxWidth) {
        return value;
    }

    while (value.length > 1 && ctx.measureText(value + '…').width > maxWidth) {
        value = value.slice(0, -1);
    }

    return value.trimEnd() + '…';
};

const wrap = (ctx, text, maxWidth, maxLines) => {
    const words = String(text ?? '').split(/\s+/).filter(Boolean);
    const lines = [];
    let current = '';

    for (let index = 0; index < words.length; index++) {
        const candidate = current ? `${current} ${words[index]}` : words[index];

        if (ctx.measureText(candidate).width <= maxWidth) {
            current = candidate;
            continue;
        }

        if (current) {
            lines.push(current);
            current = words[index];
        } else {
            lines.push(words[index]);
            current = '';
        }

        if (lines.length === maxLines) {
            const rest = [current, ...words.slice(index + 1)].filter(Boolean).join(' ');

            if (rest) {
                let last = lines[lines.length - 1];

                while (last.length > 1 && ctx.measureText(last + '…').width > maxWidth) {
                    last = last.slice(0, -1);
                }

                lines[lines.length - 1] = last.trimEnd() + '…';
            }

            return lines;
        }
    }

    if (current && lines.length < maxLines) {
        lines.push(current);
    }

    return lines;
};

const drawDiamond = (ctx, cx, cy, radius) => {
    ctx.save();
    ctx.translate(cx, cy);
    ctx.rotate(Math.PI / 4);
    ctx.fillStyle = GOLD;
    ctx.fillRect(-radius, -radius, radius * 2, radius * 2);
    ctx.restore();
};

const paintBackground = (ctx) => {
    ctx.fillStyle = '#06231b';
    ctx.fillRect(0, 0, W, H);

    let glow = ctx.createRadialGradient(90, 0, 0, 90, 0, 980);
    glow.addColorStop(0, 'rgba(217, 180, 106, 0.20)');
    glow.addColorStop(1, 'rgba(217, 180, 106, 0)');
    ctx.fillStyle = glow;
    ctx.fillRect(0, 0, W, H);

    glow = ctx.createRadialGradient(W, H, 0, W, H, 900);
    glow.addColorStop(0, 'rgba(16, 185, 129, 0.15)');
    glow.addColorStop(1, 'rgba(16, 185, 129, 0)');
    ctx.fillStyle = glow;
    ctx.fillRect(0, 0, W, H);

    ctx.save();
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.025)';
    ctx.lineWidth = 1;

    for (let x = M; x < W; x += 64) {
        ctx.beginPath();
        ctx.moveTo(x + 0.5, 34);
        ctx.lineTo(x + 0.5, H - 34);
        ctx.stroke();
    }

    for (let y = 34; y < H; y += 64) {
        ctx.beginPath();
        ctx.moveTo(34, y + 0.5);
        ctx.lineTo(W - 34, y + 0.5);
        ctx.stroke();
    }

    ctx.restore();

    ctx.strokeStyle = 'rgba(217, 180, 106, 0.30)';
    ctx.lineWidth = 1.5;
    roundRect(ctx, 34, 34, W - 68, H - 68, 30);
    ctx.stroke();
};

const drawStatusPill = (ctx, isOpen) => {
    const label = isOpen ? 'DIBUKA' : 'DITUTUP';

    ctx.font = `700 17px ${SANS}`;
    const width = trackedWidth(ctx, label, 3) + 66;
    const x = W - M - width;
    const y = 84;
    const height = 44;

    roundRect(ctx, x, y, width, height, height / 2);
    ctx.fillStyle = isOpen ? 'rgba(16, 185, 129, 0.12)' : 'rgba(255, 255, 255, 0.06)';
    ctx.fill();
    ctx.strokeStyle = isOpen ? 'rgba(110, 231, 183, 0.32)' : 'rgba(255, 255, 255, 0.16)';
    ctx.lineWidth = 1.5;
    ctx.stroke();

    ctx.fillStyle = isOpen ? '#6ee7b7' : 'rgba(255, 255, 255, 0.55)';
    ctx.beginPath();
    ctx.arc(x + 24, y + height / 2, 4, 0, Math.PI * 2);
    ctx.fill();

    tracked(ctx, label, x + 38, y + 13, 3);
};

const drawStats = (ctx, top, job) => {
    const height = 184;

    roundRect(ctx, M, top, W - M * 2, height, 26);
    ctx.fillStyle = 'rgba(255, 255, 255, 0.045)';
    ctx.fill();
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.09)';
    ctx.lineWidth = 1.5;
    ctx.stroke();

    const skills = Array.isArray(job.skills) ? job.skills.filter(Boolean) : [];
    const experience = job.experience === null || job.experience === undefined
        ? 'Terbuka'
        : job.experience === 0 ? 'Tanpa syarat' : `${job.experience} tahun`;

    const rows = [
        ['PENDIDIKAN', job.education || 'Terbuka'],
        ['PENGALAMAN', experience],
        ['KEAHLIAN', skills.length ? `${skills.length} bidang` : 'Terbuka'],
        ['DIBUKA', job.opened || '—'],
    ];

    rows.forEach(([label, value], index) => {
        const x = M + 44 + (index % 2) * 424;
        const y = top + (index > 1 ? 110 : 36);

        ctx.font = `700 15px ${SANS}`;
        ctx.fillStyle = 'rgba(255, 255, 255, 0.42)';
        tracked(ctx, label, x, y, 2.4);

        ctx.font = `600 30px ${SANS}`;
        ctx.fillStyle = '#ffffff';
        ctx.fillText(elide(ctx, value, 380), x, y + 28);
    });

    ctx.strokeStyle = 'rgba(255, 255, 255, 0.07)';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(M + 448, top + 30);
    ctx.lineTo(M + 448, top + height - 30);
    ctx.stroke();
};

const drawSkills = (ctx, top, skills, maxBottom) => {
    ctx.font = `700 15px ${SANS}`;
    ctx.fillStyle = 'rgba(255, 255, 255, 0.40)';
    tracked(ctx, 'KEAHLIAN YANG DICARI', M, top, 2.6);

    const rowHeight = 36;
    const textWidth = W - M * 2 - 30;
    const rows = [];

    for (let y = top + 34; y + rowHeight <= maxBottom && rows.length < skills.length; y += rowHeight) {
        rows.push(y);
    }

    if (!rows.length) {
        return;
    }

    const visible = skills.length > rows.length ? rows.length - 1 : skills.length;

    for (let index = 0; index < visible; index++) {
        const y = rows[index];

        drawDiamond(ctx, M + 6, y + 12, 3.5);

        ctx.font = `500 19px ${SANS}`;
        ctx.fillStyle = 'rgba(244, 240, 230, 0.90)';
        ctx.fillText(elide(ctx, String(skills[index]), textWidth), M + 26, y + 2);

        ctx.strokeStyle = 'rgba(255, 255, 255, 0.07)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(M, y + rowHeight - 4);
        ctx.lineTo(W - M, y + rowHeight - 4);
        ctx.stroke();
    }

    const remaining = skills.length - visible;

    if (remaining > 0) {
        ctx.font = `500 18px ${SANS}`;
        ctx.fillStyle = 'rgba(217, 180, 106, 0.85)';
        ctx.fillText(`+${remaining} keahlian lainnya`, M + 26, rows[visible] + 3);
    }
};

const drawApplyCard = (ctx, top, url) => {
    const height = 208;

    roundRect(ctx, M, top, W - M * 2, height, 26);
    ctx.fillStyle = 'rgba(255, 255, 255, 0.05)';
    ctx.fill();
    ctx.strokeStyle = 'rgba(217, 180, 106, 0.30)';
    ctx.lineWidth = 1.5;
    ctx.stroke();

    ctx.font = `700 17px ${SANS}`;
    ctx.fillStyle = GOLD;
    tracked(ctx, 'LAMAR SEKARANG', M + 40, top + 34, 4);

    const maxWidth = W - M * 2 - 80;
    let size = 30;

    ctx.font = `600 ${size}px ${SANS}`;

    while (size > 20 && ctx.measureText(url).width > maxWidth) {
        size -= 1;
        ctx.font = `600 ${size}px ${SANS}`;
    }

    ctx.fillStyle = '#ffffff';
    const lines = ctx.measureText(url).width > maxWidth ? wrap(ctx, url, maxWidth, 2) : [url];
    let y = top + 72;

    for (const line of lines) {
        ctx.fillText(line, M + 40, y);
        y += size + 10;
    }

    ctx.font = `400 18px ${SANS}`;
    ctx.fillStyle = 'rgba(255, 255, 255, 0.45)';
    ctx.fillText('Tanpa akun  ·  Lamaran 100% online  ·  Diproses tim HRD', M + 40, top + height - 46);
};

export async function renderOpeningPoster(job) {
    await ensureFonts();

    const canvas = document.createElement('canvas');
    canvas.width = W;
    canvas.height = H;

    const ctx = canvas.getContext('2d');
    ctx.textBaseline = 'top';

    paintBackground(ctx);

    const title = String(job.title ?? 'Lowongan').trim();
    const company = String(job.company ?? 'Majesty Group').trim();
    const subline = [job.department, job.position].filter(Boolean).join('  ·  ');
    const url = String(job.url ?? '').replace(/^https?:\/\//, '');

    drawDiamond(ctx, M + 7, 102, 7);
    ctx.font = `700 23px ${SANS}`;
    ctx.fillStyle = GOLD;
    tracked(ctx, 'MAJESTY GROUP', M + 34, 92, 6);

    drawStatusPill(ctx, Boolean(job.isOpen));

    ctx.font = `600 19px ${SANS}`;
    ctx.fillStyle = 'rgba(217, 180, 106, 0.90)';
    tracked(ctx, 'KAMI SEDANG MEREKRUT', M, 220, 5);

    const titleSize = title.length > 52 ? 58 : title.length > 36 ? 68 : title.length > 20 ? 80 : 92;
    const lineHeight = Math.round(titleSize * 1.08);

    ctx.font = `500 ${titleSize}px ${SERIF}`;
    ctx.fillStyle = '#ffffff';

    let cursor = 276;

    for (const line of wrap(ctx, title, W - M * 2, 3)) {
        ctx.fillText(line, M, cursor);
        cursor += lineHeight;
    }

    cursor += 16;
    ctx.fillStyle = 'rgba(217, 180, 106, 0.60)';
    ctx.fillRect(M, cursor, 88, 2);
    cursor += 28;

    ctx.font = `600 30px ${SANS}`;
    ctx.fillStyle = '#ffffff';
    ctx.fillText(elide(ctx, company, W - M * 2), M, cursor);
    cursor += 44;

    if (subline) {
        ctx.font = `400 23px ${SANS}`;
        ctx.fillStyle = 'rgba(255, 255, 255, 0.55)';
        ctx.fillText(elide(ctx, subline, W - M * 2), M, cursor);
        cursor += 40;
    }

    const statsTop = cursor + 28;
    drawStats(ctx, statsTop, job);

    const skills = Array.isArray(job.skills)
        ? job.skills
            .map((skill) => String(skill).replace(/^[\s\-–—•*·]+/, '').trim())
            .filter(Boolean)
        : [];
    const applyTop = H - M - 208;

    if (skills.length) {
        drawSkills(ctx, statsTop + 184 + 36, skills, applyTop - 28);
    }

    drawApplyCard(ctx, applyTop, url);

    return canvas.toDataURL('image/png');
}

export function downloadOpeningPoster(dataUrl, job) {
    const slug = String(job?.slug ?? 'lowongan')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

    const link = document.createElement('a');
    link.href = dataUrl;
    link.download = `lowongan-${slug || 'majesty'}.png`;
    link.click();
}
