<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php JHTML::_('script', 'tienda.js', 'media/com_tienda/js/'); ?>
<?php JHTML::_('stylesheet', 'pos.css', 'media/com_tienda/css/'); ?>
<?php $state = @$this->state; ?>
<?php $row = @$this->product; ?>

<form action="index.php?option=com_tienda&view=pos&tmpl=component" method="post" name="adminForm" enctype="multipart/form-data">
    <div class="pos">

        <div class="table">
            <div class="row">
                <div class="cell product_title">
                    <h2><?php echo $row->product_name; ?></h2>
                    
                    <a href="index.php?option=com_tienda&view=pos&task=addproducts&tmpl=component">
                        <?php echo JText::_( "RETURN_TO_SEARCH_RESULTS" ); ?>
                    </a>
                    
                    <p>
                        <?php echo $row->product_description_short; ?>
                    </p>
                </div>
                
                <div class="cell product_body">
                    <?php echo $this->loadTemplate( 'addtocart' ); ?>
                </div>                
            </div>
        </div>
    
    </div>
</form>