CREATE TABLE `emails` (
  `email` varchar(80) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  UNIQUE KEY `email_UNIQUE` (`email`),
  KEY `user_in_bus_idx` (`user_id`),
  CONSTRAINT `user_in_bus` FOREIGN KEY (`user_id`) REFERENCES `bus_tickets` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;