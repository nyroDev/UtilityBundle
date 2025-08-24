<a
    href="<?php echo isset($menu->route) ? $view['router']->path($menu->route, $menu->getRoutePrm()) : $menu->url; ?>"
    <?php if ($menu->goBlank): ?>
        target="_blank" rel="noopener"
    <?php endif; ?>
    <?php echo $view->render('@NyroDevUtility/Tpl/_attrs.html.php', ['attrs' => $menu->attrs]); ?>
>
    <?php if ($menu->icon): ?>
        <?php echo $view['nyrocms_admin']->getIcon($menu->icon); ?>
    <?php endif; ?>
    <?php echo $menu->label; ?>
</a>