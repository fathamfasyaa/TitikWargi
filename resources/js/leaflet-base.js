import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Center of Cianjur kota.
export const CIANJUR_CENTER = [-6.817, 107.142];

/**
 * Create a Leaflet map with OpenStreetMap tiles and the required attribution.
 */
export function createMap(element, center = CIANJUR_CENTER, zoom = 15) {
    const map = L.map(element).setView(center, zoom);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>',
    }).addTo(map);

    return map;
}

export { L };
