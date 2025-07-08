CREATE TABLE `mdl_zoom_meeting_tracking_fields` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `meeting_id` bigint(10) NOT NULL,
  `tracking_field` varchar(255) NOT NULL DEFAULT '',
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_zoommeettracfiel_mee_ix` (`meeting_id`),
  KEY `mdl_zoommeettracfiel_tra_ix` (`tracking_field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='A list of tracking field values for meetings in Zoom.'