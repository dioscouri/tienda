<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $product_id = @$this->product_id; ?>
<?php $attributes = @$this->attributes; ?>
<?php Tienda::load('TiendaUrl', 'library.url'); ?>

<?php foreach (@$attributes as $attribute) : ?>
	[<a href="<?php echo "index.php?option=com_tienda&view=productattributes&task=delete&cid[]=".$attribute->productattribute_id."&return=".base64_encode("index.php?option=com_tienda&view=products&task=edit&id=".$product_id); ?>">
    	<?php echo JText::_("Remove"); ?>
	</a>]
    [<?php echo TiendaUrl::popup( "index.php?option=com_tienda&view=products&task=setattributeoptions&id=".$attribute->productattribute_id."&tmpl=component", JText::_( "Set Attribute Options" ) ); ?>]
    <?php echo $attribute->productattribute_name; ?>
    <?php echo "(".$attribute->option_names_csv.")"; ?>
    <br/>
<?php endforeach; ?>