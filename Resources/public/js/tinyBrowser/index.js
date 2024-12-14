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

  document.body.addEventListener("click", (e) => {
    const chooseMedia = e.target.closest(".chooseMedia");
    if (chooseMedia) {
      e.preventDefault();

      window.parent.postMessage(
        {
          mceAction: "customAction",
          data: {
            url: chooseMedia.href,
          },
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
      const closeDialog = e.target.closest(
        ".cancel, .closeDialog, .closeDialogAfterClick"
      );
      if (closeDialog) {
        if (
          closeDialog.classList.contains("closeDialogAfterClick") &&
          !closeDialog.classList.contains("disabled")
        ) {
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
      if (
        e.defaultPrevented ||
        !link ||
        link.getAttribute("href") === "#" ||
        link.getAttribute("target") === "_blank"
      ) {
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
})();
