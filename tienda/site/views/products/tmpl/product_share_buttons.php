<?php
defined('_JEXEC') or die('Restricted access');
?>

<?php if ( TiendaConfig::getInstance( )->get( 'display_facebook_like', '1' ) || TiendaConfig::getInstance( )->get( 'display_tweet', '1' ) || TiendaConfig::getInstance( )->get( 'display_google_plus1', '1' ) ) : ?>       
<div class="product_like">
	<?php if ( TiendaConfig::getInstance( )->get( 'display_facebook_like', '1' ) ) : ?>
	<div class="product_facebook_like">
		<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>
		<fb:like show_faces="false" width="375"></fb:like> 
	</div>  
	<?php endif; ?>
            
	<div class="product_share_buttons">
	<?php if ( TiendaConfig::getInstance( )->get( 'display_tweet', '1' ) ) : ?>
		<div class="product_tweet">
			<a href="http://twitter.com/share" class="twitter-share-button" data-text="<?php echo TiendaConfig::getInstance( )->get( 'display_tweet_message', 'Check this out!' ); ?>" data-count="horizontal">Tweet</a>
			<script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
		</div>
	<?php endif; ?>

	<?php if ( TiendaConfig::getInstance( )->get( 'display_google_plus1', '1' ) ) : ?>
	<?php $google_plus1_size = TiendaConfig::getInstance( )->get( 'display_google_plus1_size', 'medium' ); ?>
		<div class="product_google_plus1">
			<g:plusone <?php if( strlen( $google_plus1_size ) ) echo 'size="'.$google_plus1_size.'"' ?>></g:plusone>
			<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
		</div>
	<?php endif; ?>
	</div>
<div class="reset"></div>
</div> 
<?php endif; ?>

