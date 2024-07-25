$(document).ready(function() {
	if(config.member) {
		$("#member-name").text(config.member.name);
		$("#member-email").text(config.member.id);
		$("#member-tel").text(config.member.tel);
		if(config.member.birthday) {
			$("#member-birthday").text(config.member.birthday.split(" ")[0]);
		}
	}
});