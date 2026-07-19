<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref, watch } from "vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const DefaultIcon = L.icon({
    iconUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png",
    iconRetinaUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png",
    shadowUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png",
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41],
});
L.Marker.prototype.options.icon = DefaultIcon;

const props = defineProps<{
    modelLat?: number | null;
    modelLng?: number | null;
}>();

const emit = defineEmits<{
    (e: "update:location", lat: number, lng: number): void;
}>();

const mapEl = ref<HTMLDivElement | null>(null);
let map: L.Map | null = null;
let marker: L.Marker | null = null;

// Default center: Iloilo City
const DEFAULT_CENTER: [number, number] = [10.7202, 122.5621];

const PH_CENTER: [number, number] = [12.8797, 121.7740];
const PH_DEFAULT_ZOOM = 6;

const PH_BOUNDS: L.LatLngBoundsExpression = [
    [4.5, 116.0],   // southwest
    [21.5, 127.0],  // northeast
];

function placeMarker(lat: number, lng: number) {
    if (!map) return;
    if (marker) {
        marker.setLatLng([lat, lng]);
    } else {
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on("dragend", () => {
            const pos = marker!.getLatLng();
            emit("update:location", pos.lat, pos.lng);
        });
    }
    emit("update:location", lat, lng);
}

function useMyLocation() {
    if (!navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition((pos) => {
        const { latitude, longitude } = pos.coords;
        map?.setView([latitude, longitude], 16);
        placeMarker(latitude, longitude);
    });
}

onMounted(() => {
    if (!mapEl.value) return;

    const hasExisting = props.modelLat && props.modelLng;
    const startLat = props.modelLat ?? PH_CENTER[0];
    const startLng = props.modelLng ?? PH_CENTER[1];
    const startZoom = hasExisting ? 14 : PH_DEFAULT_ZOOM;

    map = L.map(mapEl.value, {
        maxBounds: PH_BOUNDS,
        maxBoundsViscosity: 0.8,
        minZoom: 5,
    }).setView([startLat, startLng], startZoom);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution: "&copy; OpenStreetMap contributors",
        maxZoom: 19,
    }).addTo(map);

    if (hasExisting) {
        placeMarker(props.modelLat!, props.modelLng!);
    }

    map.on("click", (e: L.LeafletMouseEvent) => {
        placeMarker(e.latlng.lat, e.latlng.lng);
    });

    setTimeout(() => map?.invalidateSize(), 200);
});

onBeforeUnmount(() => {
    map?.remove();
    map = null;
});

watch(
    () => [props.modelLat, props.modelLng],
    ([lat, lng]) => {
        if (lat && lng && map) {
            map.setView([lat, lng]);
            placeMarker(lat, lng);
        }
    },
);

defineExpose({ useMyLocation });
</script>

<template>
    <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
            <p class="text-xs text-ink/60">
                Click on the map to set the exact drop-off spot.
            </p>
            <button
                type="button"
                class="text-xs font-medium text-blue-600 hover:underline flex items-center gap-1"
                @click="useMyLocation"
            >
                <font-awesome-icon icon="fa-solid fa-location-crosshairs" />
                Use my location
            </button>
        </div>
        <div
            ref="mapEl"
            class="h-[280px] w-full rounded-md border border-ink/10"
        ></div>
    </div>
</template>