/*
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

	Copyright 2009 Chris Richardson.
*/

// *********************************************************************
// Admin functions for options screen
// *********************************************************************

// Check if API is valid
function mappCheckAPI() {
	var apiKey = document.getElementById('api_key');
	var apiMessage = document.getElementById('api_message');
	var apiBlock = document.getElementById('api_block');
	var googleLink = '<a target="_blank" href="http://code.google.com/apis/maps/signup.html">' + mappressl10n.here + '</a>';

	if (apiKey.value == "") {
		apiBlock.className = 'api_error';
		apiMessage.innerHTML = mappressl10n.api_missing + googleLink;
		return;
	}

	if (typeof GBrowserIsCompatible == 'function' && GBrowserIsCompatible())
		return;

	apiBlock.className = 'api_error';
	apiMessage.innerHTML = mappressl10n.api_incompatible + googleLink;
}

// Icon picker initialization
jQuery(document).ready(function() {
	// Create icon dialog
	jQuery('#mapp_icon_list').dialog({title : mappressl10n.select_icon, "overflow-y" : "auto", autoOpen : false, resizable: true, width : "50%", height : "auto"});

	// Add click event when user picks an icon within the dialog
	jQuery('#mapp_icon_list').click(function(e) {
		// Change the default icon if something was selected
		jQuery('#default_icon').val(e.target.id);
		jQuery('#icon_picker').attr('src', e.target.src);
		jQuery('#mapp_icon_list').dialog('close');
		return false;
	});

	// Add click event to open the dialog
	jQuery('#icon_picker').click(function() {
		jQuery('#mapp_icon_list').dialog('open');
		return false;
	});
});

// *********************************************************************
// Admin functions for post edit screen
// *********************************************************************

// Admin screen initalization
jQuery(document).ready(function($){
	// Add click events for buttons and controls
    jQuery('#mapp_paypal').click(function(e) {
        window.open('https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4339298', 'Donate');
        return false;
    });

	jQuery('#mapp_recenter').click(function(e) {
		editMap.reCenter(null, true);
	});

	jQuery('#mapp_insert').click(function(e) {
		mappInsertShortCode();
	});

	jQuery('#mapp_add_location').click(function(e) {
		mappAddLocation();
	});

	// If map size radio buttons are changed then resize map
	// Note: syntax must be used as shown for radio buttons
    enableCustomMapSizeFields();
	jQuery('input[name="mapp_size"]').click(function(e) {
        // Resize the map
        editMap.reSize(jQuery('input[name="mapp_size"]:checked').val(), jQuery('#mapp_width').val(), jQuery('#mapp_height').val());
        enableCustomMapSizeFields();
	});

	// If custom map width changed then resize map
	jQuery('#mapp_width').change(function(e) {
		// If value <= 0 then use 1; if value >= 2048 then use 2048
		if ( jQuery('#mapp_width').val() <= 0 )
			jQuery('#mapp_width').val(1);

		if (jQuery('#mapp_width').val() >= 2048 )
			jQuery('#mapp_width').val(2048);

		editMap.reSize(jQuery('input[name="mapp_size"]:checked').val(), jQuery('#mapp_width').val(), jQuery('#mapp_height').val());
	});

	// If custom map height changed then resize map
	jQuery('#mapp_height').change(function(e) {
		// If value <= 0 then use 1; if value >= 2048 then use 2048
		if ( jQuery('#mapp_height').val() <= 0 )
			jQuery('#mapp_height').val(1);

		if (jQuery('#mapp_height').val() >= 2048 )
			jQuery('#mapp_height').val(2048);

		editMap.reSize(jQuery('input[name="mapp_size"]:checked').val(), jQuery('#mapp_width').val(), jQuery('#mapp_height').val());
	});
});

function enableCustomMapSizeFields() {
    // If 'custom' was clicked then enable the custom width/height fields, otherwise disable them
    if (jQuery('input[name="mapp_size"]:checked').val() == 'CUSTOM') {
        jQuery('#mapp_width').attr('readonly', false);
        jQuery('#mapp_height').attr('readonly', false);
    } else {
        jQuery('#mapp_width').attr('readonly', true);
        jQuery('#mapp_height').attr('readonly', true);
    }
}

// Insert mappress shortcode in post
function mappInsertShortCode () {
	shortcode = "[mappress]";
	send_to_editor(shortcode);
	return false;
}

// Add location by lat/lng or street address
function mappAddLocation() {
	var lat = jQuery("#mapp_input_lat").val();
	var lng = jQuery("#mapp_input_lng").val();
	var address = jQuery("#mapp_input_address").val();
	var message = jQuery("#mapp_message");

	// Clear any old messages
	message.text("");
	message.removeClass("updated fade error");

	// If lat/lng were provided, use them
	if (lat && lng) {
		var point = new GLatLng(lat, lng);
		mappAddPOI(point, lat + "," + lng, lat + "," + lng);
	// If address provided, use it
	} else if (address) {
		// Geocoder will callback mappCheckGeocodedAddress
		mappGeocoder.getLocations(address, mappCheckGeocodedAddress);
	} else {
		// Something was missing, give warning
		message.text(mappressl10n.enter_location);
		message.removeClass("updated fade error");
		message.addClass("updated fade");
		return;
	}
}

// Check the geocoded address.  If it's ok, add a row.
function mappCheckGeocodedAddress(response) {
	var address = jQuery("#mapp_input_address").val();
	var message = jQuery("#mapp_message");

	// Check response for errors
	if (!response || response.Status.code != 200) {
		message.text(mappressl10n.no_address);
		jQuery("#mapp_message").removeClass("updated fade error");
		message.addClass("error");
		return;
	}

	// Response was ok, get the geocoded address values
	var place = response.Placemark[0];
	var point = new GLatLng(place.Point.coordinates[1], place.Point.coordinates[0]);
	var exData = place.ExtendedData;
	var boundsbox = exData ? exData.LatLonBox : undefined

	// Now that we have lat/lng, add the POI
	mappAddPOI(point, address, place.address, boundsbox);
}


function mappAddPOI(point, address, corrected_address, boundsbox) {
	// Just confirm that we got lat/lng
	if (!point.lat() || !point.lng()) {
		alert ("Mappress internal error in geocoding!")
		return;
	}

	// Add the new address to the minimap
	var poi = {address : address, corrected_address : corrected_address, lat : point.lat(), lng : point.lng(), boundsbox : boundsbox, icon : '' };
	var i = editMap.addPOI(poi);

	// Clear the input fields
	jQuery("#mapp_input_address").val("");
	jQuery("#mapp_input_lat").val("");
	jQuery("#mapp_input_lng").val("");
}

