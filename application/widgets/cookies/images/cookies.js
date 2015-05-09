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
        console.log(v);
    }
    
}

function kmw_cookies_ok()
{
    jQueryKam.cookie(kmw_cookie_name,new Date(),{expires:60,path:'/'});
    jQueryKam('.kmw_cookies').fadeOut();
}
