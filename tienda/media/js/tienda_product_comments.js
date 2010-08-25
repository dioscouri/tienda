function test() {
	
	
	document.sort.submit();
	
}

function rating(id){
	var count;
	
	document.getElementById('productcomment_rating').value=id;
	for(count=1;count<=id;count++)
	{
		
		document.getElementById('rating_'+count).getElementsByTagName("img")[0].src=window.com_tienda.jbase + "media/com_tienda/images/star_10.png";
	
	}
	
	
}

function ratingme(id){
	var count;
	
	document.getElementById('productcomment_rating').value=id;
	for(count=1;count<=id;count++)
	{
		
		document.getElementById('rating_'+count).getElementsByTagName("img")[0].src="../media/com_tienda/images/star_10.png";
	
	}
	
	
}
