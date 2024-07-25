$(document).ready(function() {
	$.ajax({
		url : config.api + "notice/get",
		success : function(d) {
			if(d.code == 200) {
				d.data.forEach(row => {
					$("#list").append(`
						<dt>${row.title}</dt>
						<dd>${row.contents}</dd>
					`);
				});
			}
			else {
			}
		}
	});
});