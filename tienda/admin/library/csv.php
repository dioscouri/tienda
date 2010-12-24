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
defined('_JEXEC') or die('Restricted access');

class TiendaCSV extends JObject
{

	/*
	 * Parses content from a file into an array
	 * A field containing integer or fload doesnt need to be escaped in double-qoutes
	 * 
	 * @param $content 						String to be translated
	 * @param $fields 						Array of indexes fields which we want to process (an empty array means we want to process all fields)
	 * @param $num_fields 				Number of fields in a row (0 means that it'll be calculated from the first row -> header)
	 * @param $method 						Method to use to parse the data (1 - explode, 2 - our own (more complex and slower) method)
	 * @param $preserve_header 		Preserve header as a firt row of the result array
	 * @param $skip_first 				If first line of the content should be skipped (not parsed as a record)
	 * @param $rec_deliminer 			Delimier distinguishing records from each other (for method 2, if it's  it can be used also in field content)
	 * @param $field_deliminer 		Deliminer distinguishing fields in a record
	 * @param $clear_fields 			If we want to get rid of double quotes in string-containing fields
	 * @param $preserve_indexes 	If we want to have the same field indexes in result array as in the CSV file
	 * 
	 * @return Returns array of arrays representing records
	 */
	function toArray( $content, $fields = array(), $num_fields = 0, $method = 1, $preserve_header = false, $skip_first = true, $rec_deliminer = "\n", $field_deliminer = ",", $clear_fields = true, $preserve_indexes = true )
	{
		$result = array();
		switch($method)
		{
			case 1 : // explode method
				$result = TiendaCSV::toArrayExplode( $content, $fields, $num_fields, $preserve_header, $skip_first, $rec_deliminer, $field_deliminer, $clear_fields, $preserve_indexes );
				break;
			case 2 : // our own method
				$result = TiendaCSV::toArrayOur( $content, $fields, $num_fields, $preserve_header, $skip_first, $rec_deliminer, $field_deliminer, $clear_fields, $preserve_indexes );
				break;
		}
		return $result;
	}
	
	/*
	 * Parses content from a file into an array using explode function
	 * A field containing integer or fload doesnt need to be escaped in double-qoutes
	 * 
	 * @param $content 						String to be translated
	 * @param $num_fields 				Number of fields in a row (0 means that it'll be calculated from the first row -> header)
	 * @param $fields 						Array of indexes fields which we want to process (an empty array means we want to process all fields)
	 * @param $preserve_header 		Preserve header as a firt row of the result array
	 * @param $skip_first 				If first line of the content should be skipped (not parsed as a record)
	 * @param $rec_deliminer 			Delimier distinguishing records from each other (for method 2, if it's  it can be used also in field content)
	 * @param $field_deliminer 		Deliminer distinguishing fields in a record
	 * @param $clear_fields 			If we want to get rid of double quotes in string-containing fields
	 * @param $preserve_indexes 	If we want to have the same field indexes in result array as in the CSV file
	 * 
	 * @return Returns array of arrays representing records
	 */
	function toArrayExplode( $content, $fields = array(), $num_fields = 0, $preserve_header = false, $skip_first = true, $rec_deliminer = "\n", $field_deliminer = ",", $clear_fields = true, $preserve_indexes = true )
	{
		$result = array();
		$tmp = explode( $rec_deliminer, $content );
		
		if( !$tmp || ( !($c = count( $tmp )) ) ) // no results or a deliminer is empty => empty array
			return $result;

		if( !$num_fields ) // number of fields is not set => get it from header (firt line)
			$num_fields = count( explode( $field_deliminer, $tmp[0] ) );
			
		$c = count( $tmp ); // number of records
		if( $skip_first ) // skip first line
		{
			$tmp_head = array_shift( $tmp );
			if( $preserve_header ) // we want to preserve header
				$result[] = TiendaCSV::processFieldsToArray( $fields, explode( $field_deliminer, $tmp_head ), $clear_fields, $preserve_indexes );

			$c--; // adjust number of records
		}

		for( $i = 0; $i < $c; $i++ )
		{
			if( strlen( $tmp[$i] ) ) // process the line (skip all empty lines)
				$result[] = TiendaCSV::processFieldsToArray( $fields, explode( $field_deliminer, $tmp[$i] ), $clear_fields, $preserve_indexes );
		}
		return $result;
	}

	/*
	 * Parses content from a file into an array using our own function
	 * A field containing integer or fload doesnt need to be escaped in double-qoutes
	 * 
	 * @param $content 						String to be translated
	 * @param $num_fields 				Number of fields in a row (0 means that it'll be calculated from the first row -> header)
	 * @param $fields 						Array of indexes fields which we want to process (an empty array means we want to process all fields)
	 * @param $preserve_header 		Preserve header as a firt row of the result array
	 * @param $skip_first 				If first line of the content should be skipped (not parsed as a record)
	 * @param $rec_deliminer 			Delimier distinguishing records from each other (for method 2, if it's  it can be used also in field content)
	 * @param $field_deliminer 		Deliminer distinguishing fields in a record
	 * @param $clear_fields 			If we want to get rid of double quotes in string-containing fields
	 * @param $preserve_indexes 	If we want to have the same field indexes in result array as in the CSV file
	 * 
	 * @return Returns array of arrays representing records
	 */
	function toArrayOur( $content, $fields = array(), $num_fields = 0, $preserve_header = false, $skip_first = true, $rec_deliminer = "\n", $field_deliminer = ",", $clear_fields = true, $preserve_indexes = true )
	{
		$result = array();
		$tmp_lines = explode( $rec_deliminer, $content );
		
		if( !$tmp_lines || ( !($c = count( $tmp_lines )) ) ) // no results or a deliminer is empty => empty array
			return $result;
		
		if( !$num_fields ) // number of fields is not set => get it from header (firt line)
			$num_fields = count( explode( $field_deliminer, $tmp_lines[0] ) );
		$c = count( $tmp_lines ); // number of records

		if( $skip_first ) // skip first line
		{
			$tmp_head = array_shift( $tmp_lines );
			if( $preserve_header ) // we want to preserve header
				$result[] = TiendaCSV::processFieldsToArray( $fields, explode( $field_deliminer, $tmp_head ), $clear_fields, $preserve_indexes );

			$c--; // adjust number of records
		}

		$record = 0;
		for( $i = 0; $i < $c; $i++ )
		{
			if( !strlen( $tmp_lines[$i] ) ) // skip empty lines between records
				continue;
			
			$last_unclosed = false;
			$tmp_arr1 = array();
			$tmp_arr2 = array();
			$c_act = 0;
			while($i < $c)
			{
				$tmp_arr2 = explode( $field_deliminer, $tmp_lines[$i] );
				$c2 = count( $tmp_arr2 );
				$j = 0;
			
				if( $last_unclosed ) // last field of previous line was unclosed
				{
					// try to find a field with odd number of double quotes first
					$tmp = array();
					while( ( $j < $c2 ) && ( substr_count( $tmp_arr2[$j], '"') % 2 != 1 ) ) $tmp[] = $tmp_arr2[$j++];
					$tmp_arr1[$c_act] .= $rec_deliminer.implode($field_deliminer, $tmp); // add them to the last field of previous line
				}
			
				if( $j == $c2 ) // the last field is still open :(
				{
					$last_unclosed = true;
					$i++;
					continue; // continue to the next line
				}
				else if( $last_unclosed )// the last field was successfully closed so we can move to the next field
				{
					$c_act++;
					$last_unclosed = false;
					$j++;
				}
			
				while( $j < $c2 ) // go through rest of fields
				{
					if( $last_unclosed ) // if the last field was unclosed
					{
						$tmp = array();
						$tmp[] = $tmp_arr2[$j++]; // first in the field is the current part

						while( ( $j < $c2 ) && ( substr_count( $tmp_arr2[$j], '"') % 2 != 1 ) ) // find another unclosed field
							$tmp[] = $tmp_arr2[$j++];
						if($j < $c2) // if we  found the end -> save it
							$tmp[] = $tmp_arr2[$j];
					
						if( @strlen($tmp_arr1[$c_act]) ) // add this part to the rest of the current field
							$tmp_arr1[$c_act] .= $row_deliminer.implode($field_deliminer, $tmp); // add the result to the current field
						else
							$tmp_arr1[$c_act] = implode($field_deliminer, $tmp); // add the result to the current field
			
						if( $j == $c2 ) // if we havent find any unclosed field until the end of this line, continue to the next line
							continue;
						
						// we found another unclosed field and matched it with the first one
						$j++;
						$last_unclosed = false;
						$c_act++; // continue to the next field
					}
					else
					{
						if( substr_count( $tmp_arr2[$j], '"') % 2 == 1 ) // unclosed field => look for the end of this field
						{
							$last_unclosed = true;
							continue;
						} // closed field so mark that we work with a closed field
						else
							$last_unclosed = false;
				
						// closed field => just copy it
						@$tmp_arr1[$c_act++] = $tmp_arr2[$j++];
					}
				}
				
				if( $c_act == $num_fields ) // we finished the record so we're good to go to parse a new record
					break;
				
				$i++; // otherwise, start parsing another line
			}
			
			$result[] = TiendaCSV::processFieldsToArray( $fields, $tmp_arr1, $clear_fields, $preserve_indexes );
			$record++;
		}

		return $result;
	}

	/*
	 * Process fields while parsing CSV data:
	 * Cut out only fields we want to use
	 * Clear string-containing fields if we want so
	 * 
	 * @param $fields Array of indexes of fields we want to process (an empty array means all fields)
	 * @param $data Array of all fields
	 * @param $clear_fields If we want to clear string-contaning fields
	 * @param $preserve_indexes If we want to have the same field indexes in result array as in the CSV file
	 * 
	 * @return Array With cleaned up fields
	 */
	function processFieldsToArray( &$fields, $data, $clear_fields, $preserve_indexes )
	{
		$row = array();
		$c = count( $fields );
		$process_all = false; // process all
		if( !$c ) // array is empty
		{
			$c = count( $data );
			$process_all = true;// we want to process all fields
		}
		
		if( $process_all )
		{
			if( $clear_fields ) // clean out all fields
			{
				for($i = 0; $i < $c; $i++)
				{
					// cut off double quotation marks if there are any
					if( isset( $data[$i] ) && strlen( $data[$i ]) && ( $data[$i][0]	 == '"' ) )
						$row[$i] = substr( $data[$i], 1, strlen( $data[$i] )-2 );
					else // otherwise the value is float/integer
					{
						if( !isset( $data[$i] ) || !strlen($data[$i]) ) // the field is empty so empty it
							$row[$i] = '';
						else // otherwise it must be float or integer
						{
							$row[$i] = ( float )@$data[$i];
							if( !strcmp( ( int ) $row[$i], $row[$i] ) ) // the number is integer and not float
								$row[$i] = ( int )$row[$i];
						}
					}
				}
			}
			else // process all and we doesnt want to clear fields => return unchanged array
				return $data;
		}
		else // we process only part of the array
		{
			if( $clear_fields )
			{
				for($i = 0; $i < $c; $i++)
				{
						// cut off double quotation marks if there are any
						if( isset( $data[$fields[$i]] ) && strlen( $data[$fields[$i]] ) && ( $data[$fields[$i]][0]	 == '"' ) )
							$row[$fields[$i]] = substr( $data[$fields[$i]], 1, strlen( $data[$fields[$i]] )-2 );
						else // otherwise the value is float/integer
						{
							$idx = $preserve_indexes ? $fields[$i] : $i; // index in the result array 
							if( !isset( $data[$fields[$i]] ) || !strlen($data[$fields[$i]]) ) // the field is empty so empty it
								$row[$idx] = '';
							else // otherwise it must be float or integer
							{
								$row[$idx] = ( float )@$data[$idx];
								if( !strcmp( ( int ) $row[$i], $row[$i] ) ) // the number is integer and not float
									$row[$idx] = ( int )$row[$idx];
							}
						}
				}
			}
			else // copy only requested fields
			{
				for($i = 0; $i < $c; $i++)
					$row[$preserve_indexes ? $fields[$i] : $i] = @$data[$fields[$i]];
			}
		}
		return $row;
	}

	/*
	 * Creates a CSV string from an array
	 * 
	 * @param $content 					Array of records (in case it's not an array => return FALSE)
	 * @param $header 					Array of header fields
	 * @param $use_fields				Array of indexes of fields we want to export to CSV (an empty array means all fields)
	 * @param $field_deliminer 	Field deliminer
	 * @param $rec_deliminer 		Record deliminer
	 * @param $escaped 					Do you want to escape text in fields?
	 * 
	 * @return 									CSV string
	 */
	function fromArray($content, $header = array(), $use_fields = array(), $field_deliminer = ",", $rec_deliminer = "\n", $escaped = true )
	{
		if( !is_array( $content ) )
			return false;
			
		$result = '';
		if( count( $header ) ) // we want to export header too
			$result = TiendaCSV::processFieldsFromArray( $header, $use_fields, $field_deliminer, $escaped ).$rec_deliminer;
		
		for( $i = 0, $c = count( $content ); $i < $c; $i++ ) // export all other records
			$result.= TiendaCSV::processFieldsFromArray( $content[$i], $use_fields, $field_deliminer, $escaped ).$rec_deliminer;

		return $result;
	}
	
	/*
	 * Processes fields to export to CSV format
	 * 
	 * @param $fields 					Array of fields
	 * @param $use_fields				Array of indexes of fields we want to export to CSV (an empty array means all fields)
	 * @param $field_deliminer 	Field deliminer
	 * @param $escaped 					Do you want to escape text in fields?
	 * 
	 * @return 									CSV string
	 */
	function processFieldsFromArray( &$fields, $use_fields = array(), $field_deliminer = ',', $escaped = true )
	{
		$result = '';
		
		$c = count($use_fields);
		if( $c ) // we specified fields to export
		{
			// get only wanted fields
			$wanted = array();
			for( $i = 0; $i < $c; $i++ )
				$wanted[] = $fields[$use_fields[$i]];

			$fields = $wanted;
		}
		else // all fields are processed
			$c = count( $fields );
		
		$db = &JFactory::getDbo();
		// go through all fields and process them
		for($i = 0; $i < $c; $i++ )
		{
			if( !isset( $fields[$i] ) || !strlen( $fields[$i] ) ) // first of all, check if this property isnt empty
			{
				$result .= $field_deliminer;
				continue; // go to another field
			}
			
			// second step -> check, if the content is float/integer
			if( !strcmp( $fields[$i], ( int )$fields[$i] ) ) // the content is integer?
			{
				$result .= $field_deliminer.( int )$fields[$i];
				continue; // go to another field
			}
			else
				if( !strcmp( $fields[$i], ( float )$fields[$i] ) ) // the content is float?
				{
					$result .= $field_deliminer.( float )$fields[$i];
					continue; // go to another field
				}
			// as the last possibility -> it's a string
			if( $escaped )
			{
				$result .= $field_deliminer.'"'.$db->getEscaped( $fields[$i] ).'"'; // escape the field
			}
			else
				$result .= $field_deliminer.'"'.$fields[$i].'"';
		}
		return substr($result, strlen( $field_deliminer ) ); // cut off the first deliminer
	}
}
?>