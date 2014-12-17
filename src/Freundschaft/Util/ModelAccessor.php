<?php

namespace Freundschaft\Util;


use Freundschaft\Pattern\Singleton;


/**
 * Class ModelAccessor
 *
 * @package Freundschaft\Util
 * @property-read \Freundschaft\Models\Followers $followers
 */
class ModelAccessor extends Singleton
{
	/**
	 * Getter
	 *
	 * @param $name
	 *
	 * @return Singleton|null
	 */
	public function __get($name){
		switch( $name ){
			case 'string':
				return String::getInstance();
				break;
			default:
				// Search models and return if found
				$class_name = 'Freundschaft\\Models\\'.$this->string->camelize($name);
				if( class_exists($class_name) ){
					return call_user_func(array($class_name, 'getInstance'));
				}else{
					return null;
				}
				break;
		}
	}

}