<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('stylesheet', 'mod_tienda_layered_navigation.css', 'modules/mod_tienda_layered_navigation/includes/css/');?>

<?php if($found):?>

<div class="tienda_layered_nav_<?php echo $params->get('multi_mode', 1) ? 'multi' : 'single'?>">

<?php if(count($filters)):?>
<h2><?php echo JText::_("CURRENTLY SHOPPING BY");?></h2>
	<ul class="tienda_browse_currently" id="tienda_browse_currently">
		<?php foreach($filters as $filter):?>
		<li>
			<a class="btn-remove" title="<?php echo JText::_('REMOVE THIS ITEM');?>" href="<?php echo $filter->link;?>"><?php echo JText::_('REMOVE THIS ITEM');?></a>
			<span class="label"><?php echo $filter->label;?>:</span>		
			<span class="value"><?php echo $filter->value;?></span>					
		</li>
		<?php endforeach;?>
	</ul>
<?php endif;?>

<?php if($trackcatcount || $priceRanges || $attributes || $manufacturers):?>
<h2><?php echo JText::_("SHOPPING OPTIONS");?></h2>
<?php endif;?>

<?php if($trackcatcount > 0):?>

	<?php $rootTxt = $params->get('roottext'); ?>
	<h3><?php echo $categories[0]->isroot && !empty( $rootTxt ) ? $rootTxt : $categories[0]->category_name;?></h3>	
	
	<ul id="tienda_browse_category">
	
	<?php foreach($categories as $category):?>

		<?php if($category->category_id != $categories[0]->category_id && $category->product_total > 0):?>
		<li>
			<a href="<?php echo $category->link;?>">
				<span class="refinementLink">
					<?php echo $category->category_name;?>					
				</span>
			</a>
			<span class="narrowValue">
				(<?php echo $category->product_total;?>)
			</span>							
		</li>		
		<?php endif;?>	
	
	<?php endforeach;?>

	</ul>
<?php endif;?>

<?php if(count($priceRanges) > 0):?>
	<h3><?php echo JText::_('PRICE');?></h3>
	<ul id="tienda_browse_pricerange">
		<?php foreach($priceRanges as $priceRange):?>	
			<?php if($priceRange->total > 0):?>		
			<li>
				<a href="<?php echo $priceRange->link;?>">
					<span class="refinementLink">
						<?php echo TiendaHelperBase::currency($priceRange->price_from).' - '.TiendaHelperBase::currency($priceRange->price_to);?>
					</span>
				</a>
				<span class="narrowValue">
					(<?php echo $priceRange->total;?>)
				</span>							
			</li>	
			<?php endif;?>		
		<?php endforeach;?>
	</ul>
<?php endif;?>

<?php if(count($attributes) > 0):?>
	<?php foreach($attributes as $attribute):?>
		<h3><?php echo $attribute->productattribute_name;?></h3>
		<ul id="tienda_browse_attribute">
			<?php foreach($attribute->productattribute_options as $option):?>				
			<?php if(isset($attributeOptions[$option->productattributeoption_id]) && ($option->productattributeoption_name == $attributeOptions[$option->productattributeoption_id])) continue; ?>	
				<li>
					<a href="<?php echo $option->link;?>">
						<span class="refinementLink">	
						<?php echo $option->productattributeoption_name;?>					
						</span>
					</a>
					<span class="narrowValue">
						(<?php echo $option->total;?>)
					</span>								
				</li>			
			<?php endforeach;?>
		</ul>
	<?php endforeach;?>
<?php endif;?>

<?php if(count($ratings) > 0):?>
	<h3><?php echo JText::_('Avg. Customer Rating');?></h3>
	<ul id="tienda_browse_rating">
		<?php foreach($ratings as $rating):?>
			<?php if($rating->total > 0):?>
			<li>
				<a href="<?php echo $rating->link;?>">
					<span class="refinementLink">
						<?php echo $rating->rating_name;?>
					</span>
				</a>
				<a href="<?php echo $rating->link;?>">
					<span class="refinementLink">
						<?php echo JText::_('& Up');?>
					</span>
				</a>				
				<span class="narrowValue">
					(<?php echo $rating->total;?>)
				</span>							
			</li>
			<?php endif;?>
		<?php endforeach;?>
	</ul>
<?php endif;?>

<?php if(count($manufacturers) > 0):?>
	<h3><?php echo JText::_('MANUFACTURERS');?></h3>
	<ul id="tienda_browse_manufacturer">
		<?php foreach($manufacturers as $manufacturer):?>
			<?php if($manufacturer->total > 0):?>
			<li>
				<a href="<?php echo $manufacturer->link;?>">
					<span class="refinementLink">
						<?php echo $manufacturer->manufacturer_name;?>
					</span>
				</a>
				<span class="narrowValue">
					(<?php echo $manufacturer->total;?>)
				</span>							
			</li>
			<?php endif;?>
		<?php endforeach;?>
	</ul>
<?php endif;?>

</div>

<?php endif;?>