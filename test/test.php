<?php

error_reporting(E_ALL);

define('TL_MODE', 'FE');
define('UNIT_TESTING', true);

require __DIR__ . '/../../../../system/initialize.php';


\Input::setPost('test', '<script>alert(\'xss\')</script>');
$input = \Input::post('test');