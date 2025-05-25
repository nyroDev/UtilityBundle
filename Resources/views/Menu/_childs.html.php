<?php if ($menu->hasChilds()): ?>
    <?php if ($menu->getBeforeChildsTemplate() && $menu->getLevel()): ?>
        <?php echo $view->render($menu->getBeforeChildsTemplate(), ['menu' => $menu]); ?>
    <?php endif; ?>
    <ul
        <?php echo $view->render('@NyroDevUtility/Tpl/_attrs.html.php', ['attrs' => $menu->getChildOuterAttrs()]); ?>
    >
    <?php foreach($menu->getChilds() as $child): ?>
        <li
            <?php echo $view->render('@NyroDevUtility/Tpl/_attrs.html.php', ['attrs' => $child->getOuterAttrs()]); ?>
        >
            <?php echo $view->render($child->getTemplate(), ['menu' => $child]); ?>
            <?php echo $view->render('@NyroDevUtility/Menu/_childs.html.php', ['menu' => $child]); ?>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>