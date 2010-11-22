/*
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

	Copyright 2009 Chris Richardson.
*/

function mapp(json) {
	if (json.pois)
		this.pois = json.pois;
	else
		this.pois = new Array;
	this.editable = json.editable;
	this.size = json.size;
	this.width = parseInt(json.width);			// Pass '0' to automatically size to container <div>
	this.height = parseInt(json.height);		// Pass '0' to automatically size to container <div>
	this.zoom = parseInt(json.zoom);
	this.center = new GLatLng(parseFloat(json.centerLat), parseFloat(json.centerLng));
	this.addressFormat = json.addressFormat;
	this.defaultIcon = json.defaultIcon;
	this.mapname = json.mapname;
	this.mapDiv = document.getElementById(json.mapname);
	this.mapType = json.mapType;
	this.googlebar = json.googlebar;
	this.mapTypes = json.mapTypes;
	this.bigZoom = json.bigZoom;
	this.scrollWheelZoom = json.scrollWheelZoom;

	this.ui = new GMapUIOptions(new GSize(this.width, this.height));
	this.map;
	this.mapOptions = {};

	// Load and unload for IE
	var me = this;
	if (document.all && window.attachEvent) {
		window.attachEvent("onload", function () {
			me.display();
			me.init();
		});
		window.attachEvent("onunload", GUnload);
	// Non-IE load and unload
	} else if (window.addEventListener) {
		window.addEventListener("load", function () {
			me.display();
			me.init();
		}, false);
		window.addEventListener("unload", GUnload, false);
	}
}

mapp.prototype = {

// ---------------------------------------------------------------------------------------
// Methods for ALL maps
// ---------------------------------------------------------------------------------------

	display : function() {
		// Check that API loaded OK
		if (!GBrowserIsCompatible() || typeof(GMap2) == 'undefined')
			return;

		// If a size is set, then use it, otherwise use the explicit width and height
		if (this.size && this.size != 'CUSTOM') {
			this.width = mapSizes[this.size].width;
			this.height = mapSizes[this.size].height;
		}

		// Force the initial map size
		// For edit maps, this has no effect, but for display maps it's a workaround: some naughty tabbing plugins use hidden or resized <divs>
		this.mapOptions.size = new GSize(this.width, this.height);

		// Create map.  Note: the setCenter call is mandatory!
		this.map = new GMap2(this.mapDiv, this.mapOptions);
		this.map.setCenter(new GLatLng(0,0),0);

		// Suppress the crummy looking map types control
		this.ui.controls.maptypecontrol = false;

		// If user wants map types then show the small dropdown
		if (this.mapTypes == true)
			this.ui.controls.menumaptypecontrol = true;
		else
			this.ui.controls.menumaptypecontrol = false;


		// Set big or small zoom based on options
		if (this.bigZoom == true) {
			this.ui.controls.largemapcontrol3d = true;
			this.ui.controls.smallzoomcontrol3d = false;
		} else {
			this.ui.controls.largemapcontrol3d = false
			this.ui.controls.smallzoomcontrol3d = true;
		}

		// Disable scroll-wheel zooming
		if (this.scrollWheelZoom == true)
			this.ui.zoom.scrollwheel = true;
		else
			this.ui.zoom.scrollwheel = false;

		// Add our custom UI
		this.map.setUI(this.ui);

		// Set map type, if provided
		switch (this.mapType.toLowerCase()) {
			case 'map':
				this.map.setMapType(G_NORMAL_MAP);
				break;
			case 'satellite':
				this.map.setMapType(G_SATELLITE_MAP);
				break;
			case 'hybrid':
				this.map.setMapType(G_HYBRID_MAP);
				break;
			case 'terrain':
				this.map.setMapType(G_PHYSICAL_MAP);
				break;
		}

		// Create a marker for each poi
		len = this.pois.length;
		for (var i = 0; i < len; i++)
			this.addMarker(i);
	},

	// Auto center/zoom if: (a) auto-center property is set, (b) map center/zoom is missing, (c) no POIs exist,
	// otherwise set center/zoom values exactly as they were saved
	automaticCenter : function() {
		if (!this.center.lat() || !this.center.lng() || !this.zoom || this.pois.length == 0)
			this.reCenter(null, true);
		else {
			this.map.setCenter(this.center);
			this.map.setZoom(this.zoom);
		}
	},

	// Add a marker to an existing POI
	addMarker : function(i) {
		var markerOptions = {};

		// Set icon if provided for the POI
		if ( this.pois[i].icon !== undefined && this.pois[i].icon != '' )
			markerOptions = {icon : mappIcons[this.pois[i].icon]};

		// If no POI icon, try the map default (if no map default, we'll use google default)
		else if ( this.defaultIcon !== undefined && this.defaultIcon != '' )
			markerOptions.icon = mappIcons[this.defaultIcon];

		// For editable maps, we allow dragging
		if (this.editable) {
			markerOptions.draggable = true;
		}

		// Create a marker.
		var point = new GLatLng(this.pois[i].lat, this.pois[i].lng);
		var marker = new GMarker(point, markerOptions);

		// Assign a marker number and save it to our array
		this.pois[i].marker = marker;

		// Add the marker overlay
		this.map.addOverlay(marker);

		// Bind marker events
		this.addMarkerEvents(i);
	},

	// Add marker events - must be kept separate because editable maps rebind after any marker delete
	addMarkerEvents : function(i) {
		var me = this;
		var marker = this.pois[i].marker;

		// Clear and re-create click event to open info window
		GEvent.clearListeners(marker, "click");
		GEvent.addListener(marker, "click", function() {
			me.renderMarker(i);
		});

		// For editable maps, bind the marker drag event to update the position
		if (this.editable) {

			GEvent.addListener(marker, "dragstart", function() {
				me.map.closeInfoWindow();
			});

			GEvent.addListener(marker, "dragend", function(latlng) {
				me.pois[i].lat = latlng.lat();
				me.pois[i].lng = latlng.lng();
				me.renderMarker(i);
				me.listMarkers();
			});
		}

	},

	renderMarker : function (i, fromto) {

		var html;
		var body;

		// In 1.4.2 there was no body attribute, so we'll fake it by defaulting it to the address as entered, unless there is already a caption
		// Note that a bug in 1.4.2 also left some body text set to the string literal "undefined"
		if (this.pois[i].body == "undefined" || this.pois[i].body === undefined)
			body = this.pois[i].address;
		else
			body = this.pois[i].body;

		html 	    = '<div class="mapp-overlay-div">'
					+ '<div class="mapp-overlay-title">' + this.pois[i].caption + '</div>'
					+ '<div class="mapp-overlay-body">' + body + '</div>';

		if (this.editable == true) {
			var editlinks = "<a href='#' onclick=\"editMap.editMarker('" + i + "'); return false;\" alt='" + mappressl10n.edit + "'>" + mappressl10n.edit + "</a>"
				+ " | <a href='#' onclick=\"editMap.deleteMarker('" + i + "'); return false;\">" + mappressl10n.del + "</a>";

			html += '<div class="mapp-overlay-edit">' + editlinks + '</div>';
		}

		if (this.directions == true) {
			var directions;

			switch (fromto) {

				case 'to':
					directions = mappressl10n.directions + ': <b>' + mappressl10n.to_here + '<\/b> - <a href="#" onclick="' + this.mapname + ".renderMarker(" + i + ", 'from'); return false;\" >" + mappressl10n.from_here + "</a>"
						 + '<form onSubmit=\"return false\">'
						 + '<input type="text" id="saddr" value="" />'
						 + '<input type="hidden" id="daddr" value="' + this.pois[i].corrected_address + '"/>'
						 + "<input type=\"submit\" onclick=\"" + this.mapname + ".directionsShow(form)\" value=\"" + mappressl10n.go + "\" />"
						 + '</form>';
					break;

				case 'from':
					directions = mappressl10n.directions + ': <a href="#" onclick="' + this.mapname + ".renderMarker(" + i + ", 'to'); return false\" >" + mappressl10n.to_here + "</a> - <b>" + mappressl10n.from_here + "</b>"
						 + '<form onSubmit=\"return false\">'
						 + '<input type="text" id="daddr" value="" />'
						 + '<input type="hidden" id="saddr" value="' + this.pois[i].corrected_address + '"/>'
						 + "<input type=\"button\" onclick=\"" + this.mapname + ".directionsShow(form)\" value=\"" + mappressl10n.go + "\" />"
						 + '</form>';
					 break;

				default:
					directions = '<a href="#" onclick="' + this.mapname + ".renderMarker(" + i + ", 'to'); return false;\" >" + mappressl10n.directions + "</a>";
						 + '<form onSubmit=\"return false\">'
						 + '<input type="text" id="daddr" value="" />'
						 + '<input type="hidden" id="saddr" value="' + this.pois[i].corrected_address + '"/>'
						 + "<input type=\"button\" onclick=\"" + this.mapname + ".directionsShow(form)\" value=\"" + mappressl10n.go + "\" />"
						 + '</form>';
					 break;

			}
			html += '<div class="mapp-overlay-directions">' + directions + '</div>'
		}

		if (this.streetview == true)
			html 	+= "<a href=\"#\" onclick=\"" + this.mapname + ".streetviewShow(" + i + "); return false\">" + mappressl10n.street_view + "</a>";

		html += "</div>";	// mapp-overlay-div

		this.pois[i].marker.openInfoWindowHtml(html);
	},

	formatAddress : function (i) {

		var address = this.pois[i].address;
		var corrected_address = this.pois[i].corrected_address;

		switch (this.addressFormat) {
			case 'ENTERED':
				return address;
				break;
			case 'CORRECTED':
				// Strip USA
				if (corrected_address.lastIndexOf(', USA') > 0)
					corrected_address = corrected_address.slice(0, corrected_address.lastIndexOf(', USA'));

				// Add <br> between street and the rest of the address
				var first_comma = corrected_address.indexOf(',');

				if (first_comma > 0) {
					return corrected_address.slice(0, first_comma) + '<br/>' + corrected_address.slice(first_comma + 2, corrected_address.length);
				} else {
					return corrected_address;
				}
				break;

			default:
				return address;
				break;
		}
	},

	// Re-center and optionally re-zoom between ALL markers
	reCenter : function(i, reZoom) {
		var newCenter;
		var newZoom;
		var bounds = new GLatLngBounds();

		// Close any open infowindows
		this.map.closeInfoWindow();

		// If only 1 marker exists then center on it
		if (this.pois.length == 1)
			i = 0;

		// If a marker was given, or only 1 marker exists, then center on it
		if (i != null) {
			newCenter = this.pois[i].marker.getLatLng();

			// If no boundsbox is available [i.e. POIs from older versions], then fudge it by setting the bounds to just that marker
			bounds.extend(newCenter);

			// If a full boundsbox is available then use it	to set the zoom
			if (this.pois[i].boundsbox != undefined) {
				var boundsbox = this.pois[i].boundsbox;
				if (boundsbox.north != 0 && boundsbox.south != 0 && boundsbox.west != 0 && boundsbox.east != 0)
					bounds = new GLatLngBounds( new GLatLng(boundsbox.south, boundsbox.west), new GLatLng(boundsbox.north, boundsbox.east));
			}

			newZoom = this.map.getBoundsZoomLevel(bounds);
		} else {
		// If no specific marker then automatically center/zoom between all of them
			for (j=0; j< this.pois.length; j++)
				bounds.extend(this.pois[j].marker.getLatLng());
			newCenter = bounds.getCenter();
			newZoom = this.map.getBoundsZoomLevel(bounds);
		}

		this.map.setCenter(newCenter);

		// If we're re-zooming set the new zoom
		if (reZoom) {
			if (newZoom > 15)
				newZoom = 15;

			this.map.setZoom(newZoom);
		}
	}
}




// ---------------------------------------------------------------------------------------
// Editable maps
// ---------------------------------------------------------------------------------------

function mappEdit(json) {
	// Call ancestor constructor
	mapp.call(this, json);
}

// Inherit
extendObject (mappEdit.prototype, mapp.prototype);
extendObject (mappEdit.prototype, {
	// Post-constructor initialization
	init : function () {
		// Update hidden fields if user changes map preview
		var me = this;
		GEvent.addListener(this.map, "moveend", function() {
			me.center = me.map.getCenter();
			jQuery("#mapp_center_lat").val(me.center.lat());
			jQuery("#mapp_center_lng").val(me.center.lng());
		});

		GEvent.addListener(this.map, "zoomend", function() {
			me.zoom = me.map.getZoom();
			jQuery("#mapp_zoom").val(me.zoom);
		});

		GEvent.addListener(this.map, "maptypechanged", function() {
			me.mapType = me.map.getCurrentMapType().getName();
			jQuery("#mapp_maptype").val(me.mapType)
		});

		// Auto-center AFTER moveend listener is active, so we can record the new center
		this.automaticCenter();

		// List POIs
		if (this.editable == true)
			this.listMarkers();
	},

	// Add a POI
	addPOI : function(poi) {
		// Push the new POI into our array
		this.pois.push(poi);
		var i = this.pois.length - 1;

		// Set a default caption and body
		this.pois[i].caption = "";
		this.pois[i].body = this.formatAddress(i);

		// Add a marker for it
		this.addMarker(i);

		// Update the list of POIs
		editMap.listMarkers();

		// Open the infowindow
		this.reCenter(i, true);
		this.renderMarker(i);

		return i;
	},

	editMarker : function(i) {
		var width = this.map.getSize().width * 0.8;

		// Note: there is an IE8 bug that prevents us from using "width:100%" - it causes the cursor position to jump around
		// Instead I've used cols="120" and "max-width:100%" as a workaround
		var html    = '<div style="text-align: left; width: ' + width + 'px">'
					+ mappressl10n.title + ': <input type="text" id="markerCaption" rows="2" style="width: 90%" value="' + this.escapeQuotes(this.pois[i].caption) + '" />'
					+ '<br/><textarea id="markerBody" rows="5" cols="120" style="max-width:100%">' + this.pois[i].body + '</textarea>'
					+ "<br/><input type=\"button\" name=\"saveEditMarker\" value=\"" + mappressl10n.save + "\" onclick=\"editMap.saveEditMarker('" + i + "')\" />"
					+ "<input type=\"button\" name=\"cancelEditMarker\" value=\"" + mappressl10n.cancel + "\" onclick=\"editMap.cancelEditMarker('" + i + "')\" />"
					+ "</div>";

		this.pois[i].marker.openInfoWindowHtml(html);
	},

	saveEditMarker : function(i) {
		// Read the edited values
		var caption = jQuery("#markerCaption").val();
		var body = jQuery("#markerBody").val();

		// Update POI
		this.pois[i].caption = caption;
		this.pois[i].body = body;

		// Bind the POI's marker click event
		this.addMarkerEvents(i);

		// Re-render the marker
		this.renderMarker(i);
		this.listMarkers();
	},

	cancelEditMarker : function(i) {
		this.renderMarker(i);
	},

	listMarkers : function() {
		var html, caption;

		html	=	'<table id="mapp_poi_table" style="width: 100%;"> \r\n'
				+   '<tbody>';

		// Loop through POIs
		for (var i = 0; i < this.pois.length; i++ ) {
			// Display the POI in a list
			html    +=  '<tr style="padding: 0 0 0 0">'
					+   '<td style="width: 80%">'
					+   '<a id="mapp_poi_label" name="mapp_poi_label" style="width:90%; margin 0 0 0 0;" href="#" onclick="editMap.renderMarker(' + i + '); return false;">';

			// List markers by caption + corrected address
			if (this.pois[i].caption != "")
				html += this.pois[i].caption + ": " + this.pois[i].corrected_address;
			else
				html += this.pois[i].corrected_address;

			html += '</a>';

			// If the POI doesn't have a boundsbox then create the properties but leave it empty
			if (!this.pois[i].boundsbox)
				this.pois[i].boundsbox = {north : 0, south : 0, west : 0, east : 0};

			// Add hidden fields for post save
			html += '<input type="hidden" name="mapp_poi_address[]" value="' + this.pois[i].address + '"/>';
			html += '<input type="hidden" name="mapp_poi_caption[]" value="' + this.escapeQuotes(this.pois[i].caption) + '"/>';
			html += '<textarea style="display:none" name="mapp_poi_body[]">' + this.pois[i].body + '</textarea>';
			html += '<input type="hidden" name="mapp_poi_corrected_address[]" value="' + this.pois[i].corrected_address + '"/>';
			html += '<input type="hidden" name="mapp_poi_lat[]" size="15" value="' + this.pois[i].lat + '"/>';
			html += '<input type="hidden" name="mapp_poi_lng[]" size="15" value="' + this.pois[i].lng + '"/>';
			html += '<input type="hidden" name="mapp_poi_boundsbox_north[]" size="15" value="' + this.pois[i].boundsbox.north + '"/>';
			html += '<input type="hidden" name="mapp_poi_boundsbox_south[]" size="15" value="' + this.pois[i].boundsbox.south + '"/>';
			html += '<input type="hidden" name="mapp_poi_boundsbox_east[]" size="15" value="' + this.pois[i].boundsbox.east + '"/>';
			html += '<input type="hidden" name="mapp_poi_boundsbox_west[]" size="15" value="' + this.pois[i].boundsbox.west + '"/>';
			html +=	'</td></tr>';
		}

		html        +=  '</tbody>'
					+   '</table>';

		// List the POIs
		jQuery("#admin_poi_div").html(html);
	},

	// Escape double quotes so we can list field contents in input fields
	escapeQuotes: function(str) {
		return str.replace(/"/g, '&quot;');
		return str.replace(/'/g, "&apos;");
	},

	deleteMarker : function(i) {
		// Confirm we want to delete
		var result = confirm(mappressl10n.delete_this_marker);
		if (!result)
			return;

		// Adjust map.  Close any open infowindows, delete the marker's overlay
		this.map.closeInfoWindow();
		this.map.removeOverlay(this.pois[i].marker);

		// Remove the marker from our POI array
		this.pois.splice(i, 1);

		// List POIs
		this.listMarkers();

		// Re-bind all the marker click events, since markers are now renumbered
		for (var i = 0; i < this.pois.length; i++ )
			this.addMarkerEvents(i);
	},

	reSize : function(size, width, height) {
		// If a size is set, then use it, otherwise use the explicit width and height
		if (size && size != 'CUSTOM') {
			width = mapSizes[size].width;
			height = mapSizes[size].height;
		}

		// Resize the containing div to match the size (it may be changed by the user)
		this.mapDiv.style.width = width;
		this.mapDiv.style.height = height;
		if (this.map)
			this.map.checkResize();

		// Save the new values
		this.size = size;
		this.width = width;
		this.height = height;

		// Recenter the map
		this.reCenter(null, true);
	}
});



// ---------------------------------------------------------------------------------------
// Display maps
// ---------------------------------------------------------------------------------------
function mappDisplay(json) {
	this.initialOpenInfo = json.initialOpenInfo;
	this.directions = json.directions;
	this.traffic = 0;
	this.streetview = 0;

	// Set up the GoogleBar options
	if (this.googlebar == true) {
		this.mapOptions = {
			googleBarOptions : {
				style : "new",
				adsOptions: {
					client: "partner-pub-4213977717412159",
					channel: "mappress",
					adsafe: "high"
				}
			}
		}
	}

	// Call ancestor constructor
	mapp.call(this, json);
}

// Inherit
extendObject (mappDisplay.prototype, mapp.prototype);
extendObject (mappDisplay.prototype, {
	// Post-constructor init
	init : function () {

		this.automaticCenter();

		if (this.streetview) {
			this.streetDiv = document.getElementById(this.mapname + '_street_div');
			this.streetOuterDiv = document.getElementById(this.mapname + '_street_outer_div');
			this.streetviewPanorama;
		}

		if (this.directions) {
			this.directionsDiv = document.getElementById(this.mapname + '_directions_div');
			this.directionsOuterDiv = document.getElementById(this.mapname + '_directions_outer_div');
			this.saddr = document.getElementById(this.mapname + '_saddr');
			this.daddr = document.getElementById(this.mapname + '_daddr');
			this.saddrCorrected = document.getElementById(this.mapname + '_saddr_corrected');
			this.daddrCorrected = document.getElementById(this.mapname + '_daddr_corrected');
			this.GDirections = new GDirections(this.map, this.directionsDiv);
			// Process errors; 'this' = directions object
			GEvent.addListener(this.GDirections, "error", function() {
				switch (this.getStatus().code) {
					case 400:
						alert(mappressl10n.dir_400);
						break;
					case 500:
						alert(mappressl10n.dir_500);
						break;
					case 601:
						alert(mappressl10n.dir_601);
						break;
					case 602:
						alert(mappressl10n.dir_602);
						break;
					case 603:
						alert(mappressl10n.dir_603);
						break;
					case 604:
						alert(mappressl10n.dir_604);
						break;
					case 610:
						alert(mappressl10n.dir_610);
						break;
					case 620:
						alert(mappressl10n.dir_620);
						break;
					default:
						alert(mappressl10n.dir_default) + getStatus().code;
						break;
				}
			} );
		}

		// Enable GoogleBar
		if (this.googlebar == true)
			this.map.enableGoogleBar();

		// Traffic control; set distance from right-hand side based on whether map types are being displayed
		if (this.traffic == true) {
			if (this.mapTypes == true)
				this.map.addControl(new ExtMapTypeControl({showMapTypes: false, posRight: 100, showTraffic: true, showTrafficKey: true, showMore: false}));
			else
				this.map.addControl(new ExtMapTypeControl({showMapTypes: false, posRight: 10, showTraffic: true, showTrafficKey: true, showMore: false}));
		}

		// Display: open initial infoWindow
		if (this.initialOpenInfo == true) {
			GEvent.trigger(this.pois[0].marker, "click");
		}
	},

	// Show directions
	// From, to are optional - use only when calling from infoWindow
	directionsShow : function(form) {
		// Close everything
		this.streetviewClose();
		this.directionsClose();
		this.map.closeInfoWindow();

		// Hide all markers
		for ( var i = 0; i < this.pois.length; i++ )
			this.pois[i].marker.hide();

		// If from/to values provided copy them to the form
		this.saddr.value = form.saddr.value;
		this.daddr.value = form.daddr.value;

		// Display the directions <div>
		this.directionsOuterDiv.style.display = 'block';

		// Get directions
		this.directionsGet();
	},

	directionsGet : function() {
		// Clear any error class from the source/dest address fields
		this.saddr.className = 'mapp-address';
		this.daddr.className = 'mapp-address';

		// Check that a source address was entered
		if (!this.saddr.value || this.saddr.value == '') {
			this.saddr.className = 'mapp-address-error';
			this.saddrCorrected.innerHTML = mappressl10n.enter_address;
			return;
		}

		// Check that a dest address was entered
		if (!this.daddr.value || this.daddr.value == '') {
			this.daddr.className = 'mapp-address-error';
			this.daddrCorrected.innerHTML = mappressl10n.enter_address;
			return;
		}

		// Capture check function name for closure
		var me = this;

		// Validate the source/dest address
		// Note: closure inside closure in order serialize checks and directions.load()
		mappGeocoder.getLocations(this.saddr.value, function(response) {
			me.addressCheck(response, me.saddr, me.saddrCorrected, "saddr");
			mappGeocoder.getLocations(me.daddr.value, function(response) {
				me.addressCheck(response, me.daddr, me.daddrCorrected, "daddr");
				me.GDirections.load("from: " + me.saddr.value + " to: " + me.daddr.value );
			});
		});


	},

	addressCheck : function(response, addr, addrCorrected, addrFieldName) {
		if (response == null || response.Placemark == null || response.Status.code != 200) {
			addr.className = 'mapp-address-error';
			addrCorrected.innerHTML = mappressl10n.no_address;
			return false;
		}

		if (response.Placemark.length > 1) {
			var suggestedAddress = response.Placemark[0].address;
			addrCorrected.innerHTML = mappressl10n.did_you_mean
				+ "<a href='#' onclick='" + this.mapname + ".addressAccept(\"" + addrFieldName + "\", \"" + suggestedAddress + "\"); "
				+ "return false;'>" + suggestedAddress + "</a>";
			addr.className = 'mapp-address-error';
			return false;
		}

		// No error
		addr.value = response.Placemark[0].address;

		// Clear any error messages
		addrCorrected.innerHTML = '';
		addrCorrected.className = 'mapp-address';
		return true;
	},

	addressAccept : function(addrFieldName, suggestedAddress) {

		if (addrFieldName == "saddr") {
			this.saddr.value = suggestedAddress;
			this.saddrCorrected.innerHTML = "";
			this.saddr.className = "mapp-address";
		}

		else {
			this.daddr.value = suggestedAddress;
			this.daddrCorrected.innerHTML = "";
			this.daddr.className = "mapp-address";
		}

		this.directionsGet();
	},

	//
	// Print directions
	// 'form' = the main directions form
	directionsPrint : function() {
		// Get the elements of the main directions form
		var saddr = document.getElementById(this.mapname + '_saddr');
		var daddr = document.getElementById(this.mapname + '_daddr');

		var url = 'http://maps.google.com';

		url += '?daddr=' + daddr.value;
		url += '&saddr=' + saddr.value;
		url += '&pw=2';

		window.open(url)
	},

	directionsClose : function() {
		if (this.GDirections)
			this.GDirections.clear();
		this.directionsOuterDiv.style.display = 'none';

		// Restore our markers when directions are closed
		for ( var i = 0; i < this.pois.length; i++ )
			this.pois[i].marker.show();

		// Recenter and re-zoom
		this.reCenter(null, true);
	},

	streetviewShow : function(i) {
		// Close any existing street views and directions
		this.streetviewClose();
		this.GDirectionsClose();

			// Set options and create street view
		var streetviewOptions = { latlng : this.pois[i].marker.getLatLng() };
		this.streetviewPanorama = new GStreetviewPanorama(this.streetDiv, streetviewOptions);

		GEvent.addListener(this.streetviewPanorama, "error", this.streetviewError);

		// Note: there's no way to tell if street view creation was successful
		// Waiting for google to fix the 'initialized' event on GStreetviewPanorama
		// For now, just assume it was successful

		// Display street view <div>
		this.streetOuterDiv.style.display = 'block';
	},

	streetviewClose : function() {
		if (this.streetviewPanorama)
			this.streetviewPanorama.remove();
		if (this.streetOutderDiv)
			this.streetOuterDiv.style.display = 'none';
	},

	streetviewError : function(errorCode) {
		switch (errorCode) {
			case 603:
				alert(mappressl10n.street_603);
				break;
			case 600:
				alert(mappressl10n.street_600);
				break;
			default:
				alert(mappressl10n.street_default);
				break;
		}
	}
});

// Utility function to extend objects
function extendObject (destination, source) {
	for (var property in source)
		destination[property] = source[property];
	return destination;
}
