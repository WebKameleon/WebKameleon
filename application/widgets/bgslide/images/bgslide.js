function kmw_bgslide_run_next_image()
{
    if (kmw_bgslide_images.length==0) return;
    
    var img=kmw_bgslide_images[kmw_bgslide_next_image];
    
    jQueryKam('#kmw_bgslide_0 img').attr('src',img);
    jQueryKam('#kmw_bgslide_0 img').fadeIn(kmw_bgslide_speed, function () {
	
		
		kmw_bgslide_next_image++;
		if (kmw_bgslide_next_image==kmw_bgslide_images.length) kmw_bgslide_next_image=0;
		
		var img2=kmw_bgslide_images[kmw_bgslide_next_image];
		jQueryKam('#kmw_bgslide_1 img').hide();
		jQueryKam('#kmw_bgslide_1 img').attr('src',img2);
	
		
		if (kmw_bgslide_images.length==1) return;
		
		setTimeout(function(){
			jQueryKam('#kmw_bgslide_1 img').attr('src',jQueryKam('#kmw_bgslide_0 img').attr('src')).load(function () {
			jQueryKam('#kmw_bgslide_1 img').show();
			});
			jQueryKam('#kmw_bgslide_0 img').hide();
			kmw_bgslide_run_next_image(); 
		},kmw_bgslide_pause);	    
    });

    
    jQueryKam('.kmw_bgslide_editmode img').attr('src',kmw_bgslide_thumbs[kmw_bgslide_next_image]);

}



jQueryKam(function ($) {

    $('body').prepend('<div class="kmw_bgslide" id="kmw_bgslide_0"><img/></div><div class="kmw_bgslide" id="kmw_bgslide_1"><img/></div>');
    
    var h=$(window).height();
    $('#kmw_bgslide_body').css({height: h});
    $('.kmw_bgslide').css({height: h});
    
    kmw_bgslide_run_next_image();
    
    $(window).resize(function() {
        var h=$(window).height();
        $('#kmw_bgslide_body').css({height: h});
        $('.kmw_bgslide').css({height: h});
    });
    
});
