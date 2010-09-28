<?php 
// Awful, but this is the only way....
define( '_JEXEC', 1 );
define( 'DS', DIRECTORY_SEPARATOR );
define( 'JPATH_BASE', '..'.DS.'..'.DS.'..'.DS.'..'.DS );

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
$url .= $path;

$PayID= $_POST["paymentid"];
$TransID=$_POST["tranid"];
$ResCode=$_POST["result"];
$AutCode=$_POST["auth"];
$PosDate=$_POST["postdate"];
$TrckID=$_POST["trackid"];
$udf1=$_POST["udf1"];

if (($ResCode =="CAPTURED") || ($ResCode =="APPROVED"))
{
	$error = "0";
}
else
{
	$error = "1";
}

echo "REDIRECT=";
echo $url;
echo "/index.php?option=com_tienda&view=checkout&task=confirmPayment&error=".$error."&orderpayment_type=payment_ctriv&PaymentID=".$PayID."&TransID=".$TransID."&TrackID=".$TrckID."&postdate=".$PosDate."&resultcode=".$ResCode."&auth=".$AutCode."&udf1=".$udf1."&ErrorText=".$_POST['ErrorText'];

?>