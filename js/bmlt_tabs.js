function getDirections(lat,lon) {
	
	var destination = lat + ',' + lon;
	// check if browser supports geolocation
	if (navigator.geolocation) { 
		
		// get user's current position
		navigator.geolocation.getCurrentPosition(function (position) {   
			
			// get latitude and longitude
			var latitude = position.coords.latitude;
			var longitude = position.coords.longitude;
			startingLocation = latitude + "," + longitude;
			
			// send starting location and destination to goToGoogleMaps function
			goToGoogleMaps(startingLocation, destination);
			
		});
	}
	
	// fallback for browsers without geolocation
	else {
		
		// get manually entered postcode
		startingLocation = $('.manual-location').val();
		
		// if user has entered a starting location, send starting location and destination to goToGoogleMaps function
		if (startingLocation != '') {
			goToGoogleMaps(startingLocation, destination);
		}
		// else fade in the manual postcode field
		else {
			$('.no-geolocation').fadeIn();
		}
		
	}
	
}
					
function goToGoogleMaps(startingLocation, destination) {
	window.open("https://maps.google.com/maps?saddr=" + startingLocation + "&daddr=" + destination, '_blank');
	var url = "https://www.google.com/maps/directions?origin="+startingLocation+"&destination="+destination+"&key=AIzaSyDjKpHiNtvvK-2VhtZGg8pN-y7D_TLcyYs"
}

function getMap(lat, lon) {

	window.open("https://www.google.com/maps/q="+lat+"%2C"+lon, '_blank');
	var url = "https://www.google.com/maps/embed/v1/place?q="+lat+"%2C"+lon+"&key=AIzaSyDjKpHiNtvvK-2VhtZGg8pN-y7D_TLcyYs"

	//var url = "https://www.google.com/maps/embed/v1/view?center="+lat+"%2C"+lon+"&zoom=18&maptype=roadmap&key=AIzaSyDjKpHiNtvvK-2VhtZGg8pN-y7D_TLcyYs"

	var winW = jQuery(window).width() - 180;
	var winH = jQuery(window).height() - 180;

}

(function(a){(jQuery.browser=jQuery.browser||{}).mobile=/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))})(navigator.userAgent||navigator.vendor||window.opera);
//thisWillFireImmediately();
//function thisWillFireImmediately() {
	//jQuery('body').prepend('<div id="spinner"></div><script>var target = document.getElementById("spinner");var spinner = new Spinner().spin(target);</script>');
//}
jQuery(document).ready(function($) {
			
// go to Google Maps function - takes a starting location and destination and sends the query to Google Maps
   $('.tooltip').tooltipster({
	   animation: 'grow',
	   delay: 200,
	   theme: 'tooltipster-noir',
	   contentAsHTML: true,
	   touchDevices: false,
	   iconTouch: false,
	   interactive: true,
	   position: 'right',
	   trigger: 'hover'
	});
   $('.tooltip-map').tooltipster({
	   animation: 'grow',
	   delay: 200,
	   theme: 'tooltipster-noir',
	   contentAsHTML: true,
	   touchDevices: false,
	   iconTouch: false,
	   interactive: true,
	   position: 'left',
	   trigger: 'hover'
	});
    $("#ui-tabs").tabs();
    var d = new Date();
    var n = d.getDay();
	$('#ui-tabs').tabs( "option", "active", n );
    //$(".bmlt-table tr:even, .bmlt_simple_format_table tr:even").addClass("bmlt_alt_0");
    //$(".bmlt-table tr:odd, .bmlt_simple_format_table tr:odd").addClass("bmlt_alt_0");
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
	if(jQuery.browser.mobile)
	{
		$("#e2").prop("readonly",true);
		$(".select2-search").css({"display":"none"});
		$(".select2-search").remove();
		$("#s2id_e2").css({"width":"99%","margin-bottom":"3px"});
		$("#s2id_e3").css({"width":"99%","margin-bottom":"3px"});
		$("#s2id_e4").css({"width":"99%","margin-bottom":"3px"});
		$("#s2id_e5").css({"width":"99%","margin-bottom":"3px"});
		$("#s2id_e6").css({"width":"99%","margin-bottom":"3px"});
		$("#s2id_e6").css({"width":"99%","margin-bottom":"3px"});
		$(".bmlt-tabs .bmlt-button-weekdays").css({"width":"98%","margin-bottom":"3px"});
		$(".bmlt-tabs .bmlt-button-cities").css({"width":"98%","margin-bottom":"3px"});
	}
    $("#e2").on('click', function() {
        $("#e3").select2("val", null);
        $("#e4").select2("val", null);
        $("#e5").select2("val", null);
        $("#e6").select2("val", null);
		if(jQuery.browser.mobile)
		{
			$("#e2").prop("readonly",true);
			$(".select2-search").css({"display":"none"});
			$(".select2-search").remove();
		}
        // check the pages to find the page that is visible and hide it
        var val = $("#e2").val();
        $('.bmlt-page').each(function(index) {
            //if (this.className.indexOf('show', 0) > -1) {
                //$("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
					$("#city").css({"background-color":"#63B8EE","color":"#000"});
					$("#day").css({"background-color":"#63B8EE","color":"#000"});
                    showPage(val);
                //});
                // found the visible page now break out of the each loop.
                return;
            //}
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
            //if (this.className.indexOf('show', 0) > -1) {
                //$("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
					$("#city").css({"background-color":"#63B8EE","color":"#000"});
					$("#day").css({"background-color":"#63B8EE","color":"#000"});
                    showPage(val);
                //});
                // found the visible page now break out of the each loop.
                return;
            //}
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
            //if (this.className.indexOf('show', 0) > -1) {
                //$("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
					$("#city").css({"background-color":"#63B8EE","color":"#000"});
					$("#day").css({"background-color":"#63B8EE","color":"#000"});
                    showPage(val);
                //});
                // found the visible page now break out of the each loop.
                return;
            //}
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
            //if (this.className.indexOf('show', 0) > -1) {
                //$("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
					$("#city").css({"background-color":"#63B8EE","color":"#000"});
					$("#day").css({"background-color":"#63B8EE","color":"#000"});
                    showPage(val);
                //});
                // found the visible page now break out of the each loop.
                return;
            //}
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
            //if (this.className.indexOf('show', 0) > -1) {
                //$("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
					$("#city").css({"background-color":"#63B8EE","color":"#000"});
					$("#day").css({"background-color":"#63B8EE","color":"#000"});
                    showPage(val);
                //});
                // found the visible page now break out of the each loop.
                return;
            //}
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
            //if (this.className.indexOf('show', 0) > -1) {
                //$("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
					$("#day").css({"background-color":"#FF6B7F","color":"#fff"});
					$("#city").css({"background-color":"#63B8EE","color":"#000"});
                    showPage("days");
                //});
                // found the visible page now break out of the each loop.
                return;
            //}
        });
    });
    $("#city").on('click', function() {
        $("#e2").select2("val", null);
        $("#e3").select2("val", null);
        $("#e4").select2("val", null);
        $("#e5").select2("val", null);
        $("#e6").select2("val", null);
        // check the pages to find the page that is visible and hide it
        $('.bmlt-page').each(function(index) {
            //if (this.className.indexOf('show', 0) > -1) {
                //$("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
					$("#city").css({"background-color":"#FF6B7F","color":"#fff"});
					$("#day").css({"background-color":"#63B8EE","color":"#000"});
                    showPage("cities");
                //});
                // found the visible page now break out of the each loop.
                return;
            //}
        });
    });
    $('.custom-ul').on('click', 'a', function(event) {
        // check the pages to find the page that is visible and hide it
        $('.bmlt-page').each(function(index) {
            //if (this.className.indexOf('show', 0) > -1) {
                //$("#" + this.id).fadeOut(function() {
                    $("#" + this.id).removeClass("show").addClass("hide");
                    showPage(event.target.id);
                //});
                // found the visible page now break out of the each loop.
                return;
            //}
        });
    });
    // show the selected page

    function showPage(thisTarget) {
        $("#" + thisTarget).fadeIn().removeClass("hide").addClass("show");
    }

	$( ".ui-bmlt-header" ).removeClass("hide").addClass("show");
    $(".bmlt-tabs").removeClass("hide").addClass("show").fadeIn();	
});