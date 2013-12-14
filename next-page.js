jQuery(document).ready(function($) {
	var sending = false;
	$('#next-menu-btn').click(function() {
		if (sending) {
			return;
		}
		sending = true;
		$.ajax({
			type : 'POST',
			url : SM_Setting.ajaxurl,
			data : {
				action : SM_Setting.action,
				page : SM_Setting.next_page
			},
			timeout : 8000,
			error : function() {
				sending = false;
				alert("データを取得できません");
			},
			success : function(data) {
				sending = false;
				SM_Setting.next_page = data.next_page;
				$('.shop-menu-item:last').after(data.html);
				$('.shop-menu-item:hidden').fadeIn("slow");
				if (data.next_page == null) {
					$('#next-menu-btn').remove();
				}
			}
		});
		return false;
	});
});
