jQueryKam(function ($) {
    if (typeof(kmw_slideshowArray)!='undefined') {
        $('.bxslider-wrapper').show();
        
        for (i=0;i<kmw_slideshowArray.length;i++) {
            $("#bxslider"+kmw_slideshowArray[i].sid).bxSlider(kmw_slideshowArray[i].opt);
        }
        
    }
});