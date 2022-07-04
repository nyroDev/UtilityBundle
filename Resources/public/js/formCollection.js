(function () {
    const dataPrototypeds = document.querySelectorAll('[data-prototype][data-allow-add]');
    if (dataPrototypeds.length) {
        const divTpl = document.createElement('div'),
            addDeleteLink = (dataPrototyped, collectionEntry) => {
                if (!dataPrototyped.dataset.allowDelete) {
                    return;
                }

                const spanDel = document.createElement('span')
                spanDel.classList.add('deleteFromCollectionCont');
                if (dataPrototyped.dataset.tplAdd) {
                    spanDel.innerHTML = dataPrototyped.dataset.tplAdd;
                } else {
                    spanDel.innerHTML = '<a href="#" class="btn deleteFromCollection">' + dataPrototyped.dataset.allowDelete + '</a>';
                }

                collectionEntry.appendChild(spanDel);
            },
            addToCollection = (dataPrototyped, divAdd) => {
                divTpl.innerHTML = dataPrototyped.dataset.prototype
                    .replace(/__name__/g, dataPrototyped.dataset.index);

                while (divTpl.lastElementChild) {
                    if (divTpl.lastElementChild.matches('.form_row_collection_entry')) {
                        addDeleteLink(dataPrototyped, divTpl.lastElementChild);
                    }
                    divAdd.insertAdjacentElement('beforebegin', divTpl.lastElementChild);
                }

                dataPrototyped.dataset.index++;
            };
        dataPrototypeds.forEach(dataPrototyped => {
            const divAdd = document.createElement('div'),
                entries = dataPrototyped.querySelectorAll('.form_row_collection_entry'),
                allowDelete = dataPrototyped.dataset.allowDelete;

            dataPrototyped.dataset.index = entries.length;
            if (allowDelete && entries.length) {
                entries.forEach(entry => {
                    addDeleteLink(dataPrototyped, entry);
                });
            }

            dataPrototyped.addEventListener('click', (e) => {
                const addBtn = e.target.closest('.addToCollection');
                if (addBtn) {
                    e.preventDefault();
                    // Add new element
                    addToCollection(dataPrototyped, divAdd);
                    return;
                }

                const delBtn = e.target.closest('.deleteFromCollection');
                if (delBtn && allowDelete) {
                    e.preventDefault();
                    if (dataPrototyped.dataset.deleteConfirm && !confirm(dataPrototyped.dataset.deleteConfirm)) {
                        return;
                    }
                    delBtn.closest('.form_row_collection_entry').remove();
                    return;
                }
            });

            divAdd.classList.add('addToCollectionCont');
            if (dataPrototyped.dataset.tplAdd) {
                divAdd.innerHTML = dataPrototyped.dataset.tplAdd;
            } else {
                divAdd.innerHTML = '<a href="#" class="btn addToCollection">' + dataPrototyped.dataset.allowAdd + '</a>';
            }

            dataPrototyped.appendChild(divAdd);
        });
    }
})();