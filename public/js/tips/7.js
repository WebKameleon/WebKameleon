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
		   position: { my: "right top+20", at: "right top+20", of: last_tip },
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
	   
		last_dialog=KamDialog("<div>"+tr('tip-7')+"</div>", tr("KAMELEON GUIDE"), opt);
	}
);