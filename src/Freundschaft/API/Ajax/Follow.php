<?php

namespace Freundschaft\API\Ajax;


use Freundschaft\Pattern\Ajax;

class Follow extends Ajax
{

	
	public function ajaxFsStatus(){
		$users = array();
		if( isset($_POST['author_ids']) && is_array($_POST['author_ids'])){
			$author_ids = array_unique($_POST['author_ids']);
			$result = \Freundschaft\Models\Followers::getInstance()->getFollowStatus(get_current_user_id(), $author_ids);
			$users = array();
			foreach( $result as $user_id => $bool ){
				$users['user_'.$user_id] = $bool;
			}
		}
		wp_send_json(array(
			'logged_in' => true,
			'users' => $users,
			'nonce' => wp_create_nonce('freundschaft'),
		));
	}

	public function ajaxNoprivFsStatus(){
		wp_send_json(array(
			'logged_in' => false,
			'users' => array(),
			'nonce' => '',
		));
	}

	public function ajaxFsFollow(){
		$json = array(
			'success' => false,
			'message' => '',
		);
		try{
			// nonceをチェック
			if( !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'freundschaft')){
				throw new \Exception('不正な遷移です。', 500);
			}
			// ユーザーIDをチェック
			if( !isset($_POST['user_id']) || !is_numeric($_POST['user_id']) ){
				throw new \Exception('ユーザーIDが指定されていません。', 500);
			}
			if( !\Freundschaft\Models\Followers::getInstance()->follow(get_current_user_id(), $_POST['user_id']) ){
				throw new \Exception('すでにフォローしています。', 500);
			}
			$json = array(
				'success' => true,
				'message' => 'フォローしました',
			);
		}catch ( Exception $e ){
			$json['message'] = $e->getMessage();
		}
		wp_send_json($json);
	}

	public function ajaxFsUnfollow(){
		$json = array(
			'success' => false,
			'message' => '',
		);
		try{
			// nonceをチェック
			if( !isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'freundschaft')){
				throw new \Exception('不正な遷移です。', 500);
			}
			// ユーザーIDをチェック
			if( !isset($_POST['user_id']) || !is_numeric($_POST['user_id']) ){
				throw new \Exception('ユーザーIDが指定されていません。', 500);
			}
			if( !\Freundschaft\Models\Followers::getInstance()->unfollow(get_current_user_id(), $_POST['user_id']) ){
				throw new \Exception('このユーザーをフォローしていません。', 500);
			}
			$json = array(
				'success' => true,
				'message' => 'フォローしました',
			);
		}catch ( \Exception $e ){
			$json['message'] = $e->getMessage();
		}
		wp_send_json($json);
	}

	/**
	 * Load CSS
	 *
	 * @param string $base_url
	 * @param string $page_name
	 */
	protected function extraScripts( $base_url, $page_name = '' ) {
		wp_enqueue_style('freundschaft', $base_url.'assets/css/freundschaft.css', array(), $this->version);
	}


}