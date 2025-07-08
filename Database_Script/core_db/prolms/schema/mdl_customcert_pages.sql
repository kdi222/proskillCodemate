CREATE TABLE `mdl_customcert_pages` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `templateid` bigint(10) NOT NULL DEFAULT 0,
  `width` bigint(10) NOT NULL DEFAULT 0,
  `height` bigint(10) NOT NULL DEFAULT 0,
  `leftmargin` bigint(10) NOT NULL DEFAULT 0,
  `rightmargin` bigint(10) NOT NULL DEFAULT 0,
  `sequence` bigint(10) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_custpage_tem_ix` (`templateid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='Stores each page of a custom cert'