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

/** Import library dependencies */
jimport('joomla.plugin.plugin');

class plgTiendaGenericExport extends JPlugin 
{   
    function plgTiendaGenericExport(& $subject, $config)
    {
        parent::__construct($subject, $config);
    }
    
    function onAfterDisplayAdminComponentTienda()
    {
        $name = 'revert'; 
        $text = 'Generic Export';
        $url = JRoute::_(JURI::getInstance()->toString().'&exportFormat=csv');
        $request = JRequest::get('request');
        
        if( isset( $request['option'] ) )
        	unset( $request['option'] );
        if( isset( $request['controller'] ) )
        	unset( $request['controller'] );
        if( isset( $request['boxchecked'] ) )
        	unset( $request['boxchecked'] );
        	
        
        $additional = 'exportParams='.base64_encode( json_encode( array( 'view' => $request['view'] )) );
        $url = 'index.php?option=com_tienda&task=doTask&element=genericexport&elementTask=export&exportFormat=csv&'.$additional;

        $bar = & JToolBar::getInstance('toolbar');
        $bar->prependButton( 'link', $name, $text, $url );
    }
    
    function objectToString( $obj, $root = false )
    {
    	$arr_record = array();
			$list_vars = get_object_vars( $obj );
			foreach( $list_vars as $key => $value ) // go through all variables
			{
				if( is_object( $value ) )
					$arr_record[] = $this->objectToString( $value );
				else
				{
					if( is_array( $value ) )
						@$value = implode( "\n", $value );
					
					if( $root )
						$arr_record[] = $value;
					else
						$arr_record[] = $key.'='.urlencode( $value );
				}
			}
			if( $root )
				return $arr_record;
				
			return implode( "\n", $arr_record );
    }
    
    function export()
    {
    	Tienda::load( 'TiendaCSV', 'library.csv' );
      $request = JRequest::get('request');
        
      //// load the plugins
      JPluginHelper::importPlugin( 'tienda' );
      JModel::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_tienda/models');
      $params = json_decode( base64_decode( $request['exportParams'] ) );
      $model = JModel::getInstance($params->view,'TiendaModel');
      $list = $model->getList();

      $arr = array();
      $header = array(); // header -> it'll be filled out when 
      $fill_header = true; // we need to fill header
      for( $i = 0, $c = count( $list ); $i < $c; $i++ )
      {
      	if( $fill_header ) // need to fill header yet ?
       	{
       		$list_vars = get_object_vars( $list[$i] );
       		foreach( $list_vars as $key => $value ) // go through all variables
       		{
       			if( $fill_header )
       				$header[] = $key;
         	}
         	$fill_header = false; // header is filled
       	}
       	$arr[] = $this->objectToString( $list[$i], true );
      }
      $f_name = 'tmp/'.$params->view.'_'.time().'.csv';
      $res = TiendaCSV::FromArrayToFile( $f_name, $arr, $header );
      
      $this->render_page( $f_name, $params->view );
    }
    
    function render_page( $f_name, $view )
    {
      // add 'Back' button
      $url = 'index.php?option=com_tienda&view=$'.$view;
      $bar = & JToolBar::getInstance('toolbar');
      $bar->prependButton( 'link', 'cancel', 'COM_TIENDA_BACK', $url );
      JToolBarHelper::title( JText::_('Generic export') );

      echo JText::_('The export is complete! You can download it');
      echo ' <a href="'.$f_name.'">'.JText::_('here').'</a>.';
    }
}
?>