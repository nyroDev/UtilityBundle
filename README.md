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
            {from: 'vendor/nyrodev/utility-bundle/Resources/public/js/filemanager', to: '../tinymce/plugins/filemanager'}
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

