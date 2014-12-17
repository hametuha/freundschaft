<?php

namespace Freundschaft\Pattern;


/**
 * Singleton Class
 *
 * @package Freundschaft\Pattern
 */
abstract class Singleton
{
	/**
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Constructor
	 *
	 * @param array $settings
	 */
	protected function __construct( array $settings = array() ){
		// Do nothing
	}

	/**
	 * Instance
	 *
	 * @param array $settings
	 *
	 * @return self
	 */
	public static function getInstance( array $settings = array() ){
		$class_name = get_called_class();
		if( !isset(self::$instances[$class_name]) ){
			self::$instances[$class_name] = new $class_name($settings);
		}
		return self::$instances[$class_name];
	}

	/**
	 * Make upper came to snake case
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected function decamelize($string){
		return strtolower(preg_replace_callback('/(?<=.)([A-Z])/', function($match){
			return '_'.strtolower($match[1]);
		}, $string));
	}

}
