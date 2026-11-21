import QRCode from 'qrcode';
import '@fontsource-variable/fraunces';

const W = 1080;
const H = 1350;
const M = 76;

const GOLD = '#d9b46a';
const SANS = '"Inter Variable", "Inter", ui-sans-serif, system-ui, sans-serif';
const SERIF = '"Fraunces Variable", "Fraunces", Georgia, "Times New Roman", serif';

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

let logoPromise = null;

const ensureLogo = () => {
    logoPromise ??= new Promise((resolve) => {
        if (typeof Image === 'undefined') {
            resolve(null);
            return;
        }

        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => resolve(img);
        img.onerror = () => resolve(null);
        img.src = '/images/majesty-logo.png';
    });

    return logoPromise;
};

let qrBadgePromise = null;

const ensureQrBadge = () => {
    qrBadgePromise ??= new Promise((resolve) => {
        if (typeof Image === 'undefined') {
            resolve(null);
            return;
        }

        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => resolve(img);
        img.onerror = () => resolve(null);
        img.src = '/images/majesty-qr-badge.png';
    });

    return qrBadgePromise;
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

const drawFiligreeBracket = (ctx, x, y, dirX, dirY, len = 32) => {
    ctx.strokeStyle = 'rgba(217, 180, 106, 0.45)';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(x + dirX * len, y);
    ctx.lineTo(x, y);
    ctx.lineTo(x, y + dirY * len);
    ctx.stroke();

    drawDiamond(ctx, x, y, 3);
};

const paintDiamondLattice = (ctx, startY, endY) => {
    ctx.save();
    ctx.beginPath();
    ctx.rect(0, startY, W, endY - startY);
    ctx.clip();

    ctx.strokeStyle = 'rgba(217, 180, 106, 0.04)';
    ctx.lineWidth = 1;

    const spacing = 46;
    ctx.beginPath();
    for (let offset = -H; offset < W + H; offset += spacing) {
        ctx.moveTo(offset, startY);
        ctx.lineTo(offset + (endY - startY), endY);
    }
    for (let offset = -H; offset < W + H; offset += spacing) {
        ctx.moveTo(offset, startY);
        ctx.lineTo(offset - (endY - startY), endY);
    }
    ctx.stroke();
    ctx.restore();
};

const paintDualToneBackground = (ctx, splitY, applyTop = H - M - 214, logoImg = null) => {
    // 1. Base Luminous Alabaster Silk Canvas (Ivory to warm alabaster)
    let bodyGrad = ctx.createLinearGradient(0, splitY, 0, H);
    bodyGrad.addColorStop(0, '#ffffff');
    bodyGrad.addColorStop(0.35, '#fcfaf6');
    bodyGrad.addColorStop(1, '#f7f2e8');
    ctx.fillStyle = bodyGrad;
    ctx.fillRect(0, splitY, W, H - splitY);

    // 2. Top Midnight Navy Header (0 to splitY)
    let navyGrad = ctx.createLinearGradient(0, 0, 0, splitY);
    navyGrad.addColorStop(0, '#060f1c');
    navyGrad.addColorStop(0.65, '#0a192f');
    navyGrad.addColorStop(1, '#0e2343');
    ctx.fillStyle = navyGrad;
    ctx.fillRect(0, 0, W, splitY);

    // Subtle gold ambient glow in top-left
    let topGoldFlare = ctx.createRadialGradient(80, 20, 0, 80, 20, 650);
    topGoldFlare.addColorStop(0, 'rgba(217, 180, 106, 0.16)');
    topGoldFlare.addColorStop(1, 'rgba(217, 180, 106, 0)');
    ctx.fillStyle = topGoldFlare;
    ctx.fillRect(0, 0, W, splitY);

    // Single Champagne Gold Divider Rule between Navy and Body
    ctx.fillStyle = GOLD;
    ctx.fillRect(0, splitY, W, 2);

    // 3. Subtle Luxury Geometric Guilloché Grid (Security / Silk texture)
    paintDiamondLattice(ctx, splitY + 2, H);

    // 4. Warm Radial Gold Ambient Glow in the Center of Body
    const midY = splitY + (applyTop - splitY) / 2;
    let bodyFlare = ctx.createRadialGradient(W / 2, midY, 60, W / 2, midY, 520);
    bodyFlare.addColorStop(0, 'rgba(217, 180, 106, 0.10)');
    bodyFlare.addColorStop(0.6, 'rgba(217, 180, 106, 0.03)');
    bodyFlare.addColorStop(1, 'rgba(217, 180, 106, 0)');
    ctx.fillStyle = bodyFlare;
    ctx.fillRect(0, splitY, W, H - splitY);

    // 5. Regal Watermark of the Majesty Group Crest in the Center of Body
    if (logoImg && logoImg.naturalWidth) {
        ctx.save();
        ctx.globalAlpha = 0.045; // ultra-faint watermark
        const wmW = 480;
        const wmH = Math.round(wmW * (logoImg.naturalHeight / logoImg.naturalWidth));
        const wmX = Math.round((W - wmW) / 2);
        const wmY = Math.round(splitY + 110 + (applyTop - splitY - 110 - wmH) / 2);
        ctx.drawImage(logoImg, wmX, wmY, wmW, wmH);
        ctx.restore();
    }

    // 6. Architectural Filigree Corner Brackets (Luxury Stationery Styling)
    const bMargin = 54;
    const bTop = splitY + 18;
    const bBottom = applyTop - 18;
    drawFiligreeBracket(ctx, bMargin, bTop, 1, 1);
    drawFiligreeBracket(ctx, W - bMargin, bTop, -1, 1);
    drawFiligreeBracket(ctx, bMargin, bBottom, 1, -1);
    drawFiligreeBracket(ctx, W - bMargin, bBottom, -1, -1);

    // 7. Elegant Outer Framing Hairline (around entire poster)
    roundRect(ctx, 36, 36, W - 72, H - 72, 24);
    ctx.strokeStyle = 'rgba(197, 160, 89, 0.35)';
    ctx.lineWidth = 1;
    ctx.stroke();
};

const drawStatusPill = (ctx, isOpen, topY = 66) => {
    const label = isOpen ? 'DIBUKA' : 'DITUTUP';

    ctx.font = `600 13px ${SANS}`;
    const width = trackedWidth(ctx, label, 2.5) + 54;
    const x = W - M - width;
    const y = topY;
    const height = 36;

    roundRect(ctx, x, y, width, height, height / 2);
    ctx.fillStyle = isOpen ? 'rgba(16, 185, 129, 0.15)' : 'rgba(255, 255, 255, 0.08)';
    ctx.fill();
    ctx.strokeStyle = isOpen ? 'rgba(110, 231, 183, 0.45)' : 'rgba(255, 255, 255, 0.20)';
    ctx.lineWidth = 1;
    ctx.stroke();

    ctx.fillStyle = isOpen ? '#6ee7b7' : 'rgba(255, 255, 255, 0.60)';
    ctx.beginPath();
    ctx.arc(x + 18, y + height / 2, 3.5, 0, Math.PI * 2);
    ctx.fill();

    tracked(ctx, label, x + 30, y + 10, 2.5);
};

const drawStats = (ctx, top, job) => {
    const experience = job.experience === null || job.experience === undefined
        ? 'Terbuka'
        : job.experience === 0 ? 'Tanpa syarat' : `${job.experience} Tahun`;

    const items = [
        ['PENDIDIKAN', job.education || 'Terbuka'],
        ['PENGALAMAN', experience],
        ['TIPE PEKERJAAN', job.jobType || 'Full Time'],
        ['LOKASI', job.location || 'Batam'],
    ];

    const totalW = W - M * 2;
    const colW = totalW / 4;

    items.forEach(([label, value], index) => {
        const x = M + index * colW;

        // Label in warm gold
        ctx.font = `700 12px ${SANS}`;
        ctx.fillStyle = '#9a7428';
        tracked(ctx, label, x, top + 4, 1.8);

        // Value in deep navy
        ctx.font = `700 23px ${SANS}`;
        ctx.fillStyle = '#0a192f';
        ctx.fillText(elide(ctx, value, colW - 24), x, top + 26);

        // Vertical hairline between items
        if (index < 3) {
            ctx.strokeStyle = 'rgba(10, 25, 47, 0.08)';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(x + colW - 12, top + 4);
            ctx.lineTo(x + colW - 12, top + 52);
            ctx.stroke();
        }
    });

    // Horizontal hairline below specs
    ctx.strokeStyle = 'rgba(197, 160, 89, 0.28)';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(M, top + 72);
    ctx.lineTo(W - M, top + 72);
    ctx.stroke();
};

const cleanDescriptionItems = (text) => {
    if (!text) {
        return [];
    }

    const rawLines = String(text)
        .replace(/\r\n/g, '\n')
        .split('\n')
        .map((line) => line.trim())
        .filter(Boolean);

    const items = [];

    for (const raw of rawLines) {
        const cleaned = raw.replace(/^([-*•·–—]|\d+[.)])\s*/u, '').trim();

        if (cleaned) {
            items.push(cleaned);
        }
    }

    if (items.length === 1 && items[0].length > 100) {
        const sentences = items[0].split(/(?<=\.)\s+(?=[A-Z0-9])/).map((s) => s.trim()).filter(Boolean);
        if (sentences.length >= 2) {
            return sentences;
        }
    }

    return items;
};

const drawJobDetails = (ctx, top, descriptionText, skills, maxBottom) => {
    const descItems = cleanDescriptionItems(descriptionText);
    const validSkills = Array.isArray(skills) ? skills.filter(Boolean) : [];

    const hasDesc = descItems.length > 0;
    const hasSkills = validSkills.length > 0;

    if (!hasDesc && !hasSkills) {
        ctx.font = `500 16px ${SANS}`;
        ctx.fillStyle = '#64748b';
        ctx.fillText('Informasi detail kualifikasi dan tanggung jawab pekerjaan dapat diakses langsung', M, top + 20);
        ctx.fillText('melalui portal resmi Majesty Group Karir dengan memindai barcode di bawah.', M, top + 46);
        return;
    }

    // SCENARIO 1: Both Description & Skills are present -> 2 Columns Split
    if (hasDesc && hasSkills) {
        const col1X = M;
        const col1W = 510;

        drawDiamond(ctx, col1X + 6, top + 10, 3.5);
        ctx.font = `700 15px ${SANS}`;
        ctx.fillStyle = '#0c1e38';
        tracked(ctx, 'DESKRIPSI & TANGGUNG JAWAB', col1X + 22, top, 2.4);

        const divX = col1X + col1W + 20;
        ctx.strokeStyle = '#e2e8f0';
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.moveTo(divX, top);
        ctx.lineTo(divX, maxBottom - 4);
        ctx.stroke();

        const col2X = divX + 22;
        const col2W = (W - M) - col2X;

        drawDiamond(ctx, col2X + 6, top + 10, 3.5);
        ctx.font = `700 15px ${SANS}`;
        ctx.fillStyle = '#0c1e38';
        tracked(ctx, 'KEAHLIAN YANG DICARI', col2X + 22, top, 2.4);

        let descCursorY = top + 34;

        if (descItems.length > 1) {
            for (let index = 0; index < descItems.length; index++) {
                const item = descItems[index];
                const wrapped = wrap(ctx, item, col1W - 28, 2);
                const itemHeight = wrapped.length * 24;

                if (descCursorY + itemHeight > maxBottom) {
                    const remaining = descItems.length - index;

                    if (remaining > 0) {
                        ctx.font = `600 15px ${SANS}`;
                        ctx.fillStyle = '#9a7428';
                        ctx.fillText(`+${remaining} tanggung jawab lainnya`, col1X + 22, descCursorY);
                    }

                    break;
                }

                drawDiamond(ctx, col1X + 6, descCursorY + 9, 3);
                ctx.font = `400 16px ${SANS}`;
                ctx.fillStyle = '#1e293b';

                for (const line of wrapped) {
                    ctx.fillText(line, col1X + 22, descCursorY);
                    descCursorY += 24;
                }

                descCursorY += 6;
            }
        } else {
            const maxLines = Math.floor((maxBottom - descCursorY) / 25);
            const wrapped = wrap(ctx, descItems[0], col1W - 28, Math.max(1, maxLines));
            ctx.fillStyle = '#1e293b';
            ctx.font = `400 16px ${SANS}`;

            for (const line of wrapped) {
                ctx.fillText(line, col1X + 6, descCursorY);
                descCursorY += 25;
            }
        }

        let skillCursorY = top + 34;
        const rowHeight = 36;

        for (let index = 0; index < validSkills.length; index++) {
            if (skillCursorY + rowHeight > maxBottom) {
                const remaining = validSkills.length - index;

                if (remaining > 0) {
                    ctx.font = `600 15px ${SANS}`;
                    ctx.fillStyle = '#9a7428';
                    ctx.fillText(`+${remaining} keahlian lainnya`, col2X + 22, skillCursorY + 4);
                }

                break;
            }

            drawDiamond(ctx, col2X + 6, skillCursorY + 12, 3.5);

            ctx.font = `600 16px ${SANS}`;
            ctx.fillStyle = '#0c1e38';
            ctx.fillText(elide(ctx, String(validSkills[index]), col2W - 28), col2X + 22, skillCursorY + 2);

            ctx.strokeStyle = '#f1f5f9';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(col2X, skillCursorY + rowHeight - 4);
            ctx.lineTo(W - M, skillCursorY + rowHeight - 4);
            ctx.stroke();

            skillCursorY += rowHeight;
        }

        return;
    }

    // SCENARIO 2: Only Description (Full Width)
    if (hasDesc && !hasSkills) {
        drawDiamond(ctx, M + 6, top + 10, 3.5);
        ctx.font = `700 15px ${SANS}`;
        ctx.fillStyle = '#0c1e38';
        tracked(ctx, 'DESKRIPSI & TANGGUNG JAWAB', M + 22, top, 2.4);

        let descCursorY = top + 34;
        const fullW = W - M * 2;

        if (descItems.length > 1) {
            for (let index = 0; index < descItems.length; index++) {
                const item = descItems[index];
                const wrapped = wrap(ctx, item, fullW - 28, 2);
                const itemHeight = wrapped.length * 24;

                if (descCursorY + itemHeight > maxBottom) {
                    const remaining = descItems.length - index;

                    if (remaining > 0) {
                        ctx.font = `600 15px ${SANS}`;
                        ctx.fillStyle = '#9a7428';
                        ctx.fillText(`+${remaining} tanggung jawab lainnya`, M + 24, descCursorY);
                    }

                    break;
                }

                drawDiamond(ctx, M + 6, descCursorY + 9, 3);
                ctx.font = `400 16.5px ${SANS}`;
                ctx.fillStyle = '#1e293b';

                for (const line of wrapped) {
                    ctx.fillText(line, M + 24, descCursorY);
                    descCursorY += 24;
                }

                descCursorY += 8;
            }
        } else {
            const maxLines = Math.floor((maxBottom - descCursorY) / 26);
            const wrapped = wrap(ctx, descItems[0], fullW, Math.max(1, maxLines));
            ctx.fillStyle = '#1e293b';
            ctx.font = `400 16.5px ${SANS}`;

            for (const line of wrapped) {
                ctx.fillText(line, M, descCursorY);
                descCursorY += 26;
            }
        }

        return;
    }

    // SCENARIO 3: Only Skills (Full Width)
    if (!hasDesc && hasSkills) {
        drawDiamond(ctx, M + 6, top + 10, 3.5);
        ctx.font = `700 15px ${SANS}`;
        ctx.fillStyle = '#0c1e38';
        tracked(ctx, 'KEAHLIAN YANG DICARI', M + 22, top, 2.4);

        const rowHeight = 36;
        const textWidth = W - M * 2 - 30;
        let skillCursorY = top + 34;

        for (let index = 0; index < validSkills.length; index++) {
            if (skillCursorY + rowHeight > maxBottom) {
                const remaining = validSkills.length - index;

                if (remaining > 0) {
                    ctx.font = `600 16px ${SANS}`;
                    ctx.fillStyle = '#9a7428';
                    ctx.fillText(`+${remaining} keahlian lainnya`, M + 26, skillCursorY + 3);
                }

                break;
            }

            drawDiamond(ctx, M + 6, skillCursorY + 12, 3.5);

            ctx.font = `600 17px ${SANS}`;
            ctx.fillStyle = '#0c1e38';
            ctx.fillText(elide(ctx, String(validSkills[index]), textWidth), M + 26, skillCursorY + 2);

            ctx.strokeStyle = '#f1f5f9';
            ctx.lineWidth = 1;
            ctx.beginPath();
            ctx.moveTo(M, skillCursorY + rowHeight - 4);
            ctx.lineTo(W - M, skillCursorY + rowHeight - 4);
            ctx.stroke();

            skillCursorY += rowHeight;
        }
    }
};

const drawApplyCard = (ctx, top, displayUrl, qrTargetUrl, logoImg, qrBadgeImg) => {
    const height = 214;
    const cardWidth = W - M * 2;

    // 1. Container Card: Deep Midnight Navy
    roundRect(ctx, M, top, cardWidth, height, 24);
    let cardGrad = ctx.createLinearGradient(M, top, M + cardWidth, top + height);
    cardGrad.addColorStop(0, '#060f1e');
    cardGrad.addColorStop(1, '#0e2343');
    ctx.fillStyle = cardGrad;
    ctx.fill();

    // Elegant Champagne Gold Hairline Stroke
    ctx.strokeStyle = 'rgba(217, 180, 106, 0.40)';
    ctx.lineWidth = 1.5;
    ctx.stroke();

    // 2. Right-side QR Badge Card: Solid Porcelain White
    const badgeW = 176;
    const badgeH = 186;
    const badgeX = M + cardWidth - badgeW - 14;
    const badgeY = top + Math.round((height - badgeH) / 2);

    roundRect(ctx, badgeX, badgeY, badgeW, badgeH, 16);
    ctx.fillStyle = '#ffffff';
    ctx.fill();
    ctx.strokeStyle = 'rgba(217, 180, 106, 0.50)';
    ctx.lineWidth = 1.5;
    ctx.stroke();

    // Draw QR Code inside badge
    if (qrTargetUrl) {
        try {
            const qr = QRCode.create(qrTargetUrl, { errorCorrectionLevel: 'H' });
            const count = qr.modules.size;
            const qrBoxSize = 136;
            const cellSize = qrBoxSize / count;
            const qrStartX = badgeX + Math.round((badgeW - qrBoxSize) / 2);
            const qrStartY = badgeY + 14;

            ctx.fillStyle = '#081426'; // Deep navy for high-contrast scanning
            for (let r = 0; r < count; r++) {
                for (let c = 0; c < count; c++) {
                    if (qr.modules.get(r, c)) {
                        const cellX = qrStartX + Math.round(c * cellSize);
                        const cellY = qrStartY + Math.round(r * cellSize);
                        const cellW = Math.ceil(cellSize);
                        const cellH = Math.ceil(cellSize);
                        ctx.fillRect(cellX, cellY, cellW, cellH);
                    }
                }
            }

            // Draw Majesty Group Official Logo Badge in the center of QR code (ultra-sharp)
            const emblemW = 76;
            const emblemH = 46;
            const emblemX = qrStartX + Math.round((qrBoxSize - emblemW) / 2);
            const emblemY = qrStartY + Math.round((qrBoxSize - emblemH) / 2);

            if (qrBadgeImg && qrBadgeImg.naturalWidth) {
                ctx.drawImage(qrBadgeImg, emblemX, emblemY, emblemW, emblemH);
            } else {
                // High-precision vector fallback if badge image not yet loaded
                const bufSizeW = emblemW + 6;
                const bufSizeH = emblemH + 6;
                roundRect(ctx, emblemX - 3, emblemY - 3, bufSizeW, bufSizeH, 8);
                ctx.fillStyle = '#ffffff';
                ctx.fill();

                roundRect(ctx, emblemX, emblemY, emblemW, emblemH, 7);
                ctx.fillStyle = '#071324';
                ctx.fill();
                ctx.strokeStyle = GOLD;
                ctx.lineWidth = 1.5;
                ctx.stroke();

                ctx.font = `700 12.5px ${SERIF}`;
                ctx.fillStyle = '#ffffff';
                ctx.textBaseline = 'top';
                const mText = 'MAJESTY';
                const mw = ctx.measureText(mText).width;
                ctx.fillText(mText, emblemX + Math.round((emblemW - mw) / 2), emblemY + 11);

                ctx.font = `700 7.5px ${SANS}`;
                ctx.fillStyle = GOLD;
                const gText = 'GROUP';
                const gw = ctx.measureText(gText).width;
                const gx = emblemX + Math.round((emblemW - gw) / 2);
                const gy = emblemY + 27;
                ctx.fillText(gText, gx, gy);

                ctx.strokeStyle = 'rgba(217, 180, 106, 0.75)';
                ctx.lineWidth = 1;
                ctx.beginPath();
                ctx.moveTo(emblemX + 8, gy + 4);
                ctx.lineTo(gx - 4, gy + 4);
                ctx.moveTo(gx + gw + 4, gy + 4);
                ctx.lineTo(emblemX + emblemW - 8, gy + 4);
                ctx.stroke();
            }

            // QR Badge Label
            ctx.font = `700 10.5px ${SANS}`;
            ctx.fillStyle = '#081426';
            const labelText = 'SCAN UNTUK MELAMAR';
            const labelWidth = ctx.measureText(labelText).width;
            ctx.fillText(labelText, badgeX + Math.round((badgeW - labelWidth) / 2), badgeY + 160);
        } catch {
            // Fallback gracefully if QR creation encounters an issue
        }
    }

    // 3. Left Text Section inside Apply Card
    const textStartX = M + 36;
    const maxWidth = badgeX - textStartX - 28;

    ctx.font = `700 14px ${SANS}`;
    ctx.fillStyle = GOLD;
    tracked(ctx, 'LAMAR SEKARANG', textStartX, top + 36, 3.5);

    let size = 26;
    ctx.font = `600 ${size}px ${SANS}`;

    while (size > 18 && ctx.measureText(displayUrl).width > maxWidth) {
        size -= 1;
        ctx.font = `600 ${size}px ${SANS}`;
    }

    ctx.fillStyle = '#ffffff';
    const lines = ctx.measureText(displayUrl).width > maxWidth ? wrap(ctx, displayUrl, maxWidth, 2) : [displayUrl];
    let y = top + 66;

    for (const line of lines) {
        ctx.fillText(line, textStartX, y);
        y += size + 6;
    }

    ctx.font = `500 15px ${SANS}`;
    ctx.fillStyle = '#f5e4ba';
    ctx.fillText('Pindai barcode di samping untuk melamar langsung', textStartX, top + 120);

    ctx.font = `400 14px ${SANS}`;
    ctx.fillStyle = 'rgba(255, 255, 255, 0.50)';
    ctx.fillText('Tanpa akun  ·  Lamaran 100% online  ·  Diproses tim HRD', textStartX, top + height - 36);
};

export async function renderOpeningPoster(job) {
    const [logoImg, qrBadgeImg] = await Promise.all([
        ensureLogo(),
        ensureQrBadge(),
        ensureFonts(),
    ]);

    const canvas = document.createElement('canvas');
    canvas.width = W;
    canvas.height = H;

    const ctx = canvas.getContext('2d');
    ctx.textBaseline = 'top';

    const title = String(job.title ?? 'Lowongan').trim();
    const company = String(job.company ?? 'Majesty Group').trim();
    const subline = [job.department, job.position].filter(Boolean).join('  ·  ');

    let qrTargetUrl = 'https://www.majestygroupcareers.cloud';
    if (job.url && !job.url.includes('localhost') && !job.url.includes('127.0.0.1')) {
        qrTargetUrl = job.url;
    } else if (job.slug) {
        qrTargetUrl = `https://www.majestygroupcareers.cloud/karir/${job.slug}`;
    }

    const displayUrl = (job.url && !job.url.includes('localhost') && !job.url.includes('127.0.0.1'))
        ? job.url.replace(/^https?:\/\//, '')
        : (job.slug ? `www.majestygroupcareers.cloud/karir/${job.slug}` : 'www.majestygroupcareers.cloud');

    // 1. Calculate Title Lines & Split Coordinate
    const titleSize = title.length > 50 ? 56 : title.length > 34 ? 66 : title.length > 20 ? 76 : 84;
    const lineHeight = Math.round(titleSize * 1.08);

    ctx.font = `500 ${titleSize}px ${SERIF}`;
    const titleLines = wrap(ctx, title, W - M * 2, 2);

    let headerCursor = 130;
    headerCursor += titleLines.length * lineHeight;
    headerCursor += 14;
    const companyLineY = headerCursor;
    headerCursor += 32;

    const splitY = headerCursor + 22;
    const applyTop = H - M - 214;

    // 2. Paint Dual-Tone Background (Top Navy + Silk Alabaster Luxury Body)
    paintDualToneBackground(ctx, splitY, applyTop, logoImg);

    // 3. Render Top Midnight Navy Content
    // A. Logo (Top-Left)
    if (logoImg && logoImg.naturalWidth) {
        const targetH = 46;
        const targetW = Math.round(targetH * (logoImg.naturalWidth / logoImg.naturalHeight));
        ctx.drawImage(logoImg, M, 50, targetW, targetH);
    } else {
        drawDiamond(ctx, M + 7, 70, 7);
        ctx.font = `700 22px ${SANS}`;
        ctx.fillStyle = GOLD;
        tracked(ctx, 'MAJESTY GROUP', M + 34, 60, 6);
    }

    // B. Status Pill (Top-Right)
    drawStatusPill(ctx, Boolean(job.isOpen), 52);

    // C. Job Title
    ctx.font = `500 ${titleSize}px ${SERIF}`;
    ctx.fillStyle = '#ffffff';
    let yTitle = 130;
    for (const line of titleLines) {
        ctx.fillText(line, M, yTitle);
        yTitle += lineHeight;
    }

    // E. Company & Department Subline (with Diamond Icon)
    drawDiamond(ctx, M + 6, companyLineY + 11, 4.5);
    ctx.font = `700 22px ${SANS}`;
    ctx.fillStyle = GOLD;
    ctx.fillText(company, M + 24, companyLineY);

    if (subline) {
        const compWidth = ctx.measureText(company).width;
        const maxSublineW = (W - M) - (M + 24 + compWidth);
        if (maxSublineW > 60) {
            ctx.font = `400 19px ${SANS}`;
            ctx.fillStyle = 'rgba(255, 255, 255, 0.70)';
            ctx.fillText(elide(ctx, `  ·  ${subline}`, maxSublineW), M + 24 + compWidth, companyLineY + 2);
        }
    }

    // 4. White Body Content
    const statsTop = splitY + 24;
    drawStats(ctx, statsTop, job);

    const description = job.description ? String(job.description).trim() : '';
    const skills = Array.isArray(job.skills)
        ? job.skills
            .map((skill) => String(skill).replace(/^[\s\-–—•*·]+/, '').trim())
            .filter(Boolean)
        : [];

    const contentTop = statsTop + 92 + 30;

    drawJobDetails(ctx, contentTop, description, skills, applyTop - 28);

    // 5. Bottom Deep Navy Apply Card
    drawApplyCard(ctx, applyTop, displayUrl, qrTargetUrl, logoImg, qrBadgeImg);

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
