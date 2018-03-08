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
ClassLoader::addNamespaces([
    'HeimrichHannot',
]);


/**
 * Register the classes
 */
ClassLoader::addClasses([
    // Classes
    'HeimrichHannot\Request\Request'                          => 'system/modules/request/src/Request.php',
    'HeimrichHannot\Request\EventListener\InsertTagsListener' => 'system/modules/request/src/EventListener/InsertTagsListener.php'
]);
