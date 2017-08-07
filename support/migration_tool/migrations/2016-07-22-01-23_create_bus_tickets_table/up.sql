CREATE TABLE `bus_tickets` (
  `id_user` varchar(32) NOT NULL,
  `ticket` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `id_user_UNIQUE` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;