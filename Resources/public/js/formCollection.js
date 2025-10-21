(function () {
    let divTpl, addToCollection, addDeleteLink;

    const prototypeSelector = "[data-prototype][data-allow-add]",
        dataPrototypeds = document.querySelectorAll(prototypeSelector),
        initDataPrototypeds = () => {
            if (addToCollection) {
                return;
            }
            divTpl = document.createElement("div");
            addToCollection = (dataPrototyped, divAdd, value) => {
                const prototypeName = dataPrototyped.dataset.prototypeName || "__name__";
                divTpl.innerHTML = dataPrototyped.dataset.prototype.replace(new RegExp(prototypeName, "g"), dataPrototyped.dataset.index);

                while (divTpl.lastElementChild) {
                    if (divTpl.lastElementChild.matches(".form_row_collection_entry")) {
                        if (value) {
                            divTpl.lastElementChild.querySelector("input, textarea").value = value;
                        }
                        addDeleteLink(dataPrototyped, divTpl.lastElementChild);
                    }
                    divAdd.insertAdjacentElement("beforebegin", divTpl.lastElementChild);
                }

                const entryAdded = divAdd.previousElementSibling,
                    dataPrototypeds = entryAdded.querySelectorAll(prototypeSelector);

                if (dataPrototypeds.length) {
                    dataPrototypeds.forEach((dataPrototyped) => {
                        initDataPrototyped(dataPrototyped);
                    });
                }

                dataPrototyped.dataset.index++;
                dataPrototyped.dispatchEvent(
                    new CustomEvent("formCollectionAdd", {
                        bubbles: true,
                        cancelable: true,
                        detail: entryAdded,
                    })
                );
            };
            addDeleteLink = (dataPrototyped, collectionEntry) => {
                if (!dataPrototyped.dataset.allowDelete) {
                    return;
                }

                const spanDel = document.createElement("span");
                spanDel.classList.add("deleteFromCollectionCont");
                if (dataPrototyped.dataset.tplDelete) {
                    spanDel.innerHTML = dataPrototyped.dataset.tplDelete;
                } else {
                    spanDel.innerHTML = '<a href="#" class="btn deleteFromCollection">' + dataPrototyped.dataset.allowDelete + "</a>";
                }

                collectionEntry.appendChild(spanDel);
            };
        },
        initDataPrototyped = (dataPrototyped) => {
            initDataPrototypeds();

            if (dataPrototyped.dataset.formCollectionInited) {
                return;
            }

            dataPrototyped.dataset.formCollectionInited = true;

            const divAdd = document.createElement("div"),
                entries = dataPrototyped.querySelectorAll(":scope > .form_row_collection_entry"),
                allowDelete = dataPrototyped.dataset.allowDelete;

            dataPrototyped.dataset.index = entries.length;
            if (allowDelete && entries.length) {
                entries.forEach((entry) => {
                    addDeleteLink(dataPrototyped, entry);
                });
            }

            dataPrototyped.addEventListener("click", (e) => {
                const addBtn = e.target.closest(".addToCollection");
                if (addBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    // Add new element
                    addToCollection(dataPrototyped, divAdd);
                    return;
                }

                const delBtn = e.target.closest(".deleteFromCollection");
                if (delBtn && allowDelete) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (dataPrototyped.dataset.deleteConfirm && !confirm(dataPrototyped.dataset.deleteConfirm)) {
                        return;
                    }
                    delBtn.closest(".form_row_collection_entry").remove();
                    dataPrototyped.dispatchEvent(
                        new CustomEvent("formCollectionDelete", {
                            bubbles: true,
                            cancelable: true,
                        })
                    );
                    return;
                }
            });

            divAdd.classList.add("addToCollectionCont");
            if (dataPrototyped.dataset.tplAdd) {
                divAdd.innerHTML = dataPrototyped.dataset.tplAdd;
            } else {
                divAdd.innerHTML = '<a href="#" class="btn addToCollection">' + dataPrototyped.dataset.allowAdd + "</a>";
            }

            dataPrototyped.appendChild(divAdd);

            if (entries.length === 0 && dataPrototyped.dataset.addOnInit) {
                addToCollection(dataPrototyped, divAdd);
            }

            dataPrototyped.addEventListener("formCollectionSetValue", (e) => {
                dataPrototyped.querySelectorAll(".form_row_collection_entry").forEach((entry) => {
                    entry.remove();
                });
                dataPrototyped.dispatchEvent(
                    new CustomEvent("formCollectionDelete", {
                        bubbles: true,
                        cancelable: true,
                    })
                );
                if (e.detail && Array.isArray(e.detail)) {
                    e.detail.forEach((detail) => {
                        addToCollection(dataPrototyped, divAdd, detail);
                    });
                }
            });
        };

    if (dataPrototypeds.length) {
        dataPrototypeds.forEach((dataPrototyped) => {
            initDataPrototyped(dataPrototyped);
        });
    }

    document.body.addEventListener("formCollectionInitDataPrototyped", (e) => {
        initDataPrototyped(e.target);
    });

    document.body.addEventListener("formCollectionSearchDataPrototyped", (e) => {
        const dataPrototypeds = e.target.querySelectorAll("[data-prototype][data-allow-add]");

        if (dataPrototypeds.length) {
            dataPrototypeds.forEach((dataPrototyped) => {
                initDataPrototyped(dataPrototyped);
            });
        }
    });
})();
