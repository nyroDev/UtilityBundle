<input type="checkbox" id="switch_<?php echo $menu->getComputedId(); ?>" class="nyrodev-menu-before-childs" <?php echo $menu->isActiveOrChildActive() ? 'checked' : ''; ?>/>
<label for="switch_<?php echo $menu->getComputedId(); ?>">
    <?php echo $view['nyrodev_icon']->getIcon('chevron'); ?>
</label>