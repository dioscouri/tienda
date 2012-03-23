<?php
defined('_JEXEC') or die('Restricted access');

$rating = $this->rating;

if( $rating->clickable )
{
	for( $i = 1; $i <= $rating->count; $i++ ) : ?>
		<span id="rating_<?php echo $i; ?>">
	   	<a href="javascript:void(0);" onclick="javascript:tiendaRating(<?php echo $i; ?>);">
		   	<img id="rate_<?php echo $i; ?>" src="media/com_tienda/images/star_00.png" alt="<?php echo $i?>">
			</a>
		</span>
	<?php endfor;
}
else 
{
	switch ( $rating->rating )
	{
		case "5":
				$src = Tienda::getURL( 'ratings' )."five.png";
				$alt = JText::_('COM_TIENDA_GREAT');
				$title = JText::_('COM_TIENDA_GREAT');
				$name = JText::_('COM_TIENDA_GREAT');
			break;
		case "4.5":
				$src = Tienda::getURL( 'ratings' )."four_half.png";
				$alt = JText::_('COM_TIENDA_GREAT');
				$title = JText::_('COM_TIENDA_GREAT');
				$name = JText::_('COM_TIENDA_GREAT');
			break;
		case "4":
				$src = Tienda::getURL( 'ratings' )."four.png";
				$alt = JText::_('COM_TIENDA_GOOD');
				$title = JText::_('COM_TIENDA_GOOD');
				$name = JText::_('COM_TIENDA_GOOD');
			break;
		case "3.5":
				$src = Tienda::getURL( 'ratings' )."three_half.png";
				$alt = JText::_('COM_TIENDA_GREAT');
				$title = JText::_('COM_TIENDA_GREAT');
				$name = JText::_('COM_TIENDA_GREAT');
			break;
		case "3":
				$src = Tienda::getURL( 'ratings' )."three.png";
				$alt = JText::_('COM_TIENDA_AVERAGE');
				$title = JText::_('COM_TIENDA_AVERAGE');
				$name = JText::_('COM_TIENDA_AVERAGE');
			break;
		case "2.5":
				$src = Tienda::getURL( 'ratings' )."two_half.png";
				$alt = JText::_('COM_TIENDA_AVERAGE');
				$title = JText::_('COM_TIENDA_AVERAGE');
				$name = JText::_('COM_TIENDA_AVERAGE');
			break;
		case "2":
				$src = Tienda::getURL( 'ratings' )."two.png";
				$alt = JText::_('COM_TIENDA_POOR');
				$title = JText::_('COM_TIENDA_POOR');
				$name = JText::_('COM_TIENDA_POOR');
			break;
		case "1.5":
				$src = Tienda::getURL( 'ratings' )."one_half.png";
				$alt = JText::_('COM_TIENDA_POOR');
				$title = JText::_('COM_TIENDA_POOR');
				$name = JText::_('COM_TIENDA_POOR');
			break;
		case "1":
				$src = Tienda::getURL( 'ratings' )."one.png";
				$alt = JText::_('COM_TIENDA_UNSATISFACTORY');
				$title = JText::_('COM_TIENDA_UNSATISFACTORY');
				$name = JText::_('COM_TIENDA_UNSATISFACTORY');
			break;
		case "0.5":
				$src = Tienda::getURL( 'ratings' )."zero_half.png";
				$alt = JText::_('COM_TIENDA_UNSATISFACTORY');
				$title = JText::_('COM_TIENDA_UNSATISFACTORY');
				$name = JText::_('COM_TIENDA_UNSATISFACTORY');
			break;
		default:
				$src = Tienda::getURL( 'ratings' )."zero.png";
				$alt = JText::_('COM_TIENDA_UNRATED');
				$title = JText::_('COM_TIENDA_UNRATED');
				$name = JText::_('COM_TIENDA_UNRATED');
			break;
	}
	
	echo "<img src='".$src."' alt='".$alt."' title='".$title."' name='".$name."' align='center' border='0' />";
}