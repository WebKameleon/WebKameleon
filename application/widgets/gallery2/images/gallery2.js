jQueryKam(function ($) {
    
	for (i=0;i<kmw_Galery2Array.length;i++) {
		$(".fancybox"+kmw_Galery2Array[i].sid).fancybox(kmw_Galery2Array[i]);	
	}

});