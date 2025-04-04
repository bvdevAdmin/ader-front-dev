<?php
@error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
@ini_set("display_errors", 1);

	$ftp_host 				= '203.245.9.174';
	$user 					= 'aderwms';
	$password 				= 'bv1229';
	$dir 					= '';
	$conn 					= ftp_connect($ftp_host);

	if(!$conn){
		echo "FTP SERVER CONNECT ERROR";
		exit;
	}
	
	$result = ftp_login($conn, $user, $password);
	if(!$result){
		echo "FTP SERVER LOGIN ERROR";
	}

?>