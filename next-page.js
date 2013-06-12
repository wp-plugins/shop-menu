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
				var next_tag = $('#next-menu-btn');
				next_tag.before(data.html);
				if (data.next_page == null) {
					next_tag.remove();
				}
			}
		});
		return false;
	});
});
