CREATE TABLE `mdl_xapi_states` (
  `id` bigint(10) NOT NULL AUTO_INCREMENT,
  `component` varchar(255) NOT NULL DEFAULT '',
  `userid` bigint(10) DEFAULT NULL,
  `itemid` bigint(10) NOT NULL,
  `stateid` varchar(255) NOT NULL DEFAULT '',
  `statedata` longtext DEFAULT NULL,
  `registration` varchar(255) DEFAULT NULL,
  `timecreated` bigint(10) NOT NULL,
  `timemodified` bigint(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mdl_xapistat_comite_ix` (`component`,`itemid`),
  KEY `mdl_xapistat_use_ix` (`userid`),
  KEY `mdl_xapistat_tim_ix` (`timemodified`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='The stored xAPI states'