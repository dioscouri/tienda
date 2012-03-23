<?php 
	defined('_JEXEC') or die('Restricted access');
	$min_length = TiendaConfig::getInstance()->get( 'password_min_length', 5 );
	$req_num = TiendaConfig::getInstance()->get( 'password_req_num', 1 );
	$req_alpha = TiendaConfig::getInstance()->get( 'password_req_alpha', 1 );
	$req_spec = TiendaConfig::getInstance()->get( 'password_req_spec', 1 );
	Tienda::load('TiendaHelperImage', 'helpers.image');
	$image = TiendaHelperImage::getLocalizedName("help_tooltip.png", Tienda::getPath('images'));
	
	$js_strings = array( 'COM_TIENDA_PASSWORD_VALID', 'COM_TIENDA_PASSWORD_INVALID',
												'COM_TIENDA_PASSWORD_DO_NOT_MATCH', 'COM_TIENDA_PASSWORD_MATCH',
												'COM_TIENDA_SUCCESS', 'COM_TIENDA_ERROR' );
	TiendaHelperImage::addJsTranslationStrings( $js_strings );
	$enable_tooltips = TiendaConfig::getInstance()->get('one_page_checkout_tooltips_enabled', 0);
?>

<div style="clear: both;width:100%;">
	<div class="form_item">
		<div class="form_key">
			<?php 
				echo JText::_('COM_TIENDA_PASSWORD').': '.TiendaGrid::required();
				if( $enable_tooltips ): ?>
				<a class="img_tooltip" href="" > 
					<img src="<?php echo Tienda::getUrl('images').$image; ?>" alt='<?php echo JText::_('COM_TIENDA_HELP'); ?>' />
					<span>
						<?php echo JText::_('COM_TIENDA_PASSWORD_REQUIREMENTS'); ?>: <br />
						<?php 
							echo '- '.JText::sprintf( "COM_TIENDA_PASSWORD_MIN_LENGTH", $min_length ).'<br />';
							if( $req_num )
								echo '- '.JText::_('COM_TIENDA_PASSWORD_REQ_NUMBER').'<br />';
							if( $req_alpha )
								echo '- '.JText::_('COM_TIENDA_PASSWORD_REQ_ALPHA').'<br />';
							if( $req_spec )
								echo '- '.JText::_('COM_TIENDA_PASSWORD_REQ_SPEC').'<br />';
						?>
					</span>
				</a>
				<?php endif; ?>
		</div>
		<div class="form_input">
			<!--   Password 1st   -->
			<input id="password" name="password" type="password" onblur="tiendaCheckPassword( 'message-password', this.form, 'password', <?php echo $min_length ?>, <?php echo $req_num; ?>, <?php echo $req_alpha; ?>, <?php echo $req_spec; ?>  );" class="inputbox_required" size="30" value="" />
		</div>
		<div class="form_message" id="message-password"></div>
	</div>
	<div class="form_item">
		<div class="form_key">
			<?php echo JText::_('COM_TIENDA_VERIFY_PASSWORD').': '.TiendaGrid::required(); ?>
		</div>
		<div class="form_input">
			<!--   Password 2nd   -->
			<input id="password2" name="password2" type="password" onblur="tiendaCheckPassword2( 'message-password2', this.form, 'password', 'password2' );" class="inputbox_required" size="30" value="" />			
		</div>
		<div class="form_message" id="message-password2"></div>
	</div>
</div>