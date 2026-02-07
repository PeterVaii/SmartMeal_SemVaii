document.addEventListener('DOMContentLoaded', () => {
    const list = document.getElementById('ingredients-list');
    const tpl  = document.getElementById('ingredient-row-template');
    const add  = document.getElementById('add-ingredient');

    if (!list || !tpl || !add) return;

    add.addEventListener('click', () => {
        list.appendChild(tpl.content.cloneNode(true));
    });

    list.addEventListener('click', (e) => {
        if (!e.target.closest('.remove-ingredient')) return;
        const row = e.target.closest('.ingredient-row');
        if (row) row.remove();
    });

    if (list.children.length === 0) {
        add.click();
    }
});