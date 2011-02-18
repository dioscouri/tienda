<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php if($found):?>

<div class="tienda_layered_nav">

<h2><?php echo JText::_("Shopping Options");?></h2>

<?php if(count($categories) > 1):?>
	<h3><?php echo $categories[0]->category_name;?></h3>
	<ul id="tienda_browse_category">
		<?php foreach($categories as $category):?>
			<?php if($category->category_id != $categories[0]->category_id && $category->product_total > 0):?>
			<li>
				<a href="<?php echo $category->link;?>">
					<span class="refinementLink">
						<?php echo $category->category_name;?>
					</span>
					<span class="narrowValue">
						(<?php echo $category->product_total;?>)
					</span>
				</a>			
			</li>
			<?php endif;?>
		<?php endforeach;?>
	</ul>
<?php endif;?>

<?php if(count($manufacturers) > 0):?>
	<h3><?php echo JText::_('Manufacturers');?></h3>
	<ul id="tienda_browse_manufacturer">
		<?php foreach($manufacturers as $manufacturer):?>
			<?php if($manufacturer->total > 0):?>
			<li>
				<a href="<?php echo $manufacturer->link;?>">
					<span class="refinementLink">
						<?php echo $manufacturer->manufacturer_name;?>
					</span>
					<span class="narrowValue">
						(<?php echo $manufacturer->total;?>)
					</span>
				</a>			
			</li>
			<?php endif;?>
		<?php endforeach;?>
	</ul>
<?php endif;?>

<?php if(count($priceRanges) > 0):?>
	<h3><?php echo JText::_('Price');?></h3>
	<ul id="tienda_browse_manufacturer">
		<?php foreach($priceRanges as $priceRange):?>			
			<li>
				<a href="<?php echo $priceRange->link;?>">
					<span class="refinementLink">
						<?php echo TiendaHelperBase::currency($priceRange->price_from).' - '.TiendaHelperBase::currency($priceRange->price_to);?>
					</span>
					<span class="narrowValue">
						(<?php echo $priceRange->total;?>)
					</span>
				</a>			
			</li>			
		<?php endforeach;?>
	</ul>
<?php endif;?>

</div>

<?php endif;?>