var tip_objects=[];
var last_dialog=null;
var last_tip = null;
var next_tip_loading=false;

function run_tip(tip)
{

	if (last_tip != null) {
	    last_tip.removeClass("km_tip_active");
	}
	tip.addClass( "km_tip_active" );
	last_tip = tip;
	jQueryKam( "#km-black-bg" ).addClass( "km-black-bg-active" ).fadeIn();
	
	if (last_dialog != null) {
		last_dialog.dialog("close");
	}
	
	var tip_no=tip.attr('tip');
	
	jQueryKam.getScript( KAM_ROOT+'js/tips/'+tip_no+'.js' ).done(function() {
            setTimeout(function() {
                next_tip_loading=false;
            },900);
	});
}

function next_tip()
{
    next_tip_loading=true;
    for (k in tip_objects)
    {
        if (tip_objects[k]!=null)
        {
            run_tip(tip_objects[k]);
            tip_objects[k]=null;
            break;
        } 
    }
}


function tip_closed(e,ui)
{
    
    if (next_tip_loading) return;
    var opt = {
        width: 500,
        dialogClass: "tip-closed",
        close: function() {
            jQueryKam( "#km-black-bg" ).fadeOut();
            jQueryKam( ".km_tip" ).removeClass( "km_tip_active" );
            
            jQueryKam.get(KAM_ROOT+'ajax/tip_done/0');
            ga('send','event','tips/close',last_tip.attr('tip'));            
        },
        buttons: [
               {
                   text: tr("Yes"),
                   click: function () {
                        jQueryKam(this).dialog("close");
                        jQueryKam( "#km-black-bg" ).fadeOut();
                        jQueryKam( ".km_tip" ).removeClass( "km_tip_active" );
                        
                        jQueryKam.get(KAM_ROOT+'ajax/tip_done/0');
                        ga('send','event','tips/close',last_tip.attr('tip'));
                   }
               },
              {
                   text: tr("No"),
                   click: function () {
                        jQueryKam(this).dialog("close");
                        run_tip(last_tip);
                   }
               }
                               
       ]  
   };

    KamDialog("<div>"+tr('tip-disable-tips')+"</div>", tr("KAMELEON GUIDE"), opt);
}


function end_of_tips()
{
    jQueryKam( "#km-black-bg" ).fadeOut();
    jQueryKam( ".km_tip" ).removeClass( "km_tip_active" );
    jQueryKam.get(KAM_ROOT+'ajax/tip_done/0');
    
    ga('send','event','tips/close','end');
}


jQueryKam(function ($) {

	
	if (KAM_TIPS.length > 0)
    {
        var tips=KAM_TIPS.split(',');
		for (k in tips)
		{
			tip_objects[tips[k]]=null;
		}
		
		
		var tip_count=$(".km_tip").size();
		$(".km_tip").each(function(k,v){
			var tip_no=$(this).attr('tip');
			
			if (typeof(tip_objects[tip_no])!='undefined')
			{
				if (tip_objects[tip_no]==null) 
				{
					tip_objects[tip_no] = $(this);
				}
			}
				
			if (k==tip_count-1)
			{
				setTimeout(next_tip, 2000);
			}
		}
		);
		
		
		
        
    }
});






