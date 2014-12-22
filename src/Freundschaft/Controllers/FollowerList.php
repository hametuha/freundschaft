<?php

namespace Freundschaft\Controllers;


use Freundschaft\Pattern\Controller;


class FollowerList extends Controller
{

	protected $followers = 'followers';

	protected $following = 'following';

	/**
	 * Constructor
	 *
	 * @param array $settings
	 */
	protected function __construct( array $settings = array() ) {
		add_filter('the_content', array($this, 'theContent'));
		add_action('wp_enqueue_scripts', array($this, 'enqueueAssets'));
	}

	/**
	 * Filter 'the_content'
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function theContent($content){
		if( is_page($this->followers) ){
			return $this->getFollowersList();
		}elseif( is_page($this->following) ){
			return $this->getFollowingList();
		}else{
			return $content;
		}
	}

	/**
	 * Enqueue assets
	 */
	public function enqueueAssets(){
		wp_enqueue_style('freundschaft-follower-list', plugin_dir_url((dirname(dirname(dirname(__FILE__))))).'assets/css/follower-list.css', array(), '1.0');
	}

	/**
	 * Get followers list
	 *
	 * @return string
	 */
	protected function getFollowersList(){
		$followers = $this->models->followers->getFollowers(get_current_user_id());
		if( empty($followers) ){
			return '<p class="error">ユーザーは見つかりませんでした。</p>';
		}else{
			return $this->renderUserList($followers, 'follower');
		}
	}

	/**
	 * Get followings list
	 *
	 * @return string
	 */
	protected function getFollowingList(){
		$followers = $this->models->followers->getFollowings(get_current_user_id());
		if( empty($followers) ){
			return '<p class="error">ユーザーは見つかりませんでした。</p>';
		}else{
			return $this->renderUserList($followers, 'follower');
		}
	}


}
