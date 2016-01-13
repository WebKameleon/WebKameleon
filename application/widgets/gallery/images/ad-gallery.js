jQueryKam(function ($) {
    
    
    for (i=0;i<kmw_adGaleryArray.length;i++) {
        
    
        
        var gallery = $("#ad-gallery"+kmw_adGaleryArray[i].sid).on("click", ".ad-image", function (e) {
            var index = 0;
            var images = $("#ad-gallery"+kmw_adGaleryArray[i].sid+" .ad-thumb-list a").each(function (k) {
                if ($(this).hasClass("ad-active")) {
                    index = k;
                }
            });
            
            $.fancybox.open(images, {
                index : index,
                nextEffect : "fade",
                prevEffect : "fade",
                nextSpeed : 500,
                prevSpeed : 500,
                afterClose : function () {
                    gallery.showImage(this.index);
                }
            });
            
        }).adGallery($.extend(kmw_adGaleryArray[i].opt, {
            update_window_hash : false,
            loader_image : kmw_adGaleryArray[i].img+"/loader.gif"
        }))[0];
    }
});