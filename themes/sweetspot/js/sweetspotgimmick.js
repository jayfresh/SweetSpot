(function($){

jQuery(document).ready(function(){
	var svgns = "http://www.w3.org/2000/svg";
	var place = document.getElementById("sweetspotIcon")
	var options = {width: 55, height: 55};
	var svgDoc;
	var srcText = jQuery.ajax({url:"rosetta.svg", dataType: "text", success: function(srcText) {
		// console.log(srcText);
		if (window.DOMParser) {
			svgDoc = new DOMParser().parseFromString(srcText, "application/xml").documentElement;
			var el;
			el = $(document.importNode(svgDoc, true))[0];
			if(!el) {
				return;
			}
			var svgHolder = document.createElementNS(svgns,"svg");
			var width = options.width;
			var height = options.height;
			if(width || height) {
				if(width && height) {
					// set view box of containing svg element based on the svg viewbox and width and height.
					var viewBox = el.getAttribute("viewBox");
					var topLeft = "0 0";
					if(viewBox) {
						topLeft = viewBox.replace(/([0-9]*) +([0-9]*) +([0-9]*) +([0-9]*) */gi,"$1 $2");
					}
					svgHolder.setAttributeNS(svgns, "viewBox", "0 0 " + width + " " + height);
				} else {
					if(!width) {
						width = el.getAttribute("width");
					}
					if(!height) {
						height = el.getAttribute("height");
					}
				}
				svgHolder.setAttribute("width", width);
				svgHolder.setAttribute("height", height);

				el.setAttribute("width", "100%");
				el.setAttribute("height", "100%");
				svgHolder.setAttribute("class", "svgIcon");
				svgHolder.appendChild(el);
				place.appendChild(svgHolder);
			
				//v = u  + at
				var rosetta = document.getElementById("svg_rosetta");
				var startInterval = 1; // measured in miliseconds
				var velocity = 0, interval = startInterval; // pixels per milisecond
				var INITIAL_ACCELERATION = -0.2;
				var acceleration = INITIAL_ACCELERATION; // pixels per milisecond
				var distanceTravelled = 0; // in pixels
			
				var sweetspot = document.getElementById("svg_sweetspot");
				var click = function(ev) {
					velocity = 100;
					acceleration = INITIAL_ACCELERATION;
				};
				
				$(sweetspot).click(click);
			
				var CIRCUMFERENCE_ROSETTA = Math.PI * 2 * 400; // 2 pi r = 360 degrees in pixels
				var DISTANCE_OF_DEGREE = CIRCUMFERENCE_ROSETTA / 360;
				var spin = function() {
					velocity = velocity + (acceleration * interval);
					if(velocity <= 0) {
						acceleration = 0;
						velocity = 0;
					}
					distanceTravelled +=  (velocity * interval); // total distance in pixels
					angle = (distanceTravelled / DISTANCE_OF_DEGREE)  % 360;
					rosetta.setAttribute("transform", "rotate("+angle+" 400 400)");
					// console.log("per degree"+DISTANCE_OF_DEGREE+"\ndistance="+distanceTravelled+"\nangle="+angle+"\nspeed = "+velocity +"\ninterval = " + interval+"\nacc= "+acceleration);
				};
				
				window.setInterval(function() {
					acceleration = acceleration * 0.75;
				}, 2000);
				var intervalFn = window.setInterval(spin, interval);
				
			}
		}
	}});
});
})(jQuery);	