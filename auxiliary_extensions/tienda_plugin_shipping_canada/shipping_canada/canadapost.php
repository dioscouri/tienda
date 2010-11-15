<?php
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

require_once( dirname( __FILE__ ).DS.'minixml'.DS.'classes'.DS."doc.inc.php" );
Class CanadaPost {

	var $debug = false ;

	var $server = "sellonline.canadapost.ca";
	var $port = 30000;
	var $merchant_cpcid = "CPC_DEMO_XML";

	var $error = false;
	var $err_msg = "";
	var $xml_request = "";
	var $xml_response = "";
	var $fp;  // socket handle

	var $xml_response_tree = array();
	var $shipping_methods = array();
	var $shipping_comment = "" ;

	var $to_city = "";
	var $to_provState = "";
	var $to_country = "";
	var $to_postal_code = "" ;
	 

	function CanadaPost($key) {
		//		if( defined(CP_SERVER) ) $this->server = CP_SERVER ;
		//		if( defined( CP_PORT ) ) $this->port = CP_PORT ;
		//		if( defined( MERCHANT_CPCID ) ) $this->merchant_cpcid = MERCHANT_CPCID ;
		if(!empty($key)) $this->merchant_cpcid = $key ;
		$this->_initRequestXML();
	}


	function addItem( $quantity, $weight, $length, $width, $height, $description )	{
		$this->xml_request .=
"
		<item>
			<quantity>" . htmlspecialchars($quantity) . "</quantity>
			<weight>" . htmlspecialchars($weight) . "</weight>
			<length>" . htmlspecialchars($length) . "</length>
			<width>" . htmlspecialchars($width) . "</width>
			<height>" . htmlspecialchars($height) . "</height>
			<description>" . htmlspecialchars($description) . "</description>
			<readyToShip />
		</item>
";
	}

	function	getQuote( $city, $provstate, $country, $postal_code ){
		$this->_shipTo( $city, $provstate, $country, $postal_code ) ;
		$this->_sendRequestXML();
		$this->_getResponseXML(); 
		$this->_xmlToQuote() ;
	}

	function 	_initRequestXML(){
		$this->xml_request =
"<?xml version=\"1.0\" ?>
<eparcel>
	<language>en</language>
	<ratesAndServicesRequest>
		<merchantCPCID>" . $this->merchant_cpcid . "</merchantCPCID>
		<lineItems>" ;
		//					<itemsPrice>" . $p->price * $qty . "</itemsPrice>
	}


	// if no Postal Code input, Canada Post will return statusCode 5000 and statusMessage "XML parsing error ".
	function  _shipTo( $city, $provstate, $country, $postal_code ){
		$this->to_city = $city ;
		$this->to_provState = $provstate;
		$this->to_country = $country ;
		$this->to_postal_code = $postal_code ;

		$this->xml_request .=
"
		</lineItems>
"	.
		( strlen($this->to_city) > 0  ? "<city>" . htmlspecialchars($this->to_city) . "</city>\n" : "" ) .
		( strlen($this->to_provState) > 0  ? "		<provOrState>" . htmlspecialchars($this->to_provState) . "</provOrState>\n" : "		<provOrState> </provOrState>\n" ) .
		( strlen($this->to_country) > 0  ? "		<country>" . htmlspecialchars($this->to_country) . "</country>\n" : "" ) .
		( strlen($this->to_postal_code) > 0  ? "		<postalCode>" . htmlspecialchars($this->to_postal_code) . "</postalCode>\n" : "		<postalCode> </postalCode>\n" ) .
"
	</ratesAndServicesRequest>
</eparcel>
" ;
	}

	function	_sendRequestXML(){
		$this->fp = fsockopen ( $this->server, $this->port, $errno, $errstr, 30 );
		if (!$this->fp) {
			die("Open Socket Error: $errstr ($errno)<br>\n");
			$this->error = true ;
			$this->error_msg = $errstr ;
		} else
		fwrite( $this->fp, $this->xml_request );
	}

	function	_getResponseXML(){
		if (!$this->fp) return ;
		while(!feof ($this->fp))
		$this->xml_response .= fgets( $this->fp, 4096 );
		fclose($this->fp);
	}

	function	_xmlToQuote(){
		$startTag = 'eparcel/ratesAndServicesResponse/' ;
		$xd = new MiniXMLDoc( $this->xml_response );
		$this->statusCode = $this->fetchValue( $xd, $startTag . 'statusCode' ) ;
		$this->statusMessage = $this->fetchValue( $xd, $startTag . 'statusMessage' ) ;

		$this->error = ( 'OK' == $this->statusCode ) ;
		$this->error_msg = $this->error ? $this->statusMessage : "" ;

		if( ! $this->error ) {
			$this->shipping_comment = $this->fetchValue( $xd, $startTag . 'comment' )  ;
			$shipping_fields = array( "name", "rate", "shippingDate", "deliveryDate", "deliveryDayOfWeek",  "nextDayAM", "packingID");
			$this->shipping_methods = $this->fetchArray( $xd, $startTag, 'product', $shipping_fields ) ;
		}
	}
	
	/*
	 * 
	 */
	function fetchValue( &$xmldoc, $path ){
		$e = $xmldoc->getElementByPath( $path );
		return $e->getValue();
	}
	
	/*
	 * 
	 */
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
	
	
}
?>