$(document).ready(function() {
	if (config.member != null) {
		$('.f_member_name').text(config.member.name);
		$('.f_member_level').text(config.member.membership);
		
		$('.f_buy_total').text(config.member.buy_total);

		if (config.member.next_price != " - ") {
			if (config.language == "KR") {
				$('.f_next_price').append(`
					다음 등급까지 구매금액이
					<u data-field="member_nextlevel_to_buy">
						<font>${config.member.next_price}</font> 원
					</u>
					남았습니다.
				`)
			} else if (config.language == "EN") {
				$('.f_next_price').append(`
					Purchase price for next level is
					<u data-field="member_nextlevel_to_buy">
						<font>${config.member.next_price}</font> USD
					</u>
					left.
				`)
			}
		} else {
			if (config.language == "KR") {
				$('.f_next_price').append(`
					다음 등급의 조건이 충족되었습니다.
				`)
			} else if (config.language == "EN") {
				$('.f_next_price').append(`
					The next level conditions are satisfied.
				`)
			}
		}
		
	} else {
		let msg_alert = {
			KR : "로그인 후 다시 시도해주세요.",
			EN : "Please Log in and try again."
		}

		alert(
			msg_alert[config.language],
			function() {
				sessionStorage.setItem('r_url',location.href);
				location.href = `${config.base_url}/login`;
			}
		)
	}
});