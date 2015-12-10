<?php

	$new='ckeditor45';

?><html>
<head>
    <meta charset="utf-8">
    <title>CKEDITOR - new</title>
	<style>
		body {
			padding: 0;
			margin: 0;
		}
		#km_wysiwyg_toolbar {
			border: 1px red solid;
		}
	</style>
</head>
<body>
	

    <div id="km_wysiwyg_toolbar"></div>
    <div id="km_wysiwyg_editor" class="km_tip" tip="3">
        <textarea id="wysiwyg">Trele morele
			<img src="img/kameleon96.jpg"/>
		</textarea>
    </div>

<script type="text/javascript" src="<?php echo $new;?>/ckeditor.js"></script>
<script type="text/javascript">
	
	function tr(s) {
		return s;
	}
    CKEDITOR.replace("wysiwyg", {
        baseHref : '/kameleon/public/',
        width : "50%",
        language : "en",
        resize_dir : "horizontal",
		extraPlugins : "mask,sharedspace,tabletools",
		sharedSpaces : {
            top : document.getElementById('km_wysiwyg_toolbar')
        },
        autoParagraph : false,
        basicEntities : true,
        scayt_autoStartup : false,
        ignoreEmptyParagraph : false,
        templates_replaceContent : false,
        stylesSet : 'webkameleon_styles',
        enterMode : CKEDITOR.ENTER_P,

        xtoolbar : [ [
            "Undo", "Redo" ,
            "-",
            "Format", "Styles", "FontSize",
            "-",
            "Bold", "Italic", "Underline", "Strike",
            "-",
            "TextColor", "BGColor",
            "-",
            "Link", "Unlink","Image",
            "-",
            "NumberedList", "BulletedList",
            "-",
            "Outdent", "Indent",
            "-",
            "Blockquote",
            "-",
            "JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock",
            "-",
            "RemoveFormat", "Source"
        ] ]		
	});
</script>

<ul>
	<li>
		Przegrać plugins/mask
	</li>
	<li>
		Przegrać plugins/link
	</li>	
	<li>
		Pobrać i doinstalować sharedspace do plugins (extraPlugins)
	</li>
	<li>
		Pobrać i doinstalować tabletools do plugins (extraPlugins)
	</li>	
	
</ul>
	
</body>
</html>