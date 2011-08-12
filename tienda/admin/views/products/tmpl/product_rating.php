<?php
defined('_JEXEC') or die('Restricted access');
if( $this->clickable )
{
	for( $i = 1; $i <= $this->count; $i++ ) : ?>
		<span id="rating_<?php echo $i; ?>">
	   	<a href="javascript:void(0);" onclick="javascript:tiendaRating(<?php echo $i; ?>);">
		   	<img id="rate_<?php echo $i; ?>" src="media/com_tienda/images/star_00.png" alt="<?php echo $i?>">
			</a>
		</span>
	<?php endfor;
}
else 
{
	switch ( $this->rating )
	{
		case "5":
				$src = Tienda::getURL( 'ratings' )."five.png";
				$alt = JText::_( 'Great' );
				$title = JText::_( 'Great' );
				$name = JText::_( 'Great' );
			break;
		case "4.5":
				$src = Tienda::getURL( 'ratings' )."four_half.png";
				$alt = JText::_( 'Great' );
				$title = JText::_( 'Great' );
				$name = JText::_( 'Great' );
			break;
		case "4":
				$src = Tienda::getURL( 'ratings' )."four.png";
				$alt = JText::_( 'Good' );
				$title = JText::_( 'Good' );
				$name = JText::_( 'Good' );
			break;
		case "3.5":
				$src = Tienda::getURL( 'ratings' )."three_half.png";
				$alt = JText::_( 'Great' );
				$title = JText::_( 'Great' );
				$name = JText::_( 'Great' );
			break;
		case "3":
				$src = Tienda::getURL( 'ratings' )."three.png";
				$alt = JText::_( 'Average' );
				$title = JText::_( 'Average' );
				$name = JText::_( 'Average' );
			break;
		case "2.5":
				$src = Tienda::getURL( 'ratings' )."two_half.png";
				$alt = JText::_( 'Average' );
				$title = JText::_( 'Average' );
				$name = JText::_( 'Average' );
			break;
		case "2":
				$src = Tienda::getURL( 'ratings' )."two.png";
				$alt = JText::_( 'Poor' );
				$title = JText::_( 'Poor' );
				$name = JText::_( 'Poor' );
			break;
		case "1.5":
				$src = Tienda::getURL( 'ratings' )."one_half.png";
				$alt = JText::_( 'Poor' );
				$title = JText::_( 'Poor' );
				$name = JText::_( 'Poor' );
			break;
		case "1":
				$src = Tienda::getURL( 'ratings' )."one.png";
				$alt = JText::_( 'Unsatisfactory' );
				$title = JText::_( 'Unsatisfactory' );
				$name = JText::_( 'Unsatisfactory' );
			break;
		case "0.5":
				$src = Tienda::getURL( 'ratings' )."zero_half.png";
				$alt = JText::_( 'Unsatisfactory' );
				$title = JText::_( 'Unsatisfactory' );
				$name = JText::_( 'Unsatisfactory' );
			break;
		default:
				$src = Tienda::getURL( 'ratings' )."zero.png";
				$alt = JText::_( 'Unrated' );
				$title = JText::_( 'Unrated' );
				$name = JText::_( 'Unrated' );
			break;
	}
	
	echo "<img src='".$src."' alt='".$alt."' title='".$title."' name='".$name."' align='center' border='0' />";
}