<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>

<div class='componentheading'>
	<span><?php echo JText::_( "My Profile" ); ?></span>
</div>

	<?php if ($menu =& TiendaMenu::getInstance()) { $menu->display(); } ?>
		
<table style="width: 100%;">
<tr>
	<td style="width: 70%; max-width: 70%; vertical-align: top; padding-right: 5px;">
	
            <table class="adminlist" style="margin-bottom: 5px;">
            <thead>
            <tr>
                <th colspan="3">
                    <?php echo JText::_( "Profile Information" ); ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th style="width: 100px;">
                    <?php echo JText::_( "Basics" ); ?>
                </th>
                <td>
                    <?php
                    JLoader::import( 'com_tienda.helpers.user', JPATH_ADMINISTRATOR.DS.'components' );
                    $userinfo = TiendaHelperUser::getBasicInfo( JFactory::getUser()->id );
                    if (empty($userinfo->user_id))
                    {
                    	echo JText::_( "Please click edit to define your basic profile information" );
                    }
                    else
                    {
                        echo $userinfo->first_name." ".$userinfo->last_name."<br/>"; 	
                    }
                    ?>
                </td>
                <td>
                    <a href="<?php echo JRoute::_("index.php?option=com_tienda&view=accounts&task=edit"); ?>">
                        <?php echo JText::_( "Edit" ); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <th style="width: 100px;">
                    <?php echo JText::_( "Email" ); ?>
                </th>
                <td>
                    <?php echo JFactory::getUser()->email; ?>
                </td>
                <td>
                    <a href="<?php echo JRoute::_("index.php?option=com_user&view=user&task=edit"); ?>">
                        <?php echo JText::_( "Edit" ); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <th style="width: 100px;">
                    <?php echo JText::_( "Password" ); ?>
                </th>
                <td>
                    **********
                </td>
                <td>
                    <a href="<?php echo JRoute::_("index.php?option=com_user&view=user&task=edit"); ?>">
                        <?php echo JText::_( "Edit" ); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <th style="width: 100px;">
                    <?php echo JText::_( "Primary Shipping Address" ); ?>
                </th>
                <td>
                    <?php
                    JLoader::import( 'com_tienda.helpers.user', JPATH_ADMINISTRATOR.DS.'components' );
                    if ($address = TiendaHelperUser::getPrimaryAddress( JFactory::getUser()->id, 'shipping' ))
                    {
                        echo $address->title . " ". $address->first_name . " ". $address->last_name . "<br>";
			            echo $address->company . "<br>";
			            echo $address->address_1 . " " . $address->address_2 . "<br>";
			            echo $address->city . ", " . $address->zone_name .", " . $address->postal_code . "<br>";
			            echo $address->country_name . "<br>";
                    } 
                    else
                    {
                        echo JText::_("None Selected");	
                    }
                    ?>
                </td>
                <td>
                    <a href="<?php echo JRoute::_("index.php?option=com_tienda&view=addresses"); ?>">
                        <?php echo JText::_( "Edit" ); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <th style="width: 100px;">
                    <?php echo JText::_( "Primary Billing Address" ); ?>
                </th>
                <td>
                    <?php 
                    if ($address = TiendaHelperUser::getPrimaryAddress( JFactory::getUser()->id, 'billing' ))
                    {
                        echo $address->title . " ". $address->first_name . " ". $address->last_name . "<br>";
                        echo $address->company . "<br>";
                        echo $address->address_1 . " " . $address->address_2 . "<br>";
                        echo $address->city . ", " . $address->zone_name .", " . $address->postal_code . "<br>";
                        echo $address->country_name . "<br>";
                    } 
                    else
                    {
                        echo JText::_("None Selected"); 
                    }
                    ?>
                </td>
                <td>
                    <a href="<?php echo JRoute::_("index.php?option=com_tienda&view=addresses"); ?>">
                        <?php echo JText::_( "Edit" ); ?>
                    </a>
                </td>
            </tr>
            </tbody>
            </table>
	
		<?php
		$modules = JModuleHelper::getModules("tienda_dashboard_main");
		$document	= &JFactory::getDocument();
		$renderer	= $document->loadRenderer('module');
		$attribs 	= array();
		$attribs['style'] = 'xhtml';
		foreach ( @$modules as $mod ) 
		{
			echo $renderer->render($mod, $attribs);
		}
		?>
		
		<?php
		$modules = JModuleHelper::getModules("tienda_dashboard_right");
		if ($modules)
		{
            ?>
            </td>
            <td style="vertical-align: top; width: 30%; min-width: 30%; padding-left: 5px;">
            <?php
			
			$document	= &JFactory::getDocument();
			$renderer	= $document->loadRenderer('module');
			$attribs 	= array();
			$attribs['style'] = 'xhtml';
			foreach ( @$modules as $mod ) 
			{
				echo $renderer->render($mod, $attribs);
			}
		}
		?>
	</td>
</tr>
</table>