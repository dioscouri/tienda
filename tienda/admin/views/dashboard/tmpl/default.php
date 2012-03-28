<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>

	<table style="width: 100%;">
	<tr>
		<td style="width: 70%; max-width: 70%; vertical-align: top; padding: 0px 5px 0px 5px;">
		
		    <form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

			<table class="adminlist" style="margin-bottom: 5px;">
			<thead>
			<tr>
				<th><?php echo JText::_('COM_TIENDA_RANGE'); ?></th>
				<th><?php echo JText::_('Revenue'); ?></th>
				<th><?php echo JText::_('Orders'); ?></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<?php $attribs = array('class' => 'inputbox', 'size' => '1', 'onchange' => 'document.adminForm.submit();'); ?>
				<td style="text-align: center; width: 33%;"><h3><?php echo TiendaSelect::range( @$state->stats_interval, 'stats_interval', $attribs ); ?></h3></td>
				<td style="text-align: center; width: 33%;"><h3><?php echo TiendaHelperBase::currency( @$this->graphData->sum ); ?></h3></td>
				<td style="text-align: center; width: 33%;"><h3><?php echo TiendaHelperBase::number( @$this->graphData->total, array('num_decimals'=>'0') ); ?></h3></td>
			</tr>
			</tbody>
			</table>

			<?php
			jimport('joomla.html.pane');
			$tabs = JPane::getInstance( 'tabs' );

			echo $tabs->startPane("tabone");
			echo $tabs->startPanel( JText::_('Orders'), "orders" );

				echo "<h2>".@$this->graph->title."</h2>";
				echo @$this->graph->image;

			echo $tabs->endPanel();
			echo $tabs->startPanel( JText::_('Amounts'), "amounts" );

				echo "<h2>".@$this->graphSum->title."</h2>";
				echo @$this->graphSum->image;

			echo $tabs->endPanel();
			echo $tabs->endPane();
			?>

            <?php echo $this->form['validate']; ?>
            </form>
            
			<?php
			$modules = JModuleHelper::getModules("tienda_dashboard_main");
			$document	= &JFactory::getDocument();
			$renderer	= $document->loadRenderer('module');
			$attribs 	= array();
			$attribs['style'] = 'xhtml';
			foreach ( @$modules as $mod )
			{
				echo $renderer->render($mod, $attribs);
			}
			?>
		</td>
		<td style="vertical-align: top; width: 30%; min-width: 30%; padding: 0px 5px 0px 5px;">

			<?php
			$modules = JModuleHelper::getModules("tienda_dashboard_right");
			$document	= &JFactory::getDocument();
			$renderer	= $document->loadRenderer('module');
			$attribs 	= array();
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
