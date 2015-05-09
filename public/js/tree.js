pole = "";
tree_left = 0;
tree_top = 0;

function treeSaveId(id)
{
	if (window.name !=  "WebTreeexplorer") 
	{
		// okno window.open
		if ( typeof(top.opener)  ==  'object' )
		{
			saveId(pole,id);
		}
		else
		{
			// Jesli przez modalDialog
			// wyjscie z iframa jest przez parent
			// wczesniej var okno  =  parent;
			okno.getParam(id);
			window.close();
		}
	}
	else
	{
		top.opener.execScript("saveId('"+pole+"','"+id+"')","JavaScript");
		window.close();
	}
}


function openTree(input_name,id,variables)
{
	pole = input_name;
	width = 400;
	height = 500;
	if (tree_left == 0)
		t_left = window.screen.width/2-width/2;
	else
		t_left = tree_left;
	if (tree_top == 0)
		t_top = window.screen.height/2-height/2;
	else
		t_top = tree_top;

	if (variables.length) variables  =  "&" + variables;

	a = open('tree.php?node='+id+variables+'&pole='+pole,'WebTreeexplorer',
		"toolbar = 0,location = 0,directories = 0,\
		status = 1,menubar = 0,scrollbars = 1,resizable = 1,\
		width = "+width+",height = "+height+",left = "+t_left+",top = "+t_top+"");

}


function markTree(td)
{
	document.all[td].style.backgroundColor = '#FF0000';
	document.all[td].borderColor = '#000000';
	document.all[td].scrollIntoView(true);
}

function show_hide(id,td,img,td2)
{
	if (document.all[id].style.visibility  ==  'visible')
	{
		document.all[id].style.visibility  = 'hidden';
		document.all[id].style.display = 'none';
		
		document.all[td].rowSpan = 1;
		document.all[td2].background = '';

		re  =  new RegExp("tree_minus_e.gif");
		if (re.test(document.all[img].src))
			document.all[img].src = TREE_IMG+'/tree_plus_e.gif';
		else
			document.all[img].src = TREE_IMG+'/tree_plus.gif';

	}
	else
	{
		document.all[id].style.visibility  = 'visible';
		document.all[id].style.display = 'inline';

		document.all[td].rowSpan = 2;
		document.all[td2].background = TREE_IMG+'/tree_linia.gif';
		re  =  new RegExp("tree_plus_e");
		if (re.test(document.all[img].src))
		{
			document.all[img].src = TREE_IMG+'/tree_minus_e.gif';
		}
		else
			document.all[img].src = TREE_IMG+'/tree_minus.gif';

	}
}
