<?php

$curl = curl_init();
	
curl_setopt_array($curl, [
	CURLOPT_URL				=>"https://scert-dir.mobile-ok.com/agent/v1/token/get",
	CURLOPT_RETURNTRANSFER	=>true,
	CURLOPT_ENCODING		=>"",
	CURLOPT_MAXREDIRS		=>10,
	CURLOPT_TIMEOUT			=>30,
	CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST	=>"POST",
	CURLOPT_POSTFIELDS		=>"
		{
			'serviceId':'8069e0ab-a76e-498b-be37-74d60ba5f62c',
			'siteUrl':'www.mobile-ok.com',
			'encryptReqClientInfo':'kMH49RaobxoHxtrACdctPOJf6F3kiGErTdIyWwJ8hT9KrGo2Lrb23+FHQ...5ZOb7aAJdS7bu2DhWhjF7tDmxQ=='
		}
	",
	CURLOPT_HTTPHEADER => [
		"Content-Type: application/json;Charset:utf-8"
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

print_r($response);
print_r($err);

if (!$err) {
	$result = json_decode($response,true);
	if (isset($result[0]['content'])) {
		$kakao_message = $result[0]['content'];
	}
}

?>