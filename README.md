UtilityBundle
=============

# Using `TinymceType`

Add tinymce to your dependancies:  
```
composer require tinymce/tinymce
```

Add tinymce depency your webpack encore config:  
```js
    .addPlugin(new CopyWebpackPlugin({
        patterns: [
            {from: 'vendor/tinymce/tinymce', to: '../tinymce'}
        ]
    }))
```

## Translate Tinymce

Require tinymce npm dependancy:  
```
npm i tinymce-i18n
```

Then copy the language file you need by adding to your webpack encore config:  
```js
    .addPlugin(new CopyWebpackPlugin({
        patterns: [
            {from: 'vendor/tinymce/tinymce', to: '../tinymce'},
            {from: 'node_modules/tinymce-i18n/langs7/fr_FR.js', to: '../tinymce/langs/'},
            {
                from: 'node_modules/tinymce-i18n/langs7/fr_FR.js',
                to: '../tinymce/langs/fr.js',
                transform: (input, filename) => {
                    return input.toString().replace('tinymce.addI18n("fr_FR", {', 'tinymce.addI18n("fr", {');
                }
            }
        ]
    }))
```

## Using tinymce browser feature

Add JS and CSS generation to your webpack encore config:  
```js
    .addEntry('css/admin/tinyBrowser', './vendor/nyrodev/utility-bundle/Resources/public/css/tinyBrowser/index.css')
    .addEntry('js/admin/tinyBrowser', './vendor/nyrodev/utility-bundle/Resources/public/js/tinyBrowser/index.js')
```

Add tinymce plugins depency your webpack encore config:  
```js
    .addPlugin(new CopyWebpackPlugin({
        patterns: [
            {from: 'vendor/tinymce/tinymce', to: '../tinymce'},
            {from: 'vendor/nyrodev/utility-bundle/Resources/public/js/filemanager', to: '../tinymce/plugins/filemanager'},
            {from: 'node_modules/tinymce-i18n/langs7/fr_FR.js', to: '../tinymce/langs/'},
            {
                from: 'node_modules/tinymce-i18n/langs7/fr_FR.js',
                to: '../tinymce/langs/fr.js',
                transform: (input, filename) => {
                    return input.toString().replace('tinymce.addI18n("fr_FR", {', 'tinymce.addI18n("fr", {');
                }
            }
        ]
    }))
```

Create a route for the browser page, in `config/routes/browser.yaml`:  
**Be sure to protect this route behind a security firewall!**  
```yaml
tiny_browser:
  path: "%adminPrefix%/tinyBrowser/{type}/{dir}"
  controller: NyroDev\UtilityBundle\Controller\TinymceController::browserAction
  defaults:
    dir: null
  requirements:
    dir: '.+'
```


## Using resize assets URL

Simply import this in your `config/routes/nyrodev.yaml`:
```yaml
nyrodev_assets:
    resource: "@NyroDevUtilityBundle/Resources/config/routingAssets.yaml"
```

Then you can use the `nyrodev_assets_resize` route.