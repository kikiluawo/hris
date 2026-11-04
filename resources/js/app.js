import './bootstrap';
import '@fontsource-variable/plus-jakarta-sans';
import $ from 'jquery';
import select2 from 'select2';
import Alpine from 'alpinejs';

select2(window, $);
window.$ = window.jQuery = $;

const systemPrefersDark = () => window.matchMedia('(prefers-color-scheme: dark)').matches;

const select2Language = {
    noResults: () => 'Tidak ada hasil',
    searching: () => 'Cari…',
};

const select2Timers = new WeakMap();

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
});

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
    if ((localStorage.getItem('theme') ?? 'system') === 'system') {
        document.documentElement.classList.toggle('dark', event.matches);
    }
});

window.Alpine = Alpine;

Alpine.start();
