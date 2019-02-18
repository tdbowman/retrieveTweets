<?php
////////////////////////////////////////////
// PREPARED MYSQL STATEMENTS
////////////////////////////////////////////

// INSERT
// tweet (28)
if (!($insert_tweet = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`tweets`
(`created_at`, `id`, `id_str`, `text`, `source`, `truncated`, `in_reply_to_status_id`, `in_reply_to_status_id_str`, `in_reply_to_user_id`, `in_reply_to_user_id_str`, `in_reply_to_screen_name`, `user_id`, `geo`, `coordinates`, `place`, `quoted_status_id`, `quoted_status_id_str`, `is_quote_status`, `quoted_status`, `retweeted_status`, `quote_count`, `reply_count`,`retweet_count`, `favorite_count`, `entities`, `extended_entities`, `favorited`, `retweeted`, `possibly_sensitive`, `filter_level`, `lang`, `matching_rules`, `scopes`, `withheld_copyright`, `withheld_in_countries`, `withheld_scope`, `hashtags_count`, `symbols_count`, `urls_count`, `user_mentions_count`, `media_count`,`is_rt`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
if (!($insert_null_tweet = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`tweets`
(`id`) VALUES (?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// hashtag
if (!($insert_hashtags = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`tweet_hashtags`
(`text`, `indices`, `tweet_id`) VALUES (?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// media
if (!($insert_media = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`tweet_media`
(`sizes`, `media_url_https`, `expanded_url`, `id_str`, `url`, `id`, `type`, `indices`,  `display_url`, `media_url`, `tweet_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// user_mentions
if (!($insert_mentions = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`tweet_mentions`
(`screen_name`, `name`, `id`, `id_str`, `indices`, `tweet_id`) VALUES (?, ?, ?, ?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// symbols
if (!($insert_symbols = $mysqli->prepare("INSERT INTO ".$schema.".`tweet_symbols`
(`text`, `indices`, `tweet_id`) VALUES (?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// urls
if (!($insert_urls = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`tweet_urls`
(`url`, `expanded_url`, `display_url`, `indices`, `final_url`, `domain`, `tweet_id`) VALUES (?, ?, ?, ?, ?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}

////////////////////////////
// profile
////////////////////////////
if (!($insert_profile = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`profiles` (
`id`, `id_str`, `name`, `screen_name`, `location`, `url`, `description`, `derived`, `protected`, `verified`, `followers_count`, `friends_count`, `listed_count`, `favourites_count`, `statuses_count`, `created_at`, `utc_offset`, `time_zone`, `geo_enabled`, `lang`, `contributors_enabled`, `profile_background_color`, `profile_background_image_url`, `profile_background_image_url_https`, `profile_image_tile`, `profile_banner_url`, `profile_image_url`, `profile_image_url_https`, `profile_link_color`, `profile_sidebar_border_color`, `profile_sidebar_fill_color`, `profile_text_color`, `profile_use_background_image`, `default_profile`, `default_profile_image`, `withheld_in_countries`, `withheld_scope`,`is_translator`, `following`, `notifications`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}


////////////////////////////
// retweets
////////////////////////////
if (!($insert_retweet = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`retweets`
(`created_at`, `id`, `id_str`, `text`, `source`, `truncated`, `in_reply_to_status_id`, `in_reply_to_status_id_str`, `in_reply_to_user_id`, `in_reply_to_user_id_str`, `in_reply_to_screen_name`, `user_id`, `geo`, `coordinates`, `place`, `quoted_status_id`, `quoted_status_id_str`, `is_quote_status`, `quoted_status`, `retweeted_status`, `quote_count`, `reply_count`,`retweet_count`, `favorite_count`, `entities`, `extended_entities`, `favorited`, `retweeted`, `possibly_sensitive`, `filter_level`, `lang`, `matching_rules`, `scopes`, `withheld_copyright`, `withheld_in_countries`, `withheld_scope`, `hashtags_count`, `symbols_count`, `urls_count`, `user_mentions_count`, `media_count`,`is_rt`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// hashtag
if (!($insert_rthashtags = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`retweet_hashtags`
(`text`, `indices`, `retweet_id`) VALUES (?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// media
if (!($insert_rtmedia = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`retweet_media`
(`sizes`, `media_url_https`, `expanded_url`, `id_str`, `url`, `id`, `type`, `indices`,  `display_url`, `media_url`, `retweet_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// user_mentions
if (!($insert_rtmentions = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`retweet_mentions`
(`screen_name`, `name`, `id`, `id_str`, `indices`, `retweet_id`) VALUES (?, ?, ?, ?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// symbols
if (!($insert_rtsymbols = $mysqli->prepare("INSERT INTO ".$schema.".`retweet_symbols`
(`text`, `indices`, `retweet_id`) VALUES (?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
// urls
if (!($insert_rturls = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`retweet_urls`
(`url`, `expanded_url`, `display_url`, `indices`, `final_url`, `domain`, `retweet_id`) VALUES (?, ?, ?, ?, ?, ?, ?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}

if (!($insert_rtprofile = $mysqli->prepare("INSERT IGNORE INTO ".$schema.".`profiles` (
`id`, `id_str`, `name`, `screen_name`, `location`, `url`, `description`, `derived`, `protected`, `verified`, `followers_count`, `friends_count`, `listed_count`, `favourites_count`, `statuses_count`, `created_at`, `utc_offset`, `time_zone`, `geo_enabled`, `lang`, `contributors_enabled`, `profile_background_color`, `profile_background_image_url`, `profile_background_image_url_https`, `profile_image_tile`, `profile_banner_url`, `profile_image_url`, `profile_image_url_https`, `profile_link_color`, `profile_sidebar_border_color`, `profile_sidebar_fill_color`, `profile_text_color`, `profile_use_background_image`, `default_profile`, `default_profile_image`, `withheld_in_countries`, `withheld_scope`,`is_translator`, `following`, `notifications`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"))) {
	 $errors[]= "E".$e++." -> Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error . "<br/>";
}
