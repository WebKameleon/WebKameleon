pole_obrazka='';
page_number=0;

function init()
{
 plainToFrame();
 frames.edytor.focus();
}

function openGalery(obrazek,href)
{
	pole_obrazka=obrazek;
	a=open(href,'galeryjka',
	        "toolbar=0,location=0,directories=0,\
	        status=0,menubar=0,scrollbars=0,resizable=0,\
	        width=460,height=320");
	  return;
}

function plikGaleria(href)
{
        a=open(href,'pliczki',
                "toolbar=0,location=0,directories=0,\
                status=0,menubar=0,scrollbars=0,resizable=0,\
                width=460,height=320");
          return;
}

function openColors(kolor,ext)
{
	if (kolor.substring(0,1)=="#") kolor=kolor.substring(1,7);
	a=open('kolory.'+ext+'?u_color='+kolor,'Kolory',
	        "toolbar=0,location=0,directories=0,\
	        status=1,menubar=0,scrollbars=0,resizable=0,\
	        width=350,height=350");
	  return;
}

function ustawKolor(typ,atr,mode)
{
	if (advanced.style.visibility=='hidden')
	{
		var stext = frames.edytor.document.selection.createRange();
		if (atr == null)
		{
			stext.execCommand(typ,mode);
		}
		else
		{
			stext.execCommand(typ, mode, atr);
		}  
			stext.select();
			frames.edytor.focus();
		}
		else
		{
			document.edytujtd.bgcolor.value=atr;
			document.edytujtd.focus();
	}
}


function styl(typ,atr,mode)
{
	var stext = frames.edytor.document.selection.createRange();
	if (atr == null)
	{
		stext.execCommand(typ,mode);
	}
	else
	{
		stext.execCommand(typ, mode, atr);
	}  
	stext.select();
	frames.edytor.focus();
}

function insertClass(klasa)
{
 var node=frames.edytor.document.getElementById('userstyle');
 if (node==null)
 {
  var node= frames.edytor.document.createElement('<link id=userstyle href='+TEXTSTYLE+' rel=stylesheet type=text/css>');
  frames.edytor.document.body.appendChild(node);
 }
 var stext = frames.edytor.document.selection.createRange();
 stext.execCommand('RemoveFormat');
 text='<span class="'+klasa+'">'+stext.htmlText+'</span>';
 stext.pasteHTML(text);
 stext.select();
 frames.edytor.focus();
}

function obrazekBezGalerii()
{
 var stext = frames.edytor.document.selection.createRange();
 typ=stext.queryCommandEnabled('insertimage',true,'');
 sel=frames.edytor.document.selection.type;
 
 stext.execCommand('insertimage',true);
 stext.select();
 frames.edytor.focus();

}

function obrazekGaleria(href)
{
 var stext = frames.edytor.document.selection.createRange();
 typ=stext.queryCommandEnabled('insertimage',true,'');
 sel=frames.edytor.document.selection.type;
 if (typ==true && sel=='Control')
 {
  stext.execCommand('insertimage',true);
  stext.select();
  frames.edytor.focus();
 }
 else
  openGalery('',href);
}

function plainToFrame()
{  
 frames.edytor.document.body.innerHTML = document.edytujtd.plain.value;

 var node= frames.edytor.document.createElement('<link id=userstyle href='+TEXTSTYLE+' rel=stylesheet type=text/css>');
 frames.edytor.document.body.appendChild(node);

 frames.edytor.focus();

}
function frameToPlain()
{
	var node=frames.edytor.document.getElementById('userstyle');
	if (node!=null)
	{
		node.removeNode(true);
	}
	re= /<LINK[^>]*id=userstyle [^>]*>/gi;
	html=frames.edytor.document.body.innerHTML;
	html=html.replace(re,"");
	document.edytujtd.plain.value=html;
}

function showWysiwyg()
{
 document.edytujtd.plain.style.visibility='hidden';
 document.edytujtd.plain.style.display='none';
 document.all.edytor.style.visibility='visible';
 document.all.edytor.style.display='inline';
 advanced.style.visibility='hidden';
 advanced.style.display='none';
 plainToFrame();
 wysiwyghtml.style.visibility='visible';
 wysiwyghtml.style.display='inline';
 toolbar.style.visibility='visible';
 frames.edytor.focus();
 
 roolerbar.style.visibility='visible';
 roolerbar.style.display='inline';

 modadv.style.visibility='hidden';
 modadv.style.display='none';


 document.zakLeftHtml.src = 'img/zakl01n_left.gif';
 document.zakRightHtml.src = 'img/zakl01n_right.gif';
 zakMiddleHtml.background = 'img/zakl01n_middle.gif';
 
 document.zakLeftModAdv.src = 'img/zakl01n_left.gif';
 document.zakRightModAdv.src = 'img/zakl01n_right.gif';
 zakMiddleModAdv.background = 'img/zakl01n_middle.gif';


 document.zakLeftWysiwyg.src = 'img/zakl01a_left.gif';
 document.zakRightWysiwyg.src = 'img/zakl01a_right.gif';
 zakMiddleWysiwyg.background = 'img/zakl01a_middle.gif';
 

}

function showHtml()
{
 document.edytujtd.plain.style.visibility='visible';
 document.edytujtd.plain.style.display='inline';
 document.all.edytor.style.visibility='hidden';
 advanced.style.visibility='hidden';
 advanced.style.display='none';
 frameToPlain();
 wysiwyghtml.style.visibility='visible';
 wysiwyghtml.style.display='inline';
 toolbar.style.visibility='visible';
 
 roolerbar.style.visibility='hidden';
 roolerbar.style.display='none';
 
 modadv.style.visibility='hidden';
 modadv.style.display='none';

 document.zakLeftWysiwyg.src = 'img/zakl01n_left.gif';
 document.zakRightWysiwyg.src = 'img/zakl01n_right.gif';
 zakMiddleWysiwyg.background = 'img/zakl01n_middle.gif';
 
 document.zakLeftHtml.src = 'img/zakl01a_left.gif';
 document.zakRightHtml.src = 'img/zakl01a_right.gif';
 zakMiddleHtml.background = 'img/zakl01a_middle.gif';
 
 document.zakLeftAdv.src = 'img/zakl01n_left.gif';
 document.zakRightAdv.src = 'img/zakl01n_right.gif';
 zakMiddleAdv.background = 'img/zakl01n_middle.gif';

 document.zakLeftModAdv.src = 'img/zakl01n_left.gif';
 document.zakRightModAdv.src = 'img/zakl01n_right.gif';
 zakMiddleModAdv.background = 'img/zakl01n_middle.gif';

 document.edytujtd.plain.focus();
}
function showAdvanced()
{

 wysiwyghtml.style.visibility='hidden';
 wysiwyghtml.style.display='none';
 
 roolerbar.style.visibility='hidden';
 roolerbar.style.display='none';

 toolbar.style.visibility='hidden';

 advanced.style.visibility='visible';
 advanced.style.display='inline';

 modadv.style.visibility='hidden';
 modadv.style.display='none';
 
 document.zakLeftWysiwyg.src = 'img/zakl01n_left.gif';
 document.zakRightWysiwyg.src = 'img/zakl01n_right.gif';
 zakMiddleWysiwyg.background = 'img/zakl01n_middle.gif';
 
 document.zakLeftHtml.src = 'img/zakl01n_left.gif';
 document.zakRightHtml.src = 'img/zakl01n_right.gif';
 zakMiddleHtml.background = 'img/zakl01n_middle.gif';
 
 document.zakLeftAdv.src = 'img/zakl01a_left.gif';
 document.zakRightAdv.src = 'img/zakl01a_right.gif';
 zakMiddleAdv.background = 'img/zakl01a_middle.gif';

 document.zakLeftModAdv.src = 'img/zakl01n_left.gif';
 document.zakRightModAdv.src = 'img/zakl01n_right.gif';
 zakMiddleModAdv.background = 'img/zakl01n_middle.gif';

 if (document.edytujtd.plain.style.visibility=='visible')
	plainToFrame();
 else
	frameToPlain();
}

function showModAdv(init)
{

 wysiwyghtml.style.visibility='hidden';
 wysiwyghtml.style.display='none';
 
 roolerbar.style.visibility='hidden';
 roolerbar.style.display='none';

 toolbar.style.visibility='hidden';

 advanced.style.visibility='hidden';
 advanced.style.display='none';

 modadv.style.visibility='visible';
 modadv.style.display='inline';
 
 
 document.zakLeftWysiwyg.src = 'img/zakl01n_left.gif';
 document.zakRightWysiwyg.src = 'img/zakl01n_right.gif';
 zakMiddleWysiwyg.background = 'img/zakl01n_middle.gif';
 
 document.zakLeftHtml.src = 'img/zakl01n_left.gif';
 document.zakRightHtml.src = 'img/zakl01n_right.gif';
 zakMiddleHtml.background = 'img/zakl01n_middle.gif';
 
 document.zakLeftAdv.src = 'img/zakl01n_left.gif';
 document.zakRightAdv.src = 'img/zakl01n_right.gif';
 zakMiddleAdv.background = 'img/zakl01n_middle.gif';

 document.zakLeftModAdv.src = 'img/zakl01a_left.gif';
 document.zakRightModAdv.src = 'img/zakl01a_right.gif';
 zakMiddleModAdv.background = 'img/zakl01a_middle.gif';



 if (init) return;
 if (document.edytujtd.plain.style.visibility=='visible')
	plainToFrame();
 else
	frameToPlain();

if (showModFun != null) showModFun();
}


function insertTable()
{
 frameToPlain();

 cols=prompt(label_prompt_cols,4);
 rows=prompt(label_prompt_rows,5);
 width=prompt(label_prompt_width,100);
 border=prompt(label_prompt_border,1);
 k='';
 ww='';
 for (c=0;c<cols;c++)
 {
//  k+='<td backgroundColor="white" oncontextmenu="this.style.backgroundColor=prompt(\'Kolor ?\',this.style.backgroundColor);return false;">&nbsp;</td>';
  k+='<td>&nbsp;</td>';
 }
 w='<tr>'+k+'</tr>';
 for (r=0;r<rows;r++)
 {
  ww+=w;
 }
 tabelka='<table border='+border+' width='+width+'% >'+ww+'</table>';
 document.edytujtd.plain.value+=tabelka;
 plainToFrame();
} 
function ZapiszZmiany()
{
 vis=document.all.edytor.style.visibility;
 if (vis=='visible')
  frameToPlain();
 document.edytujtd.action.value='ZapiszTD';
 document.edytujtd.submit();
}

function ZapiszZmianyZamknij()
{
 vis=document.all.edytor.style.visibility;
 if (vis=='visible') frameToPlain();

 plainToFrame();
 frameToPlain();
 
 window.clipboardData.setData("Text",document.edytujtd.plain.value);
 window.close();

}

function wstawObrazek(img)
{
 if (pole_obrazka!='')
 {
   document.all[pole_obrazka].value=img;
   pole_obrazka='';
 }
 else
 {
   frames.edytor.focus();
   img=UIMAGES+'/'+img;
   var stext = frames.edytor.document.selection.createRange();
   stext.execCommand('insertimage',false, img);
	stext.select();
 }
}
function wstawPlik(href)
{
   	var stext = frames.edytor.document.selection.createRange();

	typ=stext.queryCommandEnabled('insertimage',true,'');
	sel=frames.edytor.document.selection.type;
	if (typ==true && sel=='Control')
	{
		obrazek=stext.item(0);
	}	

	href=UFILES+'/'+href;
	stext.execCommand('createlink',false,href);
	stext.select();
	frames.edytor.focus();

	if (typ==true && sel=='Control')
	{
		obrazek.style.visibility='hidden';
		obrazek.style.visibility='visible';
	}

//	text='<a href="'+img+'"><img border=0 src="'+img+'"></a>';
//	stext.pasteHTML(text);
//	stext.select();

}

function kameleon_inner_link(label)
{
	var stext = frames.edytor.document.selection.createRange();

	typ=stext.queryCommandEnabled('insertimage',true,'');
	sel=frames.edytor.document.selection.type;
	if (typ==true && sel=='Control')
	{
		obrazek=stext.item(0);
		html=obrazek.parentElement.outerHTML;
	}
	else
		html=stext.htmlText;
	//wykryj numer strony
	re = new RegExp("kameleon:inside_link\\(([0-9|a-z|:|\$]+)\\)");
	re_arr = re.exec(html);
	if (re_arr!=null)
		page_number=re_arr[1]; //w formacie [p|e|d|i][:][0-9]
	else
		page_number=-1;
	//alert(lang+':'+id);
	//openTree('',id,'multi=1&lang='+lang);
	openTree('',page_number,'multi=1');
}


function saveId(key,val)
{
//	alert (pole+'='+val);
	if (pole!='')
		document.all[pole].value=val;
	else
	{

		var stext = frames.edytor.document.selection.createRange();
	
		typ=stext.queryCommandEnabled('insertimage',true,'');
		sel=frames.edytor.document.selection.type;
		if (typ==true && sel=='Control')
		{
			obrazek=stext.item(0);
			html=obrazek.parentElement.outerHTML;
		}
		else
			html=stext.htmlText;
	
		//wykryj numer strony
		re = new RegExp("kameleon:inside_link\\(([0-9|a-z|:|\$]+)\\)");
		re_arr = re.exec(html);
		if (re_arr!=null)
		{
			page_number=re_arr[1];
		}
	//	page=prompt(label,page_number);
		page=val;
	
		if (page!=null)
		{
			page_number=page;
	//		stext.execCommand('RemoveFormat');
			stext.execCommand('createlink',false,'kameleon:inside_link('+page+')');
			stext.select();
			frames.edytor.focus();
			if (typ==true && sel=='Control')
			{
				obrazek.style.visibility='hidden';
				obrazek.style.visibility='visible';
			}
		}
	}
}

function kameleon_outer_link(label)
{
	var stext = frames.edytor.document.selection.createRange();

	typ=stext.queryCommandEnabled('insertimage',true,'');
	sel=frames.edytor.document.selection.type;
	if (typ==true && sel=='Control')
	{
		obrazek=stext.item(0);
		html=obrazek.parentElement.outerHTML;

	}
	else
		html=stext.htmlText;

//	stext.execCommand('RemoveFormat');
//	re = new RegExp("kameleon:inside_link\\(([0-9]+)\\)");
	re = new RegExp("kameleon:inside_link\\(([0-9|a-z|:|\$]+)\\)");

	re_arr = re.exec(html);
	if (re.test(html))
		stext.execCommand('createlink',false,'');
	stext.execCommand('createlink',true,'http://');
	stext.select();
	frames.edytor.focus();

	if (typ==true && sel=='Control')
	{
		obrazek.style.visibility='hidden';
		obrazek.style.visibility='visible';
	}
}

function td_resize_imga(obj)
{
	re = /i_resize_n/
	re_arr = re.exec(obj.src);

	if (re_arr!=null)
	{
		obj.src='img/i_resize_a.gif';
	}
	else
	{
		obj.src='img/i_resize_off_a.gif';
	}

}
function td_resize_img(obj)
{
//	alert(obj.src);

	re = /resize_a/
	re_arr = re.exec(obj.src);
	re2 = /resize_n/
	re_arr2 = re2.exec(obj.src);


	if (re_arr!=null || re_arr2!=null)
	{
		obj.src='img/i_resize_n.gif';
	}
	else
	{
		obj.src='img/i_resize_off_n.gif';
	}

}



function td_resize(obj)
{
	if (document.all.edytor.style.width=='100%')
	{
		document.all.edytor.style.width=TDEDIT_WIDTH;
		obj.src='img/i_resize_n.gif';
	}
	else
	{
		document.all.edytor.style.width='100%';
		obj.src='img/i_resize_off_n.gif';
	}
}


