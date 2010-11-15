<?
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
function	common_header( $title = "" ){
	/* Can't open socket on sourceforge.net, I have to point the demo to my site */
	$host = getEnv("HTTP_HOST") == "canship.sourceforge.net" ? "http://www.allaboutweb.ca/sf/canship/" : "" ;
?>
<html>
<head>
	<title><? print ( $title ? "Canada Post Shipping Rate Calculator - A Free PHP Shipping Tool | $title" : "" ) ; ?></title>
	<meta http-equiv="Content-type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" type="text/css" href="main.css">
	<meta name="keywords" content="Shipping Calculator, Rate Calculator, Calculation, eParcel, Canada Post, PHP, Shipping Module, eBay, Merchant Tools, e-Commerce, Online Shopping, Online Transaction, Real time, Shipping Quote, XML, Web Service, Free Shipping, Shipping Quoter, Open Source, osCommerce, Shopping Cart, Vancouver, British Columbia, Shopping Basket, Package, Free Packaging, Width, Height, Weight, Length, International, Domestic,USA, Fedex, UPS,Expedited, Xpresspost,Priority Courier,Air Parcel, Surface, Sell Online, API, French, Shipping Rate ">
	<meta name="description" content="
	Canada Post Shipping Rate Calculator - A free PHP shipping calculator for all shipping purpose, including eBay merchants, ecommerce sites.
	This tool is developed by using API of Sell Online(tm) Shipping Module from Canada Post. Once you get a retail account from Canada Post,
	you can setup your shipping profile throught their backend.
	">
</head>

<body bgcolor="#ffffff"  marginheight="0" marginwidth="0" topmargin="0" leftmargin="0" bottommargin="0" rightmargin="0">
<table border="0" cellpadding="10" cellspacing="0" bgcolor="#ff0000" width="100%">
<tr valign="middle">
	<td nowrap><img src="pics/canadapost.gif" width="178" height="60" alt="" border="0"></td>
	<td width="100%"><font color="#ffffff" class="pageTitle">Free PHP Canada Post Shipping Rate Calculator</font></td>
</tr>
</table>
<table border="0" cellpadding="5" cellspacing="0" width="100%" >
<tr>
	<td nowrap>[ <a href="index.php">Home</a> ]</td>
	<td nowrap>[ <a href="<?= $host ?>ebay.demo.php">eBay Demo</a> ]</td>
	<td nowrap>[ <a href="<?= $host ?>developer.demo.php">Developer Demo</a> ]</td>
	<td nowrap>[ <a href="merchant_tool.php">Merchant Tool</a> ]</td>
	<td nowrap>[ <a href="download.php">Download</a> ]</td>
	<td nowrap>[ <a href="index.php#contact">Contact</a> ]</td>
	<td width="100%">&nbsp;</td>
</tr>
</table>
<br><br>

<table cellpadding="0" cellspacing="0" border="0"  width="100%">
	<tr>
			<td >
<!-- --------------- Begin: Main Content -------------------------------- -->
<?
}


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  - - - - - - - - - - - - - - - -	
function	common_footer()
{
?>
<!-- --------------- End: Main Content -------------------------------- -->
<br><br>
<table border="0" cellpadding="0" cellspacing="0" width="100%" >
	<tr><td bgcolor="#ff0000"><img src='pics/blank.gif' border=0 width=1 height=1></td></tr>
	<tr><td align="right"><?= COPYRIGHT?></td></tr>
	<tr><td ><a href="http://sourceforge.net" target="_blank"><img src="pics/logo/sourceforge.gif"  border="0" alt="SourceForge.net Logo" /></a></td></tr>
</table>

			</td>
	</tr>
</table>
<br><br>

</body>
</html>
<?
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
function	displayProductForm( &$product ){
?>
<table border="0" cellpadding="5" cellspacing="0">
 		<tr>
			<td align="right" nowrap width="140"><b>Description :</b></td>
			<td><input type="Text" name="description" class="inputField" value="<? print htmlspecialchars(strtoupper($product['description'])) ; ?>" > </td>
		</tr>
		<tr>
			<td align="right" nowrap><b>Length (cm) :</b></td>
			<td><input type="Text" name="length" class="inputField" value="<? print htmlspecialchars($product['length']); ?>" ></td>
		</tr>
		<tr>
			<td align="right" nowrap><b>Width (cm) :</b></td>
			<td><input type="Text" name="width" class="inputField" value="<? print htmlspecialchars($product['width']); ?>" ></td>
		</tr>
		<tr>
			<td align="right" nowrap><b>Height (cm) :</td>
			<td><input type="Text" name="height" class="inputField" value="<? print htmlspecialchars($product['height']); ?>" ></td>
		</tr>
		<tr>
			<td align="right" nowrap><b>Weight (kg) :</td>
			<td><input type="Text" name="weight" class="inputField" value="<? print htmlspecialchars($product['weight']) ; ?>" ></td>
		</tr>
</table>
<?
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
function	getProductArray(){
	// product info
	return  array( 
		'description' => submit( 'description' ) ? submit( 'description' ) : ' Demo Item to be shipped from Canada Post' ,
		'quantity' => intval(submit( 'quantity' )) ?  intval(submit( 'quantity' )) : 1 ,
		'length' => submit( 'length' ) ? submit( 'length' ) : 30 , // cm
		'width' => submit( 'width' ) ?  submit( 'width' ) : 30 ,
		'height' => submit( 'height' ) ? submit( 'height' ) : 30 , 
		'weight' => submit( 'weight' ) ? submit( 'weight' ) : 0.5  // Kg
	);
}

function	getUrlEncode( &$product ){
	$url = "" ;
	foreach( $product as $key => $value ){
		$url .= "&" . $key . "=" . urlencode($value) ;
	}
	return empty($url) ? "" : "<a href=\"http://www.allaboutweb.ca/sf/canship/canpost.calc.php?$url\" target='_blank'>Get Shipping Cost</a>";
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
function	demo(){
	print "<pre>\n" ;
	$xd = new MiniXMLDoc();
	$xd->fromFile( "canPos_return.xml") ;
	
	print "\nStatusCode = " . fetchValue( $xd, 'eparcel/ratesAndServicesResponse/statusCode' ) ;
	print "\ncomment = " . fetchValue( $xd, 'eparcel/ratesAndServicesResponse/comment' ) ;
	print "\nstatusMessage = " . fetchValue( $xd, 'eparcel/ratesAndServicesResponse/statusMessage' ) ;
	print "\n\n" ;
	
	$shipping_fields = array( "name", "rate", "shippingDate", "deliveryDate", "deliveryDayOfWeek",  "nextDayAM", "packingID");
	var_dump( fetchArray( $xd, 'eparcel/ratesAndServicesResponse', 'product', $shipping_fields ) ) ;
	print "\n</pre>" ;
}


// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
function	submit( $var ){
	global	$HTTP_POST_VARS, $HTTP_GET_VARS ;
	return ( strlen(trim($HTTP_POST_VARS[ $var ])) ) ? trim( $HTTP_POST_VARS[ $var ] ) : trim( $HTTP_GET_VARS[ $var ] );
} 

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
function	fetchArray( &$xmldoc, $path, $tag, $fields ){
	$response =& $xmldoc->getElementByPath( $path );
	if( ! is_object($response) ) return array() ;
	
	$children =& $response->getAllChildren();
	
	$count = 0 ;
	$array = array();
	for( $i = 0; $i < $response->numChildren(); $i++){
		if( $tag == $children[$i]->name() ){;
			foreach( $fields as $field ){
				$name = $children[$i]->getElement($field) ;
				$array[$count][$field] =$name->getValue();
			}
			$count ++ ;
		}
	}
	
	return $array ;
}	
	

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
function	fetchValue( &$xmldoc, $path ){
	$e = $xmldoc->getElementByPath( $path );
	return is_object($e) ? $e->getValue() : "";
}

?>