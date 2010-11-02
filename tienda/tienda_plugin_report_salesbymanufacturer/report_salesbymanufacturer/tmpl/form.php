<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php $state = @$vars->state; ?>

<p><?php echo JText::_( "This report displays the sales by each manufacturer during a selected time period." ); ?></p>

<div>
	<table class="adminlist">
	<thead>   
		<tr>
			<th style="text-align: center;" class="key">
                <?php echo JText::_("Select Date Range"); ?>
            </th>
            <th style="text-align: center;" class="key">
                <?php echo JText::_("Manufacturer Name"); ?>
            </th>
            <th style="text-align: center;" class="key">
                <?php echo JText::_("Product Attribute Name"); ?>
            </th>
            <th style="text-align: center;" class="key">
                <?php echo JText::_("Product Attribute Option"); ?>
            </th>
            <th style="text-align: center;" class="key">
                <?php echo JText::_("Created"); ?>
            </th>           
        </tr>
        <tr>
        	<th align="left" style="text-align: left;" class="key">
        		<?php $attribs = array('class' => 'inputbox', 'size' => '1'); ?>	
				<?php echo TiendaSelect::reportrange( @$state->filter_range ? $state->filter_range : 'custom', 'filter_range', $attribs, 'range', true ); ?><br/>	
				<span class="label"><?php echo JText::_("From"); ?>:</span><br/>
				<?php echo JHTML::calendar( @$state->filter_date_from, "filter_date_from", "filter_date_from", '%Y-%m-%d %H:%M:%S' ); ?><br/>
				<span class="label"><?php echo JText::_("To"); ?>:</span><br/>
				<?php echo JHTML::calendar( @$state->filter_date_to, "filter_date_to", "filter_date_to", '%Y-%m-%d %H:%M:%S' ); ?>
        	</th>
        	<th class="key">
        		<input type="text" name="filter_manufacturer_name" id="filter_manufacturer_name" value="<?php echo @$state->filter_manufacturer_name; ?>" style="width: 250px;" />
        	</th>
        	<th class="key">
        		<input type="text" name="filter_productattribute_name" id="filter_productattribute_name" value="<?php echo @$state->filter_productattribute_name; ?>" style="width: 250px;" />
        	</th>
        	<th class="key">
        		<input type="text" name="filter_productattributeoption_name" id="filter_productattributeoption_name" value="<?php echo @$state->filter_productattributeoption_name; ?>" style="width: 250px;" />
        	</th>
        	<th class="key" style="text-align: left;">
				<div class="range">
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_("From"); ?>:</span><br/>
                            <?php echo JHTML::calendar( @$state->filter_subscriptions_date_from, "filter_subscriptions_date_from", "filter_subscriptions_date_from", '%Y-%m-%d %H:%M:%S' ); ?>
                        </div>
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_("To"); ?>:</span><br/>
                            <?php echo JHTML::calendar( @$state->filter_subscriptions_date_to, "filter_subscriptions_date_to", "filter_subscriptions_date_to", '%Y-%m-%d %H:%M:%S' ); ?>
                        </div>
                        <div class="rangeline">
                            <span class="label"><?php echo JText::_("Type"); ?>:</span><br/>
                            <?php echo TiendaSelect::subdatetype( @$state->filter_subscriptions_datetype, 'filter_subscriptions_datetype', '', 'filter_subscriptions_datetype' ); ?>
                        </div>
                   </div>
        	</th>
        </tr>
    </thead>
	</table>
</div>