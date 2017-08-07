CREATE TABLE `phones` (
  `phone` varchar(10) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  UNIQUE KEY `phone_UNIQUE` (`phone`),
  KEY `user_idx` (`user_id`),
  CONSTRAINT `user` FOREIGN KEY (`user_id`) REFERENCES `bus_tickets` (`id_user`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;