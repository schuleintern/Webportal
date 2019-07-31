
$("html, body").animate(
		{
			scrollTop: $(document).height()
		}, ($(document).height() * 20)

);
setTimeout(function() {
   $('html, body').animate(
		   {
			   scrollTop:0
		   }, ($(document).height() * 10)
   ); 
},($(document).height() * 10)+2000);


var scrolltopbottom =  setInterval(
	function(){
		$("html, body").animate(
				{
					scrollTop: $(document).height()
				},
			($(document).height() * 10)
		);
	
		setTimeout(function() {
   				$('html, body').animate(
   						{
   							scrollTop:0
   						}, 
   						($(document).height() * 10)
   				); 
		},($(document).height() * 10));

},($(document).height() * 10)*2+2000);
