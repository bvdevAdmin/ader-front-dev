$(document).ready(function() {
	if(location.pathname != "/login") {
		$("#frm-login input[name='r_url']").val(location.pathname);
	}
});