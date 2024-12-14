<?php if ($confirmed): ?>
    <h1><?php echo $view['translator']->trans('nyrodev.browser.deletion.confirmed'); ?></h1>
    <?php echo $view['translator']->trans('nyrodev.browser.deletion.confirmedMessage', [
        '%name%' => '<strong>'.$name.'</strong>',
    ]); ?>
    <nav>
        <a href="#" class="btn cancel reloadPage"><?php echo $view['translator']->trans('nyrodev.browser.deletion.close'); ?></a>
    </nav>
<?php else: ?>
    <h1><?php echo $view['translator']->trans('nyrodev.browser.deletion.confirm'); ?></h1>
    <?php echo $view['translator']->trans('nyrodev.browser.deletion.message', [
        '%name%' => '<strong>'.$name.'</strong>',
    ]); ?>
    <?php if ($isDir): ?>
        <?php echo $view['translator']->trans('nyrodev.browser.deletion.messageDir'); ?>
    <?php endif; ?>
    <nav>
        <a href="#" class="btn btnLightBorder cancel"><?php echo $view['translator']->trans('nyrodev.browser.deletion.cancel'); ?></a>
        <a href="<?php echo $tinyBrowser->getUrl('confirm', true); ?>" class="btn btnConfirm"><?php echo $view['translator']->trans('nyrodev.browser.deletion.confirmBut'); ?></a>
    </nav>
<?php endif; ?>