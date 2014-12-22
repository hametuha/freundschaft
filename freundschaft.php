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
