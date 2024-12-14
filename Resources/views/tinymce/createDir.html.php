<?php if ($newUrl): ?>
    <a href="<?php echo $newUrl; ?>" class="goToUrl"><?php echo $newFodlerName; ?></a>
<?php else: ?>
    <h1><?php echo $view['translator']->trans('nyrodev.browser.createFolder'); ?></h1>
    <?php echo $view['form']->form($form); ?>
<?php endif; ?>