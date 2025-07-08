CREATE TABLE `mdl_zoom_meeting_recordings_view` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `recordingsid` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `viewed` tinyint(1) NOT NULL DEFAULT 0,
  `timemodified` bigint(12) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_zoommeetrecoview_use_ix` (`userid`),
  KEY `mdl_zoommeetrecoview_rec_ix` (`recordingsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='A list to track when users view Zoom meeting recordings.'