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

Tienda::load( 'TiendaToolPlugin', 'library.plugins.tool' );

class plgTiendaTool_InstallSampleData extends TiendaToolPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename, 
	 *                         forcing it to be unique 
	 */
    var $_element   = 'tool_installsampledata';   
    
	function plgTiendaTool_InstallSampleData(& $subject, $config) 
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );	
	}
	
    /**
     * Overriding 
     * 
     * @param $options
     * @return unknown_type
     */
    function onGetToolView( $row )
    {
        if (!$this->_isMe($row)) 
        {
            return null;
        }
        
        // go to a "process suffix" method
        // which will first validate data submitted,
        // and if OK, will return the html?
        $suffix = $this->_getTokenSuffix();
        $html = $this->_processSuffix( $suffix );

        return $html;
    }
    
    /**
     * Validates the data submitted based on the suffix provided
     * 
     * @param $suffix
     * @return html
     */
    function _processSuffix( $suffix='' )
    {
        $html = "";
        
    	$state = $this->_getState();

        switch($suffix)
        {
            case"2":
                if (count($state->sampledata) < 1)
                {
                    JError::raiseNotice('_verifyDB', JText::_('PLEASE SELECT AT LEAST ONE DATA SET'));
                    $html .= $this->_renderForm( '1' );
                }
                else
                {
                    // migrate the data and output the results
                    $html .= $this->_doInstallSampleData($state);
                }
                break;
            case"1":
                if (empty($state->sampledata))
                {
                     JError::raiseNotice('_verifyDB', JText::_('PLEASE SELECT AT LEAST ONE DATA SET'));
                    $html .= $this->_renderForm( '1' );
                }
                else
                {
                    $suffix++;
                    // display a 'connection verified' message
                    // and request confirmation before migrating data
                    $html .= $this->_renderForm( $suffix );
                    $html .= $this->_renderView( $suffix );                    
                }
                break;
            default:
                $html .= $this->_renderForm( '1' );
                break;
        }
        
        return $html;
    }
	
    /**
     * Prepares the 'view' tmpl layout
     *  
     * @return unknown_type
     */
    function _renderView( $suffix='' )
    {
        $vars = new JObject();
        $layout = 'view_'.$suffix;
        $html = $this->_getLayout($layout, $vars);
        
        return $html;
    }
    
    /**
     * Prepares variables for the form
     * 
     * @return unknown_type
     */
    function _renderForm( $suffix='' )
    {
        $vars = new JObject();
        $vars->token = $this->_getToken( $suffix );
        $vars->state = $this->_getState();
        
        $layout = 'form_'.$suffix;
        $html = $this->_getLayout($layout, $vars);
        
        return $html;
    }

    /**
     * Gets the appropriate values from the request
     * 
     * @return object
     */
    function _getState()
    {    	
        $state = new JObject();
        $state->sampledata = JRequest::getVar('sampledata');        
 
        return $state;
    }

    /**
     * Perform the sample data installation
     * 
     * @return html
     */
    private function _doInstallSampleData($state)
    {
        $html = "";
        $vars = new JObject();
        $errors = null;                    

        static $installURL;
        $results = array();
                
        if(!empty($state->sampledata))
        {
        	$database = JFactory::getDBO();
        	$installURL = JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_tienda'.DS.'install'.DS.'sampledata'.DS;
        	
        	foreach( $state->sampledata as $sample)
        	{        		
        		$sqlfile = $installURL.$sample.".sql";        		
        		$results[ucfirst($sample)] = $this->_populateDatabase( $database, $sqlfile, $errors);    
        	}
        	           
        }         
		//if errors not empty
		if(!empty($errors))
		{
			$vars->_errors = $errors;						
			$layout = 'view_4';
		}
		else
		{
			$vars->results = $results;	        
	        $suffix = $this->_getTokenSuffix();
	        $suffix++;
	        $layout = 'view_'.$suffix;       
    	
		}
		
		$html .= $this->_getLayout($layout, $vars);
      
        return $html;
    }
    
	private function _populateDatabase (& $database, $sqlfile, & $errors)
	{					
		if( !($buffer = file_get_contents($sqlfile)) )
		{
			return -1;
		}

		$queries = $this->_splitSql($buffer);
	
		$results = array();
		$n=0;
		foreach ($queries as $query)
		{
			$query = trim($query);
			if ($query != '' && $query {0} != '#')
			{
				$database->setQuery($query);		
				if($n == '0')
				{
					$results[$n]->table = JText::_("Manufacturers");
				}
				elseif( $n == '1')
				{
					$results[$n]->table = JText::_("Categories");
				}
				else 
				{
					$results[$n]->table = JText::_("Products");
				}
				
            	$results[$n]->query = $database->getQuery();
            	$results[$n]->error = '';	
				
            	if (!$database->query())
            	{
                	$results[$n]->error = $database->getErrorMsg();
            	}
            	
            	$results[$n]->affectedRows = $database->getAffectedRows();
				
				$n++;
			}
		}	
		return $results;
	}
        
	/**
	 * @param string - $sql
	 * @return array
	 */
	private function _splitSql($sql)
	{
		$sql = trim($sql);
		$sql = preg_replace("/\n\#[^\n]*/", '', "\n".$sql);
		$buffer = array ();
		$ret = array ();
		$in_string = false;

		for ($i = 0; $i < strlen($sql) - 1; $i ++) {
			if ($sql[$i] == ";" && !$in_string)
			{
				$ret[] = substr($sql, 0, $i);
				$sql = substr($sql, $i +1);
				$i = 0;
			}

			if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\")
			{
				$in_string = false;
			}
			elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\"))
			{
				$in_string = $sql[$i];
			}
			if (isset ($buffer[1]))
			{
				$buffer[0] = $buffer[1];
			}
			$buffer[1] = $sql[$i];
		}

		if (!empty ($sql))
		{
			$ret[] = $sql;
		}
		return ($ret);
	}      
       
}
