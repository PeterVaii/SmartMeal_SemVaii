//Pomáhanie ChatGPT
document.addEventListener('DOMContentLoaded', () => {
    {
        const list = document.getElementById('ingredients-list');
        const template = document.getElementById('ingredient-row-template');
        const addButton = document.getElementById('add-ingredient');

        if (list && template && addButton) {
            addButton.addEventListener('click', function () {
                const newRow = template.content.cloneNode(true);
                list.appendChild(newRow);
            });

            list.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.remove-ingredient');
                if (!removeButton) return;
                const row = removeButton.closest('.ingredient-row');
                if (row) row.remove();
            });

            if (list.children.length === 0) addButton.click();
        }
    }

    {
        const toggles = document.querySelectorAll('.shopping-toggle');

        if (toggles.length) {
            toggles.forEach(function (checkbox) {
                checkbox.addEventListener('change', async function () {

                    const payload = {
                        name: checkbox.dataset.name || '',
                        unit: (checkbox.dataset.unit && checkbox.dataset.unit.trim() !== '') ? checkbox.dataset.unit : null,
                        checked: checkbox.checked ? 1 : 0
                    };

                    try {
                        const response = await fetch('?c=shoppingItem&a=toggle', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();

                        if (!response.ok || data.ok !== true) throw new Error(data.error || 'failed');

                        const itemElement = checkbox.closest('.shopping-item');
                        if (itemElement) itemElement.classList.toggle('checked', checkbox.checked);
                    } catch (error) {
                        checkbox.checked = !checkbox.checked;
                        alert('Nepodarilo sa uložiť. Skús znova.');
                    }
                });
            });
        }
    }

    {
        const input = document.getElementById('recipe-search');
        const list  = document.getElementById('recipes-list');

        if (input && list) {
            let timer = null;

            function escapeHtml(value) {
                const text = String(value ?? '');

                return text.replace(/[&<>"']/g, function (match) {
                    const map = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    };
                    return map[match];
                });
            }

            function render(items) {
                let resultHtml = '';

                items.forEach(function (recipe) {
                    let badgeHtml = '';
                    if (Number(recipe.is_public) === 0) {
                        badgeHtml = '<span class="badge bg-secondary ms-2">súkromný</span>';
                    }

                    let descriptionHtml = '';
                    if (recipe.description) {
                        descriptionHtml =
                            '<div class="text-muted small">' +
                            escapeHtml(recipe.description) +
                            '</div>';
                    }

                    resultHtml +=
                        '<a class="list-group-item list-group-item-action" ' +
                        'href="?c=recipe&a=show&id=' + Number(recipe.id) + '">' +
                        '<div class="fw-semibold">' +
                        escapeHtml(recipe.title) +
                        badgeHtml +
                        '</div>' +
                        descriptionHtml +
                        '</a>';
                });
                list.innerHTML = resultHtml;
            }

            async function fetchResults(query) {
                try {
                    const safeQuery = encodeURIComponent(query);

                    const response = await fetch(
                        `?c=recipe&a=search&q=${safeQuery}`,
                        { headers: { Accept: 'application/json' } }
                    );

                    const data = await response.json();

                    if (!response.ok || !data.ok) return;

                    const items = Array.isArray(data.items) ? data.items : [];

                    render(items);
                } catch (error) {
                }
            }

            function onSearchInput(event) {
                clearTimeout(timer);

                const valueFromInput = event.target.value;
                const trimmedValue = valueFromInput.trim();

                timer = setTimeout(function () {
                    fetchResults(trimmedValue);
                }, 200);
            }
            input.addEventListener('input', onSearchInput);
        }
    }
});