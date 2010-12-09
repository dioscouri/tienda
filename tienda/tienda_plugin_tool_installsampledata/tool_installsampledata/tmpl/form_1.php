<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; ?>
    <p><?php echo JText::_( "THIS TOOL INSTALL SAMPLE DATA TO TIENDA" ); ?></p>
    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_( "STEP ONE OF THREE" ); ?></span>
        <p><?php echo JText::_( "PLEASE SELECT THE SAMPLE DATA SET THAT YOU WANT TO INSTALL" ); ?></p>
    </div>    
    <fieldset>
    <?php 
    	$options = array ();			
		$options[] = JHTML::_('select.option', 'electronic', JText::_("ELECTRONIC DATA"));
		$options[] = JHTML::_('select.option', 'clothing', JText::_("CLOTHING DATA"));
		$options[] = JHTML::_('select.option', 'furniture', JText::_("FURNITURE DATA"));
		$options[] = JHTML::_('select.option', 'ebooks', JText::_("EBOOKS DATA"));					
    ?>
    
        <legend><?php echo JText::_('Sample Data Sets'); ?></legend>          
        <?php echo JHTML::_('select.radiolist',  $options, 'sampledata', 'class="inputbox"', 'value', 'text', $value, 'datatype');?>
    </fieldset>
        