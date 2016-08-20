<?php

// Comment for nyroDev/Utility-bundle
//if (session_id() == '') session_start();

mb_internal_encoding('UTF-8');
// Comment for nyroDev/Utility-bundle
//date_default_timezone_set('Europe/Rome');

// Useful to put variable in global scope
foreach($configNyro as $k=>$v) {
    $GLOBALS[$k] = $v;
}

return $configNyro;
