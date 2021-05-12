<?php
$files = scandir('.');
foreach ($files AS $file) if ($file!='.' && $file!='..' && $file!='icons.php') {
	echo '<img src="./'.$file.'" title="'.$file.'">';
}
