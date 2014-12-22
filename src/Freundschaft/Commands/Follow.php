<?php

namespace Freundschaft\Commands;
use Freundschaft\Models\Followers;

/**
 * Make user follow
 *
 * @package Freundschaft\Commands
 */
class Follow extends \WP_CLI_Command
{

	/**
	 * Make random follow-relationships of each users
	 *
	 * ## OPTIONS
	 *
	 * This command has no option.
	 *
	 * ## EXAMPLES
	 *
	 *     wp follow
	 *
	 * @synopsis
	 */
	public function __invoke( $args, $assoc_args ) {
		// モデルを取得。コードヒントが出るように
		/** @var Followers $followers */
		$followers = Followers::getInstance();
		// コマンド開始
		\WP_CLI::line('すべてのユーザーを取得します.....');
		// WP_User_Queryだと制限があるので、直接クエリを発行。
		$query = <<<SQL
			SELECT ID FROM {$followers->db->users}
			ORDER BY RAND()
SQL;
		// get_colでuser_idの配列を取得
		$user_ids = $followers->get_col($query);
		// 総人数
		$total = count($user_ids);
		\WP_CLI::line(sprintf('%d人のユーザーを取得しました', count($user_ids)));
		// まずは誰もフォローしない初心者ユーザーの人数(先頭の10%)
		$new_bee = floor($total / 10);
		// フォローしまくっている異常なユーザー（最後の10%）
		$crazy = count($user_ids) - $new_bee;
		// すべてのユーザーに対して処理
		foreach( $user_ids as $index => $user_id ){
			$followed = 0;
			if( $index < $new_bee ){
				// 初心者（誰もフォローしない）
			}else{
				if( $index >= $crazy ){
					// キチガイ（90%以上をフォロー）
					$amount = $total / 100 * rand(90, 100);
				}else{
					// 普通の人（5%〜30%をフォロー）
					$amount = $total / 100 * rand(5, 30);
				}
				// ユーザーIDのインデックスをランダムにする
				$keys = array_keys($user_ids);
				shuffle($keys);
				\WP_CLI::line('------------');
				foreach( $keys as $key ){
					$user_to_follow = $user_ids[$key];
					if( $user_to_follow == $user_id ){
						// 自分だったらスキップ
						continue;
					}
					// フォローする
					$followers->follow($user_id, $user_to_follow);
					$followed++;
					\WP_CLI::out('.');
					// 規定数に達したらループ脱出
					if( $followed >= $amount ){
						break;
					}
				}
				\WP_CLI::line('');
			}
			\WP_CLI::line(sprintf("ID:%d\tfollows\t%d", $user_id, $followed));
		}
		\WP_CLI::success('処理を完了しました');
	}

}
