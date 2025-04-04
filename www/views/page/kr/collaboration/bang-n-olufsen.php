<?php
    function get_device_type() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $mobile_agents = array('iphone', 'ipod', 'ipad', 'android', 'webos', 'blackberry', 'nokia', 'opera mini', 'windows mobile', 'windows phone', 'iemobile');

        foreach($mobile_agents as $mobile_agent) {
            if (strpos(strtolower($user_agent), strtolower($mobile_agent)) !== false) {
                return 'mo';
            }
        }
        return 'pc';
    }

    if(get_device_type() == 'pc') {
        include 'bang-n-olufsen-pc.php';
    } else {
        include 'bang-n-olufsen-mo.php';
    }
?>
<script src="/scripts/collaboration/collaboration_bang-n-olufsen.js"></script>