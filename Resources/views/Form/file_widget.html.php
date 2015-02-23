<?php echo $view['form']->widget($form) ?>
<?php if (isset($currentFile) && $currentFile): ?>
	<a href="<?php echo $view['assets']->getUrl($currentFile) ?>" target="_blank" class="currentFile"><?php echo $view['translator']->trans('admin.misc.currentFile', array('%currentFile%'=>basename($currentFile))) ?></a>
<?php endif ?>