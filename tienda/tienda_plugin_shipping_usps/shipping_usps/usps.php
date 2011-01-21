<?php
/**
 * @package Tienda
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * 
 * Derivative of work by:
 * @author  Algirdas Varnagiris <algirdas@varnagiris.net>
 * @link    http://www.varnagiris.net
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

$options = array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' );
Tienda::load( 'TiendaUSPSXmlParser', 'shipping_usps.xmlparser', $options );

class TiendaUSPS extends JObject
{
    var $server = "";
    var $user = "";
    var $pass = "";
    var $service = "";
    var $dest_zip;
    var $orig_zip;
    var $pounds;
    var $ounces;
    var $container = "";
    var $size = "REGULAR";
    var $machinable = "false";
    var $country = "USA";
    var $fcmailtype = "PACKAGE";
    var $error = null;
    
    function setServer($server) {
        $this->server = $server;
    }

    function setUserName($user) {
        $this->user = $user;
    }

    function setPass($pass) {
        $this->pass = $pass;
    }

    function setService($service) {
        /* Must be: FirstClass, Express, Priority, or Parcel */
        $this->service = $service;
    }
    
    function setFCMailtype($fcmailtype) {
        /* Must be: LETTER, FLAT, or Parcel */
        $this->fcmailtype = $fcmailtype;
    }
    
    function setDestZip($sending_zip) {
        /* Must be 5 digit zip (No extension) */
        $this->dest_zip = $sending_zip;
    }

    function setOrigZip($orig_zip) {
        $this->orig_zip = $orig_zip;
    }

    function setWeight($pounds, $ounces=0) {
        /* Must weight less than 70 lbs. */
        $this->pounds = $pounds;
        $this->ounces = $ounces;
    }

    function setContainer($cont) {
        $this->container = $cont;
    }

    function setSize($size) {
        $this->size = $size;
    }

    function setMachinable($mach) {
        /* Required for Parcel Post only, set to True or False */
        $this->machinable = $mach;
    }
    
    function setCountry($country) {
        $this->country = $country;
    }
    
    function getPrice() {
        $countries = array(
            'USA',
            'United States',
            'United States Minor Outlying Islands'
        );
      
        if (in_array($this->country, $countries))
        {
            // may need to urlencode xml portion
            $str = $this->server. "?API=RateV4&XML=<RateV4Request%20USERID=\"";
            $str .= $this->user . "\"%20PASSWORD=\"" . $this->pass . "\"><Package%20ID=\"0\"><Service>";

            if (strtolower($this->service) == 'first class')
            {
                $str .= "ONLINE</Service>";
                $str .= "<FirstClassMailType>" . $this->fcmailtype . "</FirstClassMailType>";
                $myFirstClass=1;
            }
                else 
            {
                $str .= $this->service . "</Service>";
            }
            $str .= "<ZipOrigination>" . $this->orig_zip . "</ZipOrigination>";
            $str .= "<ZipDestination>" . $this->dest_zip . "</ZipDestination>";
            $str .= "<Pounds>" . $this->pounds . "</Pounds><Ounces>" . $this->ounces . "</Ounces>";
            $str .= "<Container>" . urlencode($this->container) . "</Container><Size>" . $this->size . "</Size>";
            $str .= "<Machinable>" . $this->machinable . "</Machinable></Package></RateV4Request>";
        }
        else 
        {
            $str = $this->server. "?API=IntlRate&XML=<IntlRateRequest%20USERID=\"";
            $str .= $this->user . "\"%20PASSWORD=\"" . $this->pass . "\"><Package%20ID=\"0\">";
            $str .= "<Pounds>" . $this->pounds . "</Pounds><Ounces>" . $this->ounces . "</Ounces>";
            $str .= "<MailType>Package</MailType><Country>".urlencode($this->country)."</Country></Package></IntlRateRequest>";
        }


		/* USPS TEST ALERT */
		/*echo "<script language=javascript>alert('".$str."')</script>";*/
		
		
        $ch = curl_init();
        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $str);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // grab URL and pass it to the browser
        $ats = curl_exec($ch);

        // close curl resource, and free up system resources
        curl_close($ch);
        $xmlParser = new TiendaUSPSXmlParser();
        $array = $xmlParser->GetXMLTree($ats);
//debug(222222, $array);
        //$xmlParser->printa($array);
        if (!empty($array['ERROR'])) { // If it is error
            $error = new TiendaUSPSError();
            $error->str = $str;
            $error->level = "1";
            $error->number = $array['ERROR'][0]['NUMBER'][0]['VALUE'];
            $error->source = $array['ERROR'][0]['SOURCE'][0]['VALUE'];
            $error->description = $array['ERROR'][0]['DESCRIPTION'][0]['VALUE'];
            $error->helpcontext = $array['ERROR'][0]['HELPCONTEXT'][0]['VALUE'];
            $error->helpfile = $array['ERROR'][0]['HELPFILE'][0]['VALUE'];
            $this->error = $error;
        } else if(!empty($array['RATEV4RESPONSE'][0]['PACKAGE'][0]['ERROR'])) {
            $error = new TiendaUSPSError();
            $error->str = $str;
            $error->level = "2";
            $error->number = $array['RATEV4RESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['NUMBER'][0]['VALUE'];
            $error->source = $array['RATEV4RESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['SOURCE'][0]['VALUE'];
            $error->description = $array['RATEV4RESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['DESCRIPTION'][0]['VALUE'];
            $error->helpcontext = $array['RATEV4RESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['HELPCONTEXT'][0]['VALUE'];
            $error->helpfile = $array['RATEV4RESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['HELPFILE'][0]['VALUE'];
            $this->error = $error;        
        } else if(!empty($array['INTLRATERESPONSE'][0]['PACKAGE'][0]['ERROR'])){ //if it is international shipping error
            $error = new TiendaUSPSError();
            $error->str = $str;
            $error->level = "3";
            $error->number = $array['INTLRATERESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['NUMBER'][0]['VALUE'];
            $error->source = $array['INTLRATERESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['SOURCE'][0]['VALUE'];
            $error->description = $array['INTLRATERESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['DESCRIPTION'][0]['VALUE'];
            $error->helpcontext = $array['INTLRATERESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['HELPCONTEXT'][0]['VALUE'];
            $error->helpfile = $array['INTLRATERESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['HELPFILE'][0]['VALUE'];
            $this->error = $error;
        } else if(!empty($array['RATEV4RESPONSE'])){ // if everything OK
            $this->zone = $array['RATEV4RESPONSE'][0]['PACKAGE'][0]['ZONE'][0]['VALUE'];
            foreach ($array['RATEV4RESPONSE'][0]['PACKAGE'][0]['POSTAGE'] as $value){
//debug(99999992, $value);
/*					$curMailSvc = $value['MAILSERVICE'][0]['VALUE'];
					echo "<script language=javascript>alert('".$curMailSvc."')</script>";*/

				if (empty($myFirstClass) || $value['MAILSERVICE'][0]['VALUE'] == "First-Class Mail&lt;sup&gt;&amp;reg;&lt;/sup&gt; Package"){					
                    $price = new TiendaUSPSPrice();
                    $price->mailservice = $value['MAILSERVICE'][0]['VALUE'];
                    $price->rate = $value['RATE'][0]['VALUE'];
                    $this->list[] = $price;                    
                }
            }
        } else if (!empty($array['INTLRATERESPONSE'][0]['PACKAGE'][0]['SERVICE'])) { // if it is international shipping and it is OK
            foreach($array['INTLRATERESPONSE'][0]['PACKAGE'][0]['SERVICE'] as $value) {
                $price = new TiendaUSPSIntPrice();
                $price->id = $value['ATTRIBUTES']['ID'];
                $price->pounds = $value['POUNDS'][0]['VALUE'];
                $price->ounces = $value['OUNCES'][0]['VALUE'];
                $price->mailtype = $value['MAILTYPE'][0]['VALUE'];
                $price->country = $value['COUNTRY'][0]['VALUE'];
                $price->rate = $value['POSTAGE'][0]['VALUE'];
                $price->svccommitments = $value['SVCCOMMITMENTS'][0]['VALUE'];
                $price->svcdescription = $value['SVCDESCRIPTION'][0]['VALUE'];
                $price->maxdimensions = $value['MAXDIMENSIONS'][0]['VALUE'];
                $price->maxweight = $value['MAXWEIGHT'][0]['VALUE'];
                $this->list[] = $price;
            }
        
        }
        
		
        		/* USPS TEST ALERT */
		//$myBad = $error->description;	
		//$myType =$price->mailservice;
		//$myCost =$price->rate;
				
				
		//echo "<script language=javascript>alert('".$myType." - ".$myCost."')</script>";
        return $this;
    }
}

class TiendaUSPSError extends JObject
{
    var $number;
    var $source;
    var $description;
    var $helpcontext;
    var $helpfile;
}

class TiendaUSPSPrice extends JObject
{
    var $mailservice;
    var $rate;
}

class TiendaUSPSIntPrice extends JObject
{
    var $id;
    var $rate;
}
?> 