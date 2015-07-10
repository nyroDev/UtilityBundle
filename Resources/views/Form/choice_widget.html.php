<?php if ($expanded): ?>
<?php echo $view['form']->block($form, 'choice_widget_expanded') ?>
<?php else: ?>
<div class="selectCont"><?php echo $view['form']->block($form, 'choice_widget_collapsed') ?></div>
<?php endif ?>
