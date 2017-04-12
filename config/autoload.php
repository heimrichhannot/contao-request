<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'HeimrichHannot',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Test
	'HeimrichHannot\Request\Test\PostTest' => 'system/modules/request/test/tests/Request/Test/PostTest.php',

	// Classes
	'HeimrichHannot\Request\Request'       => 'system/modules/request/classes/Request.php',
));
