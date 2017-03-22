function kmw_articlelist_nav(limit,page)
{

    jQueryKam('.kmw_articlelist_nav a').removeClass('active');
    jQueryKam('.kmw_articlelist_nav a[rel='+page+']').addClass('active');
    
    var i=0;
    limit=parseInt(limit);
    var offset=(parseInt(page)-1)*limit; 
    jQueryKam('.kmw_articlelist').hide().slice(offset,offset+limit).fadeIn();
    
}


jQueryKam(function($){
    
    kmw_articlelist_nav(jQueryKam('.kmw_articlelist_nav').attr('rel'),1);

    $('.kmw_articlelist_nav a').click(function () {
        kmw_articlelist_nav(jQueryKam('.kmw_articlelist_nav').attr('rel'),jQueryKam(this).attr('rel'));  
    });

	


})
