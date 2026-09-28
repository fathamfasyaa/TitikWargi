import { createMap, L } from './leaflet-base';

// Point styles: orange = not yet repaired, white with a blue border = repaired.
const STYLE_UNREPAIRED = { radius: 10, color: '#ffffff', weight: 2, fillColor: '#b93a0b', fillOpacity: 1 };
const STYLE_REPAIRED = { radius: 10, color: '#1f4fb8', weight: 3, fillColor: '#ffffff', fillOpacity: 1 };

const mapElement = document.getElementById('report-map');

if (mapElement) {
    const map = createMap(mapElement);

    const reportLayer = L.layerGroup().addTo(map);
    let currentRequest = null;

    /**
     * Load the reports inside the visible part of the map.
     */
    async function loadReports() {
        // Cancel the previous request if the map moved again before it finished.
        currentRequest?.abort();
        currentRequest = new AbortController();

        const bounds = map.getBounds();
        const params = new URLSearchParams({
            south: bounds.getSouth(),
            west: bounds.getWest(),
            north: bounds.getNorth(),
            east: bounds.getEast(),
        });

        try {
            const response = await fetch(`${mapElement.dataset.endpoint}?${params}`, {
                headers: { Accept: 'application/json' },
                signal: currentRequest.signal,
            });

            if (!response.ok) {
                return;
            }

            showReports(await response.json());
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Gagal memuat laporan', error);
            }
        }
    }

    /**
     * Draw one point per report.
     */
    function showReports(featureCollection) {
        reportLayer.clearLayers();

        for (const feature of featureCollection.features) {
            // GeoJSON is [longitude, latitude]; Leaflet wants [latitude, longitude].
            const [longitude, latitude] = feature.geometry.coordinates;
            const style = feature.properties.status === 'repaired' ? STYLE_REPAIRED : STYLE_UNREPAIRED;

            L.circleMarker([latitude, longitude], style)
                .bindPopup(() => buildPopup(feature.properties))
                .addTo(reportLayer);
        }
    }

    /**
     * Build the popup with textContent, so text written by users is never run as HTML.
     */
    function buildPopup(report) {
        const popup = document.createElement('div');
        popup.className = 'report-popup';

        const lines = [
            [report.category, 'strong'],
            [`Tingkat kerusakan: ${report.severity}`],
            [report.status_label],
            [[report.address, report.kelurahan].filter(Boolean).join(', ')],
            [`${report.supports_count} warga terdampak · ${report.reported_ago}`],
        ];

        for (const [text, tag = 'p'] of lines) {
            if (!text) {
                continue;
            }

            const element = document.createElement(tag);
            element.textContent = text;
            popup.append(element);
        }

        return popup;
    }

    map.on('moveend', loadReports);
    loadReports();
}
