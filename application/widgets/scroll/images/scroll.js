jQueryKam(function ($) {
    for (i=0;i<kmw_scrollArray.length;i++) {
        
        $("#jcarousel"+kmw_scrollArray[i].sid).jCarouselLite($.extend({
            btnPrev : "#jcarousel-navi"+kmw_scrollArray[i].sid+" .jcarousel-navi-prev",
            btnNext : "#jcarousel-navi"+kmw_scrollArray[i].sid+" .jcarousel-navi-next"
        }, kmw_scrollArray[i].opt));        
    }

});