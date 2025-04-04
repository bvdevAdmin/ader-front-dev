<?php

if(!$db->update($_TABLE['GOODS'],array('STATUS' => 'DELETE'),'IDX=?',array($no))) {
	$code = 500;
}
