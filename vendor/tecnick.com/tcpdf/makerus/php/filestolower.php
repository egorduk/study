<?php
// Скрипт преобразует названия файлов шрифтов в нижний регистр
function gpFilesToLower($istrDirectory) {
	if (!$istrDirectory || !file_exists($istrDirectory) || !is_dir($istrDirectory)) {
		die('Error: directory not found: '.$istrDirectory);
	}

	$handle = opendir($istrDirectory);
	while (($file = readdir($handle)) !== false) {
		if (is_dir($file)) {
			continue;
		}
		$lstrOldName = $istrDirectory."\\".$file;
		$lstrNewName = strtolower($lstrOldName);
		if (file_exists($lstrOldName)) {
			rename($lstrOldName, $lstrNewName);
		}
	}
	closedir($handle);
}

$arg = $GLOBALS['argv'];
if (count($arg) >= 1) {
	ob_start();
	array_shift($arg);
	gpFilesToLower($arg[0]);
	$t = ob_get_clean();
	print preg_replace('!<BR( /)?>!i', "\n", $t);
} else {
	print "Usage: filestolower.php <path>\n";
}
?>