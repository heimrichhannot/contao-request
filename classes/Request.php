<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2016 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\Request;

class Request
{
	/**
	 * Object instance (Singleton)
	 *
	 * @var \Environment
	 */
	protected static $objInstance;
	
	/**
	 * Request object
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected static $request;
	
	/**
	 * Return the object instance (Singleton)
	 *
	 * @return \Symfony\Component\HttpFoundation\Request The object instance
	 *
	 */
	public static function getInstance()
	{
		if (static::$objInstance === null)
		{
			static::$objInstance = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
		}
		
		return static::$objInstance;
	}
	
	/**
	 * For test purposes use \Symfony\Component\HttpFoundation\Request::create() for dummy data
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 *
	 * @return \Environment
	 */
	public static function set(\Symfony\Component\HttpFoundation\Request $request)
	{
		static::$objInstance = $request;
		
		return static::$objInstance;
	}
	
	/**
	 * Shorthand getter for query arguments ($_GET)
	 * @param $strKey
	 *
	 * @return mixed
	 */
	public static function getGet($strKey = null)
	{
		if($strKey === null)
		{
			return static::getInstance()->query;
		}
		
		return static::getInstance()->query->get($strKey);
	}

    /**
     * Returns true if the get parameter is defined.
     *
     * @param string $strKey The key
     *
     * @return bool true if the parameter exists, false otherwise
     */
    public static function hasGet($strKey)
    {
        return static::getInstance()->query->has($strKey);
    }

	/**
	 * Shorthand getter for request arguments ($_POST)
	 * @param $strKey
	 *
	 * @return mixed
	 */
	public static function getPost($strKey = null)
	{
		if($strKey === null)
		{
			return static::getInstance()->request;
		}
		
		return static::getInstance()->request->get($strKey);
	}

    /**
     * Returns true if the post parameter is defined.
     *
     * @param string $strKey The key
     *
     * @return bool true if the parameter exists, false otherwise
     */
	public static function hasPost($strKey)
    {
        return static::getInstance()->request->has($strKey);
    }
	
	/**
	 * Prevent direct instantiation (Singleton)
	 *
	 */
	protected function __construct()
	{
	}
	
	
	/**
	 * Prevent cloning of the object (Singleton)
	 *
	 */
	final public function __clone()
	{
	}
	
}