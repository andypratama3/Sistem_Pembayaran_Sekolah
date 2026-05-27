@props([
    'checkInLat' => null,
    'checkInLng' => null,
    'checkOutLat' => null,
    'checkOutLng' => null,
    'kmlPolygons' => [],
])

@php
    $mapId = 'attendance-map-' . uniqid();
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

<div id="{{ $mapId }}" style="height: 360px; border-radius: 10px;"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    (function() {
        const mapEl = document.getElementById(@json($mapId));
        if (!mapEl || typeof L === 'undefined') return;

        const checkIn = {
            lat: Number(@json($checkInLat)),
            lng: Number(@json($checkInLng)),
        };

        const checkOut = {
            lat: Number(@json($checkOutLat)),
            lng: Number(@json($checkOutLng)),
        };
        const polygons = @json($kmlPolygons ?? []);

        const points = [];
        if (!Number.isNaN(checkIn.lat) && !Number.isNaN(checkIn.lng) && checkIn.lat && checkIn.lng) {
            points.push([checkIn.lat, checkIn.lng]);
        }
        if (!Number.isNaN(checkOut.lat) && !Number.isNaN(checkOut.lng) && checkOut.lat && checkOut.lng) {
            points.push([checkOut.lat, checkOut.lng]);
        }

        const initial = points.length > 0 ? points[0] : [-6.200000, 106.816666];
        const map = L.map(mapEl).setView(initial, points.length > 0 ? 15 : 11);
        let liveMarker = null;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const polygonBounds = [];
        if (Array.isArray(polygons) && polygons.length > 0) {
            polygons.forEach((poly) => {
                const coords = Array.isArray(poly?.coordinates) ? poly.coordinates
                    .map((c) => [Number(c.lat), Number(c.lon)])
                    .filter((c) => !Number.isNaN(c[0]) && !Number.isNaN(c[1])) : [];

                if (coords.length > 2) {
                    L.polygon(coords, {
                        color: '#dc2626',
                        weight: 2,
                        fillColor: '#fca5a5',
                        fillOpacity: 0.2,
                    }).addTo(map).bindPopup(`Area KML: ${poly?.name || 'Area'}`);

                    polygonBounds.push(...coords);
                }
            });
        }

        if (points.length === 0) {
            L.popup()
                .setLatLng(initial)
                .setContent('No GPS data available for this attendance record.')
                .openOn(map);
        }

        if (points[0]) {
            L.marker(points[0]).addTo(map).bindPopup('Check-in location');
        }

        if (points[1]) {
            L.marker(points[1]).addTo(map).bindPopup('Check-out location');
            L.polyline(points, {
                color: '#2563eb',
                weight: 3,
                opacity: 0.85,
                dashArray: '8 8'
            }).addTo(map);
        }

        if (points.length > 1) {
            map.fitBounds(points, {
                padding: [24, 24]
            });
        } else if (polygonBounds.length > 2) {
            map.fitBounds(polygonBounds, {
                padding: [24, 24]
            });
        }

        window.addEventListener('attendance:live-position', (event) => {
            const lat = Number(event?.detail?.lat);
            const lng = Number(event?.detail?.lng);

            if (Number.isNaN(lat) || Number.isNaN(lng)) {
                return;
            }

            const livePoint = [lat, lng];

            if (!liveMarker) {
                liveMarker = L.circleMarker(livePoint, {
                    radius: 7,
                    color: '#16a34a',
                    fillColor: '#22c55e',
                    fillOpacity: 0.9,
                    weight: 2,
                }).addTo(map).bindPopup('Posisi Anda (Real-time)');
            } else {
                liveMarker.setLatLng(livePoint);
            }

            if (points.length === 0) {
                map.setView(livePoint, 16);
            }
        });
    })();
</script>
