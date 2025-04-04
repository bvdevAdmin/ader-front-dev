let txt_none = {
	KR : "조회 가능한 공지사항이 존재하지 않습니다.",
	EN : "There is no Notice."
}

$(document).ready(function() {
	$.ajax({
		url : config.api + "notice/get",
		headers : {
			country : config.language
		},
		success : function(d) {
			if (d.data != null && d.data.length > 0) {
				d.data.forEach(row => {
					$("#list").append(`
						<dt>${row.board_title}</dt>
						<dd>${row.board_contents}</dd>
					`);
				});
			} else {
				$('#list').append(`
					<div class="list__none">
						${txt_none[config.language]}
					</div>
				`);
			}
		}
	});
});