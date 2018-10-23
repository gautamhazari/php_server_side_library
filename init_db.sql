CREATE DATABASE `homestead` ;
CREATE USER 'homestead'@'localhost' IDENTIFIED BY 'secret';
GRANT ALL PRIVILEGES ON *.* to 'homestead'@'localhost';

USE `homestead`;

CREATE TABLE `discovery` (
  `id` varchar(64) DEFAULT NULL,
  `value` varchar(4096) DEFAULT NULL
);

CREATE TABLE `discovery_cache` (
  `msisdn` varchar(64) DEFAULT NULL,
  `mcc` varchar(4) DEFAULT NULL,
  `mnc` varchar(4) DEFAULT NULL,
  `ip` varchar(64) DEFAULT NULL,
  `exp` varchar(64) DEFAULT NULL,
  `value` varchar(4096) DEFAULT NULL
);
 
CREATE TABLE `nonce` (
  `id` varchar(64) DEFAULT NULL,
  `value` varchar(4096) DEFAULT NULL
);
