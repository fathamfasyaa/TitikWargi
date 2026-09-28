import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Center of Cianjur kota.
export const CIANJUR_CENTER = [-6.817, 107.142];

// Point styles: orange = not yet repaired, white with a blue border = repaired.
const STYLE_UNREPAIRED = { radius: 10, color: '#ffffff', weight: 2, fillColor: '#b93a0b', fillOpacity: 1 };
const STYLE_REPAIRED = { radius: 10, color: '#1f4fb8', weight: 3, fillColor: '#ffffff', fillOpacity: 1 };

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

/**
 * The point style for a report status ("unrepaired", "awaiting_confirmation" or "repaired").
 */
export function statusStyle(status) {
    return status === 'repaired' ? STYLE_REPAIRED : STYLE_UNREPAIRED;
}

export { L };
