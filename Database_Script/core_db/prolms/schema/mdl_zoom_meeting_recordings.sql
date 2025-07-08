CREATE TABLE `mdl_zoom_meeting_recordings` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `zoomid` bigint(10) NOT NULL,
  `meetinguuid` varchar(30) NOT NULL DEFAULT '',
  `zoomrecordingid` varchar(36) NOT NULL DEFAULT '',
  `name` varchar(300) NOT NULL DEFAULT '',
  `externalurl` longtext NOT NULL,
  `passcode` varchar(30) DEFAULT NULL,
  `recordingtype` varchar(30) NOT NULL DEFAULT '',
  `recordingstart` bigint(12) NOT NULL,
  `showrecording` tinyint(1) NOT NULL DEFAULT 0,
  `timecreated` bigint(12) DEFAULT NULL,
  `timemodified` bigint(12) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_zoommeetreco_zoo_ix` (`zoomid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='A list of recording links for Zoom meeting activities.'