<?php

namespace Freundschaft\Pattern;

use Freundschaft\Util\String;
use Freundschaft\Util\Input;
use Freundschaft\Util\ModelAccessor;

/**
 * Class Controller
 * @package Freundschaft\Pattern
 * @property-read \Freundschaft\Util\String $string
 * @property-read Input $input
 * @property-read ModelAccessor $models
 */
class Controller extends Singleton
{
	/**
	 * Get view file path
	 *
	 * @param string $file File name wihout extension
	 *
	 * @return string
	 */
	protected function getView($file){
		$file .= '.php';
		$default = dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$file;
		// If default not found, throw error
		if( !file_exists($default) ){
			throw new \RuntimeException(sprintf('テンプレート%sが存在しません。', $default), 404);
		}
		// Get child theme's file
		$css_path = get_stylesheet_directory().DIRECTORY_SEPARATOR.$file;
		if( file_exists($css_path) ){
			return $css_path;
		}
		// Get parent theme's file
		$template_path = get_template_directory().DIRECTORY_SEPARATOR.$file;
		if( file_exists($template_path) ){
			return $template_path;
		}
		// Not found. Return default.
		return $default;
	}

	/**
	 * Render user list
	 *
	 * @param array $users
	 * @param string $view
	 *
	 * @return string
	 */
	protected function renderUserList(array $users, $view){
		$path = $this->getView($view);
		ob_start();
		foreach( $users as $user ){
			include $path;
		}
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return Singleton|null
	 */
	public function __get($name){
		switch( $name ){
			case 'string':
				return String::getInstance();
				break;
			case 'input':
				return Input::getInstance();
				break;
			case 'models':
				return ModelAccessor::getInstance();
				break;
			default:
				return null;
				break;
		}
	}
}
