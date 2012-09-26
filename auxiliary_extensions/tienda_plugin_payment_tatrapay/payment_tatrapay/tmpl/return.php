<?php
define( 'DS', DIRECTORY_SEPARATOR );
define( 'JPATH_BASE', '../../../../' );

// Is the user using HTTPS?
$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
// Complete the URL
$url .= $_SERVER['HTTP_HOST'];
//list of dirs
$paths =  dirname($_SERVER['PHP_SELF']);
$dirs = explode("/", $paths);
// pops out the subfolders
for($i = 0; $i < 4; $i++)
	array_pop($dirs);

$path = implode("/", $dirs);
$url .= $path.'/index.php?option=com_tienda&view=checkout&paction=process&task=confirmPayment&orderpayment_type=payment_tatrapay&';

	$url_array = array();
	$url_array[]= 'VS='.$_REQUEST['VS'];
	$url_array[]= 'RES='.$_REQUEST['RES'];
	if( !empty( $_REQUEST['SS'] ) )
		$url_array[]= 'SS='.$_REQUEST['SS'];
	$url_array[]= 'SIGN='.$_REQUEST['SIGN'];
	
	
	Header('Location: '.$url.implode( '&', $url_array ));