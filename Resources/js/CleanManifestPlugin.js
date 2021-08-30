const fs = require('fs');

class CleanManifestPlugin {

    constructor(options = {}) {
        this.options = options;
    }

    apply(compiler) {
        const outputPath = compiler.options.output.path + '/';
        const outputPublicPath = compiler.options.output.publicPath;

        let isFirst = true;
        let previousManifest = false;
        try {
            previousManifest = require(outputPath + 'manifest.json');
        } catch (e) {
            previousManifest = false;
        }

        compiler.hooks.afterEmit.tap('CleanManifestPlugin', (compilation) => {
            if (compilation.assets['manifest.json']) {
                const newManifest = JSON.parse(fs.readFileSync(outputPath + 'manifest.json', 'utf8'));

                if (previousManifest) {
                    Object.keys(previousManifest).forEach((key) => {
                        const hasChanged = newManifest[key] != previousManifest[key];
                        if (isFirst && (!newManifest[key] || hasChanged) || (!isFirst && newManifest[key] && hasChanged)) {
                            if (this.options.preview) {
                                console.log('Should delete ' + previousManifest[key]);
                            } else {
                                console.log('Delete ' + previousManifest[key]);
                                try {
                                    fs.unlinkSync(previousManifest[key].replace(outputPublicPath, outputPath));
                                } catch (e) {
                                    console.log('fail to delete ' + previousManifest[key]);
                                    console.log(e);
                                }
                            }
                        }
                    });
                }

                previousManifest = newManifest;
                isFirst = false;
            }
            return true;
        });
    }
}

module.exports = CleanManifestPlugin;