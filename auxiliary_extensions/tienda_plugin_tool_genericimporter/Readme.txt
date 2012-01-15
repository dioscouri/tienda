Importing Coupons - Using the Coupon Importer

Installation

Just use the Joomla installer to install the provided install file. Do NOT unzip the file first.


Creating the import file

We suggest using the sample import CSV file provided. Just delete the 2 data rows after you have taken a look at them to see how data should be entered.


File Structure / Field Notes

Here's the file structure for the import file. The file must be in standard CSV format.

Field		Type		Null	Comment	
coupon_name	varchar(64)		YEScoupon_code	varchar(64)		YES
coupon_type	tinyint(1)		NO	0=Per Order, 1=Per Productcoupon_group	varchar(32)		NO	price, tax, shippingcoupon_automatic	tinyint(1)		NO	0=User-Submitted, 1=Automatic
coupon_value	decimal(12,5)	YES
coupon_value_type	tinyint(1)		NO	0=Flat-rate, 1=Percentagecurrency_id		int(11)		YES	currency_id' ref from the _tienda_currencies table (def: 1=USD;2=JPY;3=EUR;4=GBP)
coupon_description	text		YES	coupon_params	text		NO	created_date	datetime		NO	GMT Only
modified_date	datetime		NO	GMT Only
start_date		datetime		NO	GMT Only	
expiration_date	datetime		YES	GMT Only
coupon_enabled	tinyint(1)		NO	
coupon_uses	int(11)		NO	Running count of the number of uses of this couponcoupon_max_uses	int(11)		NO	-1=Infinite (Default is -1)
coupon_max_uses_per_user	int(11)	NO	-1=Infinite (Default is -1)
NOTE: You can NOT include any reference to the specific products in the import if you choose the 'Per Product' coupon_type, you will need to manually set those relations after you import the coupon file.

ALSO: Be aware that when you export CSV files using Excel (and some other spreadsheets) your date fields may not be in a proper order to be imported. To be properly imported the date must be in the format of "YYYY-MM-DD HH:MM:SS". Excel,by default, will export your date as "MM/DD/YYYY HH:MM:SS". If you try to import a file with dates like this the coupon will have a blank date/time upon import.


Importing your coupon import file

Once you have your coupon import file ready to go, just follow these steps:
1. Open the Generic Importer - You can do this in one of two ways:
	a. Click the 'Generic Importer' toolbar button from the Tienda Dashboard
	b. Click Tools And Reports | Select Tools | Click on Tool-Generic Importer
2. Make sure that 'Coupon Import' is selected in the 'Choose type of import
3. Click the SUBMIT button in the toolbar
4. Use the Choose File button to locate your import file; make sure the separator is set to "," (a comma); check the 'Skip Frist Row" box
5. Click the SUBMIT button in the toolbar
6. Review the import information and if all is correct, click the SUBMIT button in the toolbar
7. Review the list of migration results, esp. noting any errors listed. If there are serious errors click the close button in the toolbar and correct your import file, otherwise click the SUBMIT button to apply the import to your site.
	NOTE: At the time of this document the importer will display a 'warning' error "TiendaTableCoupons does not support ordering". This is NOT a critical error and your coupons will import properly (as long as there are no other errors.
8. Click the Coupons menu to review the coupons you have imported. If you have any errors, just select the imported coupons, delete them, then correct your import file and repeat this process.

