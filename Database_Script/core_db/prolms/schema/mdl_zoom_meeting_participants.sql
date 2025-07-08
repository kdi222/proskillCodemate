CREATE TABLE `mdl_zoom_meeting_participants` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) DEFAULT NULL,
  `zoomuserid` varchar(35) NOT NULL DEFAULT '',
  `uuid` varchar(30) DEFAULT NULL,
  `user_email` longtext DEFAULT NULL,
  `join_time` bigint(12) NOT NULL,
  `leave_time` bigint(12) NOT NULL,
  `duration` bigint(12) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `detailsid` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_zoommeetpart_use_ix` (`userid`),
  KEY `mdl_zoommeetpart_uui_ix` (`uuid`),
  KEY `mdl_zoommeetpart_det_ix` (`detailsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='A list of each meeting existing on Moodle and when its parti'