
function kameleon_popup_img(url, size_w, size_h, txt) 
{
	new_add_w	=	10;
	new_add_h	=	29;
	new_add_h	=	57;
	
	start_size_w = 150;
	start_size_h = 100;
	
	if (size_w!=null && size_h!=null) {
		start_size_w = size_w;
		start_size_h = size_h;
	}

	if (txt==null) txt='';

	
	start_popup_left = Math.round((screen.availWidth-start_size_w)/2);
	start_popup_top = Math.round((screen.availHeight-start_size_h)/2);
	
	msg=open("","POPUP","toolbar=no,scrollbars=no,directories=no,menubar=no,status=no,width="+start_size_w+",height="+start_size_h+", top="+start_popup_top+", left="+start_popup_left+"");
	msg.document.close();
	msg.document.write("<HTML><HEAD><TITLE>"+JSTITLE+"</TITLE>\n");
	
	if (size_w==null && size_h==null) {
		msg.document.write("<script language='JavaScript'>\n");
		msg.document.write("function popup_resize(wnd, img) {\n");
		msg.document.write("new_w = Number(img.width)+"+new_add_w+";\n");
		msg.document.write("new_h = Number(img.height)+"+new_add_h+";\n");
		msg.document.write("popup_l = Math.round((screen.availWidth-new_w)/2);\n");
		msg.document.write("popup_t = Math.round((screen.availHeight-new_h)/2);\n");
		msg.document.write("wnd.moveTo(popup_l,popup_t);\n");
		msg.document.write("wnd.resizeTo(new_w,new_h);\n");
		msg.document.write("document.getElementById('popup_div').style.display='';\n");
		msg.document.write("return;\n");
		msg.document.write("}\n");
		msg.document.write("</script>\n");
	}
	msg.document.write("</HEAD>\n");
	msg.document.write("<BODY BGCOLOR=White style='margin: 0; font-size: 11px; font-family: verdana; vertical-align: center; text-align: center;'>\n");
	msg.document.write("<br><br><b>"+JSWAIT+"</b>\n");
	
	msg.document.write("<IMG SRC='"+url+"' ALT='"+JSCLOSE+"' BORDER='0' onclick='window.close()' ");
	if (size_w==null && size_h==null) msg.document.write(" onload='popup_resize(window,this)' ");
	msg.document.write(" style='cursor: hand; position: absolute; top: 0; left: 0;'>\n");
	
	msg.document.write('<DIV style="position: absolute; top: 0; left: 0; display:none" id="popup_div">'+txt+'</div>');

	msg.document.write("</BODY></HTML>\n");
	msg.focus();

};

