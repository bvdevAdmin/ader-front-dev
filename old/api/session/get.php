<?php
	if(isset($_SESSION['MEMBER_IDX'])){
        if(isset($_SESSION['EMAIL'])){
            print_r("idx : ".$_SESSION['MEMBER_IDX'].", email : ".$_SESSION['EMAIL']);
        }
    }
?>