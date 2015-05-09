

$(document).ready(function(){

window.lastSlide=3;
window.cSlide=0;
window.slideHold =4000;
window.slideGap = 0;

$("div#banner").css("display","block");

window.isIE = $.browser.msie && (parseInt($.browser.version,10)<9);
	if  (!window.isIE)
{
$('img#cloud,img#arrow,img#pc1,img#pc2,img#pc3,img#pc4,img#pc5').css("visibility","visible");
	$('div#slide2 div#ponad2').css("visibility","visible");
	$('div#slide2 img#ipad').css("visibility","visible");
	$('div#slide2 div#zarejestrowanych2').css("visibility","visible");
}
else
{
$('div#slide3 img#cloud').css({left:'596px',top:'-139px',visibility:"hidden"});
}

//$('div#banner').slideDown(1000);
//$('ul.menu_top').show();
$('div#banner').bind('mouseenter',function(){
if  (window.isIE){$('a.navi_arrow').show(0)}else
$('a.navi_arrow').fadeIn(300);
});

$('div#banner').bind('mouseleave',function(){
if  (window.isIE){$('a.navi_arrow').hide(0)}else
$('a.navi_arrow').fadeOut(200);
});


$('a#navi_arrow_left').click(function(){
 if (window.cSlide==1 || window.cSlide==0){playSlide(window.lastSlide); }
 else
 playSlide(window.cSlide-1);
 });
 
 $('a#navi_arrow_right').click(function(){
 if (window.cSlide==window.lastSlide){playSlide(1); }
 else
 playSlide(window.cSlide+1);
 });


$('div#dots a.dot').each(function(i){

$(this).click(function(){ playSlide(i+1);})

});


$('div#dots a.dot').eq(0).click();
});




function playSlide(slideNumber)
{
finished=false;
stopAll();	
switch (slideNumber)
	{
	case 1:
	// Slide 1 animation
		if  (window.isIE){	
IEclearSlide(window.cSlide,IEslide1);
		}
		else
	clearSlide(window.cSlide,slide1);
	$('div#dots a.dot').attr('class','dot');	
	$('div#dots a.dot').eq(0).attr('class','dot active');	
	break;
	case 2:
	// Slide 2 animation
			if  (window.isIE){	
IEclearSlide(window.cSlide,IEslide2);
		}
		else
	clearSlide(window.cSlide,slide2);
	$('div#dots a.dot').attr('class','dot');	
	$('div#dots a.dot').eq(1).attr('class','dot active');	
	break;
	case 3:
	// Slide 3 animation
			if  (window.isIE){	
IEclearSlide(window.cSlide,IEslide3);
		}
		else
	clearSlide(window.cSlide,slide3);
	$('div#dots a.dot').attr('class','dot');	
	$('div#dots a.dot').eq(2).attr('class','dot active');	
	break;
	}

}


function clearSlide(slideNumber,callback)
{
switch (slideNumber)
	{
	case 0:
	callback();
	break;
	case 1:
	
	
	$('div#slide1,div#slide2,div#slide3').animate({opacity:'0'},300,function(){
	
		$('div#slide1 div#ponad').stop(true).css('top','-100px');
		$('div#slide1 div#zarejestrowanych').stop(true).css('top','250px');
		$('div#slide1 div#teacher2,div#slide1 div#administrative,div#slide1 div#uczen,div#slide1 div#uczen2,div#slide1 div#teacher,div#slide1 div#student').stop(true).css('top','240px');
		$.doTimeout('slideGap',window.slideGap, function(){callback();});
		
	});
	
	
	break;
	case 2:
	

	{
		$('div#slide2').stop(true).animate({opacity:'0.00'},300,function(){
			$('div#slide2 div#ponad2,div#slide2 div#zarejestrowanych2').stop(true).css({opacity:'0.01',left:'-300px'});
			$('div#slide2 img#ipad').stop(true).css({top:'122px',left:'660px',opacity:'0',width:'149px',height:'98px'});
			

			$('div#slide2 div#orange').stop(true).css({top:'300px'});
			$('div#slide2 div#crop').stop(true).animate({opacity:"0"},0);
			$('div#slide2 div#crop img').stop().css({opacity:"0",left:'0',width:"1304px",height:"504px"});
			$('div#slide2 img#szkola1').stop().css({top:'70px',left:'766px',width:'0',height:'0'});
			$('div#slide2 img#szkola2').stop().css({top:'150px',left:'703px',width:'0',height:'0'});
			$('div#slide2 img#szkola3').stop().css({top:'141px',left:'837px',width:'0',height:'0'});
			
			$.doTimeout('slideGap',window.slideGap, function(){callback();});
		});
	}
	break;
	case 3:
	$('div#slide3').stop(true).animate({opacity:'0'},300,function(){
		
		


	$('div#slide3 img#pc1').css({left:'20px',opacity:'0',top:'135px'});
	$('div#slide3 img#pc2').css({width:'131px',height:'98px',left:'20px',opacity:'0',top:'138px'});
	$('div#slide3 img#pc3').css({width:'108px',height:'81px',left:'189px',top:'70px',opacity:'0'});
	$('div#slide3 img#pc4').css({opacity:'0',width:'84px',height:'63px',left:'148px',top:'86px'});
	$('div#slide3 img#pc5').css({opacity:'0',top:'72px',left:'100px',width:'68px',height:'51px'});
	$('div#slide3 img#cloud').css({left:'596px',top:'39px',opacity:'0'});
	$('div#slide3 img#arrow').css({left:'380px',opacity:'0',width:'0',top:'85px',height:'81px'});
	$('div#slide3 a#dowiedz').css({left:'334px',top:'270px'});
	$('div#slide3 a#na_czym').css({left:'425px',top:'270px'});
	$('div#slide3 a#polega').css({left:'489px',top:'270px'});
	$('div#slide3 a#chmura_google').css({left:'541px',top:'270px'});
	
		$.doTimeout('slideGap',window.slideGap, function(){callback();});
	});
	break;
	

	
	}
}


function slide1()
{
window.cSlide=1;
finished = false;
$('div#slide1').css('opacity','1');

	$('div#slide1 div#ponad').animate({top: '90px'},2000,'easeOutBack',function(){
		$('div#slide1 div#zarejestrowanych').animate({top: '170px'},600,'easeInSine',function(){
			$('div#slide1 div#ponad').stop(true);
			$('div#slide1 div#zarejestrowanych, div#slide1 div#ponad').animate({top:'-=40'},1500,'easeOutBack',function(){
			
			
				$('div#slide1 div#teacher2,div#slide1 div#administrative,div#slide1 div#uczen,div#slide1 div#uczen2').animate({top:'72px'},1200,'easeOutBack');
					$('div#slide1 div#teacher,div#slide1 div#student').animate({top:'92px'},1500,'easeOutBack',function(){
					// Animation Finished
					
					
					if (!finished) {	$.doTimeout('slideHold',window.slideHold, function(){playSlide(2);	});finished = true	;}
					
					
					
					
					


					
					});
				
				});
			});
		
		});
}

function slide2()
{
	finished = false;
	window.cSlide=2;
	$('div#slide2').css('opacity','1');

	
	$('div#slide2 div#ponad2').animate({left: '90px',opacity:'1'},800,'easeOutSine');
	$.doTimeout('slide2_1',300, function(){
	$('div#slide2 div#zarejestrowanych2').animate({left: '90px',opacity:'1'},1000,'easeOutSine',function(){
						
					$('div#slide2 img#ipad').animate({width: '363px',height:'236px',opacity:'1',top:'22px',left:'580px'},600,'easeOutBack',function(){
							
							
							$('div#slide2 div#orange').animate({top:'142px'},800,'easeOutBack');
							$('div#slide2 div#crop').animate({opacity:'1'},800,'easeOutBack',function(){
									$('div#slide2 div#crop img').animate({opacity:'1'},0);
								$('div#slide2 div#crop img').animate({left:'-450px',top:"-30px",width:"804px",height:"310px"},4000,'easeInOutSine',function(){
																
								if (!finished) {	$.doTimeout('slideHold',window.slideHold, function(){playSlide(3);	});finished = true	
								$('div#slide2 img#szkola1').animate({width:'24px',height:'22px',left:'-=12px',top:'-=11px'},600,'easeOutBack',function(){
									$.doTimeout('slide2_1',300, function(){
											$('div#slide2 img#szkola2').animate({width:'24px',height:'22px',left:'-=12px',top:'-=11px'},600,'easeOutBack',function(){
												$.doTimeout('slide2_2',300, function(){
													$('div#slide2 img#szkola3').animate({width:'24px',height:'22px',left:'-=12px',top:'-=11px'},600,'easeOutBack');
													});
											});
										});
									});
								
								;}
									
								});
							
							
							});
						
						
						});
				});
	});
	
}

function slide3()
{
	
	finished=false;
	window.cSlide=3;
	$('div#slide3').css('opacity','1');
	$('div#slide3 img#pc1').animate({left: '197px',top:'75px',opacity:'1'},800,'easeOutSine');
	$.doTimeout('slide3_1',200, function(){
									$('div#slide3 img#pc2').animate({left: '116px',top:'88px',opacity:'1'},800,'easeOutSine');
									$.doTimeout('slide3_2',200, function(){
										$('div#slide3 img#pc3').animate({left: '229px',top:'41px',opacity:'1'},800,'easeOutSine');
											$.doTimeout('slide3_3',200, function(){
											$('div#slide3 img#pc4').animate({left: '178px',top:'56px',opacity:'1'},800,'easeOutSine');
											$.doTimeout('slide3_4',200, function(){
													$('div#slide3 img#pc5').animate({left: '100px',top:'72px',opacity:'1'},800,'easeOutSine',function(){
															
															$('div#slide3 img#cloud').animate({opacity:'1'},800,'easeOutSine',function(){
																
																$('div#slide3 img#arrow').animate({width:'153px',opacity:'1'},1800,'easeOutElastic',function(){
																	
																	$('div#slide3 a#dowiedz').animate({top:'194px',opacity:'1'},800,'easeOutBack');
																$.doTimeout('slide3_5',200, function(){
																		$('div#slide3 a#na_czym').animate({top:'197px',opacity:'1'},800,'easeOutBack');
																	$.doTimeout('slide3_6',200, function(){
																			$('div#slide3 a#polega').animate({top:'194px',opacity:'1'},800,'easeOutBack');
																		$.doTimeout('slide3_7',200, function(){
																				$('div#slide3 a#chmura_google').animate({top:'194px',opacity:'1'},800,'easeOutBack',function(){
																				
																					if (!finished) {	$.doTimeout('slideHold',window.slideHold, function(){playSlide(1);	});finished = true	;}
																				});
																				
																				
																			});
																		});
																	});
																	
																	});
																	
																});
															});
													
													});
												});
										});
									});
	
}




function stopAll()
{
//Timers
$.doTimeout('slideHold');
$.doTimeout('slideGap');
$.doTimeout('slide3_1');
$.doTimeout('slide3_2');
$.doTimeout('slide3_3');
$.doTimeout('slide3_4');
$.doTimeout('slide3_5');
$.doTimeout('slide3_6');
$.doTimeout('slide3_7');
$.doTimeout('slide2_1');

//Slides
$('div#slide1,div#slide2,div#slide3').stop(true);
// Parts
	// 1
		$('div#slide1 div#ponad').stop(true);
		$('div#slide1 div#zarejestrowanych').stop(true);
		$('div#slide1 div#teacher2,div#slide1 div#administrative,div#slide1 div#uczen,div#slide1 div#uczen2,div#slide1 div#teacher,div#slide1 div#student').stop(true);
	// 2
		$('div#slide2 div#ponad2,div#slide2 div#zarejestrowanych2').stop(true);
		$('div#slide2 img#ipad').stop(true);
		$('div#slide2 div#orange').stop(true);
	// 3
		
	$('div#slide3 img#pc1').stop(true);
	$('div#slide3 img#pc2').stop(true);
	$('div#slide3 img#pc3').stop(true);
	$('div#slide3 img#pc4').stop(true);
	$('div#slide3 img#pc5').stop(true);
	$('div#slide3 img#cloud').stop(true);
	$('div#slide3 img#arrow').stop(true);
	$('div#slide3 a#dowiedz').stop(true);
	$('div#slide3 a#na_czym').stop(true);
	$('div#slide3 a#polega').stop(true);
	$('div#slide3 a#chmura_google').stop(true);
																				
}


/////////////////////////////////////////////////////////
////////////////////// IE !
function IEclearSlide(slideNumber,callback)
{
switch (slideNumber)
	{
	case 0:
	callback();
	break;
	case 1:
	
	$('div#slide1,div#slide2,div#slide3').animate({opacity:'0'},300,function(){
	
		$('div#slide1 div#ponad').stop(true).css('top','-100px');
		$('div#slide1 div#zarejestrowanych').stop(true).css('top','250px');
		$('div#slide1 div#teacher2,div#slide1 div#administrative,div#slide1 div#uczen,div#slide1 div#uczen2,div#slide1 div#teacher,div#slide1 div#student').stop(true).css('top','240px');
		$.doTimeout('slideGap',window.slideGap, function(){callback();});
		
	});
	
	
	break;
	case 2:
	

	{
		$('div#slide2').stop(true).animate({opacity:'0'},300,function(){
			$('div#slide2 div#ponad2,div#slide2 div#zarejestrowanych2').stop(true).css({left:'-300px',visibility:"hidden"});
			$('div#slide2 img#ipad').stop(true).css({top:'122px',left:'660px',visibility:"hidden",width:'149px',height:'98px'});
			

			$('div#slide2 div#orange').stop(true).css({top:'300px'});
			$('div#slide2 div#crop').stop(true).animate({opacity:"0"},0);
			$('div#slide2 div#crop img').stop().css({opacity:"0",left:'0',width:"1304px",height:"504px"});
			$('div#slide2 img#szkola1').stop().css({top:'70px',left:'766px',width:'0',height:'0'});
			$('div#slide2 img#szkola2').stop().css({top:'150px',left:'703px',width:'0',height:'0'});
			$('div#slide2 img#szkola3').stop().css({top:'141px',left:'837px',width:'0',height:'0'});
			
			$.doTimeout('slideGap',window.slideGap, function(){callback();});
		});
	}
	break;
	case 3:
	$('div#slide3').stop(true).animate({opacity:'0'},300,function(){
		
		


	$('div#slide3 img#pc1').css({left:'20px',visibility:"hidden",top:'135px'});
	$('div#slide3 img#pc2').css({width:'131px',height:'98px',left:'20px',visibility:"hidden",top:'138px'});
	$('div#slide3 img#pc3').css({width:'108px',height:'81px',left:'189px',top:'70px',visibility:"hidden"});
	$('div#slide3 img#pc4').css({visibility:"hidden",width:'84px',height:'63px',left:'148px',top:'86px'});
	$('div#slide3 img#pc5').css({visibility:"hidden",top:'72px',left:'100px',width:'68px',height:'51px'});
	$('div#slide3 img#cloud').css({left:'596px',top:'-139px',visibility:"hidden"});
	$('div#slide3 img#arrow').css({left:'380px',visibility:"hidden",width:'0',top:'85px',height:'81px'});
	$('div#slide3 a#dowiedz').css({left:'334px',top:'270px'});
	$('div#slide3 a#na_czym').css({left:'425px',top:'270px'});
	$('div#slide3 a#polega').css({left:'489px',top:'270px'});
	$('div#slide3 a#chmura_google').css({left:'541px',top:'270px'});
	
		$.doTimeout('slideGap',window.slideGap, function(){callback();});
	});
	break;
	

	
	}
}


function IEslide1()
{
window.cSlide=1;
finished = false;
$('div#slide1').css('opacity','1');

	$('div#slide1 div#ponad').animate({top: '90px'},2000,'easeOutBack',function(){
		$('div#slide1 div#zarejestrowanych').animate({top: '170px'},600,'easeInSine',function(){
			$('div#slide1 div#ponad').stop(true);
			$('div#slide1 div#zarejestrowanych, div#slide1 div#ponad').animate({top:'-=40'},1500,'easeOutBack',function(){
			
			
				$('div#slide1 div#teacher2,div#slide1 div#administrative,div#slide1 div#uczen,div#slide1 div#uczen2').animate({top:'72px'},1200,'easeOutBack');
					$('div#slide1 div#teacher,div#slide1 div#student').animate({top:'92px'},1500,'easeOutBack',function(){
					// Animation Finished
					
					
					if (!finished) {	$.doTimeout('slideHold',window.slideHold, function(){playSlide(2);	});finished = true	;}
					
					
					
					
					


					
					});
				
				});
			});
		
		});
}

function IEslide2()
{
	finished = false;
	window.cSlide=2;
	$('div#slide2').css('opacity','1');
	$('div#slide2 div#ponad2').css("visibility","visible");
	$('div#slide2 div#ponad2').animate({left: '90px'},800,'easeOutSine');
	$.doTimeout('slide2_1',300, function(){
 	$('div#slide2 div#zarejestrowanych2').css("visibility","visible");
	$('div#slide2 div#zarejestrowanych2').animate({left: '90px'},1000,'easeOutSine',function(){


					$('div#slide2 img#ipad').css("visibility","visible");
					$('div#slide2 img#ipad').animate({width: '363px',height:'236px',top:'22px',left:'580px'},600,'easeOutBack',function(){
							
							
							$('div#slide2 div#orange').animate({top:'142px'},800,'easeOutBack');
							$('div#slide2 div#crop').animate({opacity:'1'},800,'easeOutBack',function(){
									$('div#slide2 div#crop img').animate({opacity:'1'},0);
								$('div#slide2 div#crop img').animate({left:'-450px',top:"-30px",width:"804px",height:"310px"},4000,'easeInOutSine',function(){
																
								if (!finished) {	$.doTimeout('slideHold',window.slideHold, function(){playSlide(3);	});finished = true	
								$('div#slide2 img#szkola1').animate({width:'24px',height:'22px',left:'-=12px',top:'-=11px'},600,'easeOutBack',function(){
									$.doTimeout('slide2_1',300, function(){
											$('div#slide2 img#szkola2').animate({width:'24px',height:'22px',left:'-=12px',top:'-=11px'},600,'easeOutBack',function(){
												$.doTimeout('slide2_2',300, function(){
													$('div#slide2 img#szkola3').animate({width:'24px',height:'22px',left:'-=12px',top:'-=11px'},600,'easeOutBack');
													});
											});
										});
									});
								
								;}
									
								});
							
							
							});
						
						
						});
				});
	});
	
}

function IEslide3()
{
	
	finished=false;
	window.cSlide=3;
	$('div#slide3').css('opacity','1');

$('div#slide3 img#pc1').css("visibility","visible");
	$('div#slide3 img#pc1').animate({left: '197px',top:'75px'},800,'easeOutSine');
	$.doTimeout('slide3_1',200, function(){
										 	$('div#slide3 img#pc2').css("visibility","visible");
									$('div#slide3 img#pc2').animate({left: '116px',top:'88px'},800,'easeOutSine');
									$.doTimeout('slide3_2',200, function(){
										$('div#slide3 img#pc3').css("visibility","visible");								 
										$('div#slide3 img#pc3').animate({left: '229px',top:'41px'},800,'easeOutSine');
											$.doTimeout('slide3_3',200, function(){
											$('div#slide3 img#pc4').css("visibility","visible");									 											$('div#slide3 img#pc4').animate({left: '178px',top:'56px'},800,'easeOutSine');
											$.doTimeout('slide3_4',200, function(){
											 $('div#slide3 img#pc5').css("visibility","visible");
													$('div#slide3 img#pc5').animate({left: '100px',top:'72px'},800,'easeOutSine',function(){
															$('div#slide3 img#cloud').css("visibility","visible");
															$('div#slide3 img#cloud').animate({top:"39px"},800,'easeOutSine',function(){
																$('div#slide3 img#arrow').css("visibility","visible");
																$('div#slide3 img#arrow').animate({width:'153px'},1800,'easeOutElastic',function(){
																	
																	$('div#slide3 a#dowiedz').animate({top:'194px'},800,'easeOutBack');
																$.doTimeout('slide3_5',200, function(){
																		$('div#slide3 a#na_czym').animate({top:'197px'},800,'easeOutBack');
																	$.doTimeout('slide3_6',200, function(){
																			$('div#slide3 a#polega').animate({top:'194px'},800,'easeOutBack');
																		$.doTimeout('slide3_7',200, function(){
																				$('div#slide3 a#chmura_google').animate({top:'194px'},800,'easeOutBack',function(){
																				
																					if (!finished) {	$.doTimeout('slideHold',window.slideHold, function(){playSlide(1);	});finished = true	;}
																				});
																				
																				
																			});
																		});
																	});
																	
																	});
																	
																});
															});
													
													});
												});
										});
									});
	
}