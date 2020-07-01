<?php echo $view['form']->errors($form); ?>

<?php if (isset($form['transformer'])): ?>
	<span class="row_form_transformer">
		<?php echo $view['form']->errors($form['transformer']); ?>
		<?php echo $view['form']->widget($form['transformer']); ?>
	</span>
<?php endif; ?>

<?php echo $view['form']->errors($form['value']); ?>
<?php echo $view['form']->widget($form['value']); ?>

<?php echo $view['form']->rest($form); ?>