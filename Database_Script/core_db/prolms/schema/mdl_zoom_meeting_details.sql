CREATE TABLE `mdl_zoom_meeting_details` (
  `uuid` varchar(30) NOT NULL DEFAULT '',
  `meeting_id` bigint(15) NOT NULL,
  `end_time` bigint(12) NOT NULL,
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `start_time` bigint(12) NOT NULL,
  `duration` bigint(12) NOT NULL,
  `topic` varchar(300) NOT NULL DEFAULT '',
  `total_minutes` bigint(12) DEFAULT 0,
  `participants_count` smallint(4) DEFAULT 0,
  `zoomid` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_zoommeetdeta_uui_uix` (`uuid`),
  KEY `mdl_zoommeetdeta_zoo_ix` (`zoomid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='A queue for the Cron to add meeting report info to zoom_meet'