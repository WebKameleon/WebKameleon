


jQueryKam('body').animate(
		{scrollTop: jQueryKam(".km_tip_active").offset().top-20 }, 
		500, 
		"linear", 
		function() {

				var opt = {
						width: 500,
						dialogClass: "tip-thanks",
						close: end_of_tips,
						buttons: [
								{
										text: tr("OK"),
										click: function () {
												jQueryKam(this).dialog("close");
												end_of_tips();
										}

								}
				   
						],
		
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

				KamDialog("<div>"+tr('tip-0')+"</div>", tr("KAMELEON GUIDE"), opt);

		}
);