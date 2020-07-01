<?php echo $view['form']->widget($form); ?>
<?php if (isset($currentFile) && $currentFile): ?>
	<a href="<?php echo isset($currentFileUrl) && $currentFileUrl ? $currentFileUrl : $view['assets']->getUrl($currentFile); ?>" target="_blank" class="currentFile">
		<?php echo $view['translator']->trans('admin.misc.currentFile', ['%currentFile%' => basename($currentFile)]); ?>
	</a>
	<?php if (isset($showDelete) && $showDelete): ?>
		<a href="#" class="currentFileDelete" data-name="<?php echo $showDelete; ?>" data-confirm="<?php echo addcslashes($view['translator']->trans('admin.misc.currentFileDeleteConfirm'), '"'); ?>">
			<?php echo $view['translator']->trans('admin.misc.currentFileDelete'); ?>
		</a>
	<?php endif; ?>
<?php endif; ?>