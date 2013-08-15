<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$this->state; ?>
<?php $form = @$this->form; ?>
<?php $items = @$this->items; ?>

<?php DSC::loadHighcharts(); ?>

	<?php echo TiendaGrid::pagetooltip( JRequest::getVar('view') ); ?>

	<table style="width: 100%;">
	<tr>
		<td style="width: 70%; max-width: 70%; vertical-align: top; padding: 0px 5px 0px 5px;">
		
		    <form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" enctype="multipart/form-data">

			<table class="table table-striped table-bordered" style="margin-bottom: 5px;">
			<thead>
			<tr>
				<th><?php echo JText::_('COM_TIENDA_RANGE'); ?></th>
				<th><?php echo JText::_('COM_TIENDA_REVENUE'); ?></th>
				<th><?php echo JText::_('COM_TIENDA_ORDERS'); ?></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<?php $attribs = array('class' => 'inputbox', 'onchange' => 'document.adminForm.submit();'); ?>
				<?php
				//this is dumb, but it makes the dashboard work until caching issue is resolve 
				 if(@$state->stats_interval) : ?>
				<td style="text-align: center; width: 33%;"><h3><?php echo TiendaSelect::range( @$state->stats_interval, 'stats_interval', $attribs); ?></h3></td>
				<?php else :?>
				<td style="text-align: center; width: 33%;"><h3><?php echo TiendaSelect::range( @$state->stats_interval, 'stats_interval', $attribs, null, true ); ?></h3></td>
				<?php endif ?>
				<td style="text-align: center; width: 33%;"><h3><?php echo TiendaHelperBase::currency( @$this->sum ); ?></h3></td>
				<td style="text-align: center; width: 33%;"><h3><?php echo TiendaHelperBase::number( @$this->total, array('num_decimals'=>'0') ); ?></h3></td>
			</tr>
			</tbody>
			</table>

            <div class="section">
                <?php 
                $chart = new HighRoller();
                $chart->chart->renderTo = 'chart';
                $chart->chart->type = 'mixed';
                
                $chart->plotOptions = new stdClass();
                $chart->plotOptions->column = new stdClass();
                $chart->plotOptions->column->pointStart = strtotime( @$this->revenue[0][0] ) * 1000;
                $chart->plotOptions->column->pointInterval = @$this->interval->pointinterval;
                $chart->plotOptions->line = new stdClass();
                $chart->plotOptions->line->pointStart = strtotime( @$this->orders[0][0] ) * 1000;
                $chart->plotOptions->line->pointInterval = @$this->interval->pointinterval;
                
                $chart->xAxis = new stdClass();
                $chart->xAxis->labels = new stdClass();
                $chart->xAxis->type = 'datetime';
                $chart->xAxis->tickInterval = $chart->plotOptions->line->pointInterval;
                $chart->xAxis->labels->rotation = -45;
                $chart->xAxis->labels->align = 'right';
                $chart->xAxis->labels->step = @$this->interval->step;
                
                $left_y_axis = new stdClass();
                $left_y_axis->title = new stdClass();
                $left_y_axis->title->text = JText::_( 'COM_TIENDA_REVENUE' );
                $left_y_axis->min = 0;
                $left_y_axis->minRange = 8;
                $left_y_axis->allowDecimals = false;
                $left_y_axis->endOnTick = true;
                
                $right_y_axis = new stdClass();
                $right_y_axis->title = new stdClass();
                $right_y_axis->title->text = JText::_( 'COM_TIENDA_ORDERS' );
                $right_y_axis->min = 0;
                $right_y_axis->minRange = 8;
                $right_y_axis->allowDecimals = false;
                $right_y_axis->endOnTick = true;
                $right_y_axis->opposite = true;

                $chart->yAxis = array($left_y_axis, $right_y_axis);
                
                $chart->legend->borderWidth = '1';
                
                $series = new HighRollerSeriesData();
                $series->addName(JText::_( 'COM_TIENDA_REVENUE' ))->addData( @$this->revenue );
                $series->type = 'column';
                $chart->addSeries($series);
                
                $series = new HighRollerSeriesData();
                $series->addName(JText::_( 'COM_TIENDA_ORDERS' ))->addData( @$this->orders );
                $series->yAxis = 1;
                $series->type = 'line';
                $chart->addSeries($series);

                ?>
                
                <div id="chart" style="width: 100%;"></div>
                
                <script type="text/javascript">
                  <?php echo $chart->renderChart();?>
                </script>
            
            </div>

            <?php echo @$this->form['validate']; ?>
            </form>
            
			<?php
			$modules = JModuleHelper::getModules("tienda_dashboard_main");
			$document	= JFactory::getDocument();
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
			$document	= JFactory::getDocument();
			$renderer	= $document->loadRenderer('module');
			$attribs 	= array();
			$attribs['style'] = 'xhtml';
			foreach ( @$modules as $mod )
			{
				$mod_params = new DSCParameter( $mod->params );
				if ($mod_params->get('hide_title', '1')) { $mod->showtitle = '0'; }
				echo $renderer->render($mod, $attribs);
			}
			?>
		</td>
	</tr>
	</table>
