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
 *
 * @param int $author_id 指定しなければ現在の投稿者のID
 */
function freundschaft_btn($author_id = null){
	$redirect_to = get_permalink();
	if( is_null($author_id) ){
		$author_id = get_the_author_meta('ID');
	}
	printf('<a class="fs-btn fs-disabled" data-author-id="%d" href="%s"><span>フォローする</span></a>', $author_id, wp_login_url($redirect_to));
}

/**
 * オートローダーを登録
 */
spl_autoload_register(function( $class_name ){
	$class_name = ltrim($class_name, '\\');
	if( 0 === strpos($class_name, 'Freundschaft\\') ){
		// 名前空間がFreundschaftだったら
		$path = __DIR__.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.
		        str_replace('\\', DIRECTORY_SEPARATOR, $class_name).'.php';
		if( file_exists($path) ){
			require $path;
		}
	}
});

/**
 * プラグイン読み込み完了後に実行
 */
add_action('plugins_loaded', function(){
	// Ajaxコントローラーを初期化
	Freundschaft\API\Ajax\Follow::getInstance();
	// フォロワーリストを初期化
	Freundschaft\Controllers\FollowerList::getInstance();
	// モデルを初期化
	Freundschaft\Models\Followers::getInstance();
	// WP-CLIのコマンドを登録
	if( defined('WP_CLI') && WP_CLI ){
		WP_CLI::add_command('follow', 'Freundschaft\\Commands\\Follow');
	}
});
