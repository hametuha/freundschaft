<?php

namespace Freundschaft\Pattern;
use Freundschaft\Util\ModelAccessor;
use Freundschaft\Util\String;
use Freundschaft\Util\Input;

/**
 * Ajax Base class
 *
 * @package Freundschaft\Pattern
 * @property-read \Freundschaft\Util\String $string
 * @property-read Input $input
 * @property-read ModelAccessor $models
 */
abstract class Ajax extends Singleton
{
	/**
	 * @var string
	 */
	protected $version = '1.0';

	/**
	 * Nonce action name
	 *
	 * @var string
	 */
	protected $nonce = '';

	/**
	 * @var array method name and is
	 */
	protected $methods = array();

	/**
	 * @var string front, admin, both, login, all
	 */
	protected $screen = 'front';

	/**
	 * @var string
	 */
	protected $script_name = '';

	/**
	 * @var array
	 */
	protected $script_deps = array('jquery');

	/**
	 * @var bool
	 */
	protected $script_is_footer = true;

	/**
	 * Constructor
	 *
	 * @param array $settings
	 */
	protected function __construct( array $settings = array() ){
		// Get all methods and register them as ajax action
		foreach( get_class_methods(get_called_class()) as $method ){
			if( preg_match('/\Aajax(Nopriv)?/', $method, $match) ){
				// Check if this method is public.
				$reflection = new \ReflectionMethod(get_called_class(), $method);
				if( $reflection->isPublic() ){
					// O.k. Register method as Ajax
					$this->methods[$method] = 'ajaxNopriv' != $match[0];
				}
			}

		}
		// Set nonce action name with class name
		$this->nonce = $this->string->decamelize(str_replace('\\', '_', get_called_class()));
		// Add Ajax action
		add_action('admin_init', array($this, 'adminInit'));
		// Set script name
		if( empty($this->script_name) ){
			$class_name = explode('\\', get_called_class());
			$this->script_name = str_replace('_', '-', $this->string->decamelize($class_name[count($class_name) - 1]));
		}
		// Register scripts for front page
		if( false !== array_search($this->screen, array('front', 'both', 'all')) ){
			add_action('wp_enqueue_scripts', array($this, 'enqueueScripts' ));
		}
		// Register scripts for admin page
		if( false !== array_search($this->screen, array('admin', 'both', 'all')) ){
			add_action('admin_enqueue_scripts', array($this, 'enqueueScripts' ));
		}
		// Register scripts for login page
		if( false !== array_search($this->screen, array('login', 'all')) ){
			add_action('login_enqueue_scripts', array($this, 'enqueueScripts' ));
		}
	}

	/**
	 * Enqueue scripts
	 *
	 * @param string $page_name
	 */
	public function enqueueScripts( $page_name = '' ){
		$base_url = plugin_dir_url(dirname(dirname(dirname(__FILE__))));
		$js = $base_url.'assets/js/ajax/'.$this->script_name.(WP_DEBUG ? '' : '.min').'.js';
		$handle_name = 'freundschaft-'.$this->script_name;
		wp_enqueue_script($handle_name, $js, $this->script_deps, $this->version, $this->script_is_footer);
		$class_name = explode('\\', get_called_class());
		wp_localize_script($handle_name, 'Freundschaft'.$class_name[count($class_name) - 1], $this->getJsVars());
		$this->extraScripts($base_url, $page_name);
	}

	/**
	 * Get JS variables
	 *
	 * Override this
	 *
	 * @return array
	 */
	protected function getJsVars(){
		$vars = array(
			'endpoint' => admin_url('admin-ajax.php'),
			'actions' => array(),
		);
		foreach( $this->methods as $method => $private ){
			$method = preg_replace('/\Aajax_(nopriv_)?/', '', $this->string->decamelize($method));
			if( !isset($vars['actions'][$method]) ){
				$vars['actions'][$method] = $method;
			}
		}
		return $vars;
	}

	/**
	 * Executed after script enqueued
	 *
	 * @param string $base_url
	 * @param string $page_name
	 */
	protected function extraScripts($base_url, $page_name = ''){
		// Do nothing. Override this if required.
	}

	/**
	 * Register Ajax
	 */
	public function adminInit(){
		if( defined('DOING_AJAX') && DOING_AJAX ){
			// Fires only on Ajax
			foreach( $this->methods as $method => $is_private ){
				$key = 'wp_'.$this->string->decamelize($method);
				add_action($key, array($this, $method));
			}
		}
	}

	/**
	 * Create nonce
	 *
	 * @return string
	 */
	protected function create_nonce(){
		return wp_create_nonce($this->nonce);
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
