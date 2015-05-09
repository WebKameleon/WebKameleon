jQueryKam('body').animate(
	{scrollTop: jQueryKam(".km_tip_active").offset().top-5 }, 
	500, 
	"linear", 
	function() {
		var opt = {

			position: { my: "left top+25", at: "left top+25", of: last_tip },
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

		last_dialog=KamDialog("<div>"+tr('tip-8')+"</div>", tr("KAMELEON GUIDE"), opt);
		
		jQueryKam('.km_logo').on('click',function() {
			var href=this.href;
			jQueryKam.get(KAM_ROOT+'ajax/tip_done/8', function() {
				location.href=href;
			});
			
			return false;
		});


	}
);
