// slideshow
var slideshow_item = 0;
var slideshow_time = 0;
var slideshow_endtime = 6000;
var slideshow_last = 0;
var slideshow_slides = new Array();
var slideshow_przerzuc = 0;
var slideshow_n = 0;

function slidediv_check()
{
  if (slideshow_time>slideshow_endtime && slideshow_slides[slideshow_n][2]==true)
  {
    lasts=slideshow_last;
    slideshow_przerzuc=false;
    slideshow_last=slideshow_n;
    $("#slideshow_slide"+slideshow_n).fadeIn("slow", function () {
      $(this).css({'z-index':7});
      $("#slideshow .items a").removeClass("active");
      $("#slideshow .items a").eq(slideshow_n).addClass("active");
      $("#slideshow .click a").attr("href",slideshow_slides[slideshow_n][0]);
      if (slideshow_slides[slideshow_n][3].length>0) slideshow_endtime=parseInt(slideshow_slides[slideshow_n][3])*1000;
      else slideshow_endtime=6000;
      slideshow_n+=1;
      if (slideshow_n==slideshow_slides.length) slideshow_n=0;
      $("#slideshow_slide"+slideshow_n).css({'z-index':8, 'display':'none'});
      if (slideshow_slides[slideshow_n][2]==false) 
      {
        $("#slideshow_slide"+slideshow_n+" img").attr("src",slideshow_slides[slideshow_n][1]);
      }
      $("#slideshow_slide"+lasts).hide();      
    });
    slideshow_time=0;
  }
  else
    slideshow_time+=100;
}

function slidediv_slideloaded (ipd)
{
  slideshow_slides[ipd][2]=true;
}

function slidediv_change(cn)
{
  if (cn==-1)
  {
    if (slideshow_n>=2) slideshow_n-=2;
    else if (slideshow_n==1) slideshow_n=slideshow_slides.length-1;
    else slideshow_n=slideshow_slides.length-2;
    slidediv_slideload(slideshow_n);
  }
  else
  {
    slideshow_przerzuc=true;
    slideshow_time=slideshow_endtime;
  }  
}

function slidediv_slideload(cn)
{
  cn=parseInt(cn);
  if (cn!=slideshow_last)
  {
    if (slideshow_slides[cn][2]==false)
    {
      $("#slideshow_slide"+cn+" img").attr("src",slideshow_slides[cn][1]);
    }
    slideshow_przerzuc=true;
    slideshow_time=slideshow_endtime;
    slideshow_n=cn;  
  }
  else
  {
    slideshow_time=0;
  }
}

function slidediv_begin(first)
{
  if (first==1) $("#slideshow .items").append($("<a rel=\"0\">1</a>").click(function () { slidediv_slideload(0); }).addClass("active") );
  for (i=first;i<slideshow_slides.length;i++)
  {  
    var newdiv = document.createElement('div');
    
    var img = new Image();
    $(newdiv).attr("id","slideshow_slide"+i);
    $(img).attr("title",i);
    if (i==0)
      $(newdiv).css({'display':'block'});
    else
      $(newdiv).css({'display':'none'});
    $(img).load( function () { 
      slidediv_slideloaded($(this).attr("title"));
    });
    $(img).addClass("slidepics");
    if (slideshow_slides[i][4].length>0)
    {
      $(newdiv).html(slideshow_slides[i][4]);
    }
    else
    {
      $(newdiv).append(img);
    }
    $("#slideshow .slide_col").eq(0).append(newdiv);
    $("#slideshow .items").append($("<a rel=\""+i+"\">"+(i+1)+"</a>").click(function () { slidediv_slideload($(this).attr("rel")); }) );
  }
  
  slideshow_last=slideshow_slides.length-1;
  $("#slideshow .navi").show();
  slideshow_slides[0][2]=true;
  slideshow_timer_id = window.setInterval("slidediv_check()",100);
  slidediv_slideload(0);
}

function dodajUlubione (url,title)
{
  if (window.sidebar) { // Mozilla Firefox Bookmark
		window.sidebar.addPanel(title, url,"");
	} else if( window.external ) { // IE Favorite
		window.external.AddFavorite( url, title); }
	else if(window.opera && window.print) { // Opera Hotlist
		return true; }
}

$(function() {
	$('a[rel="zoom"]').lightBox();
});