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
		add_action('wp_ajax_fs_follower_list', array($this, 'ajaxFollowerList'));
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
			return $this->getFollowersList('follower', true);
		}elseif( is_page($this->following) ){
			if( $this->models->followers->followingCount(get_current_user_id()) ){
				return $this->getFollowersList('following', true);
			}else{
				return $this->followNone();
			}
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
	 */
	public function ajaxFollowerList(){
		$offset = absint($this->input->get('offset'));
		$type = (string)$this->input->get('type');
		if( 'following' == $type ){
			$html = $this->getFollowersList('following', false, $offset);
		}else{
			$html = $this->getFollowersList('follower', false, $offset);
		}
		wp_send_json(array(
			'offset' => $offset + 1,
			'html' => $html,
		));
	}

	/**
	 * Get followers list
	 *
	 * @param string $type
	 * @param bool $with_more_button
	 * @return string
	 */
	protected function getFollowersList($type = 'follower', $with_more_button = false, $paged = 1){
		if( 'following' == $type ){
			$followers = $this->models->followers->getFollowings(get_current_user_id(), $paged);
		}else{
			$followers = $this->models->followers->getFollowers(get_current_user_id(), $paged);
		}
		// Get list
		$output = empty($followers) ? '' : $this->renderUserList($followers, 'follower');
		// If list is not empty and need more button,
		// append it.
		if( $output && $with_more_button ){
			$output .= $this->more_button($type);
		}
		// If $output is still empty, it means no followers.
		if( !$output ){
			$output = '<p class="no-followers">ユーザーは見つかりませんでした。</p>';
		}
		return $output;
	}

	/**
	 * Get more button
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	protected function more_button($type = 'follower'){
		$view = $this->getView('follow-more');
		$url = esc_url(admin_url('admin-ajax.php?action=fs_follower_list&type='.$type));
		ob_start();
		include $view;
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Get user list for lonely user
	 *
	 * @return string
	 */
	public function followNone(){
		return '誰もフォローしていません';
	}

}
