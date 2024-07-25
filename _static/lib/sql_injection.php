<?php
function sql_injection_addslashes($arr) {
	foreach($arr as $k => $v) {
		if( is_array($arr[$k]) ) {
			$arr[$k] = sql_injection_addslashes($arr[$k]);
		}
		else {
			$arr[$k] = addslashes($v);
		}
	}

	return $arr;
}
