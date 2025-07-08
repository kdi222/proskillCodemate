CREATE TABLE `mdl_zoom_breakout_participants` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL,
  `breakoutroomid` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_zoombreapart_bre_ix` (`breakoutroomid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='A list of zoom meeting breakout rooms participants.'