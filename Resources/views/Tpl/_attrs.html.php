<?php foreach ($attrs as $key => $value): ?>
    <?php echo $key.'="'.$view->escape(trim($value)).'"'; ?>
<?php endforeach; ?>