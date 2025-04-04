<?php
/**
 *
 * File Class
 * =============================
 * Author : 양한빈
 * Date : 2021-01-13 17:00
 * Version : 1.0
 * Describe : 파일 업로드, 삭제, 변경 일자, 이름 변경
 * History : 2020-01-13 최초작성
 *
 */

class file {
	public function __construct() {
		$this->conn = $this->connect();
	}

	public function upload($source,$path,$args = array(
						'extension'=>null,
						'original_name'=>true,
						'thumbnail'=>false,
						'thumbnail_width'=>100,
						'thumbnail_height'=>100 )) {
		global $_CONFIG;

		if(is_string($source)) $source = $_FILES[$source];

		// 파일 저장 디렉토리 절대 경로 구하기
		$thisfilename	= basename(__FILE__); 
		$temp_filename	= realpath(__FILE__); 
		if(!$temp_filename) $temp_filename=__FILE__; 
		unset($temp_filename); 

		$filename = $source['name'];		// 파일 이름 알아내기
		$file_tmp = $source['tmp_name'];	// 파일 임시 저장 장소 알아내기
		$file_info = pathinfo($filename);			// 파일 확장자 알아내기

		// 실행 파일 업로드 불가
		if(strpos(strtolower($file_info['extension']),'.php,.asp,.jsp,.aspx')) {
			throw new Exception('Can not upload file : Permition Denied');
			return false;
		}
		// 정해진 확장자가 아닐 경우 에러메시지 표시후 실행 중단
		if(is_array($args['extension']) || is_string($args['extension'])) {
			if(is_string($args['extension'])) $args['extension'] = [$args['extension']];
			if(in_array(strtolower($file_info['extension']),$args['extension'])) {
				throw new Exception('Can not upload file : Accept '.implode(',',$args['extension']).' files only.');
				return false;
			}
		}

		// 디렉토리 만들기
		$temp_path = explode($_CONFIG['SEPARATOR'],str_replace($_CONFIG['PATH_UPLOAD'],'',$path.$_CONFIG['SEPARATOR'].'thumbnail'));
		$temp_path_root = $_CONFIG['PATH_UPLOAD'];
		for($i=0;$i<sizeof($temp_path);$i++) {
			$temp_path_root .= $temp_path[$i].$_CONFIG['SEPARATOR'];
			if(!is_dir($temp_path_root)) @mkdir($temp_path_root,0777);
		}

		$filename = str_replace(' ','_',strip_tags($filename));

		// 파일 이름을 타임 스탬프로 바꾸기
		if($arge['original_name'] === true) {
			$filename = time().'&&'.$filename;
		} else {
			$filename = time().'.'.strtolower($file_info['extension']);
		}

		// 파일을 정해진 저장 디렉토리에 저장
		//$filename_real = mb_convert_encoding($filename, 'utf8', 'euc-kr');
		$filename_real = $filename;
		$res = move_uploaded_file($file_tmp,$path.$filename_real);

		// 권한 설정
		@chmod($path.$filename_real,0777);


		if($res) {
			// 썸네일 만들기
			if($args['thumbnail'] === true) {
				make_thumbnail($path.$filename_real, $width, $height, $path.'thumbnail/'.$filename_real);
			}
			return $filename;
		}
		else return false;
	}

    /**
    * Constructor of class
    * @param string $filename The name of the file
    * @param boolean $binarty Optional. If file is a binary file then set TRUE, otherwise FALSE
    * @desc Constructor of class
    */
    function file($filename,$binary=false){
        $this->name=$filename;
        $this->binary=$binary;
       
        if($binary){
            $this->file=@fopen($filename,"a+b");
            if(!$this->file){
                $this->file=@fopen($filename,"rb");
            }
        }else{
            $this->file=@fopen($filename,"a+");
            if(!$this->file){
                $this->file=@fopen($filename,"r");
            }
        }
    }
   
    /**
    * Returns the filesize in bytes
    * @return int $filesize The filesize in bytes
    * @desc Returns the filesize in bytes
    */
    function get_size(){
        return filesize($this->name);
    }
   
    /**
    * Returns the timestamp of the last change
    * @return timestamp $timestamp The time of the last change as timestamp
    * @desc Returns the timestamp of the last change
    */
    function get_time(){
        return fileatime($this->name);
    }
   
    /**
    * Returns the filename
    * @return string $filename The filename
    * @desc Returns the filename
    */
    function get_name(){
        return $this->name;
    }
   
    /**
    * Returns user id of the file
    * @return string $user_id The user id of the file
    * @desc Returns user id of the file
    */
    function get_owner_id(){
        return fileowner($this->name);
    }
   
    /**
    * Returns group id of the file
    * @return string $group_id The group id of the file
    * @desc Returns group id of the file
    */
    function get_group_id(){
        return filegroup($this->name);
    }
   
    /**
    * Returns the suffix of the file
    * @return string $suffix The suffix of the file. If no suffix exists FALSE will be returned
    * @desc Returns the suffix of the file
    */
    function get_suffix(){
        $file_array=split("\.",$this->name); // Splitting prefix and suffix of real filename
        $suffix=$file_array[count($file_array)-1]; // Returning file type
        if(strlen($suffix)>0){
            return $suffix;
        }else{
            return false;
        }
    }
   
    /**
    * Sets the actual pointer position
    * @return int $offset Returns the actual pointer position
    * @desc Returns the actual pointer position
    */
    function pointer_set($offset){
        $this->action_before_reading=true;
        return fseek($this->file,$offset);
    }
   
    /**
    * Returns the actual pointer position
    * @param int $offset Returns the actual pointer position
    * @desc Returns the actual pointer position
    */
    function pointer_get(){
        return ftell($this->file);
    }
   
    /**
    * Reads a line from the file
    * @return string $line A line from the file. If is EOF, false will be returned
    * @desc Reads a line from the file
    */
    function read_line(){
        if($this->action_before_reading){
            if(rewind($this->file)){
                $this->action_before_reading=false;
                return fgets($this->file);
            }else{
                $this->halt("Pointer couldn't be reset");
                return false;
            }
        }else{
            return fgets($this->file);
        }
    }
   
    /**
    * Reads data from a binary file
    * @return string $line Data from a binary file
    * @desc Reads data from a binary file
    */
    function read_bytes($bytes,$start_byte=0){
        if(is_int($start_byte)){
            if(rewind($this->file)){
                if($start_byte>0){
                    $this->pointer_set($start_byte);
                    return fread($this->file,$bytes);
                }else{
                    return fread($this->file,$bytes);
                }
            }else{
                $this->halt("Pointer couldn't be reset");
                return false;
            }
        }else{
            $this->halt("Start byte have to be an integer");
            return false;
        }
    }
   
    /**
    * Writes data to the file
    * @param string $data The data which have to be written
    * @return boolean $written Returns TRUE if data could be written, FALSE if not
    * @desc Writes data to the file
    */
    function write($data){
        $this->action_before_reading=true;
        if(strlen($data)>0){
            if($this->binary){
                $bytes=fwrite($this->file,$data);
                if(is_int($bytes)){
                    return $bytes;
                }else{
                    $this->halt("Couldn't write data to file, please check permissions");
                    return false;
                }
            }else{
                $bytes=fputs($this->file,$data);
                if(is_int($bytes)){
                    return $bytes;
                }else{
                    $this->halt("Couldn't write data to file, please check permissions");
                    return false;
                }
            }
        }else{
            $this->halt("Data must have at least one byte");
        }
    }
   
    /**
    * Copies a file to the given destination
    * @param string $destination The new file destination
    * @return boolean $copied Returns TRUE if file could bie copied, FALSE if not
    * @desc Copies a file to the given destination
    */
    function copy($destination){
        if(strlen($destination)>0){
            if(copy($this->name,$destination)){
                return true;
            }else{
                $this->halt("Couldn't copy file to destination, please check permissions");
                return false;
            }
        }else{
            $this->halt("Destination must have at least one char");
        }
    }
   
    /**
    * Searches a string in file
    * @param string $string The string which have to be searched
    * @return array $found_bytes Pointer offsets where string have been found. On no match, function returns false
    * @desc Searches a string in file
    */
    function search($string){
        if(strlen($string)!=0){
           
            $offsets=array();
           
            $offset=$this->pointer_get();
            rewind($this->file);
           
            // Getting all data from file
            $data=fread($this->file,$this->get_size());
           
            // Replacing \r in windows new lines
            $data=preg_replace("[\r]","",$data);
           
            $found=false;
            $k=0;
           
            for($i=0;$i<strlen($data);$i++){
               
                $char=$data[$i];
                $search_char=$string[0];
               
                // If first char of string have been found and first char havn't been found
                if($char==$search_char && $found==false){
                    $j=0;
                    $found=true;
                    $found_now=true;
                }
               
                // If beginning of the string have been found and next char have been set
                if($found==true && $found_now==false){
                    $j++;
                    // If next char have been found
                    if($data[$i]==$string[$j]){
                        // If complete string have been matched
                        if(($j+1)==strlen($string)){
                            $found_offset=$i-strlen($string)+2;
                            $offsets[$k++]=$found_offset;
                        }
                    }else{
                        $found=false;
                    }
                   
                }
               
                $found_now=false;
            }
           
            $this->pointer_set($offset);
           
            return $offsets;
        }else{
            $this->halt("Search String have to be at least 1 chars");
        }
    }
   
    /**
    * Prints out a error message
    * @param string $message all occurred errors as array
    * @desc Returns all occurred errors
    */
    function halt($message){
        if($this->debug){
            printf("File error: %s\n", $message);
            if($this->error_nr!="" && $this->error!=""){
                printf("MySQL Error: %s (%s)\n",$this->error_nr,$this->error);
            }
            die ("Session halted.");
        }
    }
   
    /**
    * Switches to debug mode
    * @param boolean $switch
    * @desc Switches to debug mode
    */
    function debug_mode($debug=true){
        $this->debug=$debug;
        if(!$this->file){
            $this->halt("File couln't be opened, please check permissions");
        }
    }
}

?>