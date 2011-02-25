<?php
/**
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

Tienda::load( 'TiendaPluginBase', 'library.plugins._base' );

class plgTiendaGenericExporter extends TiendaPluginBase
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    public $_element    	= 'genericexporter';  
    
	function plgTiendaGenericExporter(& $subject, $config) 
	{		
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );			
	}
	
 	function onAfterDisplayAdminComponentTienda()
    {
        $url = 'index.php?option=com_tienda&task=doTask&element=generic_exporter&elementTask=display';
       
        $this->addToolbar( $url );
    }
    
    /**
     * 
     * Method to add toolbar
     * @param string $name
     * @param string $text
     * @param string $url
     * @param boolean $title
     * @return void
     */
    private function addToolbar($url='', $name='revert', $text='Generic Export', $title= false)
    {
    	if($title)
    	{
    		JToolBarHelper::title( JText::_( 'Generic Export' ) );
    	}
    	
    	$bar = & JToolBar::getInstance('toolbar');
        $bar->prependButton( 'link', $name, $text, $url );
    }
    
    
    /**
     * 
     * Method to display list of export types
     */
    function display()
    {
    	JToolBarHelper::title( JText::_( 'Generic Export' ) );
    	
    	//read the type files inside the /plugins/tienda/genericexporter/types
    	jimport('joomla.filesystem.file');
    	$folder = JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.'genericexporter'.DS.'types';
     	if (JFolder::exists( $folder ))
        {
            $extensions = array( 'php' );
            $exclusions = array('_base.php');
                        
            $files = JFolder::files( $folder );          
        	foreach ($files as $file)
            {
                $namebits = explode('.', $file);               
                $extension = $namebits[count($namebits)-1];
              
                if (in_array($extension, $extensions) && !in_array($file , $exclusions))
                {     
                    $classname = 'TiendaGenericExporter'.$namebits[0]; 
                	Tienda::load( $classname, 'genericexporter.types.'.$namebits[0],  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));                 
                	
                	$exporter = new $classname;
                	$types[] = $exporter->getName();
                }
            }
        }    	
    	sort($types);
    	
    	$vars = new JObject();
    	$vars->types = $types;
        $html = $this->_getLayout('default', $vars);   
      
        return $html; 
    }	
    
    function viewcolumns()
    {
    	$type = JRequest::getVar('type', 'products');

    	JToolBarHelper::title( JText::_( 'Generic Export' ).': '.ucfirst($type) );       
       	$bar = & JToolBar::getInstance('toolbar');
        $btnhtml = '<a class="toolbar" onclick="javascript: document.adminForm.submit();" href="#">';
		$btnhtml .= '<span title="Submit" class="icon-32-forward">';
		$btnhtml .= '</span>'.JText::_('Submit').'</a>';       
       	$bar->appendButton( 'Custom', $btnhtml ); 
       	
       	$url = 'index.php?option=com_tienda&task=doTask&element=generic_exporter&elementTask=display';  
        $bar->prependButton( 'link', 'cancel', JText::_('Back'), $url );       			
        
    	$classname = 'TiendaGenericExporter'.$type; 
    	Tienda::load( $classname, 'genericexporter.types.'.$type,  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));                 

    	$class = new $classname;
    	$columns = $class->getColumns();
   	
    	$vars = new JObject();
    	$vars->columns = $columns;
        $html = $this->_getLayout('form', $vars);
           
    	return $html;
    }
    
    function doExport()
    {
    	$post = JRequest::get('post');
    	$type = JRequest::getVar('type', 'products');
    	
    	$views = array('dashboard', 'orders', 'orderpayments', 'subscriptions', 'orderitems', 'products', 'users');
      	if(in_array(strtolower($type), $views))
      	{
      		$url = 'index.php?option=com_tienda&view='.$type;
      	}
      	else 
      	{
      		$url = 'index.php?option=com_tienda&view=dashboard';
      	}
    	
      	$bar = & JToolBar::getInstance('toolbar');
      	$bar->prependButton( 'link', 'cancel', JText::_('Back'), $url );
      	JToolBarHelper::title( JText::_( 'Generic export' ) );
    	debug(99999, $post);
    	
    	$f_name = 'tmp'.DS.$post['type'].'_'.time().'.csv';
    	$this->processExport($post['type'], $f_name);
    	
    	$vars = new JObject();
    	$vars->type = $post['type'];
    	$vars->link = $f_name;
        $html = $this->_getLayout('view', $vars);
    }
    
    /**
     * 
     * Enter description here ...
     * @param string $type
     * @param string $f_name
     * @return void
     */
    private function processExport($type, $f_name)
    {
    	Tienda::load( 'TiendaCSV', 'library.csv' );
    	$arr = array();
      	$header = array(); // header -> it'll be filled out when 
      	$fill_header = true; // we need to fill header
      	
      	$classname = 'TiendaGenericExporter'.$type; 
        Tienda::load( $classname, 'genericexporter.types.'.$type,  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));                 
        $class = new $classname;        	
      	$list = $class->loadDataList();
 debug(444444, $list);     	
		for( $i = 0, $c = count( $list ); $i < $c; $i++ )
	    {
	    	if( $fill_header ) // need to fill header yet ?
	       	{
	       		$list_vars = get_object_vars( $list[$i] );	       	
	       		foreach( $list_vars as $key => $value ) // go through all variables
	       		{
	       			if( $fill_header )
	       			{
	       				$header[] = $key;
	       			}
	         	}
	         	$fill_header = false; // header is filled
	    	}
	       	$arr[] = $this->objectToString( $list[$i], true );
	     }
	     $f_name = 'tmp'.DS.$type.'_'.time().'.csv';
	     //$res = TiendaCSV::FromArrayToFile( $f_name, $arr, $header );
	     
	     return;
    }
    
 	function objectToString( $obj, $root = false )
    {
    	$arr_record = array();
		$list_vars = get_object_vars( $obj );
		foreach( $list_vars as $key => $value ) // go through all variables
		{
			if( is_object( $value ) )
			{
				$arr_record[] = $this->objectToString( $value );
			}
			else
			{
				if( is_array( $value ) )
				{
					@$value = implode( "\n", $value );
				}
				
				if($root)
				{
				
				}
				$arr_record[] = $root ?  $value : $key.'='.urlencode( $value );		
			}
		}
		
		if( $root )
		{
			return $arr_record;
		}
		
		return implode( "\n", $arr_record );
    }
}