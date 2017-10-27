<?php

function vd($v) {
	echo '<pre style="white-space: pre-wrap; word-break: break-all;">';
	var_dump(...$v);
	echo '</pre>';
}

function pr(...$vs) {
	echo '<pre style="white-space: pre-wrap; word-break: break-all;">';
	foreach ($vs as $v) {
		print_r($v);
		echo "\n";
	}
	echo '</pre>';
}
