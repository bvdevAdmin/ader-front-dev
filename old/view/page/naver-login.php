<?php
$profile_result	= $naver->getUserProfile();
$profile_naver	= json_decode($profile_result, true);
?>

<script src="/scripts/member/login.js"></script>
<script>
<?php 
if ($profile_naver['resultcode'] == '00') {
	$data_user = $profile_naver['response'];
	
	/*
	param_login(
		'account_key'	=>$data_user['id'],
		'member_id'		=>$data_user['email'],
		'member_name'	=>$data_user['name'],
		'gender'		=>$data_user['gender'],
		'tel_mobile'	=>$data_user['mobile'],
		'member_birth'	=>$data_user['birthyear']."-".$data_user['birthday']
	);
	*/
	
	echo "
		memberLogin_sns('NAVER','".$data_user['id']."','".$data_user['email']."','".$data_user['name']."','".$data_user['gender']."','".$data_user['mobile']."','".$data_user['birthyear']."-".$data_user['birthday']."');
	";
} else {
	echo "
		makeMsgNoti(getLanguage(),'MSG_F_ERR_0074',null);
		
		let btn_close = document.querySelector(`#notimodal-modal .close-btn`);
		if (btn_close != null) {
			btn_close.addEventListener('click',function() {
				location.href = '/login';
			});
		}
	";
}
?> 
</script>
<main>
    <div style="height:100vh"></div>
</main>
