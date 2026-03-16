(function () {
    const templateIcon = document.querySelector("template#iconTpl");
    const templateClose = document.querySelector("template#closeTpl");
    const templateConfirmTitle = document.querySelector("template#deleteConfirmTitleTpl");
    const templateConfirmContent = document.querySelector("template#deleteConfirmContentTpl");

    const customizeSelected = (selected) => {
        if (selected.classList.contains("hideRemove")) {
            return;
        }

        const remove = document.createElement("a");
        remove.classList.add("remove");
        remove.href = "#";
        remove.innerHTML = templateIcon.innerHTML.replaceAll("IDENT", "closeCircle");

        selected.classList.add("hideRemove");
        selected.appendChild(remove);
    };

    document.body.addEventListener("nyroSelectSelectedCreated", (e) => {
        customizeSelected(e.detail);
    });

    document.querySelectorAll("nyro-select-selected").forEach(customizeSelected);

    window.confirmDialog = (options) => {
        const dialog = document.createElement("nyro-dialog");

        dialog.appendChild(templateClose.content.cloneNode(true));

        const title = templateConfirmTitle.content.cloneNode(true);
        const content = templateConfirmContent.content.cloneNode(true);

        if (options.confirmText) {
            title.querySelector("p").innerHTML = options.confirmText;
        }
        if (options.confirmBtnText) {
            content.querySelector(".confirm").innerHTML = options.confirmBtnText;
        }

        if (options.clb) {
            content.querySelector(".actions").addEventListener("click", (e) => {
                const confirm = e.target.closest(".confirm");
                if (!confirm) {
                    return;
                }
                e.preventDefault();
                options.clb();
            });
        }

        dialog.appendChild(title);
        dialog.appendChild(content);
        dialog.classList.add("nyroDialogConfirm");
        document.body.appendChild(dialog);
        dialog.open();

        return dialog;
    };

    document.body.addEventListener("click", function (e) {
        const dialogLink = e.target.closest(".dialogLink");
        if (dialogLink) {
            e.preventDefault();
            const dialog = document.createElement("nyro-dialog");

            dialog.appendChild(templateClose.content.cloneNode(true));

            dialog.loadUrl(dialogLink.href);

            document.body.appendChild(dialog);
            dialog.open();
            return;
        }

        const deleteConfirm = e.target.closest(".delete, .confirmLink");
        if (deleteConfirm) {
            e.preventDefault();
            const confirmText = deleteConfirm.classList.contains("confirmLink") ? deleteConfirm.dataset.confirmtxt : deleteConfirm.dataset.deletetxt;
            const confirmBtnText = deleteConfirm.dataset.confirmbtntxt;

            window.confirmDialog({
                confirmText: confirmText,
                confirmBtnText: confirmBtnText,
                clb: () => {
                    document.location.href = deleteConfirm.href;
                },
            });
        }
    });

    window.startEndFields = (startInput, endInput) => {
        startInput.addEventListener("change", (e) => {
            if (startInput.type === "date") {
                const startDate = new Date(e.target.value);
                if (startDate && !isNaN(startDate.getTime())) {
                    // Set end date to the same date by default
                    endInput.min = startDate.toISOString().split("T")[0];
                }
                return;
            }

            endInput.min = startInput.value;
        });

        endInput.addEventListener("change", (e) => {
            if (endInput.type === "date") {
                const endDate = new Date(e.target.value);
                if (endDate && !isNaN(endDate.getTime())) {
                    startInput.max = endDate.toISOString().split("T")[0];
                }
            }
            startInput.max = endInput.value;
        });
    };

    document.querySelectorAll(".filterFormRange").forEach((filterFormRange) => {
        window.startEndFields(filterFormRange.querySelector('input[name*="[start]"'), filterFormRange.querySelector('input[name*="[end]"'));
    });

    window.addEventListener("beforeprint", () => {
        document.querySelectorAll("nyro-tabs").forEach((tabs) => {
            tabs.beforeprint();
        });
    });

    window.addEventListener("afterprint", () => {
        document.querySelectorAll("nyro-tabs").forEach((tabs) => {
            tabs.afterprint();
        });
    });
})();
