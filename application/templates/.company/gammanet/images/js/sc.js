function sc() 
{
	size_w = 378;
	size_h = 250;
	new_size_w = size_w;
	new_size_h = size_h+25;
	
	start_popup_left = Math.round((screen.availWidth-size_w)/2);
	start_popup_top = Math.round((screen.availHeight-size_h)/2);
	
	msg=open("","SiteCredits","toolbar=no,directories=no,menubar=no,status=no,width="+new_size_w+",height="+new_size_h+", top="+start_popup_top+", left="+start_popup_left+"");
	msg.document.close();
	
	div_hm = size_h-21;
	div_hie = msg.document.body.clientHeight;
	msg.document.write('<html><head><title>site credits</title></head>');
	msg.document.write('<style>');
	msg.document.write('* {color: #6CB13C; font-size: 11px; font-family: verdana; font-weight: normal;}');
	msg.document.write('a {color: #fff; text-decoration: none;}');
	msg.document.write('p {}');
	msg.document.write('p b {width: 105px; text-align: right; float: left;}');
	msg.document.write('p a {border-bottom: 1px solid #385040; display: block; padding-bottom: 5px; margin-left: 90px;}');
	msg.document.write('body {margin: 0; background-position: top; background-color: #20943F; background-repeat: repeat-x;}');
	msg.document.write('#scdiv {padding: 45px 20px 0 90px; height:'+div_hm+'px; _height:'+div_hie+'px; background-position: left bottom; background-repeat: no-repeat;}');
	msg.document.write('#sclogo {border-bottom: 1px solid #2C3530; padding: 0 0 10px 10px;}');
	msg.document.write('</style>');
	
	if (JSIMAGES!=null)
	{
		msg.document.write('<body background="'+JSIMAGES+'/sc/sc_back.gif">');
		msg.document.write('<div id="scdiv" style="background-image: url('+JSIMAGES+'/sc/sc_backup.gif);">');
		msg.document.write('<div id="sclogo"><img src="'+JSIMAGES+'/sc/sc_title.gif"></div>');
	}
	else
	{
		msg.document.write('<body>');
		msg.document.write('<div id="scdiv">');
		msg.document.write('<div id="sclogo"><h1>site credits</h1></div>');
	}
	
	for (i = 0;i < SITECREDITS.length; ++i)
	{
		msg.document.write('<p><b>'+SITECREDITS[i][0]+':</b> <a href="'+SITECREDITS[i][2]+'" title="'+SITECREDITS[i][0]+'s" target="_blank">'+SITECREDITS[i][1]+'</a></p>');
	}	
	
	msg.document.write('</div>');
	msg.document.write('</body>');
	msg.document.write('</html>');

	msg.focus();
}
