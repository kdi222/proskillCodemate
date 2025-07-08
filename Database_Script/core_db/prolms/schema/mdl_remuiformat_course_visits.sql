CREATE TABLE `mdl_remuiformat_course_visits` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `course` bigint(10) NOT NULL,
  `userid` bigint(10) NOT NULL,
  `cm` bigint(10) NOT NULL,
  `timevisited` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mdl_remucourvisi_couuse_uix` (`course`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='Record activity visit of course'