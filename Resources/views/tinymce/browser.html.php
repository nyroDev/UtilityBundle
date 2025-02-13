<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Nyro FileManager</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300..800&display=swap" rel="stylesheet">
    <?php echo $view['nyrodev_tagRenderer']->renderWebpackLinkTags('css/admin/tinyBrowser'); ?>

<svg style="display: none;">
    <symbol id="delete" viewBox="0 0 512 512" fill="none">
        <path fill="currentColor" d="M96 512h320l32-352h-384zM320 64v-64h-128v64h-160v96l32-32h384l32 32v-96h-160zM288 64h-64v-32h64v32z"></path>
    </symbol>
    <symbol id="close" viewBox="0 0 512 512" fill="none">
        <path fill="currentColor" d="M256 0c-141.385 0-256 114.615-256 256s114.615 256 256 256 256-114.615 256-256-114.615-256-256-256zM256 464c-114.875 0-208-93.125-208-208s93.125-208 208-208 208 93.125 208 208-93.125 208-208 208z"></path>
        <path fill="currentColor" d="M336 128l-80 80-80-80-48 48 80 80-80 80 48 48 80-80 80 80 48-48-80-80 80-80z"></path>
    </symbol>
    <symbol id="upload" viewBox="0 0 512 512" fill="none">
        <path fill="currentColor" d="M446.134 193.245c1.222-5.555 1.866-11.324 1.866-17.245 0-44.183-35.817-80-80-80-7.111 0-14.007 0.934-20.566 2.676-12.399-38.676-48.645-66.676-91.434-66.676-43.674 0-80.527 29.168-92.163 69.085-11.371-3.311-23.396-5.085-35.837-5.085-70.692 0-128 57.308-128 128 0 70.694 57.308 128 128 128h64v96h128v-96h112c44.183 0 80-35.816 80-80 0-39.36-28.427-72.081-65.866-78.755zM288 320v96h-64v-96h-80l112-112 112 112h-80z"></path>
    </symbol>
    <symbol id="download" viewBox="0 0 512 512" fill="none">
        <path fill="currentColor" d="M368 224l-128 128-128-128h80v-192h96v192zM240 352h-240v128h480v-128h-240zM448 416h-64v-32h64v32z"></path>
    </symbol>
    <symbol id="view" viewBox="0 0 512 512" fill="none">
        <path fill="currentColor" d="M256 96c-111.659 0-208.441 65.021-256 160 47.559 94.979 144.341 160 256 160 111.656 0 208.438-65.021 256-160-47.558-94.979-144.344-160-256-160zM382.225 180.852c30.081 19.187 55.571 44.887 74.717 75.148-19.146 30.261-44.637 55.961-74.718 75.148-37.797 24.109-81.445 36.852-126.224 36.852-44.78 0-88.429-12.743-126.226-36.852-30.079-19.186-55.569-44.886-74.716-75.148 19.146-30.262 44.637-55.962 74.717-75.148 1.959-1.25 3.938-2.461 5.93-3.65-4.98 13.664-7.705 28.411-7.705 43.798 0 70.691 57.308 128 128 128s128-57.309 128-128c0-15.387-2.726-30.134-7.704-43.799 1.989 1.189 3.969 2.401 5.929 3.651v0zM256 208c0 26.51-21.49 48-48 48s-48-21.49-48-48 21.49-48 48-48 48 21.491 48 48z"></path>
    </symbol>
    <symbol id="folder" viewBox="0 0 512 512" fill="none">
        <path fill="currentColor" d="M416 480l96-256h-416l-96 256zM64 192l-64 288v-416h144l64 64h208v64z"></path>
    </symbol>
    <symbol id="create_folder" viewBox="0 0 512 512" fill="none">
        <path fill="currentColor" d="M288 128l-64-64h-224v416h512v-352h-224zM352 352h-64v64h-64v-64h-64v-64h64v-64h64v64h64v64z"></path>
    </symbol>
    <symbol id="grid" viewBox="0 0 512 512" fill="none">
        <path fill="currentColor" d="M32 32h128v128h-128zM32 192h128v128h-128zM32 352h128v128h-128z M192 32h128v128h-128zM192 192h128v128h-128zM192 352h128v128h-128z M352 32h128v128h-128zM352 192h128v128h-128zM352 352h128v128h-128z"></path>
    </symbol>
    <symbol id="list" viewBox="0 0 512 512" fill="none">
        <path fill="currentColor" d="M32 32h448v128h-448zM32 192h448v128h-448zM32 352h448v128h-448z"></path>
    </symbol>
    <symbol id="home" viewBox="0 0 512 512" fill="none">
        <path fill="currentColor" d="M512 304l-96-96v-144h-64v80l-96-96-256 256v16h64v160h160v-96h64v96h160v-160h64z"></path>
    </symbol>
    <symbol id="edit" viewBox="0 0 512 512" fill="none">
        <path fill="currentColor" d="M432 0c44.182 0 80 35.817 80 80 0 18.010-5.955 34.629-16 48l-32 32-112-112 32-32c13.371-10.045 29.989-16 48-16zM32 368l-32 144 144-32 296-296-112-112-296 296zM357.789 181.789l-224 224-27.578-27.578 224-224 27.578 27.578z"></path>
    </symbol>
    <symbol id="search" viewBox="0 0 512 512" fill="none">
        <path fill="currentColor" d="M496.131 435.698l-121.276-103.147c-12.537-11.283-25.945-16.463-36.776-15.963 28.628-33.534 45.921-77.039 45.921-124.588 0-106.039-85.961-192-192-192s-192 85.961-192 192 85.961 192 192 192c47.549 0 91.054-17.293 124.588-45.922-0.5 10.831 4.68 24.239 15.963 36.776l103.147 121.276c17.661 19.623 46.511 21.277 64.11 3.678s15.946-46.449-3.677-64.11zM192 320c-70.692 0-128-57.308-128-128s57.308-128 128-128 128 57.308 128 128-57.307 128-128 128z"></path>
    </symbol>
    <symbol id="refresh" viewBox="0 0 512 512" fill="none">
        <path fill="currentColor" d="M444.84 83.16c-46.804-51.108-114.077-83.16-188.84-83.16-141.385 0-256 114.615-256 256h48c0-114.875 93.125-208 208-208 61.51 0 116.771 26.709 154.848 69.153l-74.848 74.847h176v-176l-67.16 67.16z"></path>
        <path fill="currentColor" d="M464 256c0 114.875-93.125 208-208 208-61.51 0-116.771-26.709-154.847-69.153l74.847-74.847h-176v176l67.16-67.16c46.804 51.108 114.077 83.16 188.84 83.16 141.385 0 256-114.615 256-256h-48z"></path>
    </symbol>
</svg>
</head>
<body>
    <header>
        <nav>
            <?php if ($tinymceBrowser->canUpload()): ?>
                <nyro-upload
                    multiple
                    url="<?php echo $tinymceBrowser->getUrl('upload'); ?>"
                    accept="<?php echo $tinymceBrowser->getUploadAccept(); ?>"
                    title="<?php echo $view['translator']->trans('nyrodev.browser.upload'); ?>"
                ><svg class="icon icon-upload" slot="label"><use href="#upload"></use></svg></nyro-upload>
            <?php endif; ?>
            <?php if ($tinymceBrowser->canCreateDir()): ?>
                <a href="<?php echo $tinymceBrowser->getUrl('createDir'); ?>" class="btn popin" title="<?php echo $view['translator']->trans('nyrodev.browser.createFolder'); ?>">
                    <svg class="icon icon-create_folder"><use href="#create_folder"></use></svg></a>
            <?php endif; ?>
        </nav>
        <nav>
            <?php /*
            <a href="#" class="btn"><svg class="icon icon-grid"><use href="#grid"></use></svg></a>
            <a href="#" class="btn"><svg class="icon icon-list"><use href="#list"></use></svg></a>
            */ ?>
        </nav>
        <form method="get" action="<?php echo $tinymceBrowser->getUrl('current'); ?>">
            <?php foreach ($tinymceBrowser->getQueryStings() as $k => $v): ?>
                <input type="hidden" name="<?php echo $k; ?>" value="<?php echo $view->escape($v); ?>" />
            <?php endforeach; ?>
            <label for="q"><?php echo $view['translator']->trans('nyrodev.browser.filter'); ?></label>
            <input type="search" id="q" name="q" placeholder="<?php echo $view['translator']->trans('nyrodev.browser.filter'); ?>" value="<?php echo $view->escape($tinymceBrowser->getSearch()); ?>" />
            <button type="submit"><svg class="icon icon-search"><use href="#search"></use></svg></button>
        </form>
    </header>
    <section>
        <div id="currentDir">
            <div>
                <nav>
                    <a href="<?php echo $tinymceBrowser->getUrl('path', ''); ?>"><svg class="icon icon-home"><use href="#home"></use></svg></a>
                    <?php foreach ($tinymceBrowser->getPaths() as $path): ?>
                        / <a href="<?php echo $tinymceBrowser->getUrl('path', $path); ?>"><?php echo $path; ?></a>
                    <?php endforeach; ?>
                    <?php if ($tinymceBrowser->getLastPath()): ?>
                        / <strong><?php echo $tinymceBrowser->getLastPath(); ?></strong>
                    <?php endif; ?>
                </nav>
                <?php echo $view['translator']->trans('nyrodev.browser.files.'.($tinymceBrowser->getNbFiles() > 1 ? 'plural' : 'single'), [
                    '%nbFiles%' => $tinymceBrowser->getNbFiles(),
                ]); ?>
                <?php echo $view['translator']->trans('nyrodev.browser.folders.'.($tinymceBrowser->getNbDirs() > 1 ? 'plural' : 'single'), [
                    '%nbFolders%' => $tinymceBrowser->getNbDirs(),
                ]); ?>
                <?php echo $view['nyrodev']->humanSize($tinymceBrowser->getFullSize()); ?>
            </div>
            <div>
                <select id="sortBy">
                    <?php foreach ($tinymceBrowser->getSorts() as $sort => $name): ?>
                        <option value="<?php echo $view->escape($tinymceBrowser->getUrl('sortBy', $sort)); ?>"<?php echo $sort === $tinymceBrowser->getSortBy() ? ' selected' : ''; ?>><?php echo $name; ?></option>
                    <?php endforeach; ?>
                </select>
                <a href="<?php echo $tinymceBrowser->getUrl('current'); ?>" class="btn btnLightBorder" title="<?php echo $view['translator']->trans('nyrodev.browser.refresh'); ?>">
                    <svg class="icon icon-refresh"><use href="#refresh"></use></svg>
                </a>
            </div>
        </div>
        <article>
            <?php echo $view->render('@NyroDevUtiliy/tinymce/_files.html.php', [
                'tinymceBrowser' => $tinymceBrowser,
            ]); ?>
        </article>
    </section>
    <?php echo $view['nyrodev_tagRenderer']->renderWebpackScriptTags('js/admin/tinyBrowser', 'defer'); ?>
</body>
</html>