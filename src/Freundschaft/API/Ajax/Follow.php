<?php

namespace Freundschaft\API\Ajax;


use Freundschaft\Pattern\Ajax;


class Follow extends Ajax
{

	/**
	 * Current user's status
	 */
	public function ajaxFsStatus(){
		$users = array();
		$author_ids = $this->input->post('author_ids');
		if( is_array($author_ids)){
			$author_ids = array_unique($author_ids);
			$result = $this->models->followers->getFollowStatus(get_current_user_id(), $author_ids);
			$users = array();
			foreach( $result as $user_id => $bool ){
				$users['user_'.$user_id] = $bool;
			}
		}
		wp_send_json(array(
			'logged_in' => true,
			'users' => $users,
			'nonce' => $this->create_nonce(),
		));
	}

	/**
	 * Current user's status
	 */
	public function ajaxNoprivFsStatus(){
		wp_send_json(array(
			'logged_in' => false,
			'users' => array(),
			'nonce' => '',
		));
	}

	/**
	 * Follow action
	 */
	public function ajaxFsFollow(){
		$json = array(
			'success' => false,
			'message' => '',
		);
		try{
			// Check nonce
			if( !$this->input->verify_nonce($this->nonce) ){
				throw new \Exception('不正な遷移です。', 500);
			}
			// ユーザーIDをチェック
			if( !is_numeric($this->input->post('user_id')) ){
				throw new \Exception('ユーザーIDが指定されていません。', 500);
			}
			if( !$this->models->followers->follow(get_current_user_id(), $_POST['user_id']) ){
				throw new \Exception('すでにフォローしています。', 500);
			}
			/**
			 * freundschaft_follow
			 *
			 * @param int $follower_id
			 * @param int $user_id
			 */
			do_action('freundschaft_follow', get_current_user_id(), (int)$this->input->post('user_id'));
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
	 * Unfollow action
	 */
	public function ajaxFsUnfollow(){
		$json = array(
			'success' => false,
			'message' => '',
		);
		try{
			// nonceをチェック
			if( !$this->input->verify_nonce($this->nonce) ){
				throw new \Exception('不正な遷移です。', 500);
			}
			// ユーザーIDをチェック
			if( !is_numeric($this->input->post('user_id')) ){
				throw new \Exception('ユーザーIDが指定されていません。', 500);
			}
			if( !$this->models->followers->unfollow(get_current_user_id(), $_POST['user_id']) ){
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