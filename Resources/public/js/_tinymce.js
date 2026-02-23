(function () {
    let tinymceLoaded = false,
        tinymceLoading = false;

    const tinymceLoadingQueue = [];

    window.nyroTinymceLoad = (url, clb) => {
        if (tinymceLoading) {
            tinymceLoadingQueue.push(clb);
        } else if (!tinymceLoaded) {
            tinymceLoading = true;
            window.tinyMCEPreInit = {
                base: url.substr(0, url.lastIndexOf("/")),
                suffix: ".min",
            };

            const script = document.createElement("script");
            script.type = "text/javascript";
            script.addEventListener("load", () => {
                tinymceLoading = false;
                tinymceLoaded = true;
                clb();
                if (tinymceLoadingQueue.length) {
                    $.each(tinymceLoadingQueue, function (k, v) {
                        v();
                    });
                }
            });
            script.src = url;
            document.getElementsByTagName("head")[0].appendChild(script);
        } else {
            clb();
        }
    };

    window.nyroTinymceLoaded = (clb) => {
        if (!tinymceLoaded) {
            tinymceLoadingQueue.push(clb);
        } else {
            clb();
        }
    };

    window.nyroTinymce = (element, options, tinymceUrl) => {
        if (!tinymceUrl) {
            if (!element.dataset.tinymceUrl) {
                console.error("No tinymce url provided for nyroTinymce");
                return;
            }
            tinymceUrl = element.dataset.tinymceUrl;
        }
        if (!options) {
            options = {};
        }
        window.nyroTinymceLoad(tinymceUrl, () => {
            tinymce.init({
                target: element,
                ...options,
                ...JSON.parse(element.dataset.tinymceOptions || "{}"),
                oninit: (ed) => {
                    element.dispatchEvent(
                        new CustomEvent("tinmceInit", { detail: ed }),
                    );
                },
            });
        });
    };

    document.querySelectorAll("textarea.tinymce").forEach((textarea) => {
        window.nyroTinymce(textarea);
    });
})();
