var imagePath = 'images/';

//this is the array to store our custom infobox objects in
ibArray = [];
markerArray = [];
hiddenMarkerArray = [];
var styles = [
  {
   stylers: [
   { hue: "#000000" },
   { saturation: -95 },
  {gamma: 0.50}
  ]
  }
  ];

function load_map(data) {
	
	markerArray = [];
	
	//var data = eval('(' + data + ')');
	if(data.length > 0)
	{
		var maxDistance = data[data.length - 1].distance;
	
	
	
		centerLat = data[0]['details']['lat'];
		centerLong = data[0]['details']['long'];
		var latlng = new google.maps.LatLng(centerLat, centerLong);
		
		var zoomLevel;
		if(maxDistance > 50)
		{
			zoomLevel = 9
		} 
		else if(maxDistance > 20)
		{
			zoomLevel = 10
		}
		else if(maxDistance > 10)
		{
			zoomLevel = 11
		}
		else
		{
			zoomLevel = 12
		}


    var myOptions = {
      zoom: 14,
      center: new google.maps.LatLng(Number(centerLat) + 0.0032, Number(centerLong) - 0.01),
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      draggable: false,
      scrollwheel: false,
      styles: styles,
      disableDefaultUI: true
    };
		var map = new google.maps.Map(document.getElementById("map_canvas"),myOptions);
		
		
		google.maps.event.addListener(map, "click", function() {
			if(ibArray.length != 1){
			unsetIb = ibArray.length - 2 ;
			ibArray[unsetIb].box.setMap(null);
			};
		});
		
		for(var i = 0, m; m = data[i]; i++) {
			
			var latlng = new google.maps.LatLng(m.details.lat, m.details.long);
			var j = i + 1;
			var message = '';
        message = message + '<a href="'+m.details.imageLink+'" target="_blank"><img src="'+m.details.imageUrl+'" alt=""></a>';
				//message = message + '<span class="num">'+ j + '</span>';
				//message = message + '<div class="details">';
				//message = message + '<h4>'+ m.details.storename +'</h4>';
				//message = message + '<p>'+ m.details.address +',<br />'+ m.details.state +' '+ m.details.postcode +',<br />'+m.details.country+'</p>';
				//message = message + '<h4>Telephone</h4>';
				//message = message + '<p>'+ m.details.telephone +'</p>';
				
				if(typeof(m.details.url)!='undefined'){
					
				message = message + '<span class="link"><a href="'+m.details.url+'">'+m.details.url+'</a></span>';
				}
				message = message + '</div>'
			createMarker(latlng, m.details.storename, message, j, map);
		}
	}
	
}

/* An InfoBox is like an info window, but it displays
 * under the marker, opens quicker, and has flexible styling.
 * @param {GLatLng} latlng Point to place bar at
 * @param {Map} map The map on which to display this InfoBox.
 * @param {Object} opts Passes configuration options - content,
 *   offsetVertical, offsetHorizontal, className, height, width
 */

 
function CustomMarker(latlng,  map, id) {
    this.latlng_ = latlng;
	this.id_ = id;
	
    // Once the LatLng and text are set, add the overlay to the map.  This will
    // trigger a call to panes_changed which should in turn call draw.
    this.setMap(map);
}

  CustomMarker.prototype = new google.maps.OverlayView();

  CustomMarker.prototype.draw = function() {
    var me = this;

    // Check if the div has been created.
    var div = this.div_;
    if (!div) {
      // Create a overlay text DIV
      div = this.div_ = document.createElement('div');
      // Create the DIV representing our CustomMarker
      div.style.border = "none";
      div.style.position = "absolute";
      div.style.zIndex = "99999999";
      div.style.paddingLeft = "0px";
      div.style.cursor = 'pointer';
	  
	  var zind = 100 - this.id_;
	  
      //div.innerHTML = "<span class='pointer' ><a style='z-index:"+zind+"' href='#'>"+this.id_ +"</a></span>";
      div.innerHTML = "<span class='pointer' ><a style='z-index:"+zind+"' href='#'><img src='"+imagePath+"marker.png' /></a></span>";
      google.maps.event.addDomListener(div, "click", function(event) {
        google.maps.event.trigger(me, "click");
      });

      // Then add the overlay to the DOM
      var panes = this.getPanes();
      panes.overlayImage.appendChild(div);
    }

    // Position the overlay 
    var point = this.getProjection().fromLatLngToDivPixel(this.latlng_);
    if (point) {
	  div.style.left = point.x + 7 + 'px';
      div.style.top = point.y - 37 + 'px';
	}
  };

  CustomMarker.prototype.remove = function() {
    // Check if the overlay was on the map and needs to be removed.
    if (this.div_) {
      this.div_.parentNode.removeChild(this.div_);
      this.div_ = null;
    }
  };

  CustomMarker.prototype.getPosition = function() {
   return this.latlng_;
};
  
function InfoBox(opts) {
  google.maps.OverlayView.call(this);
  this.latlng_ = opts.latlng;
  this.map_ = opts.map;
  this.offsetVertical_ = -185;
  this.offsetHorizontal_ = -28;
  this.height_ = 310; //187
  this.width_ = 547; //199
  this.content_ = opts.content;
  
  var me = this;
  this.boundsChangedListener_ =
    google.maps.event.addListener(this.map_, "bounds_changed", function() {
      return me.panMap.apply(me);
    });

  // Once the properties of this OverlayView are initialized, set its map so
  // that we can display it.  This will trigger calls to panes_changed and
  // draw.
  this.setMap(this.map_);
}

/* InfoBox extends GOverlay class from the Google Maps API
 */
InfoBox.prototype = new google.maps.OverlayView();

/* Creates the DIV representing this InfoBox
 */
InfoBox.prototype.remove = function() {
  if (this.div_) {
    this.div_.parentNode.removeChild(this.div_);
    this.div_ = null;
  }
};

/* Redraw the Bar based on the current projection and zoom level
 */
InfoBox.prototype.draw = function() {
  // Creates the element if it doesn't exist already.
  this.createElement();
  if (!this.div_) return;

  // Calculate the DIV coordinates of two opposite corners of our bounds to
  // get the size and position of our Bar
  var pixPosition = this.getProjection().fromLatLngToDivPixel(this.latlng_);
  if (!pixPosition) return;

  // Now position our DIV based on the DIV coordinates of our bounds
  this.div_.style.width = this.width_ + "px";
  this.div_.style.left = (pixPosition.x + this.offsetHorizontal_) + "px";
  this.div_.style.height = this.height_ + "px";
  this.div_.style.top = (pixPosition.y + this.offsetVertical_) + "px";
  this.div_.style.display = 'block';
};

/* Creates the DIV representing this InfoBox in the floatPane.  If the panes
 * object, retrieved by calling getPanes, is null, remove the element from the
 * DOM.  If the div exists, but its parent is not the floatPane, move the div
 * to the new pane.
 * Called from within draw.  Alternatively, this can be called specifically on
 * a panes_changed event.
 */
InfoBox.prototype.createElement = function() {
  var panes = this.getPanes();
  var div = this.div_;
  if (!div) {
    // This does not handle changing panes.  You can set the map to be null and
    // then reset the map to move the div.
    div = this.div_ = document.createElement("div");
    div.style.border = "0px none";
    div.style.position = "absolute";
    div.style.background = "url('"+imagePath+"bubble-bg.png')";
	div.style.width = this.width_ + "px";
    div.style.height = this.height_ + "px";
	div.style.padding = "20px 20px 30px 20px";
	div.style.textAlign = "left";
	div.setAttribute("class", 'bubble'); //For Most Browsers
	div.setAttribute("className", 'bubble'); //For IE; harmless to other browsers.
    /*var contentDiv = document.createElement("div");
    contentDiv.style.padding = "20px";*/
    div.innerHTML = this.content_;

    var topDiv = document.createElement("div");
    topDiv.style.textAlign = "left";
	topDiv.style.float = "right";
	document.getElementById("div")
    var closeImg = document.createElement("img");
    closeImg.style.width = "32px";
    closeImg.style.height = "32px";
    closeImg.style.cursor = "pointer";
	closeImg.style.position = "absolute";
  closeImg.style.display = "none";
	closeImg.style.top = "0";
	closeImg.style.right = "0";
    // closeImg.src = imagePath+"closebigger.gif";
    topDiv.appendChild(closeImg);
	
	function removeInfoBox(ib) {
      return function() {
		setPrevMarker(ib.map_, 1);
		ib.setMap(null);
		};
    }

    google.maps.event.addDomListener(closeImg, 'click', removeInfoBox(this));
	
    div.appendChild(topDiv);
    div.style.display = 'none';
    panes.floatPane.appendChild(div);
    this.panMap();
  } else if (div.parentNode != panes.floatPane) {
    // The panes have changed.  Move the div.
    div.parentNode.removeChild(div);
    panes.floatPane.appendChild(div);
  } else {
    // The panes have not changed, so no need to create or move the div.
  }
}

/* Pan the map to fit the InfoBox.
 */
InfoBox.prototype.panMap = function() {
  // if we go beyond map, pan map
  var map = this.map_;
  var bounds = map.getBounds();
  if (!bounds) return;

  // The position of the infowindow
  var position = this.latlng_;

  // The dimension of the infowindow
  var iwWidth = this.width_;
  var iwHeight = this.height_;

  // The offset position of the infowindow
  var iwOffsetX = this.offsetHorizontal_;
  var iwOffsetY = this.offsetVertical_;

  // Padding on the infowindow
  var padX = 40;
  var padY = 40;

  // The degrees per pixel
  var mapDiv = map.getDiv();
  var mapWidth = mapDiv.offsetWidth;
  var mapHeight = mapDiv.offsetHeight;
  var boundsSpan = bounds.toSpan();
  var longSpan = boundsSpan.lng();
  var latSpan = boundsSpan.lat();
  var degPixelX = longSpan / mapWidth;
  var degPixelY = latSpan / mapHeight;

  // The bounds of the map
  var mapWestLng = bounds.getSouthWest().lng();
  var mapEastLng = bounds.getNorthEast().lng();
  var mapNorthLat = bounds.getNorthEast().lat();
  var mapSouthLat = bounds.getSouthWest().lat();

  // The bounds of the infowindow
  var iwWestLng = position.lng() + (iwOffsetX - padX) * degPixelX;
  var iwEastLng = position.lng() + (iwOffsetX + iwWidth + padX) * degPixelX;
  var iwNorthLat = position.lat() - (iwOffsetY - padY) * degPixelY;
  var iwSouthLat = position.lat() - (iwOffsetY + iwHeight + padY) * degPixelY;

  // calculate center shift
  var shiftLng =
      (iwWestLng < mapWestLng ? mapWestLng - iwWestLng : 0) +
      (iwEastLng > mapEastLng ? mapEastLng - iwEastLng : 0);
  var shiftLat =
      (iwNorthLat > mapNorthLat ? mapNorthLat - iwNorthLat : 0) +
      (iwSouthLat < mapSouthLat ? mapSouthLat - iwSouthLat : 0);

  // The center of the map
  var center = map.getCenter();

  // The new map center
  var centerX = center.lng() - shiftLng;
  var centerY = center.lat() - shiftLat;

  // center the map to the new shifted center
  map.setCenter(new google.maps.LatLng(centerY, centerX));

  // Remove the listener after panning is complete.
  google.maps.event.removeListener(this.boundsChangedListener_);
  this.boundsChangedListener_ = null;
};

function clearOverlays() {
  if (markersArray) {
	for (i in markersArray) {
	  markersArray[i].setMap(null);
	}
  }
}
  
function createMarker(point, name, html, id, map) {
	
  var g = google.maps;

	
	var marker = new CustomMarker(point, map, id);
	markerArray.push({box: marker});
	
	g.event.addListener(marker, "click", function(e) {
		
		
		var ibOptions = {latlng: marker.getPosition(), map: map, content:html};
		var infoBox = new InfoBox(ibOptions);
		ibArray.push({myId:id,box: infoBox});
		
		map.setCenter(marker.getPosition());
		//hide the current marker
		marker.setMap(null);
		//store the current marker in array
		hiddenMarkerArray.push({marker:marker});
		
		setPrevMarker(map);
		
		
		
		var location = id - 1;
	});
    
	//if(id == 1){
		//google.maps.event.trigger(marker, "click");
		//var ibOptions = {latlng: point, map: map, content:html};
		//var infoBox = new InfoBox(ibOptions);
	//}
}
  
function locationClick(location){
	location = location - 1;
	var marker = markerArray[location];
	
	google.maps.event.trigger(marker.box, "click");
	if(ibArray.length != 1){
		unsetIb = ibArray.length - 2 ;
		ibArray[unsetIb].box.setMap(null);
	};
	
}

function setPrevMarker(map, close){
	if(hiddenMarkerArray.length>1 || close == 1){
		hiddenMarkerArray[0].marker.setMap(map);
		hiddenMarkerArray.shift();
	}	
}