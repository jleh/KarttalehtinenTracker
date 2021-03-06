/* global $, L, Mustache */

$(document).ready(function(){
    tracker.initialize();
});

var API_SERVER = "http://karttalehtinen.fi/tracker2013/";
//var API_SERVER = "http://karttalehtinen.fi/ilveshiihto16/";
//var API_SERVER = "";

var tracker = function() {
    "use strict";

    var map;
    var geoJSONlayer;
    var lastPointMarker;
    var loadedTweets;

    var placeMarkers = {};

    var lastPoint = {
        time: 0,
        marker: undefined
    };
    
    var lastImageTime = 0;
    
    var pointQueue = [];
    
    function initialize() {
        map = L.map("map", {
            crs: L.TileLayer.MML.get3067Proj()
        }).setView([61.5, 25.8], 9);

        L.tileLayer.mml_wmts({ layer: "maastokartta", opacity: 0.70 }).addTo(map);

        getAllPoints();
        addLastPoint();
        getImages();
        getPlaceMarkers();
        getTweets();

        setPlaceLinks();

        setInterval(updateRoute, 10000);
        setInterval(getNewImages, 10000);
        setInterval(drawNewRoute, 5000);
        setInterval(getTweets, 60000);

        $("#description-toggle").click(function () {
            $("#info-area").removeClass("hidden-xs");
        });

        $("#description-close").click(function () {
            $("#info-area").addClass("hidden-xs");
        });
    }

    function getAllPoints() {
        $.getJSON(API_SERVER + "service/geoJSON.php?featureType=route", function (route) {
            geoJSONlayer = L.geoJson(route, {
                style: function () {
                    return {
                        color: "#A600FF",
                        opacity: 0.75,
                        weight: 8
                    };
                }
            }).addTo(map);
        });
    }

    function addLastPoint() {
        $.getJSON(API_SERVER + "service/geoJSON.php?featureType=currentLocation", function(data){
            var coord = data.features[0].geometry.coordinates;
            var text = "Viimeisin sijaintitieto klo: ";
            var time = new Date(data.features[0].properties.time * 1000);
            var icon = L.AwesomeMarkers.icon({
                prefix: "fa",
                icon: "circle",
                markerColor: "red"
            });

            lastPointMarker = L.marker([coord[1], coord[0]], { icon: icon }).addTo(map);
            
            text += ((time.getHours() + 2) < 10 ? "0" + (time.getHours() + 2) : (time.getHours() + 2));
            text += ":" + (time.getMinutes() < 10 ? "0" + time.getMinutes() : time.getMinutes());

            lastPointMarker.bindPopup(text);
            
            lastPoint.time = data.features[0].properties.time;
            lastPoint.marker = lastPointMarker;
            
            // Default location in Finland
            if(coord[0] !== 0 && coord[1] !== 0){
                map.setView(lastPointMarker.getLatLng(), 12);
            }
        });
    }
    
    // Update route on map
    function updateRoute(){
        if(pointQueue.length < 10)
            getNewRoute();
    }
    
    // Get new track points after alredy loaded
    function getNewRoute(){
        $.getJSON(API_SERVER + "service/geoJSON.php?featureType=routeAfterTime&timestamp=" + lastPoint.time, function(data){
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
    
    // Add points to route from queue
    function drawNewRoute(){
        if(pointQueue.length == 0){
            return;
        }
      
        var geometry = geoJSONlayer.getLayers()[0];
        var nextPoint = pointQueue.shift();
        var latlng = L.latLng(nextPoint[1], nextPoint[0]);

        geometry.addLatLng(latlng);
        lastPointMarker.setLatLng(latlng);
    }
    
    function getImages(){
        $.getJSON(API_SERVER + "service/image.php?mode=get", addImagesToMap);
    }
    
    function getNewImages(){
        $.getJSON(API_SERVER + "service/image.php?mode=getNew&time=" + lastImageTime, addImagesToMap);
    }
    
    function addImagesToMap(data) {
        var icon = L.AwesomeMarkers.icon({
            icon: "camera",
            prefix: "fa",
            markerColor: "cadetblue"
        });

        for (var i = 0; i < data.length; i++) {
            var marker = L.marker([data[i].lat, data[i].lon], { icon: icon }).addTo(map);

            marker.imageURL = API_SERVER + data[i].image;
            marker.on("click", function (e) {
                $("#photo-modal").find("img").attr("src", e.target.imageURL);
                $("#photo-modal").modal("show");
            });
            // marker.bindPopup('<img class="popupImg" src="' + API_SERVER + data[i].image + '"><br>' + data[i].comment);

            lastImageTime = data[i].time;
        }
    }

    function getPlaceMarkers() {
        var url = "https://gist.githubusercontent.com/jleh/11413ddf813601ec81f4/raw/map.geojson";
        
        var icons = {
            "sleep":  L.AwesomeMarkers.icon({ prefix: "fa", markerColor: "green", icon: "bed" }),
            "lunch":  L.AwesomeMarkers.icon({ prefix: "fa", markerColor: "green", icon: "cutlery" }),
            "finish":  L.AwesomeMarkers.icon({ prefix: "fa", markerColor: "green", icon: "flag-checkered" })
        };

        $.getJSON(url, function (places) {
            L.geoJson(places, {
                pointToLayer: function (feature, latlng) {
                    var icon = icons[feature.properties.type];
                    var marker = L.marker(latlng, { icon: icon });
                    var text = "<strong>" + feature.properties.name + "</strong><br>" + feature.properties.description;

                    placeMarkers[feature.properties.name] = marker;
                    marker.bindPopup(text);

                    return marker;
                }
            }).addTo(map);
        });
    }

    function setPlaceLinks() {
        $(".place-link").click(function (e) {
            var place = $(this).data("name");

            map.setView(placeMarkers[place].getLatLng(), 11);
            e.preventDefault();
        });
    }

    function getTweets() {
        if (!loadedTweets)
            loadedTweets = {};

        console.log(loadedTweets);

        $.getJSON("https://pacific-falls-72628.herokuapp.com/", function (tweets) {
            var icon = L.AwesomeMarkers.icon({ prefix: "fa", markerColor: "blue", icon: "twitter" });

            for (var i = 0; i < tweets.length; i++) {
                var tweet = tweets[i];

                if (!loadedTweets[tweet.id] && tweet.geo.type === "Point") {
                    var marker = L.marker(tweet.geo.coordinates, { icon: icon })
                        .addTo(map)
                        .bindPopup(createTweetPopup(tweet), { maxWidth: 350 });

                    loadedTweets[tweet.id] = marker;
                }
            }
        });
    }

    function createTweetPopup(tweet) {
        var template = $("#tweet-template").html();
        var textWithEntities = tweet.text;

        if (tweet.entities.urls) {
            for (var i = 0; i < tweet.entities.urls.length; i++) {
                var url = tweet.entities.urls[i];
                var link = '<a href="' + url.url + '" target="_blank">';
                var ending = url.indices[1] + link.length;

                textWithEntities = textWithEntities.slice(0, url.indices[0]) + link + textWithEntities.slice(url.indices[0]);
                textWithEntities = textWithEntities.slice(0, ending) + '</a>' + textWithEntities.slice(ending);
            }
        }

        tweet.textWithEntities = textWithEntities;
        Mustache.parse(template);

        return Mustache.render(template, tweet);
    }

    return {
        initialize: initialize
    };

}();
