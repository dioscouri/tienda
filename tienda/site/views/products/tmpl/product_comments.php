<?php
defined('_JEXEC') or die('Restricted access');

$row= @$this->row;
$click = @$this->comments_data->click;
$reviews = @$this->comments_data->reviews;
$selectsort = $this->comments_data->selectsort;
$result = @$this->comments_data->result;
$review_enable=@$this->comments_data->review_enable;
$count=@$this->comments_data->count;

$url = JURI::getInstance()->toString();
$root  = JURI::getInstance()->root();
$return_url = str_replace($root , '', $url);
$linkurl= TiendaHelperProduct::getSocialBookMarkUri( $url );
$Itemid = JRequest::getInt('Itemid', '0');
$publickey = "6LcAcbwSAAAAAIEtIoDhP0cj7AAQMK9hqzJyAbeD";
$baseurl=$this->baseurl;
$user = JFactory::getUser();
$url_validate = JRoute::_( 'index.php?option=com_tienda&controller=products&view=products&task=validateReview&format=raw' );
$share_review_enable = Tienda::getInstance()->get('share_review_enable', '0');

if (($review_enable==1)&&($result == 1 || $count > 0 ) ) { 	
	$emails = TiendaHelperProduct::getUserEmailForReview( $this->comments_data->product_id );
?>
<div id="product_review_header" class="tienda_header">
    <span><?php echo JText::_('COM_TIENDA_REVIEWS'); ?></span>
</div>
<?php } ?>
 <div>
    <div class="rowDiv" style="padding-top: 5px;">
        <?php if ($review_enable==1 && $result == 1): ?>
        	<div class="leftAlignDiv">
        		<input onclick="tiendaShowHideDiv('new_review_form');" value="<?php echo JText::_('COM_TIENDA_ADD_REVIEW'); ?>" type="button" class="btn" />
        	</div>
        <?php endif;?>    
    	<div class="rightAlignDiv">
    	<?php if ($review_enable==1 && $count > 0  ): ?>
    		<form name="sortForm" method="post" action="<?php echo JRoute::_($url); ?>">
    		<?php echo JText::_('COM_TIENDA_SORT_BY'); ?>:
    		<?php echo TiendaSelect::selectsort( $selectsort, 'default_selectsort', array('class' => 'inputbox', 'size' => '1','onchange'=>'document.sortForm.submit();') ); ?> 
    		</form>
    	<?php endif;?>
    	</div>
    </div>    
    <div id="new_review_form" class="rowPaddingDiv" style="display: none;">
    		<div id="validationmessage_comments" style="padding-top: 10px;"></div>
        <form action="<?php echo $click;?>" method="post" class="adminform" name="commentsForm" enctype="multipart/form-data" >    
            <div><?php echo JText::_('COM_TIENDA_RATING'); ?>: *</div>
            <?php echo TiendaHelperProduct::getRatingImage( 5, $this, true  ); ?>
            <?php if ($user->guest || !$user->id) {?>
            <div><?php echo JText::_('COM_TIENDA_NAME'); ?>: *</div>
            <div><input type="text" maxlength="100" class="inputbox" value="<?php echo base64_decode(JRequest::getVar('rn', ''));?>" size="40" name="user_name" id="user_name"/></div>
        	<div><?php echo JText::_('COM_TIENDA_EMAIL'); ?>: *</div>
            <div><input type="text" maxlength="100" class="inputbox" value="<?php echo base64_decode(JRequest::getVar('re', ''));?>" size="40" name="user_email" id="user_email"/></div>
        	<?php }else{?>
        	<input type="hidden" maxlength="100" class="inputbox" value="<?php echo $user->email;?>" size="40" name="user_email" id="user_email"/>
        	<input type="hidden" maxlength="100" class="inputbox" value="<?php echo $user->name;?>" size="40" name="user_name" id="user_name"/>
        	<?php }?>
            <div><?php echo JText::_('COM_TIENDA_COMMENT'); ?>: *</div>
            <div><textarea name="productcomment_text" id="productcomment_text" rows="10" style="width: 99%;" ><?php echo base64_decode(JRequest::getVar('rc', ''));?></textarea></div>
            <?php 
            	if (Tienda::getInstance()->get('use_captcha', '0') == 1 ):
            		Tienda::load( 'TiendaRecaptcha', 'library.recaptcha' );
            		$recaptcha = new TiendaRecaptcha();
            ?>
            <div><?php echo $recaptcha->recaptcha_get_html($publickey); ?></div>
            <?php endif;?>                    
            <input type="button" name="review" id="review" onclick="javscript:tiendaFormValidation( '<?php echo $url_validate; ?>','validationmessage_comments', 'addReview', document.commentsForm );" value="<?php echo JText::_('COM_TIENDA_SUBMIT_COMMENT'); ?>" />
            <input type="hidden" name="product_id"   value="<?php echo $this->comments_data->product_id;?>" />
            <input type="hidden" name="user_id" value="<?php echo $user->id; ?>" />
            <input type="hidden" name="productcomment_rating" id="productcomment_rating" value="" />
            <input type="hidden" name="Itemid" id="Itemid" value="<?php echo $Itemid; ?>" />
            <input type="hidden" name="task" value="" />
        </form>
    </div>
   <?php
   		if($review_enable==1):
   		 foreach ($reviews as $review) :
   ?>
    <div class="rowPaddingDiv">
        <div class="commentsDiv1">
			<div class="rowDiv">
                <div class="userName">
                   <span><?php echo empty($review->user_name) ? ( empty( $review->username ) ? $review->user_email : $review->username ) : $review->user_name;?></span> 
                </div>                
                <div class="dateDiv" >
                    <?php 
                    	echo "(".JHTML::_('date', $review->created_date,'').")";
                    	
                    	if($review->helpful_votes_total!=0 ){
                    		echo sprintf( JText::_('COM_TIENDA_X_OF_X_FOUND_THIS_HELPFUL'), $review->helpful_votes, $review->helpful_votes_total);
                    	}
                    ?>
                </div>                
                <div class="customerRating">
                    <span>
                        <?php echo TiendaHelperProduct::getRatingImage( $review->productcomment_rating, $this ); ?>
                	</span>
                </div>
            </div>                  
            <div id="comments" class="commentsDiv">
                <?php echo $review->productcomment_text; ?>
            </div>            
       		<?php 
						$isFeedback = TiendaHelperProduct::isFeedbackAlready( $user->id, $review->productcomment_id );    		
	       		$helpfuness_enable = Tienda::getInstance()->get('review_helpfulness_enable', '0');
	
	       		if ($helpfuness_enable && $user->id != $review->user_id && !$isFeedback) :
       		?>
       		<div id="helpful" class="commentsDiv">
      			 <?php echo JText::_('COM_TIENDA_WAS_THIS_REVIEW_HELPFUL_TO_YOU'); ?>?
      			 <a href="index.php?option=com_tienda&view=products&task=reviewHelpfullness&helpfulness=1&productcomment_id=<?php echo $review->productcomment_id; ?>&product_id=<?php echo $review->product_id; ?>"><?php echo JText::_('COM_TIENDA_YES'); ?></a> 
      			 <a href="index.php?option=com_tienda&view=products&task=reviewHelpfullness&helpfulness=0&productcomment_id=<?php echo$review->productcomment_id;?>&product_id=<?php echo $review->product_id;?>"><?php echo JText::_('COM_TIENDA_NO'); ?></a>
      			 <a href="index.php?option=com_tienda&view=products&task=reviewHelpfullness&report=1&productcomment_id=<?php echo$review->productcomment_id;?>&product_id=<?php echo $review->product_id;?>">(<?php echo JText::_('COM_TIENDA_REPORT_INAPPROPRIATE_REVIEW'); ?>)</a>
      		</div>
      		<?php
      			endif;
            if ($share_review_enable):
          ?>
		      		<div id="links" class="commentsDiv">
		      		<span class="share_review"><?php echo JText::_('COM_TIENDA_SHARE_THIS_REVIEW'); ?>:</span>      			
		      			 <a href="http://www.facebook.com/share.php?u=<?php echo $linkurl;?>" target='_blank'> <img  src="<?php echo $baseurl;?>/media/com_tienda/images/bookmark/facebook.png" alt="facebook"/></a>
		      			 <a href="http://twitter.com/home?status=<?php echo $linkurl;?>" target='_blank'> <img  src="<?php echo $baseurl;?>/media/com_tienda/images/bookmark/twitter.png" alt="twitter"/></a>
		      			 <a href="http://www.tumblr.com/login?s=<?php echo $linkurl;?>" target='_blank'> <img  src="<?php echo $baseurl;?>/media/com_tienda/images/bookmark/link-tumblr.PNG" alt="link-tumblr"/></a>
		      			 <a href="http://www.stumbleupon.com/submit?url=<?php echo $linkurl;?>&title=<?php echo $row->product_name;?>" target='_blank'> <img  src="<?php echo $baseurl;?>/media/com_tienda/images/bookmark/stumbleupon.png" alt="stumbleupon"/></a>
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