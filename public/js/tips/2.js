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
					   
                   }
			],
			position: { my: "left bottom+77", at: "left bottom+77", of: last_tip },
			dialogClass: "tip-article-title",
			closeOnEscape: true,
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

		last_dialog=KamDialog("<div>"+tr('tip-2')+"</div>", tr("KAMELEON GUIDE"), opt);

	}
);

