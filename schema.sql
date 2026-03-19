CREATE DATABASE IF NOT EXISTS `stationary` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `stationary`;

-- 1. Table: catagory
CREATE TABLE IF NOT EXISTS `catagory` (
  `cat_code` varchar(10) NOT NULL,
  `cat_desc` varchar(100) NOT NULL,
  PRIMARY KEY (`cat_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2. Table: product
CREATE TABLE IF NOT EXISTS `product` (
  `prod_id` varchar(10) NOT NULL,
  `cat_code` varchar(10) NOT NULL,
  `prod_name` varchar(100) NOT NULL,
  `prod_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `prod_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `prod_amount` int(11) NOT NULL DEFAULT 0,
  `prod_unit` varchar(20) NOT NULL,
  PRIMARY KEY (`prod_id`),
  FOREIGN KEY (`cat_code`) REFERENCES `catagory`(`cat_code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 3. Table: supplier
CREATE TABLE IF NOT EXISTS `supplier` (
  `sup_id` varchar(10) NOT NULL,
  `sup_desc` varchar(100) NOT NULL,
  `sup_address01` varchar(100) DEFAULT NULL,
  `sup_address02` varchar(100) DEFAULT NULL,
  `sup_address03` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`sup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 4. Table: customer
CREATE TABLE IF NOT EXISTS `customer` (
  `cus_id` varchar(10) NOT NULL,
  `cus_name` varchar(100) NOT NULL,
  `cus_address01` varchar(100) DEFAULT NULL,
  `cus_address02` varchar(100) DEFAULT NULL,
  `cus_address03` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`cus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insert default customer
INSERT IGNORE INTO `customer` (`cus_id`, `cus_name`, `cus_address01`, `cus_address02`, `cus_address03`, `phone`)
VALUES ('cus000', 'ลูกค้าทั่วไป', '', '', '', '');

-- 5. Table: sale
CREATE TABLE IF NOT EXISTS `sale` (
  `sale_id` varchar(10) NOT NULL,
  `cus_id` varchar(10) NOT NULL,
  `sale_date` datetime NOT NULL,
  PRIMARY KEY (`sale_id`),
  FOREIGN KEY (`cus_id`) REFERENCES `customer`(`cus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 6. Table: sale_detail
CREATE TABLE IF NOT EXISTS `sale_detail` (
  `sale_id` varchar(10) NOT NULL,
  `items` int(11) NOT NULL,
  `prod_id` varchar(10) NOT NULL,
  `sale_cost` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `sale_amount` int(11) NOT NULL,
  PRIMARY KEY (`sale_id`, `items`),
  FOREIGN KEY (`sale_id`) REFERENCES `sale`(`sale_id`) ON DELETE CASCADE,
  FOREIGN KEY (`prod_id`) REFERENCES `product`(`prod_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 7. Table: users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
