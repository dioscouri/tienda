<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>

<?php $state = @$vars->state; ?>
<?php $attribs = array('class' => ''); ?>

<?php // echo JText::_('COM_TIENDA_THIS_REPORTS_ON_BEST_SELLING_PRODUCTS'); ?>
<div class="navbar">
  <div class="navbar-inner">
    <a class="brand" href="#">BESTSELLERS</a>

    <ul class="nav navbar-form pull-left">
    	 
    	 <li class="divider-vertical"></li>
      <li><?php echo TiendaSelect::reportrange( @$state->filter_range ? $state->filter_range : 'custom', 'filter_range', $attribs, 'range', true ); ?></li>
      <li class="divider-vertical"></li>
      <li>
      	<div class="input-prepend input-append">
      	<span class="add-on "><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span>
	            <?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d 00:00:00' ); ?></div></li>
	            <li class="divider-vertical"></li>
      <li> 
	            <div class="input-prepend input-append">
	            	<span class="add-on"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span>
	            	<?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d 00:00:00' ); ?></div></li>
	           
	            <li class="divider-vertical"></li>
	            <li>  <?php //$attribs = array('class' => '',  'onchange' => 'javascript:submitbutton(\'view\').click;'); ?><span class="label pull-left"><?php echo JText::_('LIMIT'); ?></span>
	            <?php echo TiendaSelect::limit( @$state->limit ? $state->limit : '20', 'limit', $attribs, 'limit', true ); ?></li>
    </ul></li>


  </div>
</div>
        