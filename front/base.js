$(document).ready(function(){
	
	$("body").addClass("trans");
	
	$("#mobile_btn").click(function(){
		$("#menu").stop();
		$("#menu").slideToggle();
	});
	
	$("#langselect").click(function(){
		$("#langselect .content").stop();
		$("#langselect .content").slideToggle();
	});
	
	$('[data-toggle="tooltip"]').tooltip();
	
	triggerDataHrefs();
	
	
	//scrollbar
	if ($(window).width() >= 1140){
		$(".scrollbar-macosx").scrollbar();
		var h = $(window).height() - $("#sidebar_logo").outerHeight();
		$(".scrollbar-macosx.scroll-wrapper").css("height", h + "px");
	}
	
	if($("#session-msg").length && $("#backbtn").length){
		var z = $("#session-msg").outerHeight(true);
		var k = $("#backbtn").css('top').replace(/[^-\d\.]/g, '');
		z = parseInt(z)+parseInt(k);
		$("#backbtn").css('top', z+'px');
	}
	
	$("a.confirm").click(function(){
		if (!confirm("Biztos vagy benne?")) return false;
	});
	
	//order table
	$("table.shortable th").click(function(){
		var th = $(this);
		var thead = th.parent().parent();
		var table = thead.parent();
		
		var index = thead.find("th").index(th);
		//console.log("index: " + index);
		
		var data_array = [];
		
		var oldhtml = "";
		
		//build data array
		table.find("tbody tr").each(function(){
			//console.log("found tr");
			var tr = $(this);
			var ind = tr.find("td").eq(index).html();
			var html = $("<div />").append(tr.clone()).html();
			data_array.push({index: ind, html: html});
			oldhtml += html;
		});
		
		//sort array
		data_array.sort(function(a, b){
			if(a.index < b.index) return -1;
			if(a.index > b.index) return 1;
			return 0;
		});
		
		//build html
		var newhtml = "";
		for (var i = 0; i<data_array.length; i++){
			newhtml += data_array[i]["html"];
		}
		
		//if already ordered
		if (newhtml == oldhtml){
			//console.log("already ordered");
			//sort array 2
			data_array.sort(function(a, b){
				if(a.index > b.index) return -1;
				if(a.index < b.index) return 1;
				return 0;
			});
			
			//build new html
			var newhtml = "";
			for (var i = 0; i<data_array.length; i++){
				newhtml += data_array[i]["html"];
			}
		}
		
		table.find("tbody").html(newhtml);
		triggerDataHrefs();
		if (typeof afterTableLoaded === "function"){
			afterTableLoaded();
		}
		
		//var ordered_tab 
		
	});
	
});


function triggerDataHrefs(){
	
	$("[data-href]").click(function(){
		if ($(this).attr("data-target") == "_blank"){
			window.open($(this).data('href'));
		} else {
			window.location = $(this).data('href');
		}
	});
}

$(window).on('load', function(){
	$(".sameh_block").each(function(){
		var block = $(this);
		var h = 0;
		block.find('.sameh').each(function(){
			var t = $(this);
			//console.log(t.height());
			if (t.height()>h) h = t.height();
		});
		//console.log("res: " + h);
		block.find('.sameh').each(function(){
			$(this).height(h+'px');
		});
	});
	
});