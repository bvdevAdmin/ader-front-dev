<?php
session_destroy();

$json_result['code'] = 200;
echo json_encode($json_result);
exit;
?>