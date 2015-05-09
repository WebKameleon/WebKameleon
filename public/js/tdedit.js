
page_number=0;

function init()
{
	plainToFrame();
	frames.edytor.focus();
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


function showWysiwyg()
{
	document.getElementById('advanced').style.display='none';
	document.getElementById('modadv').style.display='none';
	document.getElementById('zakladka_js').style.display='none';
	document.getElementById('wysiwyghtml').style.display='block';
}

function showAdvanced()
{
	document.getElementById('wysiwyghtml').style.display='none';
	document.getElementById('modadv').style.display='none';
	document.getElementById('zakladka_js').style.display='none';
	document.getElementById('advanced').style.display='block';
}

function showJavascript()
{
	document.getElementById('wysiwyghtml').style.display='none';
	document.getElementById('modadv').style.display='none';
	document.getElementById('advanced').style.display='none';
	document.getElementById('zakladka_js').style.display='block';
}

function showModAdv(init)
{  
	document.getElementById('wysiwyghtml').style.display='none';
	document.getElementById('advanced').style.display='none';
	document.getElementById('zakladka_js').style.display='none';
	document.getElementById('modadv').style.display='block';

	if (init) return;

	showModFun();
}


function ZapiszZmiany()
{
	document.getElementById('edytujtd').action.value='ZapiszTD';
	document.getElementById('edytujtd').submit();
}

function ZapiszZmianyZamknij()
{
	window.clipboardData.setData("Text",document.getElementById('edytujtd').plain.value);
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

function wstawPhp( href )
{
	if (pole_obrazka!=''){
   		document.all[pole_obrazka].value=href;
   		pole_obrazka='';
	}	
}

function wstawPlik(href)
{
	//alert('wstawPlik: ' + href);
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
}



function saveId(key,val)
{
	//alert (pole+'='+val);
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

var km_ddmenu_state = new Array();
var km_webtd_title = '';
var km_styles = {};

km_stateFromNamedCommand = function( command, editor ){
	var editor = CKEDITOR.instances.editor1;
	CKEDITOR.env.ie && ( depressBeforeEvent = 1 );

	var retval = CKEDITOR.TRISTATE_OFF;
	try { retval = editor.document.$.queryCommandEnabled( command ) ? CKEDITOR.TRISTATE_OFF : CKEDITOR.TRISTATE_DISABLED; }catch( er ){}

	depressBeforeEvent = 0;
	return retval;
}

km_aktualizacja_tdmenu = function(){
	var edytor = CKEDITOR.instances.editor1;
	
	km_ddmenu_state['cut']=false;
	jQueryKam("#km_ddmenu_wytnij").parent().removeClass("km_sf-disable").addClass("km_sf-disable");
	if (km_stateFromNamedCommand('Cut',edytor)==2){
		km_ddmenu_state['cut']=true;
		jQueryKam("#km_ddmenu_wytnij").parent().removeClass("km_sf-disable");
	}

	km_ddmenu_state['copy']=false;
	jQueryKam("#km_ddmenu_kopiuj").parent().removeClass("km_sf-disable").addClass("km_sf-disable");
	
	//console.log(km_stateFromNamedCommand('Copy',edytor));
	
	if (km_stateFromNamedCommand('Copy',edytor)==2){
		km_ddmenu_state['copy']=true;
		jQueryKam("#km_ddmenu_kopiuj").parent().removeClass("km_sf-disable");
	}
	
	
	
	km_ddmenu_state['paste']=true;
	km_ddmenu_state['pastetext']=true;
	km_ddmenu_state['pastefromword']=true;
	km_ddmenu_state['selectall']=true;
	km_ddmenu_state['clearall']=true;
	
	//superscript
	km_ddmenu_state['indexup']=true;
	jQueryKam("#km_ddmenu_indexup").parent().removeClass("km_sf-active");
	if (edytor.getCommand('superscript').state==1){
		jQueryKam("#km_ddmenu_indexup").parent().addClass("km_sf-active");
	}
	
	km_ddmenu_state['indexdown']=true;
	jQueryKam("#km_ddmenu_indexdown").parent().removeClass("km_sf-active")
	if (edytor.getCommand('subscript').state==1){
		jQueryKam("#km_ddmenu_indexdown").parent().addClass("km_sf-active");;
	}
	
	
	km_ddmenu_state['znajdz']=true;
	km_ddmenu_state['zamien']=true;
	km_ddmenu_state['html']=true;
	km_ddmenu_state['templates']=true;
	km_ddmenu_state['format_h1']=true;
	km_ddmenu_state['format_h2']=true;
	km_ddmenu_state['format_h3']=true;
	km_ddmenu_state['format_h4']=true;
	km_ddmenu_state['format_h5']=true;
	km_ddmenu_state['format_h6']=true;
	km_ddmenu_state['format_p']=true;
	km_ddmenu_state['format_pre']=true;
	km_ddmenu_state['form']=true;
	km_ddmenu_state['checkbox']=true;
	km_ddmenu_state['radio']=true;
	km_ddmenu_state['textfield']=true;
	km_ddmenu_state['textarea']=true;
	km_ddmenu_state['select']=true;
	km_ddmenu_state['button']=true;
	km_ddmenu_state['imagebutton']=true;
	km_ddmenu_state['hiddenfield']=true;
	km_ddmenu_state['anchor']=true;
	km_ddmenu_state['maska']=true;
	km_ddmenu_state['image']=true;
	km_ddmenu_state['hr']=true;
	km_ddmenu_state['flash']=true;
	km_ddmenu_state['table']=true;
	km_ddmenu_state['specialchar']=true;
	km_ddmenu_state['iframe']=true;
	km_ddmenu_state['link']=true;
	
	km_ddmenu_state['unlink']=false;
	jQueryKam("#km_ddmenu_unlink").parent().removeClass("km_sf-disable").addClass("km_sf-disable");
	if (edytor.getCommand('unlink').state==2){
		km_ddmenu_state['unlink']=true;
		jQueryKam("#km_ddmenu_unlink").parent().removeClass("km_sf-disable");
	}
	
}

km_zmien_format = function(kmtag){
	var edytor = CKEDITOR.instances.editor1;
	var config = edytor.config, lang = edytor.lang.format;
	var tags = config.format_tags.split( ';' );
	var km_styles = {};
	for ( var i = 0 ; i < tags.length ; i++ )
	{
		var tag = tags[ i ];
		km_styles[ tag ] = new CKEDITOR.style( config[ 'format_' + tag ] );
		km_styles[ tag ]._.enterMode = edytor.config.enterMode;
	}
	edytor.focus();
	edytor.fire( 'saveSnapshot' );
	var style = km_styles[ kmtag ], elementPath = new CKEDITOR.dom.elementPath( edytor.getSelection().getStartElement() );
	style[ style.checkActive( elementPath ) ? 'remove' : 'apply' ]( edytor.document );
	edytor.fire( 'saveSnapshot' );
};

km_title_check = function(){
	if (jQueryKam(".webtd_title").val().length>0)
		jQueryKam(".webtd_notitle").hide();
	else
		jQueryKam(".webtd_notitle").show();
}

czujkaTitle = function(event){
	if (jQueryKam(event.target).closest(".webtd_title").length === 0) {
       jQueryKam(document).unbind('click', czujkaTitle);
       km_title_check();
    }
}

boki_edytora = function(){
	var w = jQueryKam("#cke_editor1").width();
	jQueryKam("#km_editor_in").css('width', (w+40)+'px');
}



jQueryKam(document).ready(function(){


	
	
	

	
	// FORMAT
	jQueryKam("#km_ddmenu_format_h1").bind('click', function(){
		if (km_ddmenu_state['format_h1']) km_zmien_format('h1');
	});
	jQueryKam("#km_ddmenu_format_h2").bind('click', function(){
		if (km_ddmenu_state['format_h2']) km_zmien_format('h2');
	});
	jQueryKam("#km_ddmenu_format_h3").bind('click', function(){
		if (km_ddmenu_state['format_h3']) km_zmien_format('h3');
	});
	jQueryKam("#km_ddmenu_format_h4").bind('click', function(){
		if (km_ddmenu_state['format_h4']) km_zmien_format('h4');
	});
	jQueryKam("#km_ddmenu_format_h5").bind('click', function(){
		if (km_ddmenu_state['format_h5']) km_zmien_format('h5');
	});
	jQueryKam("#km_ddmenu_format_h6").bind('click', function(){
		if (km_ddmenu_state['format_h6']) km_zmien_format('h6');
	});
	jQueryKam("#km_ddmenu_format_p").bind('click', function(){
		if (km_ddmenu_state['format_p']) km_zmien_format('p');
	});
	jQueryKam("#km_ddmenu_format_pre").bind('click', function(){
		if (km_ddmenu_state['format_pre']) km_zmien_format('pre');
	});
	
	
	jQueryKam("#km_ddmenu_wytnij").bind('click', function(){
		if (km_ddmenu_state['cut']) edytor.execCommand('cut');
	});
	
	jQueryKam("#km_ddmenu_kopiuj").bind('click', function(){
		if (km_ddmenu_state['copy']) edytor.execCommand('copy');
	});
	
	jQueryKam("#km_ddmenu_wklej").bind('click', function(){
		if (km_ddmenu_state['paste']) edytor.execCommand('paste');
	});
	
	jQueryKam("#km_ddmenu_wklejtxt").bind('click', function(){
		if (km_ddmenu_state['pastetext']) edytor.execCommand('pastetext');
	});
	
	jQueryKam("#km_ddmenu_wklejword").bind('click', function(){
		if (km_ddmenu_state['pastefromword']) edytor.execCommand('pastefromword');
	});
	
	jQueryKam("#km_ddmenu_selectall").bind('click', function(){
		if (km_ddmenu_state['selectall']) edytor.execCommand('selectAll');
	});
	
	jQueryKam("#km_ddmenu_clearall").bind('click', function(){
		if (km_ddmenu_state['clearall']) edytor.execCommand('newpage');
	});
	
	jQueryKam("#km_ddmenu_indexup").bind('click', function(){
		if (km_ddmenu_state['indexup']) edytor.execCommand('superscript');
	});
	
	jQueryKam("#km_ddmenu_indexdown").bind('click', function(){
		if (km_ddmenu_state['indexdown']) edytor.execCommand('subscript');
	});
	
	jQueryKam("#km_ddmenu_znajdz").bind('click', function(){
		if (km_ddmenu_state['znajdz']) edytor.execCommand('find');
	});
	
	jQueryKam("#km_ddmenu_zamien").bind('click', function(){
		if (km_ddmenu_state['zamien']) edytor.execCommand('replace');
	});
	
	jQueryKam("#km_ddmenu_html").bind('click', function(){
		if (km_ddmenu_state['html']) edytor.execCommand('source');
	});
	
	jQueryKam("#km_ddmenu_opcje").bind('click', function(){
		if (jQueryKam('#advanced').css('display')=='block'){
			jQueryKam('#advanced').hide();
			jQueryKam('#wysiwyghtml').show();
		}
		else{
			jQueryKam('#advanced').show();
			jQueryKam('#wysiwyghtml').hide();
		}
	});
	
	jQueryKam("#km_ddmenu_template").bind('click', function(){
		if (km_ddmenu_state['templates']) edytor.execCommand('templates');
	});
	
	
	// FORMULARZ
	jQueryKam("#km_ddmenu_form").bind('click', function(){
		if (km_ddmenu_state['form']) edytor.execCommand('form');
	});
	jQueryKam("#km_ddmenu_checkbox").bind('click', function(){
		if (km_ddmenu_state['checkbox']) edytor.execCommand('checkbox');
	});
	jQueryKam("#km_ddmenu_radio").bind('click', function(){
		if (km_ddmenu_state['radio']) edytor.execCommand('radio');
	});
	jQueryKam("#km_ddmenu_text").bind('click', function(){
		if (km_ddmenu_state['textfield']) edytor.execCommand('textfield');
	});
	jQueryKam("#km_ddmenu_textarea").bind('click', function(){
		if (km_ddmenu_state['textarea']) edytor.execCommand('textarea');
	});
	jQueryKam("#km_ddmenu_select").bind('click', function(){
		if (km_ddmenu_state['select']) edytor.execCommand('select');
	});
	jQueryKam("#km_ddmenu_button").bind('click', function(){
		if (km_ddmenu_state['button']) edytor.execCommand('button');
	});
	jQueryKam("#km_ddmenu_imagebutton").bind('click', function(){
		if (km_ddmenu_state['imagebutton']) edytor.execCommand('imagebutton');
	});
	jQueryKam("#km_ddmenu_hidden").bind('click', function(){
		if (km_ddmenu_state['hiddenfield']) edytor.execCommand('hiddenfield');
	});
	
	
	jQueryKam("#km_ddmenu_anchor").bind('click', function(){
		if (km_ddmenu_state['anchor']) edytor.execCommand('anchor');
	});
	
	jQueryKam("#km_ddmenu_maska").bind('click', function(){
		if (km_ddmenu_state['maska']) edytor.execCommand('maska');
	});
	
	jQueryKam("#km_ddmenu_image").bind('click', function(){
		if (km_ddmenu_state['image']) edytor.execCommand('image');
	});
	
	jQueryKam("#km_ddmenu_hr").bind('click', function(){
		if (km_ddmenu_state['hr']) edytor.execCommand('horizontalrule');
	});
	
	jQueryKam("#km_ddmenu_flash").bind('click', function(){
		if (km_ddmenu_state['flash']) edytor.execCommand('flash');
	});
	
	jQueryKam("#km_ddmenu_table").bind('click', function(){
		if (km_ddmenu_state['table']) edytor.execCommand('table');
	});
	
	jQueryKam("#km_ddmenu_specialchar").bind('click', function(){
		if (km_ddmenu_state['specialchar']) edytor.execCommand('specialchar'); 
	});
	
	jQueryKam("#km_ddmenu_iframe").bind('click', function(){
		if (km_ddmenu_state['iframe']) edytor.execCommand('iframe'); 
	});
	
	jQueryKam("#km_ddmenu_link").bind('click', function(){
		if (km_ddmenu_state['link']) edytor.execCommand('link'); 
	});
	
	jQueryKam("#km_ddmenu_unlink").bind('click', function(){
		if (km_ddmenu_state['unlink']) edytor.execCommand('unlink'); 
	});
	
	if (jQueryKam(".webtd_title").length>0){
		jQueryKam(".webtd_title").keyup(km_title_check);
		km_title_check();
		jQueryKam(document).bind('click', czujkaTitle);
		
		if (jQueryKam('#wysiwyghtml').length>0){
			var edytor = CKEDITOR.instances.editor1;
			edytor.on('selectionChange', km_aktualizacja_tdmenu, null, null, 100);	
			
			edytor.on('instanceReady', function(){
				var body = edytor.document.getBody();
				body.on('mouseup', km_aktualizacja_tdmenu, null, null, 100);
				body.on('keyup', km_aktualizacja_tdmenu, null, null, 100);
				km_aktualizacja_tdmenu();
				boki_edytora();
			});
			
			edytor.on('resize', boki_edytora);
		}
		
		
	}	
	

	
});
