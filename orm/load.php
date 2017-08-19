<?php
spl_autoload_register(function ($class) {
	if (substr($class, 0, 3) !== 'ORM') {
		return;
	} 	

	$class = str_replace('ORM', 'src', $class);
	$class = __DIR__ . '\\' . $class . '.php';

	if (file_exists($class)) {
		include $class;
	}
});