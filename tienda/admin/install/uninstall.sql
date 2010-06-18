-- -----------------------------------------------------
-- HOW TO USE THIS FILE:
-- Replace all instances of #_ with your prefix
-- In PHPMYADMIN or the equiv, run the entire SQL
-- -----------------------------------------------------

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

drop table if exists `#__tienda_addresses`;
drop table if exists `#__tienda_carts`;
drop table if exists `#__tienda_categories`;
drop table if exists `#__tienda_config`;
drop table if exists `#__tienda_countries`;
drop table if exists `#__tienda_currencies`;
drop table if exists `#__tienda_geozones`;
drop table if exists `#__tienda_geozonetypes`;
drop table if exists `#__tienda_manufacturers`;
drop table if exists `#__tienda_orderhistory`;
drop table if exists `#__tienda_orderinfo`;
drop table if exists `#__tienda_orderitems`;
drop table if exists `#__tienda_orderitemattributes`;
drop table if exists `#__tienda_orderpayments`;
drop table if exists `#__tienda_orders`;
drop table if exists `#__tienda_orderstates`;
drop table if exists `#__tienda_ordervendors`;
drop table if exists `#__tienda_productattributeoptions`;
drop table if exists `#__tienda_productattributes`;
drop table if exists `#__tienda_productcategoryxref`;
drop table if exists `#__tienda_productdownloadlogs`;
drop table if exists `#__tienda_productdownloads`;
drop table if exists `#__tienda_productfiles`;
drop table if exists `#__tienda_productprices`;
drop table if exists `#__tienda_productquantities`;
drop table if exists `#__tienda_productrelations`;
drop table if exists `#__tienda_productreviews`;
drop table if exists `#__tienda_products`;
drop table if exists `#__tienda_productvotes`;
drop table if exists `#__tienda_shippingmethods`;
drop table if exists `#__tienda_shippingrates`;
drop table if exists `#__tienda_taxclasses`;
drop table if exists `#__tienda_taxrates`;
drop table if exists `#__tienda_userinfo`;
drop table if exists `#__tienda_zonerelations`;
drop table if exists `#__tienda_zones`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;