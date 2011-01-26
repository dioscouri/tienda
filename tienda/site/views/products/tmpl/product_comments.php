<?php
defined('_JEXEC') or die('Restricted access');

$item = @$this->item;
$form = @$this->form;
$values = @$this->values;
$row= @$this->row;
$click=@$this->click;
$reviews=@$this->reviews;
$url = JURI::getInstance()->toString();
$root  = JURI::getInstance()->root();
$return_url = str_replace($root , '', $url);
$linkurl=base64_encode( $url );
$Itemid = JRequest::getInt('Itemid', '0');
$result=@$this->result;
$review_enable=@$this->review_enable;
$count=@$this->count;
$publickey = "6LcAcbwSAAAAAIEtIoDhP0cj7AAQMK9hqzJyAbeD";
$baseurl=$this->baseurl;
$user = JFactory::getUser();

if (($review_enable==1)&&($result == 1 || $count > 0 ) ) { 	
	$emails = TiendaHelperProduct::getUserEmailForReview( $row->product_id );
?>
<div id="product_review_header" class="tienda_header">
    <span><?php echo JText::_("Reviews"); ?></span>
</div>
<?php } ?>
 <div>
    <div class="rowDiv" style="padding-top: 5px;">
        <?php if ($review_enable==1 && $result == 1): ?>
        	<div class="leftAlignDiv">
        		<input onclick="tiendaShowHideDiv('new_review_form');" value="<?php echo JText::_('Add Review'); ?>" type="button" class="button" />
        	</div>
        <?php endif;?>    
    	<div class="rightAlignDiv">
    	<?php if ($review_enable==1 && $count > 0  ): ?>
    		<form name="sort" method="post" action="<?php echo JRoute::_($url); ?>">
    		<?php echo JText::_('Sort By'); ?>:
    		<?php echo TiendaSelect::selectsort( $this->selectsort, 'default_selectsort' ); ?> 
    		</form>
    	<?php endif;?>
    	</div>
    </div>    
    <div id="new_review_form" class="rowPaddingDiv" style="display: none;">
        <form action="<?php echo $click;?>" method="post" class="adminform" name="commentsForm" enctype="multipart/form-data" >    
            <div><?php echo JText::_('Rating'); ?>: *</div>
            <?php for ($count=1; $count<=5; $count++): ?>
                <span id="rating_<?php echo $count; ?>">
                <a href="javascript:void(0);" onclick="javascript:tiendaRating(<?php echo $count; ?>);">
                <img id="rate_<?php echo $count; ?>" src="media/com_tienda/images/star_00.png">
                </a>
                </span>
            <?php endfor; ?>
            <?php if ($user->guest || !$user->id) {?>
            <div><?php echo JText::_( 'Name' ); ?>: *</div>
            <div><input type="text" maxlength="100" class="inputbox" value="<?php echo base64_decode(JRequest::getVar('rn', ''));?>" size="40" name="user_name" id="user_name"/></div>
        	<div><?php echo JText::_( 'Email' ); ?>: *</div>
            <div><input type="text" maxlength="100" class="inputbox" value="<?php echo base64_decode(JRequest::getVar('re', ''));?>" size="40" name="user_email" id="user_email"/></div>
        	<?php }else{?>
        	<input type="hidden" maxlength="100" class="inputbox" value="<?php echo $user->email;?>" size="40" name="user_email" id="user_email"/>
        	<input type="hidden" maxlength="100" class="inputbox" value="<?php echo $user->name;?>" size="40" name="user_name" id="user_name"/>
        	<?php }?>
            <div><?php echo JText::_( 'Comment' ); ?>: *</div>
            <div><textarea name="productcomment_text" id="productcomment_text" rows="10" style="width: 99%;" ><?php echo base64_decode(JRequest::getVar('rc', ''));?></textarea></div>
            <?php if (TiendaConfig::getInstance()->get('use_captcha', '0') == 1 ): ?>          
            <?php Tienda::load( 'TiendaRecaptcha', 'library.recaptcha' );?>
            <?php $recaptcha = new TiendaRecaptcha(); ?>
            <div><?php echo $recaptcha->recaptcha_get_html($publickey); ?></div>
            <?php endif;?>                    
            <input type="submit" name="review" id="review"  value="<?php echo JText::_( "Submit Comment" ); ?>" />
            <input type="hidden" name="product_id"   value="<?php echo $row->product_id;?>" />
            <input type="hidden" name="user_id" value="<?php echo JFactory::getUser()->id; ?>" />
            <input type="hidden" name="productcomment_rating" id="productcomment_rating" value="" />
            <input type="hidden" name="Itemid" id="Itemid" value="<?php echo $Itemid; ?>" />
        </form>
    </div>
   <?php if($review_enable==1):?>
    <?php foreach ($reviews as $review) : ?>
    <div class="rowPaddingDiv">
        <div class="commentsDiv1">
			<div class="rowDiv">
                <div class="userName">
                   <span><?php echo empty($review->user_name) ? $review->user_email : $review->user_name;?></span> 
                </div>                
                <div class="dateDiv" >
                    <?php echo "(".JHTML::_('date', $review->created_date,'').")";?>
                    <?php if($review->helpful_votes_total!=0 ){?>
                    <?php echo sprintf( JText::_('X of X found this helpful'), $review->helpful_votes, $review->helpful_votes_total); ?>
                    <?php }?>
                </div>                
                <div class="customerRating">
                    <span>
                        <?php echo TiendaHelperProduct::getRatingImage( $review->productcomment_rating ); ?>
                	</span>
                </div>
            </div>                  
            <div id="comments" class="commentsDiv">
                <?php echo $review->productcomment_text; ?>
            </div>            
       		<?php 
			$isFeedback = TiendaHelperProduct::isFeedbackAlready( $user->id, $review->productcomment_id );    		
       		$helpfuness_enable = TiendaConfig::getInstance()->get('review_helpfulness_enable', '0');
       		?>
            <?php if ($helpfuness_enable && $user->id != $review->user_id && !$isFeedback) : ?>
       		<div id="helpful" class="commentsDiv">
      			 <?php echo JText::_( "Was this review helpful to you" ); ?>?
      			 <a href="index.php?option=com_tienda&view=products&task=reviewHelpfullness&helpfulness=1&productcomment_id=<?php echo $review->productcomment_id; ?>&product_id=<?php echo $review->product_id; ?>"><?php echo JText::_( "Yes" ); ?></a> 
      			 <a href="index.php?option=com_tienda&view=products&task=reviewHelpfullness&helpfulness=0&productcomment_id=<?php echo$review->productcomment_id;?>&product_id=<?php echo $review->product_id;?>"><?php echo JText::_( "No" ); ?></a>
      			 <a href="index.php?option=com_tienda&view=products&task=reviewHelpfullness&report=1&productcomment_id=<?php echo$review->productcomment_id;?>&product_id=<?php echo $review->product_id;?>">(<?php echo JText::_( "Report Inappropriate Review" ); ?>)</a>
      		</div>
      		<?php endif; ?>      		
      		<?php $share_review_enable = TiendaConfig::getInstance()->get('share_review_enable', '0');?>
            <?php if ($share_review_enable): ?>
      		<div id="links" class="commentsDiv">
      		<span class="share_review"><?php echo JText::_( "Share this review" ); ?>:</span>      			
      			 <a href="http://www.facebook.com/share.php?u=<?php echo $linkurl;?>" target='_blank'> <img  src="<?php echo $baseurl;?>/media/com_tienda/images/bookmark/facebook.png"/></a>
      			 <a href="http://twitter.com/home?status=<?php echo $linkurl;?>" target='_blank'> <img  src="<?php echo $baseurl;?>/media/com_tienda/images/bookmark/twitter.png"/></a>
      			 <a href="http://www.tumblr.com/login?s=<?php echo $linkurl;?>" target='_blank'> <img  src="<?php echo $baseurl;?>/media/com_tienda/images/bookmark/link-tumblr.PNG"/></a>
      			 <a href="http://www.stumbleupon.com/submit?url=<?php echo $linkurl;?>&title=<?php echo $row->product_name;?>" target='_blank'> <img  src="<?php echo $baseurl;?>/media/com_tienda/images/bookmark/stumbleupon.png"/></a>
      			 <a href="http://www.google.com/buzz/post?url=<?php echo $linkurl; ?>" target='_blank'> <img  src="<?php echo $baseurl;?>/media/com_tienda/images/bookmark/google_buzz.png"/></a>    			 
      		</div>
       		<?php endif; ?>       		
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif;?>     
    <div id="products_footer">
        <?php echo @$this->pagination->getPagesLinks(); ?>
    </div>
</div>