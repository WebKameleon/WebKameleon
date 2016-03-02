

jQueryKam(function($) {
	for (i=0;i<kmw_thmbslideArray.length;i++) {
		
		$("div#kmw_thumbslide_"+kmw_thmbslideArray[i].sid).smoothDivScroll({
				autoScrollingMode: "onStart",
				autoScrollingInterval: kmw_thmbslideArray[i].speed,
				hotSpotScrollingInterval: kmw_thmbslideArray[i].speed
		}).fadeIn(500);
		
	}
	    
	    
});
