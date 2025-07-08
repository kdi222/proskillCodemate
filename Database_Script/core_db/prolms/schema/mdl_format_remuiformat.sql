CREATE TABLE `mdl_format_remuiformat` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `courseid` bigint(10) NOT NULL,
  `sectionid` bigint(10) NOT NULL DEFAULT 0,
  `activityid` bigint(10) NOT NULL DEFAULT 0,
  `layouttype` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_formremu_cousecactlay_uix` (`courseid`,`sectionid`,`activityid`,`layouttype`),
  KEY `mdl_formremu_cou_ix` (`courseid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='Edwiser Course Format'