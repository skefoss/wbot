<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8"/>
	<title>Document</title>
	<style>
	* {
		margin: 0;
		padding: 0;
		border-box: inherit;
		color: inherit;
		background-color: inherit;
	}

	::-webkit-scrollbar,::-webkit-scrollbar-track,::-webkit-scrollbar-thumb {
		display: none;
	}

	::-moz-selection,::selection {
		background: transparent;
	}

	:root {
		box-sizing: border-box;
		color: transparent;
		background-color: transparent;
	}

	body {
		font-size: 1rem;
		font-family: monospace;
	}

	h1 {
		font-size: 2rem;
		font-weight: bold;
		text-align: center;
		color: #07f;
	}

	ul {
		list-style: none;
		display: flex;
		flex-wrap: wrap;
		justify-content: center;
		align-items: center;
		margin: 0;
		padding: 0;
	}

	li {
		width: 100%;
		color: #07f;
		font-size: 1.5rem;
	}

	</style>
</head>
<body>
<?php

$result_folders = ['out', 'tmp'];

foreach ($result_folders as $result_folder) {
	$dir = realpath(__DIR__ . "/$result_folder");
	echo "<h1>$result_folder</h1>";
	echo '<ul>';
	$files = scandir($dir);
	$classes = [];
	foreach ($files as $name) {
		if (!str_starts_with($name, '.')) {
			$file = $dir . DIRECTORY_SEPARATOR . $name;
			echo '<li class="' . implode(' ', [...$classes, is_dir($file) ? 'dir' : 'file']) . '"><a href="' . "$result_folder/$name" . '">' . $name . '</a></li>';
		}
	}
	echo '</ul>';
}
?>
</body>
</html>
