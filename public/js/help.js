	var hiddenStuff = new Array();


	function KameleonHelp(obj,txt,offset_x,offset_y)
	{
		document.all["kameleon_help"].style.visibility="hidden";
		document.all["kameleon_help"].style.left=event.offsetX;
		document.all["kameleon_help"].style.top=event.offsetY+25;
		document.all["kameleon_help_txt"].innerHTML=txt;
		document.all["kameleon_help"].style.visibility="visible";

		showHiddenStuff();

		slideHelpObj(obj,document.all["kameleon_help"],offset_x,offset_y);
	}

	function CloseKameleonHelp()
	{
		if (help_close_obj.MouseIsOver)
			setTimeout(CloseKameleonHelp,help_close_t*1000);
		else
		{
			document.all["kameleon_help"].style.visibility="hidden";
			showHiddenStuff();
		}
	}

	function CloseKameleonHelpTimeout(t,obj)
	{
		if (document.all["kameleon_help"].style.visibility=="hidden") return;
		help_close_obj=obj;
		help_close_t=t;

		setTimeout(CloseKameleonHelp,t*1000);
	}

	function getHelpAbsolutePos(el) 
	{
		var r = { x: el.offsetLeft, y: el.offsetTop };
		if (el.offsetParent) 
		{
			var tmp = getHelpAbsolutePos(el.offsetParent);
			r.x += tmp.x;
			r.y += tmp.y;
		}
		r.w=el.offsetWidth;
		r.h=el.offsetHeight;

		return r;
	}

	function ClosestTD()
	{
		helps=document.all.tags('TDHELP');
		closest_x=0;
		closest_y=0;
		closest_td=null;
		for (i=0;i<helps.length; i++)
		{
			objid=helps[i].name.substr(2);
			obj=document.all[objid];
			pos=getHelpAbsolutePos(obj);

			if (pos.x<closest_x || !closest_x)
			{
				closest_td=obj;
				closest_x=pos.x;
				closest_y=pos.y;
			}
			if (pos.x==closest_x && pos.y<closest_y)
			{
				closest_td=obj;
				closest_x=pos.x;
				closest_y=pos.y;
			}
		}
		return (closest_td);
	}

	function slideHelpObj(destObj,slideObj,offX,offY)
	{
		var SLIDE_IS_OVER = 0;
		start_y = event.clientY;
		start_x = event.clientX;
		okno_x = document.body.clientWidth;
		okno_y = document.body.clientHeight;
		var destpos=getHelpAbsolutePos(destObj); 
		end_width = destObj.offsetWidth;
		end_height = destObj.offsetHeight;
		if (offX < 0) offX = end_width + offX;
		if (offY < 0) offY = end_height + offY;
		end_y = destpos.y+offY;
		end_x = destpos.x+offX;
		midd_x = Math.ceil((end_x - start_x) / 4) + start_x;
		midd_y = start_y + 2*(end_y-start_y);
		Q_x = 2*(midd_x - start_x);
		Q_y = 2*(midd_y - start_y);
		R_x = end_x - 2*midd_x + start_x;
		R_y = end_y - 2*midd_y + start_y;
		cross = ((end_x - start_x)*(end_x - start_x)) + ((end_y - start_y)*(end_y - start_y));
		cross = Math.sqrt(cross);
		global_step = 10 / cross;
		global_speed = Math.floor(global_step * 40);
		t_x = 0;
		t_y = 0;
		myslideObj=slideObj;
		slideHelpTime();
	}

	function slideHelpTime()
	{
		speed = global_speed;
		step = global_step;
		curr_x = Math.round(start_x + t_x*Q_x + t_x*t_x*R_x);
		curr_y = Math.round(start_y + t_y*Q_y + t_y*t_y*R_y);
		t_x += step;
		t_y += step;
		myslideObj.style.top = curr_y;
		myslideObj.style.left = curr_x;
		window.scrollTo(curr_x-okno_x+160,curr_y-okno_y+120);
		if (t_x >= 1 && t_y >= 1) 
		{
			myslideObj.style.top = end_y;
			myslideObj.style.left = end_x;
//			window.scrollTo(end_x,end_y);
			slideIsOverLetsHideSomeStuff(myslideObj);
			SLIDE_IS_OVER = 1;
			return;
		}
		setTimeout(slideHelpTime,speed);
	}

	function slideIsOverLetsHideSomeStuff(obj)
	{
		
		o_cor=getHelpAbsolutePos(obj); 

		//strzalka nie
		o_cor.x+=10;
		o_cor.y+=10;
		o_cor.w-=10;
		o_cor.h-=10;


		sels=document.all.tags('SELECT');
		for (i=0;i<sels.length;i++)
		{
			s_cor=getHelpAbsolutePos(sels[i]); 
			if (sels[i].style.visibility=='hidden') continue;

			x_overlap=((o_cor.x < s_cor.x && s_cor.x<o_cor.x+o_cor.w) || ((s_cor.x < o_cor.x && o_cor.x<s_cor.x+s_cor.w)) )?1:0;
			y_overlap=((o_cor.y < s_cor.y && s_cor.y<o_cor.y+o_cor.h) || ((s_cor.y < o_cor.y && o_cor.y<s_cor.y+s_cor.h)) )?1:0;
			if (x_overlap && y_overlap)
			{
				sels[i].style._visibility = sels[i].style.visibility;
				sels[i].style.visibility='hidden';
				hiddenStuff[hiddenStuff.length]=sels[i];
			}
		}

	}

	function showHiddenStuff()
	{
		if (!hiddenStuff.length) return;

		for (i=0;i<hiddenStuff.length;i++)
		{
			hiddenStuff[i].style.visibility=hiddenStuff[i].style._visibility;
		}
		
		delete hiddenStuff;
		hiddenStuff = new Array();
	}