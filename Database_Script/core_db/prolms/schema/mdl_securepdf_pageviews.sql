CREATE TABLE `mdl_securepdf_pageviews` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `module` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `page` bigint(10) NOT NULL,
  `timecreated` bigint(10) NOT NULL DEFAULT 0,
  `timemodified` bigint(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `mdl_secupage_mod_ix` (`module`),
  KEY `mdl_secupage_use_ix` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='Table for saving users page view - for module completion'