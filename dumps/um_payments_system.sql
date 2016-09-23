/*
MySQL Data Transfer
Source Host: localhost
Source Database: um_payments_system
Target Host: localhost
Target Database: um_payments_system
Date: 23/09/2016 1:05:23 PM
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for payment_modes
-- ----------------------------
DROP TABLE IF EXISTS `payment_modes`;
CREATE TABLE `payment_modes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for payments
-- ----------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `personal_info_id` int(11) DEFAULT NULL,
  `amount` decimal(6,0) DEFAULT NULL,
  `payment_mode_id` int(11) DEFAULT NULL,
  `reference_number` varchar(250) DEFAULT NULL,
  `period_id` int(11) DEFAULT NULL,
  `fee_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for periods
-- ----------------------------
DROP TABLE IF EXISTS `periods`;
CREATE TABLE `periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_year` varchar(25) DEFAULT NULL,
  `semester` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `type` int(11) NOT NULL,
  `acquire_id` int(11) DEFAULT NULL,
  `department` varchar(255) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) NOT NULL,
  `hire_date` datetime NOT NULL,
  `designation` varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `payment_modes` VALUES ('1', 'Paypal');
INSERT INTO `payment_modes` VALUES ('2', 'Credit Card');
INSERT INTO `payment_modes` VALUES ('3', 'Others');
INSERT INTO `periods` VALUES ('1', '2016-2017', '1');
INSERT INTO `periods` VALUES ('2', '2016-2017', '2');
INSERT INTO `periods` VALUES ('3', '2017', '3');
INSERT INTO `users` VALUES ('9', 'lee.beronio@ringcentral.com', '$2y$10$20Cq1OPAstpU9k0ygT5nHu40NEUwLPhTSpj1iWso.t5fHCvd8CU2K', '2', '20152249', 'CS Tech', 'Technical Support', 'Beronio', 'Lee Van', 'Candelario', '0000-00-00 00:00:00', 'PHP Developer', '1', '2016-06-21 20:12:33', '2016-08-04 00:04:54');
