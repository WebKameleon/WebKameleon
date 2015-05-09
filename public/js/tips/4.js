jQueryKam('body').animate(
	{scrollTop: jQueryKam(".km_tip_active").offset().top-20 }, 
	500, 
	"linear", 
	function() {
		var opt = {
           
		   position: { my: "right bottom+55", at: "right bottom+55", of: last_tip },
		   dialogClass: "tip-article-save",
		   close: tip_closed,
		   autoOpen: false,
    				show: {
        				effect: 'fade',
        				duration: 500
    				},
    				hide: {
        				effect: 'fade',
        				duration: 500
    				}

       };
	   
		last_dialog=KamDialog("<div>"+tr('tip-4')+"</div>", tr("KAMELEON GUIDE"), opt);
	}
);