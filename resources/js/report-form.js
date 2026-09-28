import { CIANJUR_CENTER, createMap, L } from './leaflet-base';

const form = document.getElementById('report-form');

if (form) {
    setUpPhotos(form);
    setUpLocation(form);
}

/*
|--------------------------------------------------------------------------
| Photos: compress in the browser, show previews, max 3
|--------------------------------------------------------------------------
*/

const MAX_PHOTOS = 3;
const MAX_DIMENSION = 1600;
const JPEG_QUALITY = 0.85;

function setUpPhotos(form) {
    const input = form.querySelector('#photos');
    const previewList = form.querySelector('#photo-previews');
    const counter = form.querySelector('#photo-counter');
    const addButton = form.querySelector('#add-photo-button');
    const submitButton = form.querySelector('button[type="submit"]');

    // The photos chosen so far. A camera gives one photo at a time,
    // so we keep the earlier ones when the user adds another.
    let selectedPhotos = [];

    input.addEventListener('change', async () => {
        const newFiles = Array.from(input.files).slice(0, MAX_PHOTOS - selectedPhotos.length);

        submitButton.disabled = true;
        counter.textContent = 'Memproses foto…';

        for (const file of newFiles) {
            selectedPhotos.push(await compressPhoto(file));
        }

        submitButton.disabled = false;
        render();
    });

    function render() {
        // Put the selected photos back into the file input, so the form sends them.
        const transfer = new DataTransfer();
        selectedPhotos.forEach((photo) => transfer.items.add(photo));
        input.files = transfer.files;

        previewList.replaceChildren(...selectedPhotos.map(buildPreview));
        counter.textContent = `${selectedPhotos.length} dari ${MAX_PHOTOS} foto`;
        addButton.hidden = selectedPhotos.length >= MAX_PHOTOS;
    }

    function buildPreview(photo, index) {
        const item = document.createElement('li');
        item.className = 'relative';

        const image = document.createElement('img');
        image.src = URL.createObjectURL(photo);
        image.alt = `Foto ${index + 1}`;
        image.className = 'aspect-square w-full rounded-lg object-cover';

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.textContent = 'Hapus';
        removeButton.className =
            'absolute right-1 bottom-1 min-h-11 rounded-lg bg-ink/80 px-3 text-base font-semibold text-white';
        removeButton.addEventListener('click', () => {
            URL.revokeObjectURL(image.src);
            selectedPhotos.splice(index, 1);
            render();
        });

        item.append(image, removeButton);

        return item;
    }
}

/**
 * Resize a photo so the longest side is at most 1600 px and save it as JPEG.
 * If the browser cannot read the photo, send the original; the server checks it.
 */
async function compressPhoto(file) {
    try {
        // "from-image" turns the photo upright using its orientation data.
        const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
        const scale = Math.min(1, MAX_DIMENSION / Math.max(bitmap.width, bitmap.height));

        const canvas = document.createElement('canvas');
        canvas.width = Math.round(bitmap.width * scale);
        canvas.height = Math.round(bitmap.height * scale);
        canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
        bitmap.close();

        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', JPEG_QUALITY));
        const name = file.name.replace(/\.[^.]+$/, '') + '.jpg';

        return new File([blob], name, { type: 'image/jpeg' });
    } catch (error) {
        console.warn('Foto tidak bisa dikompres, dikirim apa adanya', error);

        return file;
    }
}

/*
|--------------------------------------------------------------------------
| Location: GPS with a pin that can be moved
|--------------------------------------------------------------------------
*/

function setUpLocation(form) {
    const mapElement = form.querySelector('#location-map');
    const latitudeInput = form.querySelector('input[name="latitude"]');
    const longitudeInput = form.querySelector('input[name="longitude"]');
    const gpsButton = form.querySelector('#gps-button');
    const statusText = form.querySelector('#location-status');
    const area = JSON.parse(mapElement.dataset.area);

    // After a validation error, start from the location the user already chose.
    const hasOldLocation = latitudeInput.value !== '' && longitudeInput.value !== '';
    const start = hasOldLocation ? [Number(latitudeInput.value), Number(longitudeInput.value)] : CIANJUR_CENTER;

    const map = createMap(mapElement, start, hasOldLocation ? 18 : 15);

    const pin = L.marker(start, {
        draggable: true,
        keyboard: true,
        title: 'Lokasi kerusakan',
        icon: L.divIcon({ className: '', html: '<span class="report-pin"></span>', iconSize: [36, 36], iconAnchor: [18, 36] }),
    }).addTo(map);

    function setLocation(latitude, longitude, message) {
        pin.setLatLng([latitude, longitude]);
        latitudeInput.value = latitude.toFixed(6);
        longitudeInput.value = longitude.toFixed(6);

        const insideArea =
            latitude >= area.south && latitude <= area.north && longitude >= area.west && longitude <= area.east;

        statusText.textContent = insideArea ? message : 'Titik ini di luar wilayah Cianjur. Geser pin ke lokasi kerusakan.';
    }

    pin.on('dragend', () => {
        const { lat, lng } = pin.getLatLng();
        setLocation(lat, lng, 'Lokasi dipilih dari peta.');
    });

    map.on('click', (event) => {
        setLocation(event.latlng.lat, event.latlng.lng, 'Lokasi dipilih dari peta.');
    });

    function useGps() {
        if (!navigator.geolocation) {
            statusText.textContent = 'HP ini tidak mendukung GPS. Geser pin ke lokasi kerusakan.';
            return;
        }

        statusText.textContent = 'Mencari lokasi Anda…';

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude, accuracy } = position.coords;
                map.setView([latitude, longitude], 18);
                setLocation(
                    latitude,
                    longitude,
                    `Lokasi dari GPS (akurasi sekitar ${Math.round(accuracy)} meter). Geser pin jika kurang tepat.`,
                );
            },
            () => {
                statusText.textContent = 'Lokasi GPS tidak didapat. Izinkan akses lokasi, atau geser pin ke lokasi kerusakan.';
            },
            { enableHighAccuracy: true, timeout: 15000 },
        );
    }

    gpsButton.addEventListener('click', useGps);

    if (!hasOldLocation) {
        useGps();
    }
}
