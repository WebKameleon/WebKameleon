jQueryKam('body').animate(
	{scrollTop: jQueryKam(".km_tip_active").offset().top-200 }, 
	500, 
	"linear", 
	function() {
		var opt = {

		   position: { my: "left-20 top+15", at: "left-20 top+15", of: last_tip },
		   dialogClass: "tip-article-pencil",
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

		last_dialog=KamDialog("<div>"+tr('tip-1')+"</div>", tr("KAMELEON GUIDE"), opt);
	}
);