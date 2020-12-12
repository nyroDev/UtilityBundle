<?php

$version = '9.14.0';

// nyro Update comment
/*
if (session_id() == '') {
    session_start();
}
*/

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');
// nyro Update comment
/*
date_default_timezone_set('Europe/Rome');
setlocale(LC_CTYPE, 'en_US'); //correct transliteration

*/

// nyro Update use config coming from Controller
// Useful to put variable in global scope
$GLOBALS['config'] = $configNyro;
foreach ($configNyro as $k => $v) {
    $GLOBALS[$k] = $v;
}
$default_language = $configNyro['default_language'];

return $configNyro;
