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


	protected function __construct( array $settings = array() ) {
		parent::__construct( $settings );
		add_action('delete_user', array($this, 'deleteUser'));
	}


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

	/**
	 * Get follower count
	 *
	 * @param int $user_id
	 *
	 * @return int
	 */
	public function followerCount($user_id){
		$query = <<<SQL
			SELECT COUNT(follower_id) FROM {$this->table}
			WHERE user_id = %d
SQL;
		return (int) $this->get_var($query, $user_id);
	}

	/**
	 * Get following count
	 *
	 * @param int $follower_id
	 *
	 * @return int
	 */
	public function followingCount($follower_id){
		$query = <<<SQL
			SELECT COUNT(user_id) FROM {$this->table}
			WHERE follower_id = %d
SQL;
		return (int) $this->get_var($query, $follower_id);
	}

	/**
	 * Get followers of specified user
	 *
	 * @param int $user_id
	 * @param int $paged
	 *
	 * @return array
	 */
	public function getFollowers($user_id, $paged = 1){
		// Get offset
		$offset = (max(1, $paged) - 1) * $this->posts_per_page;
		// Make query
		$query = <<<SQL
			SELECT u.*, f.created
			FROM {$this->table} as f
			INNER JOIN {$this->db->users} as u
			ON f.follower_id = u.ID
			WHERE f.user_id = %d
			ORDER BY f.created DESC
			LIMIT %d, %d
SQL;
		$return = array();
		$result = $this->get_results($query, $user_id, $offset, $this->posts_per_page);
		foreach( $result as $row ){
			$return[] = new \WP_User($row);
		}
		return $return;
	}

	/**
	 * Get followings
	 *
	 * @param int $user_id
	 * @param int $paged
	 *
	 * @return array
	 */
	public function getFollowings($user_id, $paged = 1){
		// Get offset
		$offset = (max(1, $paged) - 1) * $this->posts_per_page;
		// Make query
		$query = <<<SQL
			SELECT u.*, f.created
			FROM {$this->table} as f
			INNER JOIN {$this->db->users} as u
			ON f.user_id = u.ID
			WHERE f.follower_id = %d
			ORDER BY f.created DESC
			LIMIT %d, %d
SQL;
		$return = array();
		$result = $this->get_results($query, $user_id, $offset, $this->posts_per_page);
		foreach( $result as $row ){
			$return[] = new \WP_User($row);
		}
		return $return;
	}

	/**
	 * Get users who follows none
	 *
	 * @param int $limit
	 *
	 * @return array
	 */
	public function getLonelyUsers($limit = 10){
		$limit = absint($limit);
		$query = <<<SQL
			SELECT u.ID, u.user_login, u.display_name FROM {$this->db->users} AS u
			WHERE ID NOT IN (
				SELECT follower_id FROM {$this->table}
				GROUP BY follower_id
			)
			LIMIT %d
SQL;
		return $this->get_results($query, $limit);
	}

	/**
	 * Detect if 2 users follow each other
	 *
	 * @param int $user_id_1
	 * @param int $user_id_2
	 *
	 * @return bool
	 */
	public function followingEachOther($user_id_1, $user_id_2){
		$query = <<<SQL
			SELECT count(*) FROM {$this->table}
			WHERE (follower_id = %d AND user_id = %d)
			   OR (follower_id = %d AND user_id = %d)
SQL;
		return 2 === (int) $this->get_var($query, $user_id_1, $user_id_2, $user_id_2, $user_id_1);

	}

	/**
	 * Delete user record
	 *
	 * @param int $user_id
	 *
	 * @return int
	 */
	public function deleteUser($user_id){
		$following = (int) $this->delete(array('follower_id' => $user_id));
		$follower  = (int) $this->delete(array('user_id' => $user_id));
		return $follower + $following;
	}
}
