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
    	$name='revert';
    	$text= JText::_("GENERIC EXPORT");
        $url = 'index.php?option=com_tienda&task=doTask&element=genericexporter&elementTask=display';
       
        $bar = & JToolBar::getInstance('toolbar');
        $bar->prependButton( 'link', $name, $text, $url );
    }
        
    /**
     * 
     * Method to display list of export types
     */
    function display()
    {
    	require_once( JPATH_SITE.DS.'libraries'.DS.'joomla'.DS.'html'.DS.'html'.DS.'select.php' );
    	JToolBarHelper::title( JText::_( 'GENERIC EXPORT' ) );
    	
    	$bar = & JToolBar::getInstance('toolbar');
        $btnhtml = '<a class="toolbar" onclick="javascript: document.adminForm.submit();" href="#">';
		$btnhtml .= '<span title="Submit" class="icon-32-forward">';
		$btnhtml .= '</span>'.JText::_('SUBMIT').'</a>';       
       	$bar->appendButton( 'Custom', $btnhtml ); 
    	
    	//read the type files inside the /plugins/tienda/genericexporter/models
    	jimport('joomla.filesystem.file');
    	$folder = JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.'genericexporter'.DS.'models';
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
                    $classname = 'TiendaGenericExporterModel'.$namebits[0]; 
                	Tienda::load( $classname, 'genericexporter.models.'.$namebits[0],  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));                 
                	
                	if(class_exists($classname))
                	{
                		$exporter = new $classname;
                		$models[] = $exporter->getName();
                	}                	
                }
            }
        }    	
        
        $folderTypes = JPATH_SITE.DS.'plugins'.DS.'tienda'.DS.'genericexporter'.DS.'types';
     	if (JFolder::exists( $folderTypes ))
        {
            $extensions = array( 'php' );  
            $exclusions = array('_base.php');      
                        
            $typeFiles = JFolder::files( $folderTypes );                    
        	foreach ($typeFiles as $typeFile)
            {
                $namebits = explode('.', $typeFile);               
                $extension = $namebits[count($namebits)-1];
              
                if (in_array($extension, $extensions) && !in_array($typeFile , $exclusions))
                {                        
                	$classname = 'TiendaGenericExporterType'.$namebits[0]; 
                	Tienda::load( $classname, 'genericexporter.types.'.strtolower($namebits[0]),  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));                 
              	
                	if(class_exists($classname))
                	{ 
                		$exporterType = new $classname;
                		$types[] = $exporterType->getFormat();
                	}                	
                }
            }
        } 
        
    	sort($models);
    	sort($types);
    	
    	$vars = new JObject();
    	$vars->models 	= $models;
    	$vars->types 	= $types;
        $html = $this->_getLayout('default', $vars);   
      
        return $html; 
    }	
    
    function filters()
    {
    	$model = JRequest::getVar('model', 'products');
    	$type = JRequest::getVar('type');
    	
    	if(empty($type) || empty($model))
    	{
    		JFactory::getApplication()->redirect('index.php?option=com_tienda&task=doTask&element=genericexporter&elementTask=display', JText::_("Model or Export Type is empty!"), 'notice');
    	}    		

    	JToolBarHelper::title( JText::_( 'GENERIC EXPORT' ).': '.ucfirst($model) );       
       	$bar = & JToolBar::getInstance('toolbar');
        $btnhtml = '<a class="toolbar" onclick="javascript: document.adminForm.submit();" href="#">';
		$btnhtml .= '<span title="'.JText::_('SUBMIT').'" class="icon-32-forward">';
		$btnhtml .= '</span>'.JText::_('SUBMIT').'</a>';       
       	$bar->appendButton( 'Custom', $btnhtml ); 
       	
       	$url = 'index.php?option=com_tienda&task=doTask&element=genericexporter&elementTask=display';  
        $bar->prependButton( 'link', 'cancel', JText::_('BACK'), $url );       			
        
    	$classname = 'TiendaGenericExporterModel'.$model; 
    	Tienda::load( $classname, 'genericexporter.models.'.$model,  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));                 

    	if(class_exists($classname))
        {
          	$class = new $classname;
    		$filters = $class->getFilters();
    		
    		//if empty we process to download page
    		if(!count($filters))
    		{
    			JFactory::getApplication()->redirect("index.php?option=com_tienda&task=doTask&element=genericexporter&elementTask=doExport&model={$model}&type={$type}");
    		}    		
        }
        else 
        {
        	JFactory::getApplication()->enqueueMessage( JText::_( "Class ".$classname." not found!" ), 'notice' );
        }
    	
   	
    	$vars = new JObject();
    	$vars->filters = $filters;
        $html = $this->_getLayout('form', $vars);
           
    	return $html;
    }
    
    function doExport()
    {    	
    	
    	$post = JRequest::get('post');
    
    	$model = JRequest::getVar('model', 'products');
    	$type = JRequest::getVar('type');
    	
    	$views = array('dashboard', 'orders', 'orderpayments', 'subscriptions', 'orderitems', 'products', 'users', 'config');
      	if(in_array(strtolower($model), $views))
      	{
      		$url = 'index.php?option=com_tienda&view='.$model;
      	}
      	else 
      	{
      		$url = 'index.php?option=com_tienda&view=dashboard';
      	}
    	
      	//add toolbar
      	$bar = & JToolBar::getInstance('toolbar');
      	$bar->prependButton( 'link', 'cancel', JText::_('BACK'), $url );
      	JToolBarHelper::title( JText::_( 'GENERIC EXPORT' )." : ". ucfirst($model));
    	
    	$export = $this->processExport($type, $model);
	
    	if(!empty($export->_errors))
    	{
    		JFactory::getApplication()->enqueueMessage( $export->_errors, 'notice' );
    		return;
    	}
    	//success message    
    	JFactory::getApplication()->enqueueMessage( JText::_( "EXPORT IS COMPLETE PLEASE CLICK THE LINK BELOW TO DOWNLOAD" ), 'message' );
  
    	$vars = new JObject();
    	$vars->name = $export->_name;
    	$vars->link = $export->_link;
        $html = $this->_getLayout('view', $vars);
        return $html;
    }
    
    /**      
     * Method to process the export
     * @param string $type - csv, xml, etc
     * @param string $model - see /plugins/tienda/genericexporter/models
     * @return void
     */
    private function processExport($type, $model)
    {    	
    	$classname = 'TiendaGenericExporterType'.$type; 
        Tienda::load( $classname, 'genericexporter.types.'.strtolower($type),  array( 'site'=>'site', 'type'=>'plugins', 'ext'=>'tienda' ));                 

        $export = '';
        if(class_exists($classname))
        {
        	$exporterType = new $classname();
        	$exporterType->setModel($model);
        	$export = $exporterType->processExport(); 
        }
          
	    return $export;
    }
}