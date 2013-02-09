
$(document).ready(function(){
  tracker.initialize();
});


var tracker = function(){
  var map;
  var osmLayer;
  var peruskarttaLayer;
  var markerLayer;
  
  var routeStyle = new OpenLayers.Style({
    strokeWidth: 7,
    strokeColor: "#93C740",
    strokeOpacity: 0.7
  });
  
  function initialize(){
    map = new OpenLayers.Map('map');
    map.addControl(new OpenLayers.Control.LayerSwitcher());
    
    addPeruskarttaLayer();
    addOSMLayer();
    getAllPoints();
    addLastPoint();
    
    map.setCenter(new OpenLayers.LonLat(23.177327, 61.666618).transform(
        new OpenLayers.Projection("EPSG:4326"),
        map.getProjectionObject()), 16
    );
      
    selectControl = new OpenLayers.Control.SelectFeature()
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
    var geoJSONlayer = new OpenLayers.Layer.Vector("Reitti", {
      strategies: [new OpenLayers.Strategy.Fixed()],
      protocol: new OpenLayers.Protocol.HTTP({
        url: "service/geoJSON.php?featureType=route",
        format: new OpenLayers.Format.GeoJSON()
      }),
      styleMap: new OpenLayers.StyleMap(routeStyle)
    });
    
    map.addLayer(geoJSONlayer);
  }
  
  function addLastPoint(){
    markerLayer = new OpenLayers.Layer.Markers("Paikat");
    map.addLayer(markerLayer);
    
    $.getJSON("service/geoJSON.php?featureType=currentLocation", function(data){
      var coord = data.features[0].geometry.coordinates;
      var marker = new OpenLayers.Marker(new OpenLayers.LonLat(coord[0], coord[1]).transform(
        new OpenLayers.Projection("EPSG:4326"),
        map.getProjectionObject()), getMarkerIcon());
      
      marker.events.register('mousedown', marker, function(evt){
        onMarkerSelect(marker);
        OpenLayers.Event.stop(evt);
      });
      
      markerLayer.addMarker(marker);
    });
  }
  
  function onMarkerSelect(marker){
    var text = "Nykyinen sijainti";
    
    var popup = new OpenLayers.Popup.FramedCloud("popup",
      marker.lonlat, null, text, null, true);
    map.addPopup(popup);
  }
  
  function getMarkerIcon(){
    var size = new OpenLayers.Size(29,30);
    var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
    return new OpenLayers.Icon('http://www.openlayers.org/dev/img/marker.png',size,offset);
  }
  
  return {
    initialize: initialize,
    addPeruskarttaLayer: addPeruskarttaLayer,
    addOSMLayer: addOSMLayer,
    getAllPoints: getAllPoints
  }
}();