<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>
<div class="container-fluid">
<div class="span5">
<h3>FILTERS<?php // echo JText::_('COM_TIENDA_RESULTS'); ?></h3>
<div class="accordion" id="accordion2">
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseAbout">
       About this report
      </a>
    </div>
    <div id="collapseAbout" class="accordion-body collapse">
      <div class="accordion-inner">
      <?php echo JText::_('COM_TIENDA_THIS_REPORT_DISPLAYS_THE_SALES_BY_EACH_MANUFACTURER_DURING_A_SELECTED_TIME_PERIOD'); ?>
      </div>
    </div>
  </div>  
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">
       <?php echo JText::_('COM_TIENDA_SELECT_DATE_RANGE'); ?>
      </a>
    </div>
    <div id="collapseOne" class="accordion-body collapse in">
      <div class="accordion-inner">
        <?php $attribs = array('class' => 'inputbox', 'size' => '1'); ?>   
                <?php echo TiendaSelect::reportrange( @$state->filter_range ? $state->filter_range : 'custom', 'filter_range', $attribs, 'range', true ); ?>
                <div class="input-prepend input-append">    
                <span class="add-on"><?php echo JText::_('COM_TIENDA_FROM'); ?>:</span>
                <?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' ); ?>
                </div>
                <div class="input-prepend input-append">
                <span class="add-on"><?php echo JText::_('COM_TIENDA_TO'); ?>:</span>
                <?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d %H:%M:%S' ); ?>
                </div>
      </div>
    </div>
  </div>
  <div class="accordion-group">
    <div class="accordion-heading">
      <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">
        <?php echo JText::_('COM_TIENDA_MANUFACTURER_NAME'); ?>
      </a>
    </div>
    <div id="collapseTwo" class="accordion-body collapse in">
      <div class="accordion-inner">
         <span class="label"><?php echo JText::_('COM_TIENDA_TYPE'); ?>:</span>
       <?php echo TiendaSelect::subdatetype( @$state->filter_datetype, 'filter_datetype', '', 'filter_datetype' ); ?>
       <input type="text" name="filter_manufacturer_name" id="filter_manufacturer_name" value="<?php echo @$state->filter_manufacturer_name; ?>" style="width: 250px;" />
    
      </div>
    </div>
  </div>
</div>
</div>