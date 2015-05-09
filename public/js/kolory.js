function preview(obj)
{
  view.bgColor=obj.bgColor;
  document.kolory.hexcolor.value=obj.bgColor;
  HEXtoRGB(document.kolory.hexcolor);
}

function userpreview(obj)
{
 col=obj.value;
 view.bgColor=col;
}

function zapiszKolor(obj)
{
  color=document.kolory.hexcolor.value;
  if (top.opener.execScript) {
    top.opener.execScript("ustawKolor('forecolor','"+color+"')","JavaScript"); //for IE
  } else {
    eval('self.opener.' + "ustawKolor('forecolor','"+color+"')"); //for Firefox
  } 
  //top.opener.execScript("ustawKolor('forecolor','"+color+"')","JavaScript");
  window.close();
}

function decToHex(dec)
{
 ar=new Array ('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
 if (dec>255 || dec<0) return 'FF';
 dd=Math.floor(dec/16);
 pp=dec%16;
 hex=''+ar[dd]+''+ar[pp]+'';
 return hex;
}

function RGBtoHEX(r,g,b)
{
 if (r.lenght==0)
  r=0;
 if (g.lenght==0)
  g=0;
 if (b.lenght==0)
  b=0;
 r=parseInt(r);
 g=parseInt(g);
 b=parseInt(b);
 if (isNaN(r)) return;
 if (isNaN(g)) return;
 if (isNaN(b)) return;

 r=decToHex(r);
 g=decToHex(g);
 b=decToHex(b);

 color='#'+r+''+g+''+b+'';
 document.kolory.hexcolor.value=color;
}

function HEXtoRGB(hex)
{
 val=hex.value;
 rh=val.substring(1,3);
 gh=val.substring(3,5);
 bh=val.substring(5,7);

 r=parseInt(rh,16);
 g=parseInt(gh,16);
 b=parseInt(bh,16);

 document.kolory.rcolor.value=r;
 document.kolory.gcolor.value=g;
 document.kolory.bcolor.value=b;
}

