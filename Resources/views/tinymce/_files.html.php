<?php foreach ($tinymceBrowser->getDirectories() as $directory): ?>
    <div class="dir">
        <a href="<?php echo $tinymceBrowser->getUrl('path', ($tinymceBrowser->getPath() ? $tinymceBrowser->getPath().'/' : '').$directory->getBasename()); ?>" class="dir">
            <svg class="icon icon-folder media"><use href="#folder"></use></svg>
            <strong><?php echo $directory->getBasename(); ?></strong>
        </a>
        <nav>
            <span></span>
            <span>
                <a href="<?php echo $tinymceBrowser->getUrl('rename', $directory->getBasename()); ?>" class="btn btnLightBorder popin" title="<?php echo $view['translator']->trans('nyrodev.browser.rename'); ?>">
                    <svg class="icon icon-edit"><use href="#edit"></use></svg>
                </a>
                <a href="<?php echo $tinymceBrowser->getUrl('delete', $directory->getBasename()); ?>" class="btn btnLightBorder popin" title="<?php echo $view['translator']->trans('nyrodev.browser.delete'); ?>">
                    <svg class="icon icon-delete"><use href="#delete"></use></svg>
                </a>
            </span>
        </nav>
    </div>
<?php endforeach; ?>
<?php foreach ($tinymceBrowser->getFiles() as $file): ?>
    <div class="file">
        <a
            href="<?php echo $view->escape($tinymceBrowser->getFileUrl($file)); ?>"
            class="chooseMedia"
            <?php foreach ($tinymceBrowser->getFileChooseAttrs($file) as $k => $v): ?>
                <?php echo $k.'="'.$view->escape($v).'"'; ?>
            <?php endforeach; ?>>
            <img src="<?php echo $tinymceBrowser->getResizeFileUrl($file); ?>" alt="<?php echo $view->escape($file->getBasename()); ?>" class="media" />
            <strong><?php echo $file->getBasename(); ?></strong>
        </a>
        <nav>
            <span>
                <?php echo $view['nyrodev']->humanSize($file->getSize()); ?>
            </span>
            <span>
                <a href="<?php echo $view->escape($tinymceBrowser->getFileUrl($file)); ?>" class="btn btnLightBorder" target="_blank" title="<?php echo $view['translator']->trans('nyrodev.browser.view'); ?>">
                    <svg class="icon icon-view"><use href="#view"></use></svg>
                </a>
                <a href="<?php echo $tinymceBrowser->getUrl('rename', $file->getBasename()); ?>" class="btn btnLightBorder popin" title="<?php echo $view['translator']->trans('nyrodev.browser.rename'); ?>">
                    <svg class="icon icon-edit"><use href="#edit"></use></svg>
                </a>
                <a href="<?php echo $tinymceBrowser->getUrl('delete', $file->getBasename()); ?>" class="btn btnLightBorder popin" title="<?php echo $view['translator']->trans('nyrodev.browser.delete'); ?>">
                    <svg class="icon icon-delete"><use href="#delete"></use></svg>
                </a>
            </span>
        </nav>
    </div>
<?php endforeach; ?>