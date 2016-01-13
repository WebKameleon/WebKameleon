jQueryKam(function ($) {
    
    for (i=0;i<kmw_sitemapArray.length;i++) {
        $("#sitemap_"+kmw_sitemapArray[i].sid).on("click", ".sitemap_box_plus, .sitemap_box_minus", function (e) {
            $(this).toggleClass("sitemap_box_plus sitemap_box_minus").siblings("ul").slideToggle();
        });        
    }
    

});