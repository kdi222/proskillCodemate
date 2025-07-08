CREATE TABLE `mdl_zoom_breakout_groups` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `groupid` bigint(10) NOT NULL,
  `breakoutroomid` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_zoombreagrou_bre_ix` (`breakoutroomid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='A list of zoom meeting breakout rooms groups.'