$(function(){
	get_news('top_news');
	
	$("#top_news").click(function(){get_news('top_news');});
	$("#activity_news").click(function(){get_news('activity_news');});
	$("#admin_news").click(function(){get_news('admin_news');});
	$("#academy_news").click(function(){get_news('academy_news');});
	$("#hiring_news").click(function(){get_news('hiring_news');});
	$("#released_news").click(function(){get_news('released_news');});
	$("#enews").click(function(){get_news('enews');});
	//$("#search_record").click(function(){rss_feed});
	
	//$("#rss_feed").click(function(){});
	
	$("#rss_feed").click(function() {
        $('#rss-dialog').simpledialog2({
            mode: "blank",
            headerText: "訂閱RSS",
            headerClose: false,
            blankContent: true,
            themeDialog: 'd',
            width: '75%',
            zindex: 1000
        });
		
		$( "#menu-panel" ).panel( "close" );
    });
    
	$("#search_record").click(function() {
        $('#search-dialog').simpledialog2({
            mode: "blank",
            headerText: "搜尋公告",
            headerClose: false,
            blankContent: true,
            themeDialog: 'd',
            width: '75%',
            zindex: 1000
        });
		
		$( "#menu-panel" ).panel( "close" );
    });
    
    $("#select-all-news").click(function() {
		if($("#select-all-news").attr('checked'))
			$("input[name='search_box[]']").each(function() {
				$(this).attr("checked", true);
			});
		else
			$("input[name='search_box[]']").each(function() {
				$(this).attr("checked", false);
			});
	})
	
});

function get_news(category) {
	$.mobile.loading( "show", {
		text: "請稍後...",
		textVisible: true,
		theme: "a",
		html: ""
	});
	$("#news-list").html('');
	$.get("/nttunews/news/"+category,function(response) {
		res = JSON.parse(response);
		res = res["data"];
		var res_count = 0;
		var res_str = "";
		for(;res_count<res.length;res_count++) {
			res_str += '<li data-role="list-divider" data-theme="a">'+res[res_count]['date']+'</li>';
			res_str += '<li>';
			res_str += '<a href="'+res[res_count]['link']+'" target="_blank">'+'<h2>'+res[res_count]['title']+'</h2>'+'</a>';
			res_str += '</li>';
		}
		$("#news-list").append(res_str);
		$("#news-list").listview('refresh');
		$( "#menu-panel" ).panel( "close" );
		$.mobile.loading( "hide" );
		if(category=="top_news" || category=="activity_news")
			return;
		else
		{
			$("#top_news").removeClass("ui-btn-active");
			$("#activity_news").removeClass("ui-btn-active");
		}
	});
}
