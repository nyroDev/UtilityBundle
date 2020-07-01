<div class="form_button">
	<span class="formRequiredFields">
		<?php echo $view['translator']->trans('nyrodev.required'); ?>
	</span>
    <?php
    $more = null;
    if (isset($form->vars['attr']) && isset($form->vars['attr']['more'])) {
        $more = $form->vars['attr']['more'];
        unset($form->vars['attr']['more']);
    }
    echo $view['form']->widget($form);
    if ($form->vars && isset($form->vars['attr']) && isset($form->vars['attr']['data-cancelurl']) && $form->vars['attr']['data-cancelurl']) {
        echo '<a href="'.$form->vars['attr']['data-cancelurl'].'" class="button cancel">'.$view['translator']->trans('admin.misc.cancel').'</a>';
    }
    echo $more;
    ?>
</div>
