<?php

//////////////////////////////////////////
// PREPARED MYSQL STATEMENTS
//////////////////////////////////////////
// user (41)
if (!($insert_user = $mysqli->prepare("INSERT IGNORE INTO `".$schema."`.`".$table."` (
`id`, `id_str`, `name`, `screen_name`, `location`, `description`, `url`, `entities`, `protected`, `followers_count`, `friends_count`, `listed_count`, `created_at`, `favourites_count`, `utc_offset`, `time_zone`, `geo_enabled`, `verified`, `statuses_count`, `lang`, `contributors_enabled`, `is_translator`, `is_translation_enabled`, `profile_background_color`, `profile_background_image_url`, `profile_background_image_url_https`, `profile_image_tile`, `profile_image_url`, `profile_image_url_https`, `profile_link_color`, `profile_sidebar_border_color`, `profile_sidebar_fill_color`, `profile_text_color`, `profile_use_background_image`, `default_profile`, `default_profile_image`, `following`, `follow_request_sent`, `notifications`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
