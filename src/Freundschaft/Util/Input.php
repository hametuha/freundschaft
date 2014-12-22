<?php

namespace Freundschaft\Util;


use Freundschaft\Pattern\Singleton;

/**
 * Input utility
 *
 * @package Freundschaft\Util
 */
class Input extends Singleton
{

	/**
	 * Return $_GET
	 *
	 * @param string $key
	 *
	 * @return null
	 */
	public function get($key){
		return isset($_GET[$key]) ? $_GET[$key] : null;
	}

	/**
	 * Return $_POST
	 *
	 * @param string $key
	 *
	 * @return null
	 */
	public function post($key){
		return isset($_POST[$key]) ? $_POST[$key] : null;
	}

	/**
	 * Return $_REQUEST
	 *
	 * @param string $key
	 *
	 * @return null
	 */
	public function request($key){
		return isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;
	}

	/**
	 * Verify nonce
	 *
	 * @param string $action
	 * @param string $key
	 *
	 * @return bool
	 */
	public function verify_nonce($action , $key = '_wpnonce'){
		return wp_verify_nonce($this->request($key), $action);
	}

}