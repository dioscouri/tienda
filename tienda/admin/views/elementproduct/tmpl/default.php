<?php
/**
 * @version	1.5
 * @package	Tienda
 * @author 	Dioscouri Design
 * @link 	http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die('Restricted access');
$state = @$this->state;
$rows = @$this->get('List');
$form = @$this->form;

JHTML::_('behavior.modal');
JHTML::_('behavior.tooltip');
$model = $this->getModel();
$page = $this->get('Pagination'); 
?>
<form action="<?php echo JRoute::_( @$form['action'] .'&tmpl=component&object='.$this->object )?>" method="post" name="adminForm">
<div class="pull-left">
	<?php echo TiendaSelect::productstates(@$state->filter_state, 'product_state', array('class' => 'inputbox', 'onchange' => 'this.form.submit();' ) ); ?>
</div>
<?php echo TiendaGrid::searchform(@$state->filter,JText::_('COM_TIENDA_SEARCH'), JText::_('COM_TIENDA_RESET') ) ?>

<table class="dsc-clear table table-striped">
	<thead>
		<tr>
			<th width="2%" class="title">
				<?php echo DSCGrid::sort( 'ID', 'tbl.product_id', @$state->direction , @$state->order ); ?>
			</th>
			<th style="width:50px;">
				<?php echo JText::_('COM_TIENDA_IMAGE'); ?>
			</th>
			<th class="title">
				<?php echo DSCGrid::sort( 'Name', 'tbl.product_name', @$state->direction, @$state->order ); ?>
			</th>
			<th class="title">
				<?php echo DSCGrid::sort( 'Description', 'tbl.product_description', @$state->direction, @$state->order ); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="15"><?php echo $page->getListFooter(); ?></td>
		</tr>
	</tfoot>
	<tbody>
	<?php
	$k = 0;
	for ($i=0, $n=count( $rows ); $i < $n; $i++)
	{
		$row = &$rows[$i];

		$onclick = "
					window.parent.Dsc.select{$model->getName()}(
					'{$row->product_id}', '".str_replace(array("'", "\""), array("\\'", ""), $row->product_name)."', '".$this->object."'
					);";
		?>
		<tr class="<?php echo "row$k"; ?>">
			
			
			<td style="text-align: center;"><a style="cursor: pointer;"
				onclick="<?php echo $onclick; ?>"> <?php echo $row->product_id;?> </a>
			</td>
			<td>
			<?php
				if (!empty($row->product_full_image) )
				{
					echo TiendaHelperProduct::getImage($row->product_id, '', $row->product_name, 'thumb', false, false, array('width' => 60 ));
				}
			?>	
			</td>				
			<td><a style="cursor: pointer;"
				onclick="<?php echo $onclick; ?>"> <?php echo htmlspecialchars($row->product_name, ENT_QUOTES, 'UTF-8'); ?>
			</a></td>
			<td style="text-align: center;"><a style="cursor: pointer;"
				onclick="<?php echo $onclick; ?>"> <?php echo $row->product_description;?>
			</a></td>
			
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</tbody>
</table>

<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
</form>