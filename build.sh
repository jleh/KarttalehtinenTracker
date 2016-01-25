#!/bin/bash

uglifyjs js/leaflet.js js/jquery.js js/leaflet.awesome-markers.js js/proj4.js js/proj4leaflet.js \
js/MML.js js/bootstrap.js js/tracker16.js --compress -o built.js
