<?php 

//지정된 자릿수의 랜덤한 숫자를 반환합니다. 최대 10까지 가능합니다. 4 이면 1000 에서 9999 사이의 랜덤 숫자 
function get_rand_number($len=4) { 


    $len = abs((int)$len); 
    if ($len < 1) $len = 1; 
    else if ($len > 10) $len = 10; 


    return rand(pow(10, $len - 1), (pow(10, $len) - 1)); 
} 


//넘어온 세자리수를 36진수로 변환해서 반환합니다. preg_match_callback 을 통해서만 사용됩니다. 
function get_simple_36($m){ 


    $str = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
    $div = floor($m[0] / 36); 
    $rest = $m[0] % 36; 


    return $str[$div] . $str[$rest]; 
} 


//지정된 자리수에 존재하는 소수 전체를 배열로 반환합니다. max len = 5 
function get_simple_prime_number($len=5){ 

    $len = abs((int)$len); 
    if ($len < 1) $len = 1; 
    else if ($len > 5) $len = 5; 

    $prime_1 = Array(1, 2, 3, 5, 7); 

    if ($len == 1) return $prime_1; 

    $start = pow(10, ($len - 1)) + 1;//101 
    $end = pow(10, $len) - 1;//999 
    $prime = $prime_1; 

    unset($prime[0]);//1제거 
    unset($prime[1]);//2제거 
    $array = Array(); 
    for($i = 11; $i <= $end; $i+=2){//10보다 큰 소수에는 짝수가 없다. 

        $max = floor(sqrt($i)); 
        foreach($prime as $j) { 

            if ($j > $max) break; 
            if ($i % $j == 0) continue 2; 
        } 

        $prime[] = $i; 
        if ($i >= $start) $array[] = $i; 
    } 

    return $array; 
} 


//지정된 자릿수의 숫자로된 시리얼을 반환합니다. - 를 포함하고 싶지 않을때는 $cut 이 $len 보다 크거나 같으면 됩니다. max len = 36 
function get_serial($len=16, $cut=4, $hipen='-'){ 


    $len = abs((int)$len); 
    if ($len < 1) $len = 16; 
    else if ($len > 36) $len = 36; 


    $cut = abs((int)$cut); 
    if ($cut < 1) $cut = 4; 
    else if ($cut > $len) $cut = $len; 


    list($usec, $sec) = explode(' ', microtime()); 
    $base_number = (string)$sec . str_replace('0.', '', (string)$usec); 
    $base_number .= (string)get_rand_number(10) . (string)get_rand_number(8);//36자리 유니크한 숫자 문자열 

  


    $prime = get_simple_prime_number(5);//5자리 소수 배열 
    shuffle($prime); 
    $prime = $prime[0];//랜덤한 5자리 소수 


    $serial = bcmul(substr($base_number, 0, $len), $prime); 
    $serial_length = strlen($serial); 
    $sub = $len - $serial_length; 


    if ($sub > 0) $serial .= (string)get_rand_number($sub); 
    else if ($sub < 0) $serial = substr($serial, 0, $len); 


    return preg_replace("`(.{" . $cut . "})`", "$1" . $hipen, $serial, floor(($len-1) / $cut)); 
} 


//지정된 자릿수의 숫자와 영문으로된 시리얼을 반환합니다. - 를 포함하고 싶지 않을때는 $cut 이 $len 보다 크거나 같으면 됩니다. max len = 24 
function get_serial_mix($len=16, $cut=4, $hipen='-'){ 
	$len = intval($len);
	$cut = intval($cut);

    $len = abs((int)$len); 
    if ($len < 1) $len = 16; 
    else if ($len > 24) $len = 24; 


    $cut = abs((int)$cut); 
    if ($cut < 1) $cut = 4; 
    else if ($cut > $len) $cut = $len; 


    $len2 = (int)($len * 3 / 2); 
    if ($len2 % 2 == 1) $len2 += 1; 


    $serial = get_serial($len2, $len2, $hipen); 


    $serial = substr(preg_replace_callback("`.{3}`", "get_simple_36", $serial), 0, $len); 


    return preg_replace("`(.{" . $cut . "})`", "$1" . $hipen, $serial, floor(($len-1) / $cut)); 
} 


//echo get_serial_mix(16, 4, '-'); 
?>