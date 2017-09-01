<?php
// Comment for nyroDev/Utility-bundle
//if (session_id() == '') session_start();

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');

// Useful to put variable in global scope
foreach($configNyro as $k=>$v) {
    $GLOBALS[$k] = $v;
}

return $configNyro;