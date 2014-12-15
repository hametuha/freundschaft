<?php
/*
Plugin Name: Freundschaft
Plugin URI: https://github.com/hametuha/freundschaft
Description: Following each other, then you are friends.
Author: Hametuha inc.
Version: 1.0
Author URI: http://hametuha.co.jp
License: GPL2 or Later
*/


/**
 * データベースの作成
 */
add_action('admin_init', function(){
	// Ajaxなら何もしない
	if( defined('DOING_AJAX') && DOING_AJAX ){
		return;
	}
	// このプラグインのいまのバージョン
	$current_version = '1.0';
	// データベースに保存されているバージョン
	$db_version = get_option('freundschaft_table_version', 0);
	if( version_compare($current_version, $db_version, '>') ){
		// データベースが古いので、テーブルを作成！
		// $wpdbを呼び出し
		global $wpdb;
		$table_name = "{$wpdb->prefix}followers";
		$query = <<<SQL
			CREATE TABLE {$table_name} (
			     follower_id BIGINT NOT NULL,
			     user_id BIGINT NOT NULL,
			     created DATETIME NOT NULL,
			     PRIMARY KEY (follower_id, user_id)
			) ENGINE=InnoDB CHARACTER SET UTF8;
SQL;
		// dbDeltaのためのファイルを読み込み
		require_once ABSPATH.'/wp-admin/includes/upgrade.php';
		dbDelta($query);
		// 現在のバージョンを保存
		update_option('freundschaft_table_version', $current_version);
		// 管理画面にメッセージを表示
		add_action('admin_notices', function() use ($table_name, $current_version){
			printf('<div class="updated"><p>%sを%sに更新しました。</p></div>', $table_name, $current_version);
		});
	}
});

/**
 * フォローボタンを出力する
 */
function freundschaft_btn(){
	$redirect_to = get_permalink();
	$author_id = get_the_author_meta('ID');
	printf('<a class="fs-btn fs-disabled" data-author-id="%d" href="%s"><span>フォローする</span></a>', $author_id, wp_login_url($redirect_to));
}

/**
 * Ajaxを実装
 */
add_action('admin_init', function(){
	if( defined('DOING_AJAX') && DOING_AJAX ){
		// Ajaxリクエストのときだけ実行
		add_action('wp_ajax_nopriv_fs_status', '_freundschaft_not_logged_in');
		add_action('wp_ajax_fs_status', '_freundschaft_logged_in');
	}
});

/**
 * ログイン済みユーザーのAjax
 */
function _freundschaft_logged_in(){
	$users = array();
	if( isset($_POST['author_ids']) && is_array($_POST['author_ids'])){
		foreach( array_unique($_POST['author_ids']) as $author_id ){
			if( is_numeric($author_id) ){
				// とりあえず全部true
				$users['user_'.$author_id] = true;
			}
		}
	}
	wp_send_json(array(
		'logged_in' => true,
		'users' => $users,
		'nonce' => wp_create_nonce('freundschaft'),
	));
}

/**
 * ログインしていないユーザーのAjax
 */
function _freundschaft_not_logged_in(){
	wp_send_json(array(
		'logged_in' => false,
		'users' => array(),
		'nonce' => '',
	));
}


/**
 * JSとCSSを読み込み
 */
add_action('wp_enqueue_scripts', function(){
	// assetsディレクトリのURLを取得
	$assets_url = plugin_dir_url(__FILE__).'assets';
	$asset_version = '1.0';
	// JSを読み込み。WP_DEBUGがtrueじゃなければ圧縮ファイル。
	// jQueryに依存するので、それを指定
	wp_enqueue_script('freundschaft', $assets_url.'/js/freundschaft'.(WP_DEBUG ? '' : '.min').'.js', array('jquery'), $asset_version);
	// JSに変数を渡す
	wp_localize_script('freundschaft', 'Freundschaft', array(
		'endpoint' => admin_url('admin-ajax.php'),
		'action' => 'fs_status',
	));
	// CSSを読み込み。
	wp_enqueue_style('freundschaft', $assets_url.'/css/freundschaft.css', array(), $asset_version);
});
