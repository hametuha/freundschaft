<?php

namespace Freundschaft\Models;

use Freundschaft\Pattern\Model;


/**
 * Followers Model
 *
 * @package Freundschaft\Model
 */
class Followers extends Model
{


	protected $columns = array(
		'follower_id' => '%d',
		'user_id' => '%d',
		'created' => '%s',
	);

	protected $timestamp_on_create = 'created';

	/**
	 * Get following status of specified user ids
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
		$result = $this->get_col($query, $follower_id);
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
		return (bool) $this->insert(array(
			'follower_id' => $follower_id,
			'user_id' => $user_id,
		));
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
		return (bool) $this->delete(array(
			'follower_id' => $follower_id,
			'user_id' => $user_id,
		));
	}
}
