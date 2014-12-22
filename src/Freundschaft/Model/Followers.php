<?php

namespace Freundschaft\Model;

use Freundschaft\Pattern\Singleton;


/**
 * Followers Model
 *
 * @package Freundschaft\Model
 * @property-read \wpdb $db
 * @property-read string $table
 */
class Followers extends Singleton
{
	/**
	 * Get following status of
	 *
	 * @param int $follower_id
	 * @param array $user_ids
	 *
	 * @return array
	 */
	public function getFollowStatus($follower_id, $user_ids = array()){
		// Convert all ids to int
		$user_ids = array_map('intval', (array)$user_ids);
		if( empty($user_ids) ){
			return array();
		}
		// Make Query
		$where_in = implode(', ', $user_ids);
		$query = <<<SQL
			SELECT user_id FROM {$this->table}
			WHERE  follower_id = %d
			  AND  user_id in ({$where_in})
SQL;
		// Get result as array
		$result = $this->db->get_col($this->db->prepare($query, $follower_id));
		// Make return array['user_id' => true|false]
		$return = array();
		foreach( $user_ids as $user_id){
			if( !isset($return[$user_id]) ){
				// If user id found in $result, it means following.
				$return[$user_id] = false !== array_search($user_id, $result);
			}
		}
		return $return;
	}

	/**
	 * Follow
	 *
	 * @param int $follower_id
	 * @param int $user_id
	 *
	 * @return bool
	 */
	public function follow($follower_id, $user_id){
		return (bool) $this->db->insert($this->table, array(
			'follower_id' => $follower_id,
			'user_id' => $user_id,
			'created' => current_time('mysql'),
		), array('%d', '%d', '%s'));
	}

	/**
	 * Unfollow
	 *
	 * @param int $follower_id
	 * @param $user_id
	 *
	 * @return bool
	 */
	public function unfollow($follower_id, $user_id){
		return (bool) $this->db->delete($this->table, array(
			'follower_id' => $follower_id,
			'user_id' => $user_id,
		), array('%d', '%d'));
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
				return $this->db->prefix.'followers';
				break;
			default:
				return null;
				break;
		}
	}

}
