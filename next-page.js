jQuery(document).ready(function($) {
	$('#next-menu-btn').css("display", "block").click(function() {
		$('#loader').css("display", "block");
		$('#next-menu-btn').css("display", "none");
		$.ajax({
			type : 'POST',
			url : SM_Setting.ajaxurl,
			data : {
				action : SM_Setting.action,
				page : SM_Setting.next_page,
				category : $("#shop-category").val()
			},
			timeout : 8000,
			error : function() {
				$('#loader').css("display", "none");
				$('#next-menu-btn').css("display", "block");
				alert("データを取得できません");
			},
			success : function(data) {
				SM_Setting.next_page = data.next_page;
				var html = createHtml( data );
				$('.shop-menu-item:last').after( html );
				$('.shop-menu-item:hidden').fadeIn("slow");
				$('#loader').css("display", "none");
				if (data.next_page ) {
					$('#next-menu-btn').css("display", "block");
				}
			}
		});
		return false;
	});
	function createHtml( data ){
		var items = data.items
		var html = '';
		var target = data.window_open ? ' target="_blank" ' : "";
		for ( var i = 0; i < items.length; i++){
			html += '<div class="shop-menu-item"><a href="' + items[i].url + '"' + target + '>'
			+ items[i].img_tag + '<p class="shop-menu-name">' + items[i].title + '</p>';
			if ( data.show_price ){
				html += '<p class="shop-menu-price">' + items[i].price + '</p>';
			}
			html += '</a></div>';
		}
		return html;
	}
});
