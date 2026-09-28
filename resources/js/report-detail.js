import { createMap, L, statusStyle } from './leaflet-base';

// A small map that shows where the report is.
const mapElement = document.getElementById('report-location-map');

if (mapElement) {
    const location = [Number(mapElement.dataset.latitude), Number(mapElement.dataset.longitude)];
    const map = createMap(mapElement, location, 17);

    L.circleMarker(location, statusStyle(mapElement.dataset.status)).addTo(map);
}
