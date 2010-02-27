-- -----------------------------------------------------
-- HOW TO USE THIS FILE:
-- Replace all instances of #_ with your prefix
-- In PHPMYADMIN or the equiv, run the entire SQL
-- -----------------------------------------------------

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

drop table `#__tienda_addresses`;
drop table `#__tienda_carts`;
drop table `#__tienda_categories`;
drop table `#__tienda_config`;
drop table `#__tienda_countries`;
drop table `#__tienda_currencies`;
drop table `#__tienda_geozones`;
drop table `#__tienda_geozonetypes`;
drop table `#__tienda_manufacturers`;
drop table `#__tienda_orderhistory`;
drop table `#__tienda_orderinfo`;
drop table `#__tienda_orderitems`;
drop table `#__tienda_orderitemattributes`;
drop table `#__tienda_orderpayments`;
drop table `#__tienda_orders`;
drop table `#__tienda_orderstates`;
drop table `#__tienda_ordervendors`;
drop table `#__tienda_productattributeoptions`;
drop table `#__tienda_productattributes`;
drop table `#__tienda_productcategoryxref`;
drop table `#__tienda_productdownloadlogs`;
drop table `#__tienda_productdownloads`;
drop table `#__tienda_productfiles`;
drop table `#__tienda_productprices`;
drop table `#__tienda_productquantities`;
drop table `#__tienda_productrelations`;
drop table `#__tienda_productreviews`;
drop table `#__tienda_products`;
drop table `#__tienda_productvotes`;
drop table `#__tienda_shippingmethods`;
drop table `#__tienda_shippingrates`;
drop table `#__tienda_taxclasses`;
drop table `#__tienda_taxrates`;
drop table `#__tienda_userinfo`;
drop table `#__tienda_zonerelations`;
drop table `#__tienda_zones`;