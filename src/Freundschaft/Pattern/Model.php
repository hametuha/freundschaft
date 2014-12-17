<?php

namespace Freundschaft\Pattern;

/**
 * Abstract class of Model
 *
 * @package Freundschaft\Pattern
 * @property-read \wpdb $db
 * @property-read string $table
 * @method int query(string $query) Do $wpdb->query with prepared statement.
 * @method array get_results(string $query) Do $wpdb->get_result with prepared statement.
 * @method null|\stdClass get_row(string $query) Do $wpdb->get_result with prepared statement.
 * @method string get_var(string $query) Do $wpdb->get_var with prepared statement.
 * @method array get_col(string $query) Do $wpdb->get_col with prepared statement.
 */
abstract class Model extends Singleton
{

	/**
	 * @var string
	 */
	protected $name = '';

	/**
	 * @var array
	 */
	protected $columns = array();

	/**
	 * Column to auto insert current timestamp
	 *
	 * @var string
	 */
	protected $timestamp_on_create = '';

	/**
	 * Column name to auto update current timestamp
	 *
	 * @var string
	 */
	protected $timestamp_on_update = '';

	/**
	 * If true, this table will be unique on multi site
	 *
	 * @var bool
	 */
	protected $unique_on_multisite = false;

	/**
	 * Constructor
	 *
	 * @param array $settings
	 */
	protected function __construct( array $settings = array() ) {
		// Set table name
		if( empty($this->name) ){
			$name = explode('\\', get_called_class());
			$this->name = $this->decamelize($name[count($name) - 1]);
		}
	}

	/**
	 * Check model setting
	 *
	 * @return bool|\WP_Error
	 */
	public function testSetting(){
		$error = new \WP_Error();
		// Trigger error if $columns is empty
		if( empty($this->columns) ){
			$error->add(500, sprintf('Modelのサブクラス%sは必ず$columnsを設定しなければなりません。', get_called_class()));
		}else{
			$invalid_columns = array();
			foreach( $this->columns as $column => $place_holder ){
				if( false === array_search($place_holder, array('%s', '%d', '%f')) ){
					$invalid_columns[] = $column;
				}
			}
			if( !empty($invalid_columns) ){
				$error->add(500, sprintf('Modelのサブクラス%sは必ず$columnsを設定しなければなりません。', get_called_class()));
			}
		}
		// Check table name
		if( !preg_match('/\A[a-zA-Z_0-9]+\z/', $this->table) ){
			$error->add(500, sprintf('テーブル名%sには英数字とアンダースコア以外が含まれています。', $this->table));
		}
		return $error->get_error_messages() ? $error : true;
	}

	/**
	 * Insert data
	 *
	 * @param array $values
	 *
	 * @return false|int
	 */
	public function insert( array $values ){
		$data = $this->getValueAndWhere($values);
		if( !empty($this->timestamp_on_create) ){
			if( !isset($data['values'][$this->timestamp_on_create]) ){
				// Add current time to values
				$data['values'][$this->timestamp_on_create] = current_time('mysql');
				$data['wheres'][] = $this->columns[$this->timestamp_on_create];
			}
		}
		return $this->db->insert($this->table, $data['values'], $data['wheres']);
	}

	/**
	 * Update
	 *
	 * @param array $values
	 * @param array $where
	 *
	 * @return false|int
	 */
	public function update( array $values, array $where ){
		$data = $this->getValueAndWhere($values);
		if( !empty($this->timestamp_on_update) ){
			if( !isset($data['values'][$this->timestamp_on_update]) ){
				// Add current time to values
				$data['values'][$this->timestamp_on_update] = current_time('mysql');
				$data['wheres'][] = $this->columns[$this->timestamp_on_update];
			}
		}
		$wheres = $this->getValueAndWhere($where);
		return $this->db->update($this->table, $data['values'], $wheres['values'], $data['wheres'], $wheres['wheres']);
	}

	/**
	 * Delete
	 *
	 * @param array $where
	 *
	 * @return false|int
	 */
	public function delete( array $where ){
		$wheres = $this->getValueAndWhere($where);
		return $this->db->delete($this->table, $wheres['values'], $wheres['wheres']);
	}


	/**
	 * Get values and wheres
	 *
	 * @param array $data
	 *
	 * @return array ['values' => [], 'wheres' => []]
	 * @throws \InvalidArgumentException
	 */
	protected function getValueAndWhere( array $data ){
		if( empty($data) ){
			throw new \InvalidArgumentException('配列が空です。', 500);
		}
		$values = array();
		$wheres = array();
		foreach( $data as $column_name => $value ){
			// Throw exception if invalid columns name is past.
			if( !isset($this->columns[$column_name]) ){
				throw new \InvalidArgumentException(sprintf('存在しないカラム%sが指定されています。', $column_name), 500);
			}
			// Create wheres.
			$values[$column_name] = $value;
			$wheres[] = $this->columns[$column_name];
		}
		return compact('values', 'wheres');
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

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return null
	 */
	public function __get( $name ){
		switch( $name ){
			case 'db':
				global $wpdb;
				return $wpdb;
				break;
			case 'table':
				if( is_multisite() && $this->unique_on_multisite){
					return $this->db->base_prefix.$this->name;
				}else{
					return $this->db->prefix.$this->name;
				}
				break;
			default:
				return null;
				break;
		}
	}

	/**
	 * Method overload
	 *
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function __call($name, $arguments = array()){
		switch( $name ){
			case 'get_var':
			case 'get_result':
			case 'get_row':
			case 'get_col':
			case 'query':
				if( count($arguments) > 1 ){
					return call_user_func_array(array($this->db, $name), array(call_user_func_array(array($this->db, 'prepare'), $arguments)));
				}else{
					return call_user_func_array(array($this->db, $name), $arguments);
				}
				break;
			default:
				// Do nothing
				break;
		}
	}

}
