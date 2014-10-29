<?php
// Скрипт комментирует строки с включением шрифтов в документ
function gpNoIncludeFonts($istrDirectory) {
	if (!$istrDirectory || !file_exists($istrDirectory) || !is_dir($istrDirectory)) {
		die('Error: directory not found: '.$istrDirectory);
	}
	$handle = opendir($istrDirectory);
	while (($file = readdir($handle)) !== false) {
		if (is_dir($file)) {
			continue;
		}
		if (pathinfo($file, PATHINFO_EXTENSION) != 'php') {
			continue;
		}
		$lstrFileName = $istrDirectory.'/'.$file;
		if (file_exists($lstrFileName)) {
			// Читаем содержимое файла
			$lstrFileData = file_get_contents($lstrFileName);
			// Осуществляем замену
			$lstrFileData = str_replace('$file', '//$file', $lstrFileData);
			// Перезаписываем файл
			file_put_contents($lstrFileName, $lstrFileData);
		}
	}
	closedir($handle);
}

$arg = $GLOBALS['argv'];
if (count($arg) >= 1) {
	ob_start();
	array_shift($arg);
	gpNoIncludeFonts($arg[0]);
	$t = ob_get_clean();
	print preg_replace('!<BR( /)?>!i', "\n", $t);
} else {
	print "Usage: no_include_font.php <path>\n";
}
?>