var objHref;

function previewtime()
{
	if (preview_img.readyState=="complete")
	{
		html=html+"<br>"+preview_img.width+" x "+preview_img.height;

		if (preview_img.width>160)
		{
			preview_img.width=160;
		}
	}
	else
	{
		setTimeout(previewtime,20);
	}

}
function bodytime()
{
	if (document.getElementById('view'))
	{
    if (document.getElementById('view').document.readyState=="complete")
  	{
  		document.getElementById('view').document.body.innerHTML="<img id='img_id' src='/'>";
  		document.getElementById('view').document.body.innerHTML="<img id='img_id' src='"+objHref+"'>";
  		preview_img=frames.view.document.getElementById('img_id');
  		setTimeout(previewtime,20);
  	}
  	else
  	{
  		setTimeout(bodytime,20);
  	}
  }
}



function tryedit(obj,allow)
{
	if (edit=document.getElementById('edit_file_img'))
	{
		edit.style.display='none';
	
		if (obj.selectedIndex==-1) return;
		if (!rozmiary.length) return;
		if (allow==0) return;
		
		row=rozmiary[obj.selectedIndex].toString();
		cols=row.split(',');
		dir=cols[1];
	
		if (dir==0)
		{
			edit.style.display='';
		}
	}
}

function edit_file(galeria,edytor)
{
	lista=document.getElementById('select_lista');

	e=edytor.split(';');
	if (e.length>1)
	{
		edytor='';

		v=lista.value.split('.');
		ext=v[v.length-1].toLowerCase();
		
		for (i=0;i<e.length;i++)
		{
			para=e[i].split(':');
			if (para[0].indexOf(ext)>=0) edytor=para[1];
		}

		
	}

	if (edytor=='') return;
	window.open(edytor+'?plik='+lista.value+'&galeria='+galeria,'_blank','directories=no,menubar=no,resizable=yes,toolbar=no,width=700,height=500');
}

function edit_file_new(galeria,edytor,plik)
{
	e=edytor.split(';');
	if (e.length>1)
	{
		edytor='';

		v=plik.split('.');
		ext=v[v.length-1].toLowerCase();
		
		for (i=0;i<e.length;i++)
		{
			para=e[i].split(':');
			if (para[0].indexOf(ext)>=0) edytor=para[1];
		}
	}
	if (edytor=='') return;
	window.open(edytor+'?plik='+plik+'&galeria='+galeria,'_blank','directories=no,menubar=no,resizable=yes,toolbar=no,width=700,height=500');
}

function preview(obj)
{
	var t = Math.random();

	if ( document.getElementById('final_button') != null)
		if (document.getElementById('final_button')._value != null)
		{
			document.getElementById('final_button').value=document.getElementById('final_button')._value;
		}


	if (obj.selectedIndex==-1) return;
	if (!rozmiary.length) return;

	
	row=rozmiary[obj.selectedIndex].toString();
	cols=row.split(',');
	dir=cols[1];

	//var href=UFILES+'/'+obj[obj.selectedIndex].value;
	var href=obj[obj.selectedIndex].value;
	var hrefA=href+'?'+t;

	if (dir==0)
	{
		if (document.getElementById('preview_check'))
		if (document.getElementById('preview_check').checked) 
		{
			for (i=href.length;i;i--)
			{
				if (href.substr(i,1)=='.')
				{
					ext=href.substr(i+1);
					break;
				}
			}

			if (ext.length && (ext.toLowerCase()=="gif" || ext.toLowerCase()=="jpg" || ext.toLowerCase()=="jpeg" || ext.toLowerCase()=="png") )
			{
				document.getElementById('view').src=hrefA;
				objHref=hrefA;
			}
			else
			{
				if (ext.length && ext.toLowerCase()=="zip" )
				{
					document.all['final_button']._value=document.all['final_button'].value;
					document.all['final_button'].value=label_unzip;
					document.getElementById('view').src="empty.php";
				}
				else
				{
					document.getElementById('view').src=hrefA;  
				}
			}
		}
	}
	else
	{
		document.getElementById('view').src="empty.php";
	}
	  
}


km_uimages = function(hrefA){
	if (jQueryKam("#km_uimages").length==0){
		var div = jQueryKam("<div></div>").attr("id","km_uimages");
		var divIn = jQueryKam("<div></div>").addClass("km_uimagesIn").html('<img src="'+hrefA+'" style="max-width:550px; max-height:400px" />');
		div.append(divIn);
		jQueryKam("body").append(div);
		jQueryKam("#km_uimages").dialog({
			autoOpen: false,
			dialogClass : 'km_sharedialog',
			height: 500,
			width: 600,
			modal: true,
			title: 'Podgląd',
			buttons: {
				"Anuluj": {
					text:'Zamknij',
					id:'km_setup_serwis_cancel',
					click : function() { 
						jQueryKam( this ).dialog( "close" ); 
					}
				}
			}
		});
	}
	else
	{
		jQueryKam("#km_uimages").remove();	
		km_uimages(hrefA);
	}
	jQueryKam("#km_uimages").dialog("open");
}

function preview_new(href)
{
	var href=href+'?'+Math.random();
	window.open(href, "podglad","status=1,toolbar=0,width=800,height=400");	  
}

function PreviewMode_now(obj)
{
	if (jQueryKam('#preview_check').is(':checked'))
	{
		jQueryKam(".icon_preview").show();
	}
	else
	{
		jQueryKam(".icon_preview").hide();
	}
}


function PreviewMode(obj)
{
	if (document.getElementById('preview_check').checked)
	{
		document.getElementById('view').style.visibility="visible";
		preview(document.getElementById('select_lista'));
	}
	else
	{
		document.getElementById('view').src="empty.php";
	}
}


function resize(obj)
{
	return;
	if (obj.selectedIndex==-1) return;
	if (!rozmiary.length) return;



	row=rozmiary[obj.selectedIndex].toString();


	cols=row.split(',');
	dir=cols[1];
	if (dir==1) return;


	document.all.view.style.visibility='hidden';
	href=UFILES+'/'+obj[obj.selectedIndex].value;
	img=document.createElement('<img src='+href+'>');
	img.src=href;
	w=img.width;
	h=img.height;
	if (w>h)
		max=w;
	else
		max=h;
	zoom=150;
	if (max>zoom)
	{
		if (w==max)
		{
			document.galeria.view.width=zoom;
			hp=(h*zoom)/w;
			hp=Math.round(hp);
			document.galeria.view.height=hp;
		}
		else
		{
			document.galeria.view.height=zoom;
			wp=(w*zoom)/h;
			wp=Math.round(wp);
			document.galeria.view.width=wp;
		}
	}
	else
	{
		document.galeria.view.width=w;
		document.galeria.view.height=h;
	}
	document.all.view.style.visibility='visible';
}


function deleteFile(obj,label1,label2)
{
	sel=obj.selectedIndex;
	if (sel==-1) 
		alert (label1);
	else
	{

		row=rozmiary[sel].toString();
		cols=row.split(',');
		dir=cols[1];
		if (confirm(label2))
		{
			document.getElementById('akcja').value='UsunPlik';
			document.getElementById('formularz').submit();		
		}
	}
}

function deleteFile_new(type, obj, label2)
{
	if (type=='dir'){
		if (confirm(label2))
		{
			document.getElementById('lista').value=obj;
			document.getElementById('akcja').value='UsunPlikNew';
			document.getElementById('formularz').submit();		
		}
	}
	else {
		document.getElementById('lista').value=obj;
		document.getElementById('akcja').value='UsunPlikNew';
		document.getElementById('formularz').submit();
	}
	
}


function SetDir(obj)
{
	sel=obj.selectedIndex;
	if (sel==-1) return;

	row=rozmiary[sel].toString();
	cols=row.split(',');
	dir=cols[1];
	if (jQueryKam(obj).attr("rel"))
	{
		dir_name=jQueryKam(obj).attr("rel"); 
		document.galeria.newdir.value=dir_name;
		document.galeria.submit();
	}
	else
	{
		document.galeria.action.value='Download';
		document.galeria.submit();
		document.galeria.action.value='';
	}
}

function setCKEditor(field,type)
{
  // wstawianie obrazka z ufiles
    if (type==1)
      window.parent.CKEDITOR.dialog.getCurrent().getContentElement( 'info', 'txtUrl' ).setValue(field);
    else if (type==2)
      window.parent.CKEDITOR.dialog.getCurrent().getContentElement( 'Link', 'txtUrl' ).setValue(field);
    else if (type==3)
      window.parent.CKEDITOR.dialog.getCurrent().getContentElement( 'info', 'plikiUrl' ).setValue(field);
    else if (type==4)
      window.parent.CKEDITOR.dialog.getCurrent().getContentElement( 'info', 'obrazkiUrl' ).setValue(field);
    else if (type==5)
      window.parent.CKEDITOR.dialog.getCurrent().getContentElement( 'info', 'src' ).setValue(field);  
}

function checkCKEditor(type){
	if (type==1)
    	file = window.parent.CKEDITOR.dialog.getCurrent().getContentElement( 'info', 'txtUrl' ).getValue();
    else if (type==2)
    	file = window.parent.CKEDITOR.dialog.getCurrent().getContentElement( 'Link', 'txtUrl' ).getValue();
    else if (type==3)
    	file = window.parent.CKEDITOR.dialog.getCurrent().getContentElement( 'info', 'plikiUrl' ).getValue();
    else if (type==4)
    	file = window.parent.CKEDITOR.dialog.getCurrent().getContentElement( 'info', 'obrazkiUrl' ).getValue();
    else if (type==5)
    	file = window.parent.CKEDITOR.dialog.getCurrent().getContentElement( 'info', 'src' ).getValue();
    if (file.length>0){
    	jQueryKam('.rel').each(function(){
			var a = jQueryKam(this);
			if (a.attr('title')==file) a.parent().parent().addClass('file_active');
		});
    }
}

function newDir(msg)
{
    jQueryKam("#uploader").hide();
	kat=prompt(msg,'newdir');
	if (kat==null) return;
    if (kat.length>0)
    {
	       jQueryKam('#akcja').attr('value','DodajKatalog');//document.galeria.action.value='DodajKatalogFiles';
	       jQueryKam('#f_newdir').attr('value',kat);
	       document.getElementById('formularz').submit();
    }
}

function dirUp()
{
	document.galeria.newdir.value='..';
	document.galeria.submit();
}

function wgrajPlik()
{
	jQueryKam("#uploader").show();
	jQueryKam("#kameleonPromptDivId").hide();
}

jQueryKam(document).ready(function(){
	jQueryKam(".tabelka .file").bind('click', function(){
		jQueryKam(".file_active").removeClass('file_active');
		jQueryKam(this).parent().parent().addClass('file_active');
	});
});