CREATE TABLE `user_properties` (
  `username` varchar(45) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `info` longtext,
  `user_id` varchar(32) DEFAULT NULL,
  UNIQUE KEY `username_UNIQUE` (`username`),
  KEY `user_bus_foreign_idx` (`user_id`),
  CONSTRAINT `user_bus_foreign` FOREIGN KEY (`user_id`) REFERENCES `bus_tickets` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;