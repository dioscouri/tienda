<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php $state = @$vars->state; ?>
<?php $form = @$vars->form; ?>
<?php $order_id = @$vars->order_id; ?>
<?php $results = @$vars->results; ?>

<style type="text/css">
table.admintable td                      { padding: 3px; }
table.admintable td.key,
table.admintable td.paramlist_key {
    background-color: #f6f6f6;
    text-align: right;
    width: 140px;
    color: #666;
    font-weight: bold;
    border-bottom: 1px solid #e9e9e9;
    border-right: 1px solid #e9e9e9;
}

table.paramlist td.paramlist_description {
    background-color: #f6f6f6;
    text-align: left;
    width: 170px;
    color: #333;
    font-weight: normal;
    border-bottom: 1px solid #e9e9e9;
    border-right: 1px solid #e9e9e9;
}

table.admintable td.key.vtop { vertical-align: top; }
</style>

<div id="ups_tracking">
	<table class="admintable" style="clear: both; width: 100%;">
    	<?php foreach($results as $result) {?>
    	<tr>
    	    <td style="width: 100px; text-align: right;" class="key">
    	        <?php echo JText::_("Activity"); ?>
    	    </td>
    	    <td>
    	      
    	      <table class="admintable" style="clear: both; width: 100%;">
			    	<tr>
			    	    <td style="width: 100px; text-align: right;" class="key">
			    	        <?php echo JText::_("Date"); ?>
			    	    </td>
			    	    <td><?php echo $result['date']?>
				    
			    	    </td>
			    	</tr>
			    	<tr>
			    	    <td style="width: 100px; text-align: right;" class="key">
			    	        <?php echo JText::_("Time"); ?>
			    	    </td>
			    	    <td><?php echo $result['time']?>
				    
			    	    </td>
			    	</tr>
			    	<tr>
			    	    <td style="width: 100px; text-align: right;" class="key">
			    	        <?php echo JText::_("Description"); ?>
			    	    </td>
			    	    <td><?php echo $result['description']?>
				    
			    	    </td>
			    	</tr>
			    	<tr>
			    	    <td style="width: 100px; text-align: right;" class="key">
			    	        <?php echo JText::_("Location"); ?>
			    	    </td>
			    	    <td><?php echo $result['location']?>
				    
			    	    </td>
			    	</tr>
			</table>		
	    
    	    </td>
    	</tr>
    	<?php }?>
		</table>		
</div>