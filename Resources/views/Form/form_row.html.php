<div class="form_row<?php echo ' form_row_'.implode(' form_row_', $form->vars['block_prefixes']).($required ? ' form_required' : '').($valid && $view['nyrodev']->isPost() ? ' form_valid' : '').(count($errors) > 0 ? ' form_error' : '') ?>">
    <?php echo $view['form']->label($form) ?>
    <?php echo $view['form']->widget($form) ?>
	<span class="formIndicator">*</span>
    <?php echo $view['form']->errors($form) ?>
</div>