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

Tienda::load( 'TiendaHelperBase', 'helpers._base' );
jimport('joomla.filesystem.file');

class TiendaHelperCategory extends TiendaHelperBase
{
    static $categories = array();
    
    /**
     * Gets the list of available category layout files
     * from the template's override folder
     * and the tienda products view folder
     * 
     * Returns array of filenames
     * Array
     * (
     *     [0] => view.php
     *     [1] => camera.php
     *     [2] => cameras.php
     *     [3] => computers.php
     *     [4] => laptop.php
     * )
     *  
     * @param array $options
     * @return array
     */
    function getLayouts( $options=array() )
    {
        $layouts = array();
        // set the default exclusions array
        $exclusions = array(
            'form_askquestion.php',
            'product_buy.php',
            'product_children.php',
            'product_comments.php',
            'product_files.php',
            'product_gallery.php',
            'product_rating.php',
            'product_relations.php',
            'product_requirements.php',
            'product_share_buttons.php',
            'quickadd.php',
            'search.php',
            'view.php'
        );
        // TODO merge $exclusions with $options['exclude']
        
        jimport('joomla.filesystem.file');
        $app = JFactory::getApplication();
        if ($app->isAdmin())
        {
            // TODO This doesn't account for when templates are assigned to menu items.  Make it do so
            $db = JFactory::getDBO();
            $db->setQuery( "SELECT `template` FROM #__templates_menu WHERE `menuid` = '0' AND `client_id` = '0';" );
            $template = $db->loadResult();
        }
            else
        {
            $template = $app->getTemplate();
        }
        $folder = JPATH_SITE.DS.'templates'.DS.$template.DS.'html'.DS.'com_tienda'.DS.'products';
        
        if (JFolder::exists( $folder ))
        {
            $extensions = array( 'php' );
            
            $files = JFolder::files( $folder );
            foreach ($files as $file)
            {
                $namebits = explode('.', $file);
                $extension = $namebits[count($namebits)-1];
                if (in_array($extension, $extensions))
                {
                    if (!in_array($file, $exclusions))
                    {
                        $layouts[] = $file;
                    }
                }
            }
        }

        // now do the media templates folder
        $folder = Tienda::getPath( 'categories_templates' );

        if (JFolder::exists( $folder ))
        {
            $extensions = array( 'php' );
            
            $files = JFolder::files( $folder );
            foreach ($files as $file)
            {
                $namebits = explode('.', $file);
                $extension = $namebits[count($namebits)-1];
                if (in_array($extension, $extensions))
                {
                    if (!in_array($file, $exclusions) && !in_array($file, $layouts))
                    {
                        $layouts[] = $file;
                    }
                }
            }
        }
        
        sort( $layouts );
        
        return $layouts;    
    }
    
    /**
     * Determines a category's layout 
     * 
     * @param int $category_id
     * @return unknown_type
     */
    function getLayout( $category_id )
    {
        static $template;
				$dispatcher = JDispatcher::getInstance();
        
        $layout = 'default';
        
        jimport('joomla.filesystem.file');
        $app = JFactory::getApplication();
        if (empty($template))
        {
            if ($app->isAdmin())
            {
                $template = $app->getTemplate();
                $db = JFactory::getDBO();
                $db->setQuery( "SELECT `template` FROM #__templates_menu WHERE `menuid` = '0' AND `client_id` = '0';" );
                $template = $db->loadResult();
            }
                else
            {
                $template = $app->getTemplate();
            }            
        }
        $templatePath = JPATH_SITE.DS.'templates'.DS.$template.DS.'html'.DS.'com_tienda'.DS.'products'.DS.'%s'.'.php';
        $mediaPath = Tienda::getPath( 'categories_templates' ) . DS . '%s'.'.php';

        if (isset($this) && is_a( $this, 'TiendaHelperCategory' )) 
        {
            $helper =& $this;
        } 
            else 
        {
            $helper =& TiendaHelperBase::getInstance( 'Category' );
        }
        
        if (empty($helper->categories[$category_id]))
        {
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $helper->categories[$category_id] = JTable::getInstance( 'Categories', 'TiendaTable' );
            $helper->categories[$category_id]->load( $category_id );            
        }
        $category = $helper->categories[$category_id];

        // if the $category->category_layout file exists in the template, use it
        if (!empty($category->category_layout) && JFile::exists( sprintf($templatePath, $category->category_layout) ))
        {
					$new_layout = $dispatcher->trigger('onGetLayoutCategory', array( $category, $category->category_layout ) ); 		
					
					if( count( $new_layout ) )
						return $new_layout[0];
					else
						return $category->category_layout;
        }
        
        // if the $category->category_layout file exists in the media folder, use it
        if (!empty($category->category_layout) && JFile::exists( sprintf($mediaPath, $category->category_layout) ))
        {
					$new_layout = $dispatcher->trigger('onGetLayoutCategory', array( $category, $category->category_layout ) ); 		
					
					if( count( $new_layout ) )
						return $new_layout[0];
					else
						return $category->category_layout;
        }
            
       // if all else fails, use the default!
			$new_layout = $dispatcher->trigger('onGetLayoutCategory', array( $category, $layout ) );
			
			if( count( $new_layout ) )
				return $new_layout[0];
			else
				return $layout;
    }
    
    /**
     * Gets a category's image
     * 
     * @param $id
     * @param $by
     * @param $alt
     * @param $type
     * @param $url
     * @return unknown_type
     */
	function getImage( $id, $by='id', $alt='', $type='thumb', $url=false )
	{
		switch($type)
		{
			case "full":
				$path = 'categories_images';
			  break;
			case "thumb":
			default:
				$path = 'categories_thumbs';
			  break;
		}
		
		$tmpl = "";
		if (strpos($id, '.'))
		{
			// then this is a filename, return the full img tag if file exists, otherwise use a default image
			$src = (JFile::exists( Tienda::getPath( $path ).DS.$id))
				? Tienda::getUrl( $path ).$id : 'media/com_tienda/images/noimage.png';
			
			// if url is true, just return the url of the file and not the whole img tag
			$tmpl = ($url)
				? $src : "<img src='".$src."' alt='".JText::_( $alt )."' title='".JText::_( $alt )."' align='middle' border='0' />";

		}
			else
		{
			if (!empty($id))
			{
				// load the item, get the filename, create tmpl
				JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
				$row = JTable::getInstance('Categories', 'TiendaTable');
				$row->load( (int) $id );
				$id = $row->category_full_image;

				$src = (JFile::exists( Tienda::getPath( $path ).DS.$row->category_full_image))
					? Tienda::getUrl( $path ).$id : 'media/com_tienda/images/noimage.png';

				// if url is true, just return the url of the file and not the whole img tag
				$tmpl = ($url)
					? $src : "<img src='".$src."' alt='".JText::_( $alt )."' title='".JText::_( $alt )."' align='middle' border='0' />";
			}			
		}
		return $tmpl;
	}
	
	/**
	 * Returns a formatted path for the category
	 * @param $id
	 * @param $format
	 * @return unknown_type
	 */
	function getPathName( $id, $format='flat', $linkSelf=false )
	{
		$name = '';
		if (empty($id))
		{
			return $name;
		}
		
	    if (isset($this) && is_a( $this, 'TiendaHelperCategory' )) 
        {
            $helper =& $this;
        } 
            else 
        {
            $helper =& TiendaHelperBase::getInstance( 'Category' );
        }
        
        if (empty($helper->categories[$id]))
        {
            JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
            $helper->categories[$id] = JTable::getInstance( 'Categories', 'TiendaTable' );
            $helper->categories[$id]->load( $id );            
        }
        $item = $helper->categories[$id];		

		if (empty($item->category_id))
		{
			return $name;
		}
		$path = $item->getPath();

		switch ($format)
		{
		    case "array":
		        $name = array();
		        foreach (@$path as $cat)
                {
                    $include_root = TiendaConfig::getInstance()->get('include_root_pathway', false );
                    if (!$cat->isroot || $include_root )
                    {
                        $pathway_object = new JObject();
                        $pathway_object->name = $cat->category_name;
                            $slug = $cat->category_alias ? ":$cat->category_alias" : "";
                            $link = "index.php?option=com_tienda&view=products&filter_category=".$cat->category_id.$slug;
                        $pathway_object->link = $link;
                        $pathway_object->id = $cat->category_id; 
                        $name[] = $pathway_object;
                    }
                }
                
                // add the item
                $pathway_object = new JObject();
                $pathway_object->name = $item->category_name;
                    $slug = $item->category_alias ? ":$item->category_alias" : "";
                    $link = "index.php?option=com_tienda&view=products&filter_category=".$item->category_id.$slug;
                $pathway_object->link = $link;
                $pathway_object->id = $item->category_id; 
                $name[] = $pathway_object;                
		      break;
			case "bullet":
				foreach (@$path as $cat)
				{
					if (!$cat->isroot)
					{
						$name .= '&bull;&nbsp;&nbsp;';
						$name .= JText::_( $cat->category_name );
						$name .= "<br/>";
					}
				}
					$name .= '&bull;&nbsp;&nbsp;';
					$name .= JText::_( $item->category_name );
			  break;
            case 'links':
                // get the root category
                JTable::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'tables' );
                $root = JTable::getInstance('Categories', 'TiendaTable')->getRoot();                
                $root_itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->category($root->category_id, true);
		        
                $include_root = TiendaConfig::getInstance()->get('include_root_pathway', false );
                if ($include_root)
                {
                    $link = JRoute::_( "index.php?option=com_tienda&view=products&filter_category=".$root->category_id."&Itemid=".$root_itemid, false );
                    $name .= " <a href='$link'>".JText::_('All Categories').'</a> ';
                }
                
			    foreach (@$path as $cat) 
			    {
			        if (!$cat->isroot) 
			        {
			            if (!$itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->category($cat->category_id, true))
			            {
			                $itemid = $root_itemid;
			            }
			            $slug = $cat->category_alias ? ":$cat->category_alias" : "";
			            $link = JRoute::_("index.php?option=com_tienda&view=products&filter_category=".$cat->category_id.$slug."&Itemid=".$itemid, false);
			            if (!empty($name)) { $name .= " > "; }
			            $name .= " <a href='$link'>".JText::_( $cat->category_name ).'</a> ';
			        }
			    }
		            
			    if (!empty($name)) { $name .= " > "; }
			    
                if ($linkSelf)
                {
                    if (!$itemid = Tienda::getClass( "TiendaHelperRoute", 'helpers.route' )->category($item->category_id, true))
                    {
                        $itemid = $root_itemid;
                    }
                    $slug = $item->category_alias ? ":$item->category_alias" : "";
                	$link = JRoute::_("index.php?option=com_tienda&view=products&filter_category=".$item->category_id.$slug."&Itemid=".$itemid, false);
                	$name .= " <a href='$link'>".JText::_( $item->category_name ).'</a> ';
                }
                    else
                {
                    $name .= JText::_( $item->category_name );	
                }
			        
                break;
			default:
				foreach (@$path as $cat)
				{
					if (!$cat->isroot)
					{
						$name .= " / ";
						$name .= JText::_( $cat->category_name );
					}
				}
					$name .= " / ";
					$name .= JText::_( $item->category_name );
			  break;
		}

		return $name;
	}
	
    /**
     * Finds the prev & next items in the list 
     *  
     * @param $id   product id
     * @return array( 'prev', 'next' )
     */
    function getSurrounding( $id )
    {
        $return = array();
        
        $prev = intval( JRequest::getVar( "prev" ) );
        $next = intval( JRequest::getVar( "next" ) );
        if ($prev || $next) 
        {
            $return["prev"] = $prev;
            $return["next"] = $next;
            return $return;
        }
        
        $app = JFactory::getApplication();
        JModel::addIncludePath( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_tienda'.DS.'models' );
        $model = JModel::getInstance( 'Categories', 'TiendaModel' );
        $ns = $app->getName().'::'.'com.tienda.model.'.$model->getTable()->get('_suffix');
        $state = array();
        
        $state['limit']     = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $state['limitstart'] = $app->getUserStateFromRequest($ns.'limitstart', 'limitstart', 0, 'int');
        $state['filter']    = $app->getUserStateFromRequest($ns.'.filter', 'filter', '', 'string');
        $state['direction'] = $app->getUserStateFromRequest($ns.'.filter_direction', 'filter_direction', 'ASC', 'word');
                
        $state['order']             = $app->getUserStateFromRequest($ns.'.filter_order', 'filter_order', 'tbl.lft', 'cmd');
        $state['filter_id_from']    = $app->getUserStateFromRequest($ns.'id_from', 'filter_id_from', '', '');
        $state['filter_id_to']      = $app->getUserStateFromRequest($ns.'id_to', 'filter_id_to', '', '');
        $state['filter_name']       = $app->getUserStateFromRequest($ns.'name', 'filter_name', '', '');
        $state['filter_parentid']   = $app->getUserStateFromRequest($ns.'parentid', 'filter_parentid', '', '');
        $state['filter_enabled']    = $app->getUserStateFromRequest($ns.'enabled', 'filter_enabled', '', '');
                
        foreach (@$state as $key=>$value)
        {
            $model->setState( $key, $value );   
        }
        $rowset = $model->getList();
            
        $found = false;
        $prev_id = '';
        $next_id = '';

        for ($i=0; $i < count($rowset) && empty($found); $i++) 
        {
            $row = $rowset[$i];     
            if ($row->category_id == $id) 
            { 
                $found = true; 
                $prev_num = $i - 1;
                $next_num = $i + 1;
                if (isset($rowset[$prev_num]->category_id)) { $prev_id = $rowset[$prev_num]->category_id; }
                if (isset($rowset[$next_num]->category_id)) { $next_id = $rowset[$next_num]->category_id; }
    
            }
        }
        
        $return["prev"] = $prev_id;
        $return["next"] = $next_id; 
        return $return;
    }
}