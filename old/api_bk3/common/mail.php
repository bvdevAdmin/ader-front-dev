
<?php
/*
 +=============================================================================
 | 
 | 회원 공통함수 (메일)
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.06.19
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/

include_once("/var/www/www/class/PHPMailer/PHPMailer/class.PHPMailer.php");
include_once("/var/www/www/class/PHPMailer/PHPMailer/class.SMTP.php");
include_once("/var/www/www/class/PHPMailer/PHPMailer/class.Exception.php");

function checkMailStatus($db,$country,$mail_code,$member_idx,$member_id,$mapping_arr){
    $select_mail_setting_sql = "
		SELECT
			MS.MEMBER_FLG		AS MEMBER_FLG,
			MS.ADMIN_FLG		AS ADMIN_FLG
		FROM
			MAIL_SETTING MS
		WHERE
			MS.MAIL_CODE = '".$mail_code."'
	";
	
	$db->query($select_mail_setting_sql);
	
	foreach($db->fetch() as $setting_data){
		$member_flg = $setting_data['MEMBER_FLG'];
		$admin_flg = $setting_data['ADMIN_FLG'];
		
		if ($member_flg == true) {
			$select_mail_template_sql = "
				SELECT
					MT.MAIL_TITLE		AS MAIL_TITLE,
					MT.MAIL_BODY		AS MAIL_BODY
				FROM
					MAIL_TEMPLATE MT
				WHERE
					MT.COUNTRY = '".$country."' AND
					MT.TEMPLATE_TYPE = 'M' AND
					MT.MAIL_CODE = '".$mail_code."'
			";
			
			$db->query($select_mail_template_sql);
			
			foreach($db->fetch() as $template_data){
				$mail_title = convertMapping($template_data['MAIL_TITLE'],$mapping_arr[$member_idx]);
				$mail_body = convertMapping($template_data['MAIL_BODY'],$mapping_arr[$member_idx]);
				
				commonSendMail($member_id, $mail_title, $mail_body);
			}
		}
		
		if ($admin_flg == true) {
			$select_mail_template_sql = "
				SELECT
					MT.MAIL_TITLE		AS MAIL_TITLE,
					MT.MAIL_BODY		AS MAIL_BODY
				FROM
					MAIL_TEMPLATE MT
				WHERE
					MT.COUNTRY = '".$country."' AND
					MT.TEMPLATE_TYPE = 'A' AND
					MT.MAIL_CODE = '".$mail_code."'
			";
			
			$db->query($select_mail_template_sql);
			
			foreach($db->fetch() as $template_data){
				$mail_title = convertMapping($template_data['MAIL_TITLE'],$mapping_arr[$member_idx]);
				$mail_body = convertMapping($template_data['MAIL_BODY'],$mapping_arr[$member_idx]);
				
				commonSendMail($member_id, $mail_title, $mail_body);
			}
		}
	}
}

// ex) mapping_arr -> mapping_arr['member_id'][keyword] = value
function convertMapping($tag, $mapping){
    $keys_arr = array_keys($mapping);
	foreach($keys_arr as $key){
		$tag = str_replace('{'.$key.'}',$mapping[$key],$tag);
	}
	
	return $tag;
}

function commonSendMail($member_id, $mail_title, $mail_body){
    $smtp       = "smtp.gmail.com";
    $from_id 	= "admin@bvdev.co.kr";
    $pass 		= "bvdevadmin!";
    $mail       = new \PHPMailer\PHPMailer\PHPMailer(true);

    $mail->IsSMTP();
    $mail->Host        = $smtp;
    $mail->SMTPAuth    = true;
    $mail->Username    = $from_id;
    $mail->Password    = $pass;
    $mail->SMTPSecure  = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port        = 587;
    $mail->setFrom($from_id, 'ADER');
    $mail->addAddress($member_id, '');
    $mail->isHTML(true);
    $mail->Subject     = htmlspecialchars_decode($mail_title);
    $mail->Body        = htmlspecialchars_decode($mail_body);
    $mail->send();
}

?>