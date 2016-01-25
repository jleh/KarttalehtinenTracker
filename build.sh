#!/bin/bash

# build javascript
uglifyjs \
    js/leaflet.js \
    js/jquery.js  \
    js/leaflet.awesome-markers.js \
    js/proj4.js js/proj4leaflet.js \
    js/MML.js \
    js/bootstrap.js \
    js/tracker16.js \
    --compress -o built.js

# build css
cat css/bootstrap.min.css > built.css
cat leaflet.awesome-markers.css >> built.css
cat css/trackerOL.css >> built.css
