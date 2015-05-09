$(document).ready(function(){
window.hint = $('span#hint');
window.hintbg = $('span#hintbg');

$('a#up').click(function(){

 $('html').stop(true).animate({scrollTop : 0},'slow');

});


  
  if ($("div#top_menu2 a,ul.sub_images li a").length>0)
  {
  
$("div#top_menu2 a,ul.sub_images li a").each(function(i){

	$(this).bind('mousemove', function(e){
		window.mouseX=e.pageX;
		window.mouseY=e.pageY;
		showHint($(this));
		})

		$(this).bind('mouseout', function(e){
		window.hintbg.stop(true).animate({opacity:'0'},300,'easeOutSine')
		
		});
	});
	
	}


});

function showHint(obj)
{
rel = obj.attr('rel');
parent = obj.parent().parent(); 
offset = parent.offset();

parentheight = parent.height();
if (parentheight <20) parentheight=35;

window.hint.html(rel);
window.hintbg.css({left:window.mouseX-window.hint.width()/2 - 6 ,top:offset.top+parentheight-10}).stop(true).animate({opacity:'1'},300,'easeOutSine');


}