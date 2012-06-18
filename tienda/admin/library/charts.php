<?php

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

class TiendaCharts extends DSCCharts {
	
	/**
	 * renderGoogleChart function.
	 * 
	 * @access public
	 * @param mixed $data
	 * @param string $title. (default: 'A Tienda Google Chart')
	 * @param string $type. (default: 'Column')
	 * @param int $width. (default: 900)
	 * @param int $height. (default: 250)
	 * @return void
	 */
	public static function renderGoogleChart($data, $title='A Tienda Google Chart', $type='Column', $width=800, $height=250)
    {
       parent::renderGoogleChart($data, $title, $type, $width, $height);
    }	
}