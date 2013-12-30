jQuery(document).ready( function($) {
    // drop down selection listener

	$( "#tabs" ).tabs();
	
	var d = new Date();
	
	var n = d.getDay();
	
	$( "#tabs" ).tabs("select", n);
	
	$(".bmlt-table tr:even, .bmlt_simple_format_table tr:even").addClass("bmlt_alt_0");
	
	$(".bmlt-table tr:odd, .bmlt_simple_format_table tr:odd").addClass("bmlt_alt_0");
	
	$("#tabs").show();

	$( ".icon-map" ).button({ icons: { primary: "ui-icon-link" } });

	$( ".icon-format" ).button({ icons: { primary: "ui-icon-link" } });

	$(".showlegend").on('click', function(e){
		e.preventDefault();
        $( "#thislegend" ).removeClass("hide").addClass("show");
		$( ".selector" ).dialog({ position: { my: "center", at: "center", of: window } });
		$( "#thislegend" ).dialog({ height: 800, width: 700, hide: "fade", show: "fade", title: "Meeting Formats", closeOnEscape: true, modal: false, position: { my: "center", at: "center", of: window } });
		$( "#thislegend" ).dialog({ close: function (event, ui) { $(this).dialog("destroy");$(this).removeClass("show").addClass("hide"); }});
	});

	$("#e2").select2({
                placeholder: "Cities",
				dropdownAutoWidth: true,
                allowClear: false,
                width: "copy",
				minimumResultsForSearch: 1
     });
	
	$("#e3").select2({
                placeholder: "Home Groups",
				dropdownAutoWidth: true,
                allowClear: false,
                width: "copy",
				minimumResultsForSearch: 1
     });
	
	$("#e4").select2({
                placeholder: "Locations",
				dropdownAutoWidth: true,
                allowClear: false,
                width: "copy",
				minimumResultsForSearch: 1
     });
	
	$("#e5").select2({
                placeholder: "Zip Codes",
				dropdownAutoWidth: true,
                allowClear: false,
                width: "copy",
				minimumResultsForSearch: 1
     });
	
	$("#e2").on('click', function(){
		$("#e3").select2("val", null);
		$("#e4").select2("val", null);
		$("#e5").select2("val", null);
        // check the pages to find the page that is visible and hide it
		var val = $("#e2").val();
        $('.page').each(function(index) {
            if(this.className.indexOf('show', 0) > -1){
                $("#"+this.id).fadeOut(function(){
                    $("#"+this.id).removeClass("show").addClass("hide");
                    showPage(val);
                });
                // found the visible page now break out of the each loop.
                return;
            }
        });
   });

	$("#e3").on('click', function(){
        // check the pages to find the page that is visible and hide it
		$("#e2").select2("val", null);
		$("#e4").select2("val", null);
		$("#e5").select2("val", null);
		var val = $("#e3").val();
        $('.page').each(function(index) {
            if(this.className.indexOf('show', 0) > -1){
                $("#"+this.id).fadeOut(function(){
                    $("#"+this.id).removeClass("show").addClass("hide");
                    showPage(val);
                });
                // found the visible page now break out of the each loop.
                return;
            }
        });
   });

	$("#e4").on('click', function(){
        // check the pages to find the page that is visible and hide it
		$("#e2").select2("val", null);
		$("#e3").select2("val", null);
		$("#e5").select2("val", null);
		var val = $("#e4").val();
        $('.page').each(function(index) {
            if(this.className.indexOf('show', 0) > -1){
                $("#"+this.id).fadeOut(function(){
                    $("#"+this.id).removeClass("show").addClass("hide");
                    showPage(val);
                });
                // found the visible page now break out of the each loop.
                return;
            }
        });
   });

	$("#e5").on('click', function(){
        // check the pages to find the page that is visible and hide it
		$("#e2").select2("val", null);
		$("#e3").select2("val", null);
		$("#e4").select2("val", null);
		var val = $("#e5").val();
        $('.page').each(function(index) {
            if(this.className.indexOf('show', 0) > -1){
                $("#"+this.id).fadeOut(function(){
                    $("#"+this.id).removeClass("show").addClass("hide");
                    showPage(val);
                });
                // found the visible page now break out of the each loop.
                return;
            }
        });
   });

	$("#day").on('click', function(){

		$("#e2").select2("val", null);
		$("#e3").select2("val", null);
		$("#e4").select2("val", null);
		$("#e5").select2("val", null);
	
        // check the pages to find the page that is visible and hide it
        $('.page').each(function(index) {
            if(this.className.indexOf('show', 0) > -1){
                $("#"+this.id).fadeOut(function(){
                    $("#"+this.id).removeClass("show").addClass("hide");
                    showPage("days");
                });
                // found the visible page now break out of the each loop.
                return;
            }
        });
   });

    $('.custom-ul').on('click', 'a', function(event){
        // check the pages to find the page that is visible and hide it
        $('.page').each(function(index) {
            if(this.className.indexOf('show', 0) > -1){
                $("#"+this.id).fadeOut(function(){
                    $("#"+this.id).removeClass("show").addClass("hide");
                    showPage(event.target.id);
                });
                // found the visible page now break out of the each loop.
                return;
            }
        });
   });

    // show the selected page
    function showPage(thisTarget){
        $("#"+thisTarget).fadeIn().removeClass("hide").addClass("show");
    }

});