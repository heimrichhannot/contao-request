<?php

/*
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0+
 */

error_reporting(E_ALL);

define('TL_MODE', 'FE');
define('UNIT_TESTING', true);

require __DIR__.'/../../../../system/initialize.php';

\Input::setPost('test', '<script>alert(\'xss\')</script>');
$input = \Input::post('test');
