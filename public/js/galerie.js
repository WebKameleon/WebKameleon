
var galeriaPole='';
var kolorPole='';

function otworzGalerie(galeria,id)
{
	galeriaPole=id;
	a=open('ufiles.php?galeria='+galeria,'galeryjka',"toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=0,resizable=0,width=840,height=420");
	return;
}

function wstawObrazek(img)
{
	document.getElementById(galeriaPole).value = img;
}


function ustawKolor(par,kolor)
{
	document.getElementById(kolorPole).value=kolor;
}

function otworzPalete(id)
{
	kolorPole=id;
	kolor=document.getElementById(kolorPole).value;
	if (kolor.substring(0,1)=="#") kolor=kolor.substring(1,7);
	a=open('kolory.php?u_color='+kolor,'Kolory','toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=0,resizable=0,width=400,height=400');
	return;
}


function wstawPhp( href )
{
	wstawObrazek(href);
}