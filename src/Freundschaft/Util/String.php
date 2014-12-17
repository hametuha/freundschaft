<?php

namespace Freundschaft\Util;


use Freundschaft\Pattern\Singleton;

class String extends Singleton
{


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