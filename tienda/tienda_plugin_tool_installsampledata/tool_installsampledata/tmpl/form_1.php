<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<?php echo @$vars->token; 
debug(3124, $vars);

?>

    <p><?php echo JText::_( "THIS TOOL INSTALL SAMPLE DATA TO TIENDA" ); ?></p>

    <div class="note">
        <span style="float: right; font-size: large; font-weight: bold;"><?php echo JText::_( "STEP ONE OF THREE" ); ?></span>
        <p><?php echo JText::_( "PLEASE SELECT THE SAMPLE DATA SETS THAT YOU WANT TO INSTALL" ); ?></p>
    </div>
    
    <fieldset>
        <legend><?php echo JText::_('Sample Data Sets'); ?></legend>   
        <input type="checkbox" name="sampledata[electronic]" value="electronic" /><?php echo JText::_('Electronics Data'); ?>  
        <br/> 
        <input type="checkbox" name="sampledata[clothing]" value="clothing" /><?php echo JText::_('Clothing Data'); ?>  
        <br/>
        <input type="checkbox" name="sampledata[furniture]" value="furniture" /><?php echo JText::_('Furniture Data'); ?>  
        <br/>
        <input type="checkbox" name="sampledata[ebooks]" value="ebooks" /><?php echo JText::_('Ebooks Data'); ?>    
    </fieldset>
        