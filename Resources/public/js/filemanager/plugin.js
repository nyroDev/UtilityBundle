tinymce.PluginManager.add("filemanager", (editor, url) => {
  editor.options.register("external_filemanager_path", {
    processor: "string",
    default: "",
  });
  editor.options.register("filemanager_title", {
    processor: "string",
    default: "",
  });

  if (editor.options.get("external_filemanager_path")) {
    editor.options.set("file_picker_callback", (callback, value, meta) => {
      const dialog = editor.windowManager.openUrl({
        title: editor.options.get("filemanager_title"),
        url: editor.options
          .get("external_filemanager_path")
          .replace("_TYPE_", meta.filetype),
        width: Math.min(1600, window.innerWidth - 20),
        height: Math.min(1200, window.innerHeight - 40),
        onMessage: (dialogApi, details) => {
          callback(details.data.url);
          dialog.close();
        },
      });
    });
  } else {
    console.warn("external_filemanager_path not provided");
  }

  return {
    getMetadata: () => ({
      name: "nyro Filemanager",
      url: "https://www.nyro.dev/",
    }),
  };
});
