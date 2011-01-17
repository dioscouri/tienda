<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

/**
 * Class to translate PHP Array element into XML and vice versa.
 * Adepted for Joomla & Tienda
 *
 * @author    Marco Vito Moscaritolo
 * @author	  Daniele Rosario
 * @copyright GPL 3
 * @tutorial  http://mavimo.org/varie/array_xml_php
 * @example   index.php
 * @version   0.8
 */

class TiendaArrayToXML {
  /**
   * @staticvar string - String to use as key for node attributes into array
   * @todo      Convert this into a value settable from user
   */
  const attr_arr_string = 'attributes';
  /**
   * The main function for converting to an XML document.
   * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
   *
   * @static
   * @param  array $data
   * @param  string $rootNodeName - what you want the root node to be - defaultsto data.
   * @param  SimpleXMLElement $xml - should only be used recursively
   * @return string XML
   */
  public static function toXml($data, $rootNodeName = 'data', &$xml = NULL, $root_attributes = null) {
    if (is_null($xml)) {
      $xml = new SimpleXMLElement('<' . $rootNodeName . ' '.$root_attributes.' />');
      
    }

    // loop through the data passed in.
    foreach($data as $key => $value) {
      // if numeric key, assume array of rootNodeName elements
      if (is_numeric($key)) {
        $key = $rootNodeName;
      }
      // Check if is attribute
      if($key == TiendaArrayToXML::attr_arr_string) {
        // Add attributes to node
        foreach($value as $attr_name => $attr_value) {
          $xml->addAttribute($attr_name, $attr_value);
        }
      } else {
        // delete any char not allowed in XML element names
        $key = preg_replace('/[^a-z0-9\-\_\.\]/i', '', $key);

        // if there is another array found recrusively call this function
        if (is_array($value)) {

          // create a new node unless this is an array of elements
          $node = TiendaArrayToXML::isAssoc($value) ? $xml->addChild($key) : $xml;

          // recrusive call - pass $key as the new rootNodeName
          TiendaArrayToXML::toXml($value, $key, $node);
        } else {
          // add single node.
          $value = htmlentities($value);
          $xml->addChild($key,$value);
        }
      }
    }
    // pass back as string. or simple xml object if you want!
    $dom = dom_import_simplexml($xml)->ownerDocument; 
	$dom->formatOutput = true; 
    return $dom->saveXML();
  }

  /**
   * The main function for converting to an array.
   * Pass in a XML document and this recrusively loops through and builds up an array.
   *
   * @static
   * @param  string $obj - XML document string (at start point)
   * @param  array  $arr - Array to generate
   * @return array - Array generated
   */
  public static function toArray( $obj, &$arr = NULL ) {
    if ( is_null( $arr ) )   $arr = array();
    if ( is_string( $obj ) ) $obj = new SimpleXMLElement( $obj );

    // Get attributes for current node and add to current array element
    $attributes = $obj->attributes();
    foreach ($attributes as $attrib => $value) {
      $arr[TiendaArrayToXML::attr_arr_string][$attrib] = (string)$value;
    }

    $children = $obj->children();
    $executed = FALSE;
    // Check all children of node
    foreach ($children as $elementName => $node) {
      // Check if there are multiple node with the same key and generate a multiarray
      if($arr[$elementName] != NULL) {
        if($arr[$elementName][0] !== NULL) {
          $i = count($arr[$elementName]);
          TiendaArrayToXML::toArray($node, $arr[$elementName][$i]);
        } else {
          $tmp = $arr[$elementName];
          $arr[$elementName] = array();
          $arr[$elementName][0] = $tmp;
          $i = count($arr[$elementName]);
          TiendaArrayToXML::toArray($node, $arr[$elementName][$i]);
        }
      } else {
        $arr[$elementName] = array();
        TiendaArrayToXML::toArray($node, $arr[$elementName]);
      }
      $executed = TRUE;
    }
    // Check if is already processed and if already contains attributes
    if(!$executed && $children->getName() == "" && !isset ($arr[TiendaArrayToXML::attr_arr_string])) {
      $arr = (String)$obj;
    }
    return $arr;
  }

  /**
   * Determine if a variable is an associative array
   *
   * @static
   * @param  array $obj - variable to analyze
   * @return boolean - info about variable is associative array or not
   */
  private static function isAssoc( $array ) {
    return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
  }
}