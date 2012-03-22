<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $form = @$this->form; ?>
<?php $row = @$this->row; ?>

<form action="<?php echo JRoute::_( @$form['action'] ) ?>" method="post" class="adminform" name="adminForm" enctype="multipart/form-data" >

	<?php echo TiendaGrid::pagetooltip( 'products_view' ); ?>

    <table style="width: 100%;">
    <tr>
        <td style="width: 70%; max-width: 70%; vertical-align: top; padding: 0px 5px 0px 0px;">


		<fieldset>
			<legend><?php echo JText::_('COM_TIENDA_PRODUCT'); ?></legend>
				<table class="admintable">
					<tr>
						<td width="100" align="right" class="key">
							<?php echo JText::_('Name'); ?>:
						</td>
						<td>
							<?php echo @$row->product_name; ?>
						</td>
					</tr>
					<tr>
						<td width="100" align="right" class="key">
							<?php echo JText::_('Model'); ?>:
						</td>
						<td>
							<?php echo @$row->product_model; ?>
						</td>
					</tr>
					<tr>
						<td width="100" align="right" class="key">
							<?php echo JText::_('SKU'); ?>:
						</td>
						<td>
							<?php echo @$row->product_sku; ?>
						</td>
					</tr>
					<tr>
						<td width="100" align="right" class="key">
							<?php echo JText::_('Enabled'); ?>:
						</td>
						<td>
							<?php echo TiendaGrid::boolean( $row->product_enabled ); ?>
						</td>
					</tr>
					<tr>
						<td width="100" align="right" class="key">
							<?php echo JText::_('Current Image'); ?>:
						</td>
						<td>
							<?php
							jimport('joomla.filesystem.file');
							if (!empty($row->product_full_image) && JFile::exists( Tienda::getPath( 'products_images').DS.$row->product_full_image ))
							{
								?>
								<img src="<?php echo Tienda::getURL( 'products_images').$row->product_full_image; ?>" style="display: block;" />
								<?php	
							}
							?>
						</td>
					</tr>
				</table>
		</fieldset>
		
            <?php
            $modules = JModuleHelper::getModules("tienda_product_dashboard_main");
            $document   = &JFactory::getDocument();
            $renderer   = $document->loadRenderer('module');
            $attribs    = array();
            $attribs['style'] = 'xhtml';
            foreach ( @$modules as $mod )
            {
                echo $renderer->render($mod, $attribs);
            }
            ?>
        </td>
        <td style="vertical-align: top; width: 30%; min-width: 30%; padding: 0px 0px 0px 5px;">

            <?php
            $modules = JModuleHelper::getModules("tienda_product_dashboard_right");
            $document   = &JFactory::getDocument();
            $renderer   = $document->loadRenderer('module');
            $attribs    = array();
            $attribs['style'] = 'xhtml';
            foreach ( @$modules as $mod )
            {
                $mod_params = new JParameter( $mod->params );
                if ($mod_params->get('hide_title', '1')) { $mod->showtitle = '0'; }
                echo $renderer->render($mod, $attribs);
            }
            ?>
        </td>
    </tr>
    </table>

<input type="hidden" name="id" value="<?php echo @$row->product_id; ?>" />
<input type="hidden" name="task" id="task" value="" />
</form>