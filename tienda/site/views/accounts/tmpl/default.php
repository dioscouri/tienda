<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'menu.css', 'media/com_tienda/css/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>

<div class='componentheading'>
	<span><?php echo JText::_('COM_TIENDA_MY_PROFILE'); ?></span>
</div>

	<?php if ($menu = TiendaMenu::getInstance()) { $menu->display(); } ?>
		
<table style="width: 100%;">
<tr>
	<td style="width: 70%; max-width: 70%; vertical-align: top; padding-right: 5px;">
	
            <table class="adminlist" style="margin-bottom: 5px;">
            <thead>
            <tr>
                <th colspan="3">
                    <?php echo JText::_('COM_TIENDA_PROFILE_INFORMATION'); ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th style="width: 100px;">
                    <?php echo JText::_('COM_TIENDA_BASICS'); ?>
                </th>
                <td>
                    <?php
                    Tienda::load( 'TiendaHelperUser', 'helpers.user' );
                    $userinfo = TiendaHelperUser::getBasicInfo( JFactory::getUser()->id );
                    if (empty($userinfo->user_id))
                    {
                    	echo JText::_('COM_TIENDA_PLEASE_CLICK_EDIT_TO_DEFINE_YOUR_BASIC_PROFILE_INFORMATION');
                    }
                    else
                    {
                        echo $userinfo->first_name." ".$userinfo->last_name."<br/>"; 	
                    }
                    ?>
                </td>
                <td>
                    <a href="<?php echo JRoute::_("index.php?option=com_tienda&view=accounts&task=edit"); ?>">
                        <?php echo JText::_('COM_TIENDA_EDIT'); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <th style="width: 100px;">
                    <?php echo JText::_('COM_TIENDA_EMAIL'); ?>
                </th>
                <td>
                    <?php echo JFactory::getUser()->email; ?>
                </td>
                <td>
                    <a href="<?php echo JRoute::_( $this->url_profile ); ?>">
                        <?php echo JText::_('COM_TIENDA_EDIT'); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <th style="width: 100px;">
                    <?php echo JText::_('COM_TIENDA_PASSWORD'); ?>
                </th>
                <td>
                    **********
                </td>
                <td>
                    <a href="<?php echo JRoute::_( $this->url_profile ); ?>">
                        <?php echo JText::_('COM_TIENDA_EDIT'); ?>
                    </a>
                </td>
            </tr>
            <?php if ( Tienda::getInstance()->get( 'display_subnum', 0 ) ) : ?>
            <tr>
                <th style="width: 100px;">
                    <?php echo JText::_('COM_TIENDA_SUB_NUM'); ?>
                </th>
                <td colspan="2">
		            	<?php Tienda::load( 'TiendaHelperSubscription', 'helpers.subscription' ); ?>
    	        		<?php echo TiendaHelperSubscription::displaySubNum( $userinfo->sub_number ); ?>                    
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <th style="width: 100px;">
                    <?php echo JText::_('COM_TIENDA_PRIMARY_SHIPPING_ADDRESS'); ?>
                </th>
                <td>
                    <?php
                    Tienda::load( 'TiendaHelperUser', 'helpers.user' );
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
                        echo JText::_('COM_TIENDA_NONE_SELECTED');	
                    }
                    ?>
                </td>
                <td>
                    <a href="<?php echo JRoute::_("index.php?option=com_tienda&view=addresses"); ?>">
                        <?php echo JText::_('COM_TIENDA_EDIT'); ?>
                    </a>
                </td>
            </tr>
            <tr>
                <th style="width: 100px;">
                    <?php echo JText::_('COM_TIENDA_PRIMARY_BILLING_ADDRESS'); ?>
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
                        echo JText::_('COM_TIENDA_NONE_SELECTED'); 
                    }
                    ?>
                </td>
                <td>
                    <a href="<?php echo JRoute::_("index.php?option=com_tienda&view=addresses"); ?>">
                        <?php echo JText::_('COM_TIENDA_EDIT'); ?>
                    </a>
                </td>
            </tr>
            </tbody>
            </table>
	
		<?php
		$modules = JModuleHelper::getModules("tienda_dashboard_main");
		$document	= JFactory::getDocument();
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