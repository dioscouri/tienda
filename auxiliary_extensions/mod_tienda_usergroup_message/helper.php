<?php
/**
 * @package Featured Items
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2009 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die( 'Restricted Access' );

jimport( 'joomla.application.component.model' );

class modTiendaUsergroup_MessageHelper extends JObject
{
	/**
	 * Sets the modules params as a property of the object
	 * @param unknown_type $params
	 * @return unknown_type
	 */
	public function __construct( $params )
	{
		$this->params = $params;
		$this->user = JFactory::getUser();
		$this->defines = Tienda::getInstance();
	}
	
	/**
	 * Gets the various db information to sucessfully display item
	 * @return unknown_type
	 */
	public function displayMessageForUser()
	{
	    $display = false;
	    
	    $ids = array();
	    $param_ids = explode(',', $this->params->get('usergroup_ids') );
	    foreach( $param_ids as $id )
	    {
	        $id = trim($id);
	        if (!empty($id))
	        {
	            $ids[] = $id;
	        }
	    }

	    $groups = $this->getGroups();

	    foreach ($ids as $id) 
	    {
	        if (in_array($id, $groups)) 
	        {
	            $display = true;
	            break;
	        }
	    }
    
		return $display;
	}
	
	public function getMessage()
	{
	    return $this->params->get('usergroup_message');
	}
	
	public function getGroups()
	{
	    $groups = array();
	    
	    if (empty($this->user->id)) 
	    {
	        $groups[] = $this->defines->get('default_user_group', '1');
	        return $groups;
	    }
	    
	    $model = Tienda::getClass( "TiendaModelUserGroups", 'models.usergroups' );
	    $model->setState( 'filter_user', $this->user->id );
	    if ($usergroups = $model->getList()) 
	    {
	        foreach ($usergroups as $usergroup) 
	        {
	            $groups[] = $usergroup->group_id;
	        }
	    }
	    
	    if (empty($groups)) 
	    {
	        $groups = array();
	        $groups[] = $this->defines->get('default_user_group', '1');
	    }

	    return $groups;
	}

}
?>