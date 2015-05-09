<?php
	$js=array();
	foreach (scandir($dir.'/images/js') AS $file)
	{
		$ext=strtolower(substr($file,-3));
		if ($ext=='.js') $js[]=$file;
	}

	if (count($js)) sort($js);

	foreach ($js AS $file)
		echo "<script src=\"{template_images}/js/$file\" type=\"text/javascript\"></script>\n\t"; 
