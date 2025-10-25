import NyroUpload from "./nyro-upload";

(function () {
    const fetchOptions = (options = {}) => {
        return Object.assign(
            {
                method: "GET",
                mode: "cors",
                credentials: "same-origin",
                cache: "no-cache",
                redirect: "follow",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-JS-FETCH": 1,
                },
            },
            options
        );
    };

    const templatePopin = document.createElement("template");

    templatePopin.innerHTML = `
    <dialog class="dialog">
        <a href="#" class="close closeDialog"><svg class="icon icon-close"><use href="#close"></use></svg></a>
        <div class="dialogIn"></div>
    </dialog>
    `;

    const getMediaData = (chooseMedia) => {
        const data = Object.assign({}, chooseMedia.dataset);
        data.url = chooseMedia.getAttribute("href");
        data.fullUrl = chooseMedia.href;

        return data;
    };

    document.body.addEventListener("click", (e) => {
        const chooseMedia = e.target.closest(".chooseMedia");
        if (chooseMedia) {
            e.preventDefault();

            window.parent.postMessage(
                {
                    mceAction: "customAction",
                    data: getMediaData(chooseMedia),
                },
                "*"
            );

            return;
        }

        const popin = e.target.closest(".popin");
        if (!popin) {
            return;
        }

        e.preventDefault();

        document.body.append(templatePopin.content.cloneNode(true));

        const dialog = document.body.children[document.body.children.length - 1],
            dialogIn = dialog.querySelector(".dialogIn"),
            close = dialog.querySelector(".closeDialog");

        dialog.addEventListener("close", () => {
            if (dialog.querySelector(".reloadPage")) {
                document.location.reload();
            }
            dialog.remove();
        });

        dialog.addEventListener("click", (e) => {
            const closeDialog = e.target.closest(".cancel, .closeDialog, .closeDialogAfterClick");
            if (closeDialog) {
                if (closeDialog.classList.contains("closeDialogAfterClick") && !closeDialog.classList.contains("disabled")) {
                    setTimeout(() => {
                        dialog.close();
                    }, 5);
                } else {
                    e.preventDefault();
                    dialog.close();
                }

                if (closeDialog.classList.contains("reloadPage")) {
                    document.location.reload();
                }

                if (closeDialog.classList.contains("closeAllDialog")) {
                    document.querySelectorAll("dialog[open]").forEach((dialogOpened) => {
                        dialogOpened.close();
                    });
                }
                return;
            }

            const link = e.target.closest("a");
            if (e.defaultPrevented || !link || link.getAttribute("href") === "#" || link.getAttribute("target") === "_blank") {
                return;
            }

            e.preventDefault();

            fetchIntoDialogPromise(fetch(link.href, fetchOptions()));
        });

        dialogIn.addEventListener("submit", (e) => {
            const form = e.target.closest("form");
            if (!form || form.dataset.leaveDialog) {
                return;
            }

            e.preventDefault();

            dialog.classList.add("loading");

            fetchIntoDialogPromise(
                fetch(
                    form.action,
                    fetchOptions({
                        method: form.method,
                        body: new FormData(form),
                    })
                ),
                form.dataset.newDialog
            );
        });

        const checkGoToUrl = (content) => {
            const goToUrl = content.querySelector(".goToUrl");
            if (goToUrl) {
                if (goToUrl.classList.contains("reload")) {
                    document.location.reload();
                    return;
                }
                document.location.href = goToUrl.href;
            }
        };

        const fetchIntoDialogPromise = (responsePromise, newDialog) => {
            return responsePromise
                .then((response) => {
                    return response.text();
                })
                .then((response) => {
                    dialog.classList.remove("loading");

                    if (newDialog) {
                        const previewDialog = createDialog({
                            type: newDialog,
                        });
                        previewDialog.in.innerHTML = response;
                        previewDialog.dialog.showModal();
                        checkGoToUrl(previewDialog.in);
                    } else {
                        dialogIn.innerHTML = response;
                        const autoClosePopin = dialogIn.querySelector(".autoClosePopin");
                        if (autoClosePopin) {
                            dialog.close();
                            return;
                        }
                        checkGoToUrl(dialogIn);
                    }
                    if (window.appDispatchTriggers) {
                        window.appDispatchTriggers();
                    }
                });
        };

        fetchIntoDialogPromise(fetch(popin.href, fetchOptions()));

        dialog.showModal();
    });

    const uploader = document.querySelector("nyro-upload");
    if (uploader) {
        uploader.addEventListener("uploadEnded", (e) => {
            document.location.reload();
        });
    }

    const sortBy = document.getElementById("sortBy");
    if (sortBy) {
        sortBy.addEventListener("change", () => {
            document.location.href = sortBy.value;
        });
    }

    const multipleKey = document.body.dataset.multiple;
    if (multipleKey) {
        const multipleSelection = document.getElementById("multipleSelection"),
            multipleNbSelected = multipleSelection.querySelector("#nbSelected"),
            multipleCont = multipleSelection.querySelector(".fileDirCont");

        const selection = (() => {
            try {
                const raw = sessionStorage.getItem(multipleKey);
                if (!raw) {
                    return new Map();
                }
                const arr = JSON.parse(raw);
                if (!Array.isArray(arr)) {
                    return new Map();
                }
                return new Map(arr);
            } catch (e) {
                return new Map();
            }
        })();

        const saveSelection = () => {
            try {
                if (selection.size === 0) {
                    sessionStorage.removeItem(multipleKey);
                    return;
                }
                sessionStorage.setItem(multipleKey, JSON.stringify(Array.from(selection.entries())));
            } catch (e) {
                // Do nothing
            }
        };

        const updateSelection = (checkExisting) => {
            document.body.classList.toggle("hasSelection", selection.size > 0);
            if (selection.size === 0) {
                multipleCont.innerHTML = "";
                return;
            }

            multipleNbSelected.textContent = selection.size;
            multipleSelection.classList.toggle("needSelectionS", selection.size > 1);

            const fullHtml = [];
            selection.entries().forEach(([url, fileHtml]) => {
                fullHtml.push(fileHtml);
                if (checkExisting) {
                    const fileItem = document.querySelector(`.file:has(a[href="${CSS.escape(url)}"])`);
                    if (fileItem) {
                        fileItem.querySelector(".checkMedia").checked = true;
                    }
                }
            });
            multipleCont.innerHTML = fullHtml.join("");
            multipleCont.querySelectorAll(".checkMedia").forEach((checkMedia) => {
                checkMedia.checked = true;
            });
        };

        updateSelection(true);

        document.body.addEventListener("change", (e) => {
            const checkMedia = e.target.closest(".checkMedia");
            if (checkMedia) {
                const file = checkMedia.closest(".file"),
                    fileUrl = file.querySelector(".chooseMedia").getAttribute("href");
                if (checkMedia.checked) {
                    selection.set(fileUrl, file.outerHTML);
                } else {
                    selection.delete(fileUrl);
                    const checkMediaItems = document.querySelectorAll(`.file:has(a[href="${CSS.escape(fileUrl)}"]) .checkMedia`);
                    checkMediaItems.forEach((cm) => {
                        cm.checked = false;
                    });
                }

                saveSelection();
                updateSelection();
            }
        });

        multipleSelection.querySelector(":scope > nav").addEventListener("click", (e) => {
            const actionable = e.target.closest("[data-action]");
            if (!actionable) {
                return;
            }

            e.preventDefault();
            if (actionable.dataset.action === "useSelection") {
                const datas = [];

                multipleCont.querySelectorAll(".file .chooseMedia").forEach((chooseMedia) => {
                    datas.push(getMediaData(chooseMedia));
                });

                selection.clear();
                saveSelection();

                window.parent.postMessage(
                    {
                        mceAction: "multipleSelection",
                        multiple: multipleKey,
                        datas: datas,
                    },
                    "*"
                );
            } else if (actionable.dataset.action === "clearSelection") {
                selection.clear();
                saveSelection();
                updateSelection();
                document.querySelectorAll(".checkMedia:checked").forEach((checkMedia) => {
                    checkMedia.checked = false;
                });
            }
        });
    }
})();
