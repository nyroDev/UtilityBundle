(function () {
    const currentFileDeletes = document.querySelectorAll(".currentFileDelete");

    if (currentFileDeletes.length) {
        currentFileDeletes.forEach((deleteButton) => {
            deleteButton.addEventListener("click", (e) => {
                e.preventDefault();
                if (confirm(deleteButton.dataset.confirm)) {
                    const row = deleteButton.closest(".form_row");
                    row.append(
                        '<input type="hidden" name="' +
                            deleteButton.dataset.name +
                            '" value="1" />',
                    );
                    row.querySelector(".currentFile").remove();
                    deleteButton.remove();
                }
            });
        });
    }
})();
