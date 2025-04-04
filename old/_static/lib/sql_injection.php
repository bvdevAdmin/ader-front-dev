<?php
function sql_injection_addslashes($arr) {
	while( list($k, $v) = each($arr) ) {
		if( is_array($arr[$k]) ) {
			while( list($k2, $v2) = each($arr[$k]) ) {
				if(is_array($arr[$k][$k2])) {
					while( list($k3, $v3) = each($arr[$k][$k2]) ) {
						$arr[$k][$k2][$k3] = addslashes($v3);
					}
					@reset($arr[$k][$k2]);
				}
				else {
					$arr[$k][$k2] = addslashes($v2);
				}
			}
			@reset($arr[$k]);
		}
		else {
			$arr[$k] = addslashes($v);
		}
	}

	return $arr;
}
?>