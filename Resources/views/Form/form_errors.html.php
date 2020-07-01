<?php if ($errors && count($errors)): ?>
    <ul class="form_errors">
        <?php foreach ($errors as $error): ?>
            <li><?php echo $view['nyrodev']->trans($error->getMessage()); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
