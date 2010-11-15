<?
	/** ensure this file is being included by a parent file */
	defined('_JEXEC') or die('Restricted access');
	
	require( "minixml/minixml.inc.php" );

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
	
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
function	fetchArray( &$xmldoc, $path, $tag, $fields ){
	$response =& $xmldoc->getElementByPath( $path );
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
	return $e->getValue();
}

?>