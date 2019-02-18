CREATE TABLE `altmetric_twitter_keys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `twitter_altmetric_id` bigint(11) NOT NULL,
  `posts:author:id_on_source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `posts:author:tweet_id` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `result` json DEFAULT NULL,
  `flag` tinyint(4) DEFAULT NULL,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `posts:author:tweet_id` (`posts:author:tweet_id`),
  KEY `flag` (`flag`),
  KEY `twitter_altmetric_id` (`twitter_altmetric_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `profiles` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id` bigint(20) DEFAULT NULL,
  `id_str` varchar(20) DEFAULT NULL,
  `name` text,
  `screen_name` text,
  `location` text,
  `url` text,
  `description` longtext,
  `derived` text,
  `protected` tinyint(1) DEFAULT NULL,
  `verified` tinyint(1) DEFAULT NULL,
  `followers_count` int(11) DEFAULT NULL,
  `friends_count` int(11) DEFAULT NULL,
  `listed_count` int(11) DEFAULT NULL,
  `favourites_count` int(11) DEFAULT NULL,
  `statuses_count` int(11) DEFAULT NULL,
  `created_at` date DEFAULT NULL,
  `utc_offset` text,
  `time_zone` text,
  `geo_enabled` tinyint(1) DEFAULT NULL,
  `lang` text,
  `contributors_enabled` tinyint(1) DEFAULT NULL,
  `profile_background_color` text,
  `profile_background_image_url` text,
  `profile_background_image_url_https` text,
  `profile_image_tile` tinyint(1) DEFAULT NULL,
  `profile_banner_url` text,
  `profile_image_url` text,
  `profile_image_url_https` text,
  `profile_link_color` text,
  `profile_sidebar_border_color` text,
  `profile_sidebar_fill_color` text,
  `profile_text_color` text,
  `profile_use_background_image` tinyint(1) DEFAULT NULL,
  `default_profile` tinyint(1) DEFAULT NULL,
  `default_profile_image` tinyint(1) DEFAULT NULL,
  `withheld_in_countries` text,
  `withheld_scope` text,
  `is_translator` tinyint(1) DEFAULT NULL,
  `following` tinyint(1) DEFAULT NULL,
  `notifications` tinyint(1) DEFAULT NULL,
  `flag` tinyint(1) DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_scholar` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `retweet_hashtags` (
  `hid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `text` varchar(150) DEFAULT NULL,
  `text_clean` varchar(150) DEFAULT NULL,
  `indices` text,
  `hashtag_uid` int(11) DEFAULT NULL,
  `retweet_id` bigint(20) DEFAULT NULL,
  UNIQUE KEY `hid` (`hid`),
  KEY `retweetid` (`retweet_id`),
  KEY `text` (`text`),
  KEY `clean` (`text_clean`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `retweet_media` (
  `med_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sizes` longtext NOT NULL,
  `media_url_https` text NOT NULL,
  `expanded_url` text NOT NULL,
  `id_str` text NOT NULL,
  `url` text NOT NULL,
  `id` text NOT NULL,
  `type` text NOT NULL,
  `indices` text NOT NULL,
  `display_url` text NOT NULL,
  `media_url` text NOT NULL,
  `retweet_id` bigint(20) DEFAULT NULL,
  UNIQUE KEY `med_id` (`med_id`),
  KEY `retweet_id` (`retweet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `retweet_mentions` (
  `mid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `screen_name` varchar(20) DEFAULT NULL,
  `name` text,
  `id` bigint(20) DEFAULT NULL,
  `id_str` text,
  `indices` text,
  `retweet_id` bigint(20) DEFAULT NULL,
  UNIQUE KEY `mid` (`mid`),
  KEY `retweet_id` (`retweet_id`),
  KEY `sid` (`screen_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `retweet_symbols` (
  `sid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `text` text,
  `indices` text,
  `retweet_id` bigint(20) DEFAULT NULL,
  UNIQUE KEY `sid` (`sid`),
  KEY `retweet_id` (`retweet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `retweet_urls` (
  `pk` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` text,
  `expanded_url` text,
  `display_url` text,
  `indices` text,
  `final_url` text,
  `domain` varchar(65) DEFAULT NULL,
  `retweet_id` bigint(20) DEFAULT NULL,
  `domain_id` int(11) DEFAULT NULL,
  `resolved` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`pk`),
  UNIQUE KEY `uid` (`pk`),
  KEY `retweet_id` (`retweet_id`),
  KEY `urlid` (`domain_id`),
  KEY `did` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `retweets` (
  `tid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `id` bigint(20) NOT NULL,
  `id_str` varchar(20) CHARACTER SET utf8mb4 DEFAULT NULL,
  `text` text COLLATE utf8mb4_unicode_ci,
  `clean_text` text COLLATE utf8mb4_unicode_ci,
  `text_no_breaks` text COLLATE utf8mb4_unicode_ci,
  `source` mediumtext COLLATE utf8mb4_unicode_ci,
  `truncated` tinyint(1) DEFAULT NULL,
  `in_reply_to_status_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `in_reply_to_status_id_str` mediumtext COLLATE utf8mb4_unicode_ci,
  `in_reply_to_user_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `in_reply_to_user_id_str` mediumtext COLLATE utf8mb4_unicode_ci,
  `in_reply_to_screen_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `user_id` bigint(20) DEFAULT NULL,
  `geo` mediumtext COLLATE utf8mb4_unicode_ci,
  `coordinates` mediumtext COLLATE utf8mb4_unicode_ci,
  `place` mediumtext COLLATE utf8mb4_unicode_ci,
  `quoted_status_id` bigint(20) DEFAULT NULL,
  `quoted_status_id_str` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_quote_status` tinyint(4) DEFAULT NULL,
  `quoted_status` longtext COLLATE utf8mb4_unicode_ci,
  `retweeted_status` longtext COLLATE utf8mb4_unicode_ci,
  `quote_count` int(11) DEFAULT NULL,
  `reply_count` int(11) DEFAULT NULL,
  `retweet_count` int(11) DEFAULT NULL,
  `favorite_count` int(11) DEFAULT NULL,
  `entities` longtext COLLATE utf8mb4_unicode_ci,
  `extended_entities` longtext COLLATE utf8mb4_unicode_ci,
  `favorited` tinyint(1) DEFAULT NULL,
  `retweeted` tinyint(1) DEFAULT NULL,
  `possibly_sensitive` tinyint(1) DEFAULT NULL,
  `filter_level` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lang` mediumtext COLLATE utf8mb4_unicode_ci,
  `matching_rules` longtext COLLATE utf8mb4_unicode_ci,
  `scopes` longtext COLLATE utf8mb4_unicode_ci,
  `withheld_copyright` tinyint(4) DEFAULT NULL,
  `withheld_in_countries` longtext COLLATE utf8mb4_unicode_ci,
  `withheld_scope` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hashtags_count` int(11) DEFAULT NULL,
  `symbols_count` int(11) DEFAULT NULL,
  `urls_count` int(11) DEFAULT NULL,
  `user_mentions_count` int(11) DEFAULT NULL,
  `media_count` int(11) DEFAULT NULL,
  `is_rt` int(11) DEFAULT NULL,
  `_search` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `_scraper` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `tid` (`tid`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `id_str_UNIQUE` (`id_str`),
  KEY `idx_tweets_id` (`id`),
  KEY `idx_tweets_user_id` (`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `tweet_hashtags` (
  `hid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `text` varchar(150) DEFAULT NULL,
  `text_clean` varchar(150) DEFAULT NULL,
  `indices` text,
  `tweet_id` bigint(20) DEFAULT NULL,
  `hashtag_uid` int(11) DEFAULT NULL,
  UNIQUE KEY `hid` (`hid`),
  KEY `tweetid` (`tweet_id`),
  KEY `text` (`text`),
  KEY `clean` (`text_clean`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `tweet_media` (
  `med_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sizes` longtext NOT NULL,
  `media_url_https` text NOT NULL,
  `expanded_url` text NOT NULL,
  `id_str` text NOT NULL,
  `url` text NOT NULL,
  `id` text NOT NULL,
  `type` text NOT NULL,
  `indices` text NOT NULL,
  `display_url` text NOT NULL,
  `media_url` text NOT NULL,
  `tweet_id` bigint(20) DEFAULT NULL,
  `tid` int(11) DEFAULT NULL,
  UNIQUE KEY `med_id` (`med_id`),
  KEY `tweetid` (`tweet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `tweet_mentions` (
  `mid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `screen_name` varchar(20) DEFAULT NULL,
  `name` text,
  `id` bigint(20) DEFAULT NULL,
  `id_str` text,
  `indices` text,
  `tweet_id` bigint(20) DEFAULT NULL,
  UNIQUE KEY `mid` (`mid`),
  KEY `tweetid` (`tweet_id`),
  KEY `sid` (`screen_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `tweet_symbols` (
  `sid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `text` text,
  `indices` text,
  `tweet_id` bigint(20) DEFAULT NULL,
  `tid` int(11) DEFAULT NULL,
  UNIQUE KEY `sid` (`sid`),
  KEY `tweetid` (`tweet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `tweet_urls` (
  `pk` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` text,
  `expanded_url` text,
  `display_url` text,
  `indices` text,
  `final_url` text,
  `domain` varchar(65) DEFAULT NULL,
  `tweet_id` bigint(20) DEFAULT NULL,
  `domain_id` int(11) DEFAULT NULL,
  `resolved` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`pk`),
  UNIQUE KEY `uid` (`pk`),
  KEY `tweetid` (`tweet_id`),
  KEY `urlid` (`domain_id`),
  KEY `did` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `tweets` (
  `tid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT NULL,
  `id` bigint(20) NOT NULL,
  `id_str` varchar(20) CHARACTER SET utf8mb4 DEFAULT NULL,
  `text` text COLLATE utf8mb4_unicode_ci,
  `clean_text` text COLLATE utf8mb4_unicode_ci,
  `text_no_breaks` text COLLATE utf8mb4_unicode_ci,
  `source` mediumtext COLLATE utf8mb4_unicode_ci,
  `truncated` tinyint(1) DEFAULT NULL,
  `in_reply_to_status_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `in_reply_to_status_id_str` mediumtext COLLATE utf8mb4_unicode_ci,
  `in_reply_to_user_id` mediumtext COLLATE utf8mb4_unicode_ci,
  `in_reply_to_user_id_str` mediumtext COLLATE utf8mb4_unicode_ci,
  `in_reply_to_screen_name` mediumtext COLLATE utf8mb4_unicode_ci,
  `user_id` bigint(20) DEFAULT NULL,
  `geo` mediumtext COLLATE utf8mb4_unicode_ci,
  `coordinates` mediumtext COLLATE utf8mb4_unicode_ci,
  `place` mediumtext COLLATE utf8mb4_unicode_ci,
  `quoted_status_id` bigint(20) DEFAULT NULL,
  `quoted_status_id_str` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_quote_status` tinyint(4) DEFAULT NULL,
  `quoted_status` longtext COLLATE utf8mb4_unicode_ci,
  `retweeted_status` longtext COLLATE utf8mb4_unicode_ci,
  `quote_count` int(11) DEFAULT NULL,
  `reply_count` int(11) DEFAULT NULL,
  `retweet_count` int(11) DEFAULT NULL,
  `favorite_count` int(11) DEFAULT NULL,
  `entities` longtext COLLATE utf8mb4_unicode_ci,
  `extended_entities` longtext COLLATE utf8mb4_unicode_ci,
  `favorited` tinyint(1) DEFAULT NULL,
  `retweeted` tinyint(1) DEFAULT NULL,
  `possibly_sensitive` tinyint(1) DEFAULT NULL,
  `filter_level` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lang` mediumtext COLLATE utf8mb4_unicode_ci,
  `matching_rules` longtext COLLATE utf8mb4_unicode_ci,
  `scopes` longtext COLLATE utf8mb4_unicode_ci,
  `withheld_copyright` tinyint(4) DEFAULT NULL,
  `withheld_in_countries` longtext COLLATE utf8mb4_unicode_ci,
  `withheld_scope` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hashtags_count` int(11) DEFAULT NULL,
  `symbols_count` int(11) DEFAULT NULL,
  `urls_count` int(11) DEFAULT NULL,
  `user_mentions_count` int(11) DEFAULT NULL,
  `media_count` int(11) DEFAULT NULL,
  `is_rt` int(11) DEFAULT NULL,
  `_search` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `_scraper` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `tid` (`tid`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `id_str_UNIQUE` (`id_str`),
  KEY `idx_tweets_id` (`id`),
  KEY `idx_tweets_user_id` (`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;