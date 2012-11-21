<?php defined('_JEXEC') or die('Restricted access'); ?>



<ul class="nav nav-tabs" id="myTab">
  <li class="active"><a href="#"><?php echo JText::_('COM_TIENDA_POS_STEP1_SELECT_USER'); ?></a></li>
 <li class="disabled"><a href=""><?php echo JText::_('COM_TIENDA_POS_STEP2_SELECT_PRODUCTS'); ?></a></li>
  <li class="disabled"><a href=""><?php echo JText::_('COM_TIENDA_POS_STEP3_SELECT_PAYMENT_SHIPPING_METHODS'); ?></a></li>
  <li  class="disabled"><a href=""><?php echo JText::_('COM_TIENDA_POS_STEP4_REVIEW_SUBMIT_ORDER'); ?></a></li>
    <li  class="disabled"><a href=""><?php echo JText::_('COM_TIENDA_POS_STEP5_PAYMENT_CONFIRMATION'); ?></a></li>
</ul>
<div class="progress">
  <div class="bar" style="width: 10%;"></div>
</div>

            <h2><?php echo JText::_('COM_TIENDA_SELECT_USER_OR_CREATE_ONE'); ?></h2>
          <div class="clearfix">  
            <div id="validation_message"></div>

            <div class="option well well-small">
            	<label class="radio">
            	<input type="radio" name="user_type" value="existing" <?php if ($this->session->get('user_type', '', 'tienda_pos') == 'existing' || $this->session->get('user_type', '', 'tienda_pos') == '') { ?>checked="checked" <?php } ?> /><?php echo JText::_('COM_TIENDA_USE_AN_EXISTING_USER'); ?>

            	</label>
                <div id="existing" class="option_data">
                    <?php echo $this->getModel('elementUser')->fetchElement( 'user_id', $this->session->get('user_id', '', 'tienda_pos' ) ); ?>
                    <?php echo $this->getModel('elementUser')->clearElement( 'user_id', '' ); ?>
                </div>
            </div>
            
            <div class="option well well-small">
            	<label class="radio">
                <input type="radio" name="user_type" value="new" <?php if ($this->session->get('user_type', '', 'tienda_pos') == 'new') { ?>checked="checked" <?php } ?> /><?php echo JText::_('COM_TIENDA_CREATE_A_NEW_USER'); ?>
                </label>
                <div id="new" class="option_data">
                    <input type="text" name="new_email" value="<?php echo $this->session->get('new_email', JText::_('COM_TIENDA_EMAIL'), 'tienda_pos' ); ?>" size="40" onclick="tiendaClearInput( this, '<?php echo JText::_('COM_TIENDA_EMAIL'); ?>' );" />
                    <input type="text" name="new_name" value="<?php echo $this->session->get('new_name', JText::_('COM_TIENDA_FULLNAME'), 'tienda_pos' ); ?>" size="75" onclick="tiendaClearInput( this, '<?php echo JText::_('COM_TIENDA_FULLNAME'); ?>' );" />
                </div>
                <div class="option_data">
                    <input type="checkbox" name="new_username_create" value="yes" checked="checked" />
                    <?php echo JText::_('COM_TIENDA_AUTO_CREATE_USERNAME'); ?>
                    <input type="text" name="new_username" value="<?php echo $this->session->get('new_username', JText::_('COM_TIENDA_USERNAME'), 'tienda_pos' ); ?>" size="40" onclick="tiendaClearInput( this, '<?php echo JText::_('COM_TIENDA_USERNAME'); ?>' );" />
                </div>
            </div>
            
            <div class="option well well-small">
            	<label class="radio">
                <input type="radio" name="user_type" value="anonymous" <?php if ($this->session->get('user_type', '', 'tienda_pos') == 'anonymous') { ?>checked="checked" <?php } ?> /><?php echo JText::_('COM_TIENDA_ANONYMOUS'); ?>
                </label>
                <div id="anonymous" class="option_data">
                    <input type="checkbox" name="anon_emails" value="yes" />
                    <?php echo JText::_('COM_TIENDA_SEND_ANON_EMAILS'); ?>
                    <div class="option_data">
                        <?php echo JText::_('COM_TIENDA_ANON_EMAILS'); ?>
                        <br/>
                        <input type="text" name="anon_email" value="<?php echo $this->session->get('anon_email', JText::_('COM_TIENDA_EMAIL'), 'tienda_pos' ); ?>" size="40" onclick="tiendaClearInput( this, '<?php echo JText::_('COM_TIENDA_EMAIL'); ?>' );" />
                    </div>
                </div>
            </div>
            
            <div class="continue">
                <?php $onclick = "tiendaValidation( '" . $this->validation_url . "', 'validation_message', 'saveStep1', document.adminForm, true, '".JText::_('COM_TIENDA_VALIDATING')."' );"; ?> 
                <input onclick="<?php echo $onclick; ?>" value="<?php echo JText::_('COM_TIENDA_CONTINUE_STEP1'); ?>" type="button" class="button btn btn-success" />
            </div>
        </div>
        

<input type="hidden" name="nextstep" id="nextstep" value="step2" />