jQueryKam(function(){
    jQueryKam('.ss-required-asterisk').each(function () {
        jQueryKam(this).attr('title',jQueryKam(this).prev().attr("aria-label"));
    });
    
    jQueryKam('.kmw_form #ss-submit').val(kmw_form_submit);
    
    jQueryKam('.kmw_form form').attr('target','kmw_form').on('submit',function() {
        jQueryKam('.kmw_form_iframe').fadeIn();
        jQueryKam('.kmw_form').fadeOut();
    });
});