-- -----------------------------------------------------
-- HOW TO USE THIS FILE:
-- Replace all instances of #_ with your prefix
-- In PHPMYADMIN or the equiv, run the entire SQL
-- -----------------------------------------------------

ALTER TABLE #__tienda_geozones DROP FOREIGN KEY `fk_geozonetype`;
ALTER TABLE #__tienda_taxrates DROP FOREIGN KEY `fk_TaxClass_TaxRates`;
ALTER TABLE #__tienda_taxrates DROP FOREIGN KEY `fk_geozones_taxrates`;
ALTER TABLE #__tienda_carts DROP FOREIGN KEY `fk_carts_products`;
ALTER TABLE #__tienda_orders DROP FOREIGN KEY `fk_OrderState_Order`;
ALTER TABLE #__tienda_orders DROP FOREIGN KEY `fk_currencies_orders`;
ALTER TABLE #__tienda_orderhistory DROP FOREIGN KEY `fk_OrderState_OrderHistory`;
ALTER TABLE #__tienda_orderhistory DROP FOREIGN KEY `fk_Orders_OrderHistory`;
ALTER TABLE #__tienda_products DROP FOREIGN KEY `fk_taxclasses_products`;
ALTER TABLE #__tienda_orderitems DROP FOREIGN KEY `fk_Order_OrderItem`;
ALTER TABLE #__tienda_orderitems DROP FOREIGN KEY `fk_Product_OrderItem`;
ALTER TABLE #__tienda_orderpayments DROP FOREIGN KEY `fk_Orders_OrderPayment`;
ALTER TABLE #__tienda_orderinfo DROP FOREIGN KEY `fk_Orders_OrderInfo`;
ALTER TABLE #__tienda_productcategoryxref DROP FOREIGN KEY `fk_Product_ProductCategory`;
ALTER TABLE #__tienda_productcategoryxref DROP FOREIGN KEY `fk_Category_ProductCategory`;
ALTER TABLE #__tienda_productdownloads DROP FOREIGN KEY `fk_Product_ProductDownload`;
ALTER TABLE #__tienda_productfiles DROP FOREIGN KEY `fk_Product_ProductFiles`;
ALTER TABLE #__tienda_productdownloadlogs DROP FOREIGN KEY `fk_ProductFile_ProductDownloadLog`;
ALTER TABLE #__tienda_productprices DROP FOREIGN KEY `fk_Product_ProductPrices`;
ALTER TABLE #__tienda_productrelations DROP FOREIGN KEY `fk_Product_ProductRelationsA`;
ALTER TABLE #__tienda_productrelations DROP FOREIGN KEY `fk_Product_ProductRelationsB`;
ALTER TABLE #__tienda_productreviews DROP FOREIGN KEY `fk_Product_ProductReview`;
ALTER TABLE #__tienda_productvotes DROP FOREIGN KEY `fk_Product_ProductVotes`;
ALTER TABLE #__tienda_shippingmethods DROP FOREIGN KEY `fk_taxclass_shippingmethods`;
ALTER TABLE #__tienda_shippingrates DROP FOREIGN KEY `fk_geozone_shippingrates`;
ALTER TABLE #__tienda_addresses DROP FOREIGN KEY `fk_addresses_countries`;
ALTER TABLE #__tienda_addresses DROP FOREIGN KEY `fk_zones_addresses`;
ALTER TABLE #__tienda_zones DROP FOREIGN KEY `fk_countries_zones`;
ALTER TABLE #__tienda_zonerelations DROP FOREIGN KEY `fk_geozone_zonerelations`;
ALTER TABLE #__tienda_zonerelations DROP FOREIGN KEY `fk_geozone_zones`;

alter table `#__tienda_addresses` ENGINE=MYISAM;
alter table `#__tienda_carts` ENGINE=MYISAM;
alter table `#__tienda_categories` ENGINE=MYISAM;
alter table `#__tienda_config` ENGINE=MYISAM;
alter table `#__tienda_countries` ENGINE=MYISAM;
alter table `#__tienda_currencies` ENGINE=MYISAM;
alter table `#__tienda_geozones` ENGINE=MYISAM;
alter table `#__tienda_geozonetypes` ENGINE=MYISAM;
alter table `#__tienda_manufacturers` ENGINE=MYISAM;
alter table `#__tienda_orderhistory` ENGINE=MYISAM;
alter table `#__tienda_orderinfo` ENGINE=MYISAM;
alter table `#__tienda_orderitems` ENGINE=MYISAM;
alter table `#__tienda_orderitemattributes` ENGINE=MYISAM;
alter table `#__tienda_orderpayments` ENGINE=MYISAM;
alter table `#__tienda_orders` ENGINE=MYISAM;
alter table `#__tienda_ordershippings` ENGINE=MYISAM;
alter table `#__tienda_orderstates` ENGINE=MYISAM;
alter table `#__tienda_ordertaxclasses` ENGINE=MYISAM;
alter table `#__tienda_ordertaxrates` ENGINE=MYISAM;
alter table `#__tienda_ordervendors` ENGINE=MYISAM;
alter table `#__tienda_productattributeoptions` ENGINE=MYISAM;
alter table `#__tienda_productattributes` ENGINE=MYISAM;
alter table `#__tienda_productcategoryxref` ENGINE=MYISAM;
alter table `#__tienda_productdownloadlogs` ENGINE=MYISAM;
alter table `#__tienda_productdownloads` ENGINE=MYISAM;
alter table `#__tienda_productfiles` ENGINE=MYISAM;
alter table `#__tienda_productprices` ENGINE=MYISAM;
alter table `#__tienda_productquantities` ENGINE=MYISAM;
alter table `#__tienda_productrelations` ENGINE=MYISAM;
alter table `#__tienda_productreviews` ENGINE=MYISAM;
alter table `#__tienda_products` ENGINE=MYISAM;
alter table `#__tienda_productvotes` ENGINE=MYISAM;
alter table `#__tienda_shippingmethods` ENGINE=MYISAM;
alter table `#__tienda_shippingrates` ENGINE=MYISAM;
alter table `#__tienda_taxclasses` ENGINE=MYISAM;
alter table `#__tienda_taxrates` ENGINE=MYISAM;
alter table `#__tienda_userinfo` ENGINE=MYISAM;
alter table `#__tienda_zonerelations` ENGINE=MYISAM;
alter table `#__tienda_zones` ENGINE=MYISAM;
