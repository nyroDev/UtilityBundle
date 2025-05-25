<span class="menuText">
    <?php if ($menu->icon): ?>
        <?php echo $view['nyrocms_admin']->getIcon($menu->icon); ?>
    <?php endif; ?>
    <?php echo $menu->content; ?>
</span>