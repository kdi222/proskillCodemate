CREATE TABLE `mdl_customcert_issues` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `userid` bigint(10) NOT NULL DEFAULT 0,
  `customcertid` bigint(10) NOT NULL DEFAULT 0,
  `code` varchar(40) DEFAULT NULL,
  `emailed` tinyint(1) NOT NULL DEFAULT 0,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_custissu_cus_ix` (`customcertid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='Stores each issue of a customcert'