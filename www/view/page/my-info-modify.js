$(document).ready(function() {
	if(config.member) {
		$("#member-name").text(config.member.name);
		$("#member-email").text(config.member.id);
		$("#member-tel").text(config.member.tel);
		$("#member-birthday").text(config.member.birthday.split(" ")[0]);
	}
	$("#btn-change-pw").click(function() {
		modal('pwchange');
	});
});