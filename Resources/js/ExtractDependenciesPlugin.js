const fs = require('fs');

class ExtractDependenciesPlugin {

    constructor(options = {}) {
        this.options = options;
    }

    apply(compiler) {
        const outputPath = compiler.options.output.path + '/';
        const outputPublicPath = compiler.options.output.publicPath;

        compiler.hooks.afterEmit.tap('ExtractDependenciesPlugin', (compilation) => {
            const outputs = {};

            compilation.entrypoints.forEach((entry) => {
                const name = entry.options.name;
                outputs[name] = [];
                entry.chunks.forEach(chunk => {
                    chunk.files.forEach(file => {
                        outputs[name].push(outputPublicPath + file);
                    });
                    chunk.auxiliaryFiles.forEach(file => {
                        outputs[name].push(outputPublicPath + file);
                    });
                });
            });

            fs.writeFileSync(outputPath + 'dependencies.json', JSON.stringify(outputs));

            return true;
        });
    }
}

module.exports = ExtractDependenciesPlugin;