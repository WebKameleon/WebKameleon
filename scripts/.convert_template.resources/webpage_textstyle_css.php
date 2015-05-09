<?php
	foreach (scandir($dir.'/images') AS $file)
	{
		$ext=strtolower(substr($file,-4));
		if ($ext=='.css' && (strstr($file,'textstyle') || strstr($file,'szablon')) ) 
			echo "<link href=\"{template_images}/${file}\" rel=\"stylesheet\" type=\"text/css\" />\n\t";
	}
