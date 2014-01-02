jQuery(document).ready(function($) {
    // drop down selection listener
    $("#ui-tabs").tabs();
    var d = new Date();
    var n = d.getDay();
	$('#ui-tabs').tabs( "option", "active", n );
    $(".bmlt-table tr:even, .bmlt_simple_format_table tr:even").addClass("bmlt_alt_0");
    $(".bmlt-table tr:odd, .bmlt_simple_format_table tr:odd").addClass("bmlt_alt_0");
    $("#e2").select2({
        placeholder: "Cities",
        dropdownAutoWidth: true,
        allowClear: false,
        width: "copy",
        minimumResultsForSearch: 1
    });
    $("#e3").select2({
        placeholder: "Groups",
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
        placeholder: "Zips",
        dropdownAutoWidth: true,
        allowClear: false,
        width: "copy",
        minimumResultsForSearch: 1
    });
    $("#e6").select2({
        placeholder: "Formats",
        dropdownAutoWidth: true,
        allowClear: false,
        width: "copy",
        minimumResultsForSearch: 1
    });
    $("#e2").on('click', function() {
        $("#e3").select2("val", null);
        $("#e4").select2("val", null);
        $("#e5").select2("val", null);
        $("#e6").select2("val", null);
        // check the pages to find the page that is visible and hide it
        var val = $("#e2").val();
        $('.bmlt-page').each(function(index) {
            if (this.className.indexOf('show', 0) > -1) {
                $("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
                    showPage(val);
                });
                // found the visible page now break out of the each loop.
                return;
            }
        });
    });
    $("#e3").on('click', function() {
        // check the pages to find the page that is visible and hide it
        $("#e2").select2("val", null);
        $("#e4").select2("val", null);
        $("#e5").select2("val", null);
        $("#e6").select2("val", null);
        var val = $("#e3").val();
        $('.bmlt-page').each(function(index) {
            if (this.className.indexOf('show', 0) > -1) {
                $("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
                    showPage(val);
                });
                // found the visible page now break out of the each loop.
                return;
            }
        });
    });
    $("#e4").on('click', function() {
        // check the pages to find the page that is visible and hide it
        $("#e2").select2("val", null);
        $("#e3").select2("val", null);
        $("#e5").select2("val", null);
        $("#e6").select2("val", null);
        var val = $("#e4").val();
        $('.bmlt-page').each(function(index) {
            if (this.className.indexOf('show', 0) > -1) {
                $("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
                    showPage(val);
                });
                // found the visible page now break out of the each loop.
                return;
            }
        });
    });
    $("#e5").on('click', function() {
        // check the pages to find the page that is visible and hide it
        $("#e2").select2("val", null);
        $("#e3").select2("val", null);
        $("#e4").select2("val", null);
        $("#e6").select2("val", null);
        var val = $("#e5").val();
        $('.bmlt-page').each(function(index) {
            if (this.className.indexOf('show', 0) > -1) {
                $("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
                    showPage(val);
                });
                // found the visible page now break out of the each loop.
                return;
            }
        });
    });
    $("#e6").on('click', function() {
        // check the pages to find the page that is visible and hide it
        $("#e2").select2("val", null);
        $("#e3").select2("val", null);
        $("#e4").select2("val", null);
        $("#e5").select2("val", null);
        var val = $("#e6").val();
        $('.bmlt-page').each(function(index) {
            if (this.className.indexOf('show', 0) > -1) {
                $("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
                    showPage(val);
                });
                // found the visible page now break out of the each loop.
                return;
            }
        });
    });
    $("#day").on('click', function() {
        $("#e2").select2("val", null);
        $("#e3").select2("val", null);
        $("#e4").select2("val", null);
        $("#e5").select2("val", null);
        $("#e6").select2("val", null);
        // check the pages to find the page that is visible and hide it
        $('.bmlt-page').each(function(index) {
            if (this.className.indexOf('show', 0) > -1) {
                $("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
                    showPage("days");
                });
                // found the visible page now break out of the each loop.
                return;
            }
        });
    });
    $('.custom-ul').on('click', 'a', function(event) {
        // check the pages to find the page that is visible and hide it
        $('.bmlt-page').each(function(index) {
            if (this.className.indexOf('show', 0) > -1) {
                $("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
                    showPage(event.target.id);
                });
                // found the visible page now break out of the each loop.
                return;
            }
        });
    });
    // show the selected page

    function showPage(thisTarget) {
        $("#" + thisTarget).fadeIn().removeClass("hide").addClass("show");
    }

	$('.show-popup').click(function(event){
	event.preventDefault(); // disable normal link function so that it doesn't refresh the page
	$('.overlay-bg').show(); //display your popup
	});
	 
	// hide popup when user clicks on close button
	$('.close-btn').click(function(){
	$('.overlay-bg').hide(); // hide the overlay
	});
	 
	// hides the popup if user clicks anywhere outside the container
	$('.overlay-bg').click(function(){
		$('.overlay-bg').hide();
	})
	// prevents the overlay from closing if user clicks inside the popup overlay
	$('.overlay-content').click(function(){
		return false;
	});	
	$( "#days" ).removeClass("hide").addClass("show");
	$( ".ui-bmlt-header" ).removeClass("hide").addClass("show");
    $("#bmlt-tabs").fadeIn().removeClass("hide").addClass("show");	
});