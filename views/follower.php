<?php /** @var WP_User $user */ ?>
<div class="follower-list">
	<?php echo get_avatar($user->ID, 64); ?>
	<h3 class="follower-name">
		<?php echo esc_html($user->display_name) ?>
		<small class="from"><?php echo human_time_diff(strtotime($user->created, current_time('timestamp'))) ?>前</small>
	</h3>
	<div class="description">
		<?php echo wpautop(get_the_author_meta('description', $user->ID)) ?>
	</div>
</div>
