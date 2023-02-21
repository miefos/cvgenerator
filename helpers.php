<?php

use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\WebM;

function cv_generator_user_has_video($current_user_id_backup = null) {
	$current_user_id = wp_get_current_user()->ID;
	if (!$current_user_id)
		$current_user_id = $current_user_id_backup;

	$uniq_video_id = get_user_meta($current_user_id, 'cv_generator_media_folder_id', true);
	if (!$uniq_video_id) {
		return false;
	}

	return true;
}

function cv_generator_get_current_users_cv($current_user_id_backup = null) {
	$current_user_id = wp_get_current_user()->ID;
	if (!$current_user_id)
		$current_user_id = $current_user_id_backup;

	if ($current_user_id) {
		$users_cvs = get_posts([
			'post_type'     => 'cv',
			'author'        =>  $current_user_id,
			'post_status' => array('publish', 'pending', 'draft', 'future', 'private', 'inherit')
		]);

		return $users_cvs[0] ?? null; // should not be more than one
	} else {
		return null;
	}
}