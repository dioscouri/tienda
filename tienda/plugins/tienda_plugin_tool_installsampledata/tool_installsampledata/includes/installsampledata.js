window.onload = showSample();

function showSample()
{	
	var displayStyle = $('sampledatatype').style.display;
	
	if(displayStyle == 'none')
	{
		$('sampledatatype').setAttribute('style', '');		
	}
	else
	{
		$('sampledatatype').style.display = 'none';
	}	
	
}