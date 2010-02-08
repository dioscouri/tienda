<?php 
defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.application.module.helper');
?>

<h2><?php echo JTEXT::_('Please Login or Register'); ?></h2>

<div id=register_wrapper>
	<p>
	<?php echo JTEXT::_('To take full advantage of your shopping experience please: ').
    '<a href='.JRoute::_("index.php?option=com_user&view=register").'>'.JText::_("Register").
    '</a>'; 
	?>
</div>

<div id=login_wrapper>
	<?php
		$module = JModuleHelper::getModule( 'mod_login' );
		$attribs['style'] = 'xhtml';
		echo JModuleHelper::renderModule( $module, $attribs );
	?>
</div>
