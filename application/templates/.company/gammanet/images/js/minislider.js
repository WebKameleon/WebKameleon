var spanWidth;
var multi;
var divWidth = 887;
var intervalHandle;
var div;
var span;

$(document).ready(function(){

if ($("div.sideslide").length ) {



var span = $("span.innerslide");
var div = $("div.sideslide");
div.css('visibility','visible');

newWidth = 0;
span.children('a').each(function(){

newWidth+= parseInt($(this).outerWidth(true) ,10);

});
span.width(newWidth);
var spanWidth = span.width();

if (spanWidth==0)
{spanWidth=1};
var multi = Math.ceil(	divWidth  / spanWidth)+2;

var content = span.html();

span.css("display","block");
span.width(divWidth*multi);
for (i=1;i<multi;i++)
{
span.append(content);
}



var intervalHandle = setInterval(function(){


if (div.scrollLeft() >= spanWidth)
{
div.scrollLeft(0);
}

div.scrollLeft(div.scrollLeft()+1);

}
, 50);


}
});


