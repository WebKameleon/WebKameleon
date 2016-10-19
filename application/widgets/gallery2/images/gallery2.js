jQueryKam(function ($) {
    
	if (typeof(kmw_Galery2Array)!='undefined') {
		for (i=0;i<kmw_Galery2Array.length;i++) {
			$(".fancybox"+kmw_Galery2Array[i].sid).fancybox(kmw_Galery2Array[i]);	
		}
	}


});