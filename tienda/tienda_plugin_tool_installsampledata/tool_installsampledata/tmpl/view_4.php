<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $errors = $vars->_errors; ?>
<p><?php echo JText::_('COM_TIENDA_THIS_TOOL_INSTALL_SAMPLE_DATA_TO_TIENDA'); ?></p>
<div style="margin-bottom: 10px; background-color:#EFE7B8;border-bottom-color:#F0DC7E;border-bottom-style:solid;border-bottom-width:3px;border-top-color:#F0DC7E;border-top-style:solid;border-top-width:3px;color:#CC0000;padding-left: 10px;">  
	<p><?php echo JText::_('COM_TIENDA_ERROR_INSTALLING_SAMPLE_DATA_TO_TIENDA'); ?></p>
</div>
<table class="adminlist" style="clear: both;">
	<thead>
    	<tr>
        	<th style="width: 5px;">
            	<?php echo JText::_('COM_TIENDA_NUM'); ?>
            </th>
            <th>
            	<?php echo JText::_('COM_TIENDA_MESSAGE'); ?>
            </th>
            <th>
                <?php echo JText::_('COM_TIENDA_SQL_QUERY'); ?>
            </th>
        </tr>
        <tbody>
        	<?php $i=0; $k=0; ?>
        	<?php foreach($errors as $error):?>
			<tr class='row<?php echo $k; ?>'>
				<td align="center">
                    <?php echo $i + 1; ?>
                </td>
			    <td>
			    	<?php echo $error['msg']; ?>
			    </td>
			    <td>
			    	<?php echo $error['sql']; ?>
			    </td>
			</tr>
			 <?php ++$i; $k = (1 - $k); ?>
			<?php endforeach;?>
        
        </tbody>
</table>
