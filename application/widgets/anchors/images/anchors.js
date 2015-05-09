
jQueryKam(function($) {
    $(".kmw_anchors a").
        click( function () {
            var found=false;
            var title=$(this).html();
            $(":header:contains('"+title+"')").each(function () {
                               
                if (!found && title==$(this).html()) {
                    $('#preview-content,html,body').animate({scrollTop: $(this).offset().top},'slow');
                    found=true;
                }
            });
            return false;
        });    
});