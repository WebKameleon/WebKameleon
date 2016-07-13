var kmw_cookie_name;

function kmw_cookies(mode)
{
    if (kmw_cookie_name==null) kmw_cookie_name='kmw_cookies_info';
    
    
    
    var v=jQueryKam.cookie(kmw_cookie_name);
    if (typeof(v)=='undefined') {
        var c=jQueryKam('.kmw_cookies');
        var b=jQueryKam('body');
        switch (mode) {
            case 0:
                b.prepend(c);
                break;
            case 1:
                b.append(c);
                break;
            
        }
        
        c.fadeIn();
    }
    
    if (jQueryKam( window ).width()<475) jQueryKam('.kmw_cookies').click(function() {jQueryKam(this).removeClass('kmw_cookies_mobile');}).addClass('kmw_cookies_mobile');
    
}

function kmw_cookies_ok()
{
    jQueryKam.cookie(kmw_cookie_name,new Date(),{expires:60,path:'/'});
    jQueryKam('.kmw_cookies').fadeOut();
}


jQueryKam(function() {
    if (typeof(kmw_cookies_display_mode)=='undefined') {
        kmw_cookies_display_mode=2;
    }
    kmw_cookies(kmw_cookies_display_mode);
});