<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'tienda.css', 'media/com_tienda/css/'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>

<div class='componentheading'>
    <span><?php echo JText::_( "Edit Basic Information" ); ?></span>
</div>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" onsubmit="tiendaFormValidation( '<?php echo @$form['validation']; ?>', 'validationmessage', document.adminForm.task.value, document.adminForm )" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >
    <div style="float: right;">
        <input type="button" onclick="tiendaSubmitForm('save');" value="<?php echo JText::_('Submit'); ?>" />    
    </div>

    <?php
    echo "<< <a href='".JRoute::_("index.php?option=com_tienda&view=accounts")."'>".JText::_( 'Cancel and Return to Profile' )."</a>";
    ?>
    
    <div id="validationmessage"></div>

	<table>
	    <tbody>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key">
	            <?php echo JText::_( 'Title' ); ?>
	        </th>
	        <td>
	            <input name="title" id="title"
	            type="text" size="5" maxlength="250"
	            value="<?php echo @$row->title; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key">
	             <?php echo JText::_( 'First name' ); ?>
	        </th>
	        <td>
	            <input name="first_name" id="first_name" 
	            type="text" size="35" maxlength="250"
	            value="<?php echo @$row->first_name; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key">
	             <?php echo JText::_( 'Middle name' ); ?>
	        </th>
	        <td>
	           <input type="text" name="middle_name"
	            id="middle_name" size="25" maxlength="250"
	            value="<?php echo @$row->middle_name; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key">
	             <?php echo JText::_( 'Last name' ); ?>
	        </th>
	        <td>
	           <input type="text" name="last_name"
	            id="last_name" size="45" maxlength="250"
	            value="<?php echo @$row->last_name; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key"> 
	          <?php echo JText::_( 'Company' ); ?>
	        </th>
	        <td><input type="text" name="company" id="company"
	            size="48" maxlength="250"
	            value="<?php echo @$row->company; ?>" /></td>
	    </tr>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key">
	            <?php echo JText::_( 'Phone' ); ?>
	        </th>
	        <td>
	            <input type="text" name="phone_1" id="phone_1"
	            size="25" maxlength="250" 
	            value="<?php echo @$row->phone_1; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key">
	            <?php echo JText::_( 'Cell' ); ?>
	        </th>
	        <td>
	            <input type="text" name="phone_2" id="phone_2"
	            size="25" maxlength="250"
	            value="<?php echo @$row->phone_2; ?>" />
	        </td>
	    </tr>
	    <tr>
	        <th style="width: 100px; text-align: right;" class="key">
	            <?php echo JText::_( 'Fax' ); ?>
	        </th>
	        <td>
	            <input type="text" name="fax" id="fax" 
	            size="25" maxlength="250" 
	            value="<?php echo @$row->fax; ?>" />
	        </td>
	    </tr>
        <tr>
            <th style="width: 100px; text-align: right;" class="key">
                <?php echo JText::_( 'Email Format' ); ?>
            </th>
            <td>
                <?php echo TiendaSelect::booleans( @$row->html_emails, 'html_emails', '', '', '', '', JText::_("HTML"), JText::_("Plain Text") ); ?>
            </td>
        </tr>
	    </tbody>
	</table>

    <input type="button" onclick="tiendaSubmitForm('save');" value="<?php echo JText::_('Submit'); ?>" />

    <input type="hidden" name="id" value="<?php echo @$row->user_id; ?>" />
    <input type="hidden" name="task" id="task" value="" />
    <?php echo @$form['validate']; ?>
</form>