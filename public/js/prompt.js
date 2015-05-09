


function getTopFunction() 
{

	var currentFunction = arguments.callee.caller;
	while (currentFunction) 
	{
		lastCurrentFunction=currentFunction
		currentFunction = currentFunction.caller;
	}
		
	 return lastCurrentFunction;
	 
}
	






// moje


var kameleonPromptFun = null;
var kameleonPromptReturnValue=null;

function kameleonPromptClose(cancel)
{
	kameleonPromptDiv=document.getElementById('kameleonPromptDivId');
	kameleonPromptDiv.style.display='none';

	if (!cancel)
	{
		if (typeof(kameleonPromptFun) == 'function')
		{
			kameleonPromptReturnValue=document.getElementById('kameleonPromptValue').value;
			kameleonPromptFun();
		}
	}
	else
	{
		kameleonPromptFun=null;
	}
}



window.prompt=function(opis,v) 
{
	if (typeof(kameleonPromptFun) == 'function')
	{
		kameleonPromptFun=null;
		return kameleonPromptReturnValue;
	}



	newDiv=false;

	kameleonPromptDiv=document.getElementById('kameleonPromptDivId');
	if (kameleonPromptDiv==null) 
	{
		kameleonPromptDiv = document.createElement("div");
		newDiv=true;
	}
  kameleonPromptDiv.innerHTML = "<div class=\"km_schowek_header\">"+opis+"<a class=\"km_close\" href=\"javascript:kameleonPromptClose(true)\">X</a></div><div class=\"km_schowek_items\"><ul><li><input type=\"text\" id=\"kameleonPromptValue\" value=\""+v+"\"/></li></ul></div><div class=\"km_schowek_buttons\"><input type=\"button\" value=\"OK\" onclick=\"kameleonPromptClose(false)\" /></div>";
	kameleonPromptDiv.className='km_prompt';
	kameleonPromptDiv.id='kameleonPromptDivId';
	kameleonPromptDiv.style.display='block';
	

	
	if (newDiv) document.body.appendChild(kameleonPromptDiv);
	
	document.getElementById('kameleonPromptValue').focus();

	kameleonPromptFun=getTopFunction();

	return null;
}

