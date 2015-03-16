<div class="form_row<?php echo ' form_row_'.implode(' form_row_', $form->vars['block_prefixes']).($required ? ' form_required' : '').($valid && $view['nyrodev']->isPost() ? ' form_valid' : '').(count($errors) > 0 ? ' form_error' : '') ?>">
	<?php
	$help = null;
	if (isset($form->vars['attr']) && isset($form->vars['attr']['help'])) {
		$help = $form->vars['attr']['help'];
		unset($form->vars['attr']['help']);
	}
	?>
    <?php echo $view['form']->label($form) ?>
    <?php echo $view['form']->widget($form) ?>
	<span class="formIndicator">*</span>
	<?php if (isset($help) && $help) : ?>
		<span class="help"><?php echo $help ?></span>
	<?php endif ?>
    <?php echo $view['form']->errors($form) ?>
</div>