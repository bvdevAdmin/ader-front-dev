<?php

if(!$db->update($_TABLE['GOODS'],array('IS_SOLDOUT' => $soldout),'IDX=?',array($no))) {
	$code = 500;
}
