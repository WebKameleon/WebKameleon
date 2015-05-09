jQueryKam('body').animate(
	{scrollTop: jQueryKam(".km_tip_active").offset().top-20 }, 
	500, 
	"linear", 
	function() {
		var opt = {
           
		   position: { my: "right top+30", at: "right top+30", of: last_tip },
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
	   
		last_dialog=KamDialog("<div>"+tr('tip-6')+"</div>", tr("KAMELEON GUIDE"), opt);
	}
);