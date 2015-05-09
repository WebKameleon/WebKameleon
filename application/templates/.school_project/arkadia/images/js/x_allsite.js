oblicz_date = function (d,o,t,x)
{
	return[x=~~(t=(d-o)/864e5),x=~~(t=(t-x)*24), x=~~(t=(t-x)*60),~~((t-x)*60)]
}

initLicznik = function (y,m,d,h,mm,sid){
	setInterval('sprawdzLicznik('+y+','+m+','+d+','+h+','+mm+',\''+sid+'\')',1000);	
}

sprawdzLicznik = function (y,m,d,h,mm,sid)
{
	czas=oblicz_date(new Date(y,m,d,h,mm,00),new Date());
	if (czas[0] < 0 || czas[1] < 0 || czas[2]<0 || czas[3]<0){
		$("#licznik_ile"+sid).html("<span class=\"dni\">"+(czas[0]*-1)+"</span><span class=\"godziny\">"+(czas[1]*-1)+"</span><span class=\"minuty\">"+(czas[2]*-1)+"</span>");
		if ($('#licznik_co'+sid).hasClass('licznik_do')) $('#licznik_co'+sid).removeClass('licznik_do').addClass('licznik_od');
	}
	else{
		$("#licznik_ile"+sid).html("<span class=\"dni\">"+czas[0]+"</span><span class=\"godziny\">"+czas[1]+"</span><span class=\"minuty\">"+czas[2]+"</span>");		
	}

}
zakladka = function (nazwa) {
  $(".zakladka").hide();
  $(".zakladka_"+nazwa).show();
  $(".zakladki li").removeClass("active");
  $(".zakladki a[rel*="+nazwa+"]").parent().addClass("active");
}

var display_guestbook = function () {
	var guestbook_div = $("#display_guestbook_div");
	guestbook_div.html('');
	pozycja = $("<ul></ul>").addClass("guestbook");
	guestbook_div.append(pozycja);
	for (var i in users){
		$(".guestbook").append('<li><span class="name">'+users[i].name+'</span><span class="date">'+users[i].timestamp+'</span></li>');
	}
}

function drukuj(sid){ 
	if ($("#divdruk").length==0){ 
		$("body").append($('<div id="divdruk"></div>')); 
	} 
	var zawartosc = $("#webtd_sid_"+sid).html();
	$("#divdruk").html(zawartosc); 
	window.print();
}


$(document).ready(function(){
	$(".etap_box .label p").hover(function(){
		var c = $('<div></div>').attr('id','informacje_etap');
		$('body').append(c);
		var infolot = $(this).parent().parent().find(".help").html();	
		var offset = $(this).offset();
		$("#informacje_etap").show().css({top: offset.top+5, left: offset.left}).html(infolot);
	},
	function(){
		$("#informacje_etap").hide();
	});
	
	if ($(".zakladki"))
	{
		//console.log('zakladki')
		var zakladki = $(".zakladki a");
		for (var n=0;n<zakladki.length;n++) {
			if (zakladki.eq(n).attr("rel"))
			zakladki.eq(n).bind('click',function(event){
				zakladka(event.target.rel);
			});
		};
	}
	
	 if($("#stars-wrapper1").length > 0) {
	 // $j("#hidden_star_1").val($j('[name=star_1]').val()).valid();
		 $("#stars-wrapper1").stars({
			 cancelShow: false,
			 callback: function(ui, type, value){
				// console.log($('[name=star_1]').val());
				 	if ($("#hidden_star_1").length>0)
					 	$("#hidden_star_1").val($('[name=star_1]').val()).valid();
					 if (value<5) {
					 $('#starDetails1').show();
					 radioGroup1 = 1;
				 } else {
					 $('#starDetails1').hide();
					 radioGroup1 = 0;
				 }
			 }
		 });
	 }
	
});