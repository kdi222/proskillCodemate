CREATE TABLE `mdl_zoom_meeting_breakout_rooms` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `zoomid` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_zoommeetbrearoom_zoo_ix` (`zoomid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='A list of zoom meeting breakout rooms.'