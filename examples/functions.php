<?php

function vd(...$v) {
	if (empty($v)) {
		return;
	}

	echo '<pre style="white-space: pre-wrap; word-break: break-all;">';
	var_dump(...$v);
	echo '</pre>';
}

function pr(...$vs) {
	if (empty($vs)) {
		return;
	}

	foreach ($vs as $v) {
		echo '<pre style="white-space: pre-wrap; word-break: break-all;">';
		print_r($v);
		echo '</pre>';
	}
}
