jQueryKam('body').animate(
	{scrollTop: jQueryKam(".km_tip_active").offset().top-20 }, 
	500, 
	"linear", 
	function() {
		var opt = {
           buttons: [
                   {
                       text: tr("Next Tip"),
                       click: function () {
                           //jQueryKam(this).dialog("close");
						   next_tip();
                       }
                   }],
		   position: { my: "top-60", at: "top-60", of: last_tip },
		   dialogClass: "tip-article-content",
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
		
		last_dialog=KamDialog("<div>"+tr('tip-3')+"</div>", tr("KAMELEON GUIDE"), opt);
	}
);