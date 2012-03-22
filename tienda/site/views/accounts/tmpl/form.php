<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; 
JFilterOutput::objectHTMLSafe( $row );
?>

<div class='componentheading'>
    <span><?php echo JText::_('COM_TIENDA_EDIT_BASIC_INFORMATION'); ?></span>
</div>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" onsubmit="tiendaFormValidation( '<?php echo @$form['validation']; ?>', 'validationmessage', document.adminForm.task.value, document.adminForm )" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >
    <div style="float: right;">
        <input type="button" onclick="tiendaSubmitForm('save');" value="<?php echo JText::_('COM_TIENDA_SUBMIT'); ?>" />    
    </div>

    <?php
    echo "<< <a href='".JRoute::_("index.php?option=com_tienda&view=accounts")."'>".JText::_('COM_TIENDA_CANCEL_AND_RETURN_TO_PROFILE')."</a>";
    ?>
    
    <div id="validationmessage"></div>

	<table>
	    <tbody>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key">
	            <?php echo JText::_('COM_TIENDA_TITLE'); ?>
	        </th>
	        <td>
	            <input name="title" id="title"
	            type="text" size="5" maxlength="250"
	            value="<?php echo @$row->title; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key">
	             <?php echo JText::_('COM_TIENDA_FIRST_NAME'); ?>
	        </th>
	        <td>
	            <input name="first_name" id="first_name" 
	            type="text" size="35" maxlength="250"
	            value="<?php echo @$row->first_name; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key">
	             <?php echo JText::_('COM_TIENDA_MIDDLE_NAME'); ?>
	        </th>
	        <td>
	           <input type="text" name="middle_name"
	            id="middle_name" size="25" maxlength="250"
	            value="<?php echo @$row->middle_name; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key">
	             <?php echo JText::_('COM_TIENDA_LAST_NAME'); ?>
	        </th>
	        <td>
	           <input type="text" name="last_name"
	            id="last_name" size="45" maxlength="250"
	            value="<?php echo @$row->last_name; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key"> 
	          <?php echo JText::_('COM_TIENDA_COMPANY'); ?>
	        </th>
	        <td><input type="text" name="company" id="company"
	            size="48" maxlength="250"
	            value="<?php echo @$row->company; ?>" /></td>
	    </tr>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key">
	            <?php echo JText::_('COM_TIENDA_PHONE'); ?>
	        </th>
	        <td>
	            <input type="text" name="phone_1" id="phone_1"
	            size="25" maxlength="250" 
	            value="<?php echo @$row->phone_1; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key">
	            <?php echo JText::_('COM_TIENDA_CELL'); ?>
	        </th>
	        <td>
	            <input type="text" name="phone_2" id="phone_2"
	            size="25" maxlength="250"
	            value="<?php echo @$row->phone_2; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key">
	            <?php echo JText::_('COM_TIENDA_FAX'); ?>
	        </th>
	        <td>
	            <input type="text" name="fax" id="fax" 
	            size="25" maxlength="250" 
	            value="<?php echo @$row->fax; ?>" />
	        </td>
	    </tr>
        <tr>
            <th style="width: 100px; text-align: right;" class="key">
                <?php echo JText::_('COM_TIENDA_EMAIL_FORMAT'); ?>
            </th>
            <td>
                <?php echo TiendaSelect::booleans( @$row->html_emails, 'html_emails', '', '', '', '', JText::_('COM_TIENDA_HTML'), JText::_('COM_TIENDA_PLAIN_TEXT') ); ?>
            </td>
        </tr>
	    </tbody>
	</table>

    <input type="button" onclick="tiendaSubmitForm('save');" value="<?php echo JText::_('COM_TIENDA_SUBMIT'); ?>" />

    <input type="hidden" name="id" value="<?php echo @$row->user_id; ?>" />
    <input type="hidden" name="task" id="task" value="" />
    <?php echo @$form['validate']; ?>
</form>