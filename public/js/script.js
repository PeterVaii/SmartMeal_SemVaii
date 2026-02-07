document.addEventListener('DOMContentLoaded', () => {
    {
        const list = document.getElementById('ingredients-list');
        const tpl  = document.getElementById('ingredient-row-template');
        const add  = document.getElementById('add-ingredient');

        // ak nie sme na stránke receptu, preskoč
        if (list && tpl && add) {
            add.addEventListener('click', () => {
                list.appendChild(tpl.content.cloneNode(true));
            });

            list.addEventListener('click', (e) => {
                if (!e.target.closest('.remove-ingredient')) return;
                e.target.closest('.ingredient-row')?.remove();
            });

            // ak je prázdne, pridaj 1 riadok (typicky create view)
            if (list.children.length === 0) {
                add.click();
            }
        }
    }

    {
        const toggles = document.querySelectorAll('.shopping-toggle');
        if (!toggles.length) return;

        toggles.forEach(cb => {
            cb.addEventListener('change', async () => {
                const payload = {
                    name: cb.dataset.name || '',
                    unit: (cb.dataset.unit && cb.dataset.unit.trim() !== '') ? cb.dataset.unit : null,
                    checked: cb.checked ? 1 : 0
                };

                try {
                    const res = await fetch('?c=shoppingItem&a=toggle', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await res.json();
                    if (!res.ok || !data.ok) throw new Error(data.error || 'failed');

                    cb.closest('.shopping-item')?.classList.toggle('checked', cb.checked);
                } catch (e) {
                    cb.checked = !cb.checked;
                    alert('Nepodarilo sa uložiť. Skús znova.');
                }
            });
        });
    }
});