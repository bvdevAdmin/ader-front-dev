<?php
/**
 *
 * Session Class
 * =============================
 * Author : 양한빈
 * Date : 2021-01-29 12:00
 * Version : 1.0
 * Describe : 세션을 DB에서 관리
 * History : 2020-01-29 최초작성
 *
 */

class session {
	protected $db;
	protected $dbms;

	public function __construct($db = DBMS,$name = DB['NAME'],$server = DB['SERVER'],$id = DB['USER'],$pw = DB['PASSWORD']) {
		$this->dbms = $db;
		$this->db = new db($db,$name,$server,$id,$pw);
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
		register_shutdown_function('session_write_close');
		session_start();
	}

	public function __destruct() {
		//@session_write_close();
		return true;
	}

	public function open($path,$name) {
		return true;
	}

	public function close() {
		//@session_write_close();
		return true;
	}

	public function read($session_id) {
		global $_TABLE;
		if($this->db->count($_TABLE['SESSION'],'SESSION_ID = ?',array($session_id)) == 0) {
			$this->db->insert($_TABLE['SESSION'],array('SESSION_ID'=>$session_id,'DATA'=>'','IP_V4'=>$_SERVER['REMOTE_ADDR']));
			return '';
		}
		else {
			return $this->db->get($_TABLE['SESSION'],'SESSION_ID = ?',array($session_id),'DATA')[0]['DATA'];
		}
	}

	public function write($session_id, $data) {
		global $_TABLE;

		$access_date = date('Y-m-d H:i:s',strtotime('-30 minute'));
		@$this->db->delete($_TABLE['SESSION'],'ACCESS_DATE < ?',array($access_date));

		$query = array(
			'ACCESS_DATE'=>date('Y-m-d H:i:s'),
			'DATA'=>$data
		);

		return $this->db->update($_TABLE['SESSION'],$query,'SESSION_ID="'.$session_id.'"');
	}

	public function destroy($session_id) {
		global $_TABLE;
		return $this->db->delete($_TABLE['SESSION'],'SESSION_ID = ?',array($session_id));
	}

	public function gc($maxlifetime) {
		global $_TABLE;
		$access_date = date('Y-m-d H:i:s',time()-$maxlifetime);
		return $this->db->delete($_TABLE['SESSION'],'ACCESS_DATE < ?',array($access_date));
	}
}
?>