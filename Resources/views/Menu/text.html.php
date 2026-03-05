<span class="menuText">
    <?php if ($menu->icon): ?>
        <?php echo $view['nyrodev_icon']->getIcon($menu->icon); ?>
    <?php endif; ?>
    <?php echo $menu->content; ?>
</span>