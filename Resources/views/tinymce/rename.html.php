<?php if ($newUrl): ?>
    <a href="#" class="goToUrl reload"><?php echo $newName; ?></a>
<?php else: ?>
    <h1><?php echo $view['translator']->trans('nyrodev.browser.rename'); ?></h1>
    <?php echo $view['form']->form($form); ?>
<?php endif; ?>