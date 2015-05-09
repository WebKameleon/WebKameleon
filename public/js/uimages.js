function preview(obj)
{
	if (obj.selectedIndex==-1) return;
	href=UIMAGES+'/'+obj[obj.selectedIndex].value;
	dir=rozmiary[obj.selectedIndex][1];
	var t = new String;
	if (dir==0)
	{
		p_opis.innerText='Rozmiar: '+rozmiary[obj.selectedIndex][0]+' B';
		document.all.view.src=href+'?'+t.uniqueID;
	}
	else
		p_opis.innerText='Katalog: '+rozmiary[obj.selectedIndex][0]; 
  
}
function resize(obj)
{
	if (obj.selectedIndex==-1) return;
	dir=rozmiary[obj.selectedIndex][1];
	if (dir==1) return;
	document.all.view.style.visibility='hidden';
	href=UIMAGES+'/'+obj[obj.selectedIndex].value;
	img=document.createElement('<img src='+href+'>');
	img.src=href;
	w=img.width;
	h=img.height;
	p_opis.innerText=p_opis.innerText+'\nwymiary: '+w+'x'+h;
	
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


function wstawObrazek(obj)
{
	img=obj[obj.selectedIndex].value;
	top.opener.execScript("wstawObrazek('"+img+"')","JavaScript");
	window.close();
}

function deleteFile(obj,label1,label2)
{
	sel=obj.selectedIndex;
	if (sel==-1) 
		alert (label1);
	else
	{
		dir=rozmiary[sel][1];
		if (dir==1)
		{
			if (confirm(label2))
			{
				document.galeria.action.value='UsunKatalog';
				document.galeria.submit();
			}
		}
		else
		{
			if (confirm(label2))
			{
				document.galeria.action.value='UsunObrazek';
				document.galeria.submit();		
			}
		}
	}
}

function SetDir(obj)
{

	dir=rozmiary[obj.selectedIndex][1];
	if (dir==1)
	{
		dir_name=rozmiary[obj.selectedIndex][0]; 
		document.galeria.curdir.value=dir_name;
		document.galeria.submit();
	}
}

function newDir(msg)
{
    kat=prompt(msg,'newdir');
	if (kat==null) return;
    if (kat.length>0)
    {
	       document.galeria.action.value='DodajKatalog';
    	   document.galeria.newdir.value=kat;
	       document.galeria.submit();
    }
}

function dirUp()
{
	document.galeria.curdir.value='';
	document.galeria.submit();
}
