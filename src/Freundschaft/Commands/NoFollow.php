<?php

namespace Freundschaft\Commands;
use Freundschaft\Models\Followers;

/**
 * Make user follow
 *
 * @package Freundschaft\Commands
 */
class NoFollow extends \WP_CLI_Command
{

	/**
	 * Get users who follows none.
	 *
	 * ## OPTIONS
	 *
	 * This command has no option.
	 *
	 * ## EXAMPLES
	 *
	 *     wp lonely-users
	 *
	 * @synopsis
	 */
	public function __invoke( $args, $assoc_args ) {
		// モデルを取得。コードヒントが出るように
		/** @var Followers $followers */
		$followers = Followers::getInstance();
		$users = $followers->getLonelyUsers(10);
		if( $users ){
			foreach( $users as $user ){
				\WP_CLI::line(sprintf("%d\t%s", $user->ID, $user->display_name));
			}
			\WP_CLI::error(sprintf('At least, %d users are lonely...', count($users)));
		}else{
			\WP_CLI::success('Wow! None is lonely.');
		}
	}

}
