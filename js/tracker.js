
$(document).ready(function(){
    $("#map").height($(window).height() - 100);
    $("#map").width($(window).width() - 40);
    tracker.initialize();
});


var tracker = function(){
    
    var map;
    var marker;
    var fullRoute;
    
    var peruskarttaTiles = 'http://tiles.kartat.kapsi.fi/peruskartta/{z}/{x}/{y}.jpg';
    var attribution = '© <a href="http://www.maanmittauslaitos.fi/avoindata_lisenssi_versio1_20120501">Maanmittauslaitos</a>';
    
    var routeOptions = {
        color: 'green',
        opacity: 0.6,
        weight: 8
    }
    
    function initialize(){
        map = L.map('map').setView([60.1852, 24.9593], 15);
        
        L.tileLayer(peruskarttaTiles, {
            attribution: attribution,
            maxZoom: 16
        }).addTo(map);
        
        map.on('click', mapClick);
        
        getTestRoute();
    }
    
    
    function mapClick(e){
        console.log(e.latlng);
    }
    
    function getTestRoute(){
        $.getJSON('http://www.karttalehtinen.fi/ilveshiihto/points.php', function(data){
            lastPoint(data);
            drawRoute(data);
        });
    }
    
    // Draws full route
    function drawRoute(data){
        var points = [];
        
        for(var i = 0; i < data.length; i++){
            points.push(new L.LatLng(data[i].lat, data[i].long));
        }
        fullRoute = L.polyline(points, routeOptions).addTo(map);
    }
    
    // Draws marker for last point and puts time on popup
    function lastPoint(data){
        var lastPoint = data[data.length-1];
        map.panTo(new L.LatLng(lastPoint.lat, lastPoint.long));
        marker = L.marker([lastPoint.lat, lastPoint.long]).addTo(map);
            
        marker.bindPopup("Viimeisin sijainti. Klo: " + formatDate(lastPoint.time * 1000) );
    }
    
    function formatDate(timestamp){
        var time = new Date(parseInt(timestamp));
        
        var minutes = formatLeadingZeros(time.getMinutes());
        var hours = formatLeadingZeros(time.getHours());
        var seconds = formatLeadingZeros(time.getSeconds());
        
        var timeStr = hours + ":" + minutes + ":" + seconds;
        
        function formatLeadingZeros(value){
            return (value < 10 ? "0" + value : value);
        }
        
        return timeStr;
    }
    
    // Activates fullscreen mode
    function fullscreen(){
        var $mapDiv = $("#map");
        $mapDiv.show();
        $mapDiv.css('top', 0);
        $mapDiv.css('left', 0);
        $mapDiv.css('height', $(window).height());
        $mapDiv.css('width', $(window).width());
    }
    
    return {
        initialize: initialize,
        fullscreen: fullscreen
    }
}();