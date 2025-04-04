<?php
/**
 *
 * FCM Push Server Class
 * =============================
 * Author : 양한빈
 * Date : 2021-01-13 17:00
 * Version : 1.0
 * Describe : 푸시 서버 이용을 위한 클래스.
 * History : 2020-01-13 최초작성
 *
 */

class push {

	private $db;

	public function __construct() {
		$this->db = new db();
	}

	public function send($message = null,$id = null) {
		if($message == null) {
		}

		$url = 'https://fcm.googleapis.com/fcm/send';
		$headers = array (
			'Authorization: key=' . GOOGLE_SERVER_KEY,
			'Content-Type: application/json'
		);
		$fields = array ( 
			'data' => array ('message' => $message),
			'notification' => array ('body' => $message),
			'priority' => 'high'
		);
		if(is_array($id)) {
			$fields['registration_ids'] = $id;
		} else {
			$fields['to'] = $id;
		}

		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode($fields) );
		$result = curl_exec ($ch);
		curl_close($ch);

		return $result;
	}

	public function regist($args) {
		$db->insert($_TABLE['PUSH'],$args);
	}

	public function refresh() {
	}
}
?>