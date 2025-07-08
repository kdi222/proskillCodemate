CREATE TABLE `mdl_local_reports_wkflo` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `type` varchar(127) NOT NULL DEFAULT '',
  `subtype` varchar(127) DEFAULT NULL,
  `userid` bigint(10) NOT NULL,
  `data` longtext DEFAULT NULL,
  `timemodified` bigint(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='workflow'