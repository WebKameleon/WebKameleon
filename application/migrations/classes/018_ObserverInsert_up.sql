INSERT INTO observer (pri,event,active,result,days,mail_from,mail_to,mail_subject,mail_html,lang,mail_msg)
VALUES (10,'share_server',1,0,0,'{me}','{him}','Udostępniam Ci stronę {server.nazwa_long}',1,'pl',
'<div itemscope="" itemtype="http://schema.org/EmailMessage" style="border: 1px solid #f0f0f0; max-width: 650px; font-family: Arial, sans-serif; color: #000;">
<div style="padding: 14px 10px 4px 10px; line-height: 21px; margin-bottom: 13px;"><span style="font-size: 20px; color: #333;">Udostępniam Ci stronę WWW.</span></div>

<div style="font-size: 13px; background-color: #FFF; padding: 0px 7px 7px 10px;">
<table cellpadding="0" cellspacing="0">
	<tbody role="list">
		<tr itemprop="about" itemscope="" itemtype="http://schema.org/CreativeWork" role="listitem">
			<td style="vertical-align: top; padding-bottom: 7px; font-size:16px; text-align: center;"><img alt="Strona www" src="http://{_server.HTTP_HOST}/favicon.ico" style="vertical-align: middle" /></td>
			<td style="vertical-align: top; padding-bottom: 7px; font-size:16px; padding-left: 5px;">&nbsp;</td>
			<td><span itemprop="action" itemscope="" itemtype="http://schema.org/ViewAction"><a href="http://{_server.HTTP_HOST}/index.get?setServer={server.nazwa}" itemprop="url" style="vertical-align:middle; text-decoration:none; color:#1154cc;">{server.nazwa_long} </a> </span></td>
		</tr>
	</tbody>
</table>
</div>

<div style="background-color:#f5f5f5; padding: 2px 12px;">
<table cellpadding="0" cellspacing="0" style="width: 100%;">
	<tbody>
		<tr>
			<td style="padding: 0; color: #808080; font-size:11px;" valign="middle">Web Kameleon pozwala na łatwe tworzenie profesjonalnych stron WWW</td>
			<td style="text-align: right" valign="middle"><a href="http://{_server.HTTP_HOST}"><img alt="Logo usługi Web Kameleon" src="http://{_server.HTTP_HOST}/skins/kameleon/img/wk_logo.png" style="border: 0;vertical-align: middle;padding-top: 12px;padding-bottom: 4px;margin-left: 34px;" /> </a></td>
		</tr>
	</tbody>
</table>
</div>
</div>
<meta content="{server.nazwa_long}" itemprop="name" />');

INSERT INTO observer (pri,event,active,result,days,mail_from,mail_to,mail_subject,mail_html,lang,mail_msg)
VALUES (10,'share_server',1,0,0,'{me}','{him}','Website {server.nazwa_long}',1,'en',
'<div itemscope="" itemtype="http://schema.org/EmailMessage" style="border: 1px solid #f0f0f0; max-width: 650px; font-family: Arial, sans-serif; color: #000;">
<div style="padding: 14px 10px 4px 10px; line-height: 21px; margin-bottom: 13px;"><span style="font-size: 20px; color: #333;">I''ve shared the website with you.</span></div>

<div style="font-size: 13px; background-color: #FFF; padding: 0px 7px 7px 10px;">
<table cellpadding="0" cellspacing="0">
	<tbody role="list">
		<tr itemprop="about" itemscope="" itemtype="http://schema.org/CreativeWork" role="listitem">
			<td style="vertical-align: top; padding-bottom: 7px; font-size:16px; text-align: center;"><img alt="Website" src="http://{_server.HTTP_HOST}/favicon.ico" style="vertical-align: middle" /></td>
			<td style="vertical-align: top; padding-bottom: 7px; font-size:16px; padding-left: 5px;">&nbsp;</td>
			<td><span itemprop="action" itemscope="" itemtype="http://schema.org/ViewAction"><a href="http://{_server.HTTP_HOST}/index.get?setServer={server.nazwa}" itemprop="url" style="vertical-align:middle; text-decoration:none; color:#1154cc;">{server.nazwa_long} </a> </span></td>
		</tr>
	</tbody>
</table>
</div>

<div style="background-color:#f5f5f5; padding: 2px 12px;">
<table cellpadding="0" cellspacing="0" style="width: 100%;">
	<tbody>
		<tr>
			<td style="padding: 0; color: #808080; font-size:11px;" valign="middle">Web Kameleon: easyly create and share professional websites</td>
			<td style="text-align: right" valign="middle"><a href="http://{_server.HTTP_HOST}"><img alt="Logo of service Web Kameleon" src="http://{_server.HTTP_HOST}/skins/kameleon/img/wk_logo.png" style="border: 0;vertical-align: middle;padding-top: 12px;padding-bottom: 4px;margin-left: 34px;" /> </a></td>
		</tr>
	</tbody>
</table>
</div>
</div>
<meta content="{server.nazwa_long}" itemprop="name" />');


