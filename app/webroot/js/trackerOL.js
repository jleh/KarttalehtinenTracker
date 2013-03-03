
$(document).ready(function(){
  tracker.initialize();
});


var tracker = function(){
  var map;
  var osmLayer;
  var peruskarttaLayer;
  var markerLayer;
  var geoJSONlayer;
  
  var routeStyle = new OpenLayers.Style({
    strokeWidth: 7,
    strokeColor: "#DE6514",
    strokeOpacity: 0.7
  });
  
  var format = new OpenLayers.Format.GeoJSON();
  var projection = new OpenLayers.Projection("EPSG:4326");
  
  var updateInterval;
  var imageUpdater;
  var drawerInterval;
  
  var lastPoint = {
    time: 0,
    marker: undefined
  };
  
  var lastImageTime = 0;
  
  var pointQueue = [];
  
  function initialize(){
    map = new OpenLayers.Map('map');
    map.addControl(new OpenLayers.Control.LayerSwitcher());
    
    addPeruskarttaLayer();
    addOSMLayer();
    getAllPoints();
    addLastPoint();
    getImages();
    
    map.setCenter(new OpenLayers.LonLat(23.177327, 61.666618).transform(
        projection,
      map.getProjectionObject()), 16
    );
 
    updateInterval = setInterval(function() {updateRoute();}, 10000);
    imageUpdater = setInterval(getNewImages, 10000);
    drawerInterval = setInterval(drawNewRoute, 3000);
  }
  
  function addPeruskarttaLayer(){
    peruskarttaLayer = new OpenLayers.Layer.MML("Peruskartta");
    map.addLayer(peruskarttaLayer);
  }
  
  function addOSMLayer(){
    osmLayer = new OpenLayers.Layer.OSM("OpenStreetMap");
    map.addLayer(osmLayer);
  }
  
  function getAllPoints() {
    geoJSONlayer = new OpenLayers.Layer.Vector("Reitti", {
      strategies: [new OpenLayers.Strategy.Fixed()],
      protocol: new OpenLayers.Protocol.HTTP({
        url: "points/route.json",
        format: format
      }),
      styleMap: new OpenLayers.StyleMap(routeStyle)
    });
    
    map.addLayer(geoJSONlayer);
  }
  
  function addLastPoint(){
    markerLayer = new OpenLayers.Layer.Markers("Paikat");
    map.addLayer(markerLayer);
    
    $.getJSON("points/dummy.json", function(data){
      var coord = data.features[0].geometry.coordinates;
      var marker = new OpenLayers.Marker(new OpenLayers.LonLat(coord[0], coord[1]).transform(
        projection, map.getProjectionObject()), getMarkerIcon());
      
      marker.events.register('mousedown', marker, function(evt){
        onMarkerSelect(marker);
        OpenLayers.Event.stop(evt);
      });
      
      lastPoint.time = data.features[0].properties.time;
      lastPoint.marker = marker;
      
      // Default location in Finland
      if(coord[0] === 0 && coord[1] === 0){
        map.setCenter(new OpenLayers.LonLat(24.933064, 60.168617).transform(
        projection, map.getProjectionObject()) ,16);
      }
      else {
        map.setCenter(marker.lonlat ,16);
      }
      
      markerLayer.addMarker(marker);
    });
  }
  
  function onMarkerSelect(marker){
    var text = "Viimeisin sijaintitieto klo: ";
    var time = new Date(lastPoint.time*1000);
    text += ((time.getHours() + 2) < 10 ? "0" + (time.getHours() + 2) : (time.getHours() + 2));
    text += ":" + (time.getMinutes() < 10 ? "0" + time.getMinutes() : time.getMinutes());
    
    var popup = new OpenLayers.Popup.FramedCloud("popup",
      marker.lonlat, null, text, null, true);
    map.addPopup(popup);
  }
  
  function getMarkerIcon(){
    var size = new OpenLayers.Size(29,30);
    var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
    return new OpenLayers.Icon('http://www.openlayers.org/dev/img/marker.png',size,offset);
  }
  
  // Update route on map
  function updateRoute(){
    if(pointQueue.length < 10)
      getNewRoute();
  }
  
  // Get new track points after alredy loaded
  function getNewRoute(){
    $.getJSON("points/dummy.json" + lastPoint.time, function(data){
      if(data.features[0].geometry.coordinates.length < 3){
        return;
      }
      
      var coords = data.features[0].geometry.coordinates;
      for(var i = 0; i < coords.length; i++){
        pointQueue.push(coords[i]);
      }
      
      lastPoint.time = data.features[0].properties.time;
    });
  }
  
  function updateLastPoint(feature){
    if(feature.geometry.coordinates == undefined){
      return;
    }
    
    var coords = feature.geometry.coordinates;
    var px = map.getPixelFromLonLat(new OpenLayers.LonLat(coords[0], coords[1]).transform(
            projection, map.getProjectionObject()));

    lastPoint.marker.moveTo(px);
    lastPoint.time = feature.properties.time;
  }
  
  // Add points to route from queue
  function drawNewRoute(){
    if(pointQueue.length == 0){
      return;
    }
    
    var geometry = geoJSONlayer.features[0].geometry;
    var nextPoint = pointQueue.shift();

    geometry.addPoint(new OpenLayers.Geometry.Point(nextPoint[0], nextPoint[1]).transform(
            projection, map.getProjectionObject()));
    geoJSONlayer.redraw();
    
    var px = map.getLayerPxFromViewPortPx(map.getPixelFromLonLat(new OpenLayers.LonLat(nextPoint[0], nextPoint[1]).transform(
            projection, map.getProjectionObject())));
    lastPoint.marker.moveTo(px);
  }
  
  function centerToCurrentLocation(){
    map.panTo(lastPoint.marker.latlon);
  }
  
  
  function getImages(){
    $.getJSON("points/dummy.json", addImagesToMap);
  }
  
  function getNewImages(){
    $.getJSON("points/dummy.json" + lastImageTime, addImagesToMap);
  }
  
  function addImagesToMap(data){
    var size = new OpenLayers.Size(23,18);
    var offset = new OpenLayers.Pixel(0,0);
    
    if(data == null)
      return;
    
    for(var i = 0; i < data.length; i++){
        var coords = new OpenLayers.LonLat(data[i].lon, data[i].lat).transform(projection, map.getProjectionObject());
        var marker = new OpenLayers.Marker(coords, new OpenLayers.Icon('img/photo.png', size, offset));
        marker.imageURL = data[i].image;
        marker.imageComment = data[i].comment;
        marker.events.register('mousedown', marker, function(evt){
          var text = '<img class="popupImg" src="' + this.imageURL + '"><br>' + this.imageComment;
          
          var popup = new OpenLayers.Popup.FramedCloud("popup",
          this.lonlat, null, text, null, true);
          map.addPopup(popup);
          
          OpenLayers.Event.stop(evt);
        });
        
        markerLayer.addMarker(marker);
        lastImageTime = data[i].time;
    }
  }
  
  return {
    initialize: initialize,
    addPeruskarttaLayer: addPeruskarttaLayer,
    addOSMLayer: addOSMLayer,
    getAllPoints: getAllPoints,
    updateRoute: updateRoute,
    centerToCurrentLocation: centerToCurrentLocation,
    getImages: getImages,
    drawNewRoute: drawNewRoute
  };
}();