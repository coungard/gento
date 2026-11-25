/**
 * Минимальный набор скриптов витрины: мобильное меню и подсказки в формах.
 * Сознательно без сборщика состояний — страница работает и без JS.
 */
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-nav-toggle]');
    const panel = document.getElementById('mobile-nav');

    if (toggle && panel) {
        toggle.addEventListener('click', () => {
            const isHidden = panel.classList.toggle('hidden');

            toggle.setAttribute('aria-expanded', String(!isHidden));
            panel.setAttribute('aria-hidden', String(isHidden));
        });
    }

    document.querySelectorAll('[data-auto-submit]').forEach((field) => {
        field.addEventListener('change', () => {
            field.form?.requestSubmit();
        });
    });
});
