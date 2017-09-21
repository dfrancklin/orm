<?php

spl_autoload_register(function ($class) {
	$root = 'ORM';

	if (substr($class, 0, strlen($root)) !== $root) {
		return;
	}

	$class = substr_replace($class, 'src', 0, strlen($root));
	$class = __DIR__ . DIRECTORY_SEPARATOR . $class . '.php';

	if (file_exists($class)) {
		include $class;
	}
});
