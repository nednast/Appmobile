<template>
  <div class="map-wrapper">
    <!-- Web fallback -->
    <div v-if="!isNative" ref="webMapRef" class="map-container"></div>

    <!-- Native Capacitor -->
    <div v-else ref="mapRef" class="map-container"></div>

    <div v-if="selectedAd && routeInfo" class="route-panel">
      <h3>{{ selectedAd.title }}</h3>
      <p>📍 {{ routeInfo.distance }}</p>
      <p>⏱ {{ routeInfo.duration }}</p>
      <button @click="selectedAd = null">✕ Fermer</button>
    </div>

    <div v-if="statusMsg" class="map-status">
      {{ statusMsg }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { Capacitor } from '@capacitor/core'
import { decode } from '@googlemaps/polyline-codec'
import type { Ad } from '~/composables/useAds'

const config = useRuntimeConfig()
const { coords, error: geoError, requestPosition } = useGeolocation()
const { fetchAds } = useAds()

const isNative = Capacitor.isNativePlatform()

const mapRef = ref<HTMLElement | null>(null)
const webMapRef = ref<HTMLElement | null>(null)
const ads = ref<Ad[]>([])
const selectedAd = ref<Ad | null>(null)
const routeInfo = ref<{ distance: string; duration: string } | null>(null)
const statusMsg = ref<string>('📡 Récupération de la position...')

// ─── NATIVE (Android) ─────────────────────────────────────────
let nativeMap: any

const initNativeMap = async () => {
  const { GoogleMap } = await import('@capacitor/google-maps')

  nativeMap = await GoogleMap.create({
    id: 'main-map',
    element: mapRef.value!,
    apiKey: config.public.GOOGLE_MAPS_API_KEY,
    config: { center: coords.value!, zoom: 14 }
  })

  await nativeMap.addMarker({
    coordinate: coords.value!,
    title: 'Ma position',
  })

  await loadNativeAdsMarkers()
  await setupNativeMarkerClick()
}

const loadNativeAdsMarkers = async () => {
  ads.value = await fetchAds(coords.value!.lat, coords.value!.lng)
  for (const ad of ads.value) {
    await nativeMap.addMarker({
      coordinate: { lat: ad.lat, lng: ad.lng },
      title: ad.title,
      snippet: ad.distance != null ? `${ad.distance.toFixed(1)} km` : '',
    })
  }
}

const setupNativeMarkerClick = async () => {
  await nativeMap.setOnMarkerClickListener((marker: any) => {
    const adIndex = Number(marker.markerId) - 1
    if (adIndex >= 0 && adIndex < ads.value.length) {
      const ad = ads.value[adIndex]
      if (ad) {
        selectedAd.value = ad
        routeInfo.value = null
        getNativeRoute(ad)
      }
    }
  })
}

const getNativeRoute = async (ad: Ad) => {
  if (!coords.value) return
  try {
    const origin = `${coords.value.lat},${coords.value.lng}`
    const dest = `${ad.lat},${ad.lng}`
    const key = config.public.GOOGLE_MAPS_API_KEY

    const res = await fetch(
      `https://maps.googleapis.com/maps/api/directions/json` +
      `?origin=${origin}&destination=${dest}&mode=walking&key=${key}`
    )
    const data = await res.json()
    if (!data.routes?.length) {
      routeInfo.value = { distance: '—', duration: '—' }
      return
    }

    const leg = data.routes[0].legs[0]
    routeInfo.value = {
      distance: leg?.distance?.text ?? '—',
      duration: leg?.duration?.text ?? '—'
    }

    const path = decode(data.routes[0].overview_polyline.points)
      .map(([lat, lng]) => ({ lat, lng }))

    await nativeMap.addPolylines([{
      points: path,
      strokeColor: '#C9A84C',
      strokeWeight: 4,
      geodesic: true,
    } as any])
  } catch (e) {
    routeInfo.value = { distance: '—', duration: '—' }
  }
}

// ─── WEB (browser) ────────────────────────────────────────────
let webMap: any
let webDirections: any
let webDirectionsRenderer: any

const initWebMap = async () => {
  await new Promise<void>((resolve) => {
    const script = document.createElement('script')
    script.src = `https://maps.googleapis.com/maps/api/js?key=${config.public.GOOGLE_MAPS_API_KEY}&libraries=places,geometry`
    script.onload = () => resolve()
    document.head.appendChild(script)
  })

  const g = (window as any).google.maps

  webMap = new g.Map(webMapRef.value!, {
    center: coords.value!,
    zoom: 14,
    styles: [
      { elementType: 'geometry', stylers: [{ color: '#1a0a00' }] },
      { elementType: 'labels.text.fill', stylers: [{ color: '#c9a84c' }] },
      { elementType: 'labels.text.stroke', stylers: [{ color: '#1a0a00' }] },
      { featureType: 'road', elementType: 'geometry', stylers: [{ color: '#3b2208' }] },
      { featureType: 'water', elementType: 'geometry', stylers: [{ color: '#0a0a1a' }] },
      { featureType: 'poi', stylers: [{ visibility: 'off' }] },
    ]
  })

  webDirections = new g.DirectionsService()
  webDirectionsRenderer = new g.DirectionsRenderer({
    polylineOptions: { strokeColor: '#C9A84C', strokeWeight: 4 },
    suppressMarkers: true
  })
  webDirectionsRenderer.setMap(webMap)

  new g.Marker({
    position: coords.value!,
    map: webMap,
    title: 'Ma position',
    icon: {
      path: g.SymbolPath.CIRCLE,
      scale: 8,
      fillColor: '#C9A84C',
      fillOpacity: 1,
      strokeColor: '#fff',
      strokeWeight: 2,
    }
  })

  await loadWebAdsMarkers()
}

const loadWebAdsMarkers = async () => {
  const g = (window as any).google.maps
  ads.value = await fetchAds(coords.value!.lat, coords.value!.lng)

  for (const ad of ads.value) {
    const marker = new g.Marker({
      position: { lat: ad.lat, lng: ad.lng },
      map: webMap,
      title: ad.title,
    })

    marker.addListener('click', () => {
      selectedAd.value = ad
      routeInfo.value = null
      getWebRoute(ad)
    })
  }
}

const getWebRoute = (ad: Ad) => {
  if (!coords.value) return
  const g = (window as any).google.maps

  webDirections.route(
    {
      origin: coords.value,
      destination: { lat: ad.lat, lng: ad.lng },
      travelMode: g.TravelMode.WALKING,
    },
    (result: any, status: any) => {
      if (status === 'OK' && result) {
        webDirectionsRenderer.setDirections(result)
        const leg = result.routes[0].legs[0]
        routeInfo.value = {
          distance: leg?.distance?.text ?? '—',
          duration: leg?.duration?.text ?? '—'
        }
      } else {
        routeInfo.value = { distance: '—', duration: '—' }
      }
    }
  )
}

// ─── Init ─────────────────────────────────────────────────────
onMounted(async () => {
  await requestPosition()

  if (geoError.value) {
    statusMsg.value = `⚠️ ${geoError.value}`
    return
  }

  if (!coords.value) {
    statusMsg.value = "⚠️ Position indisponible"
    return
  }

  statusMsg.value = ''

  try {
    if (isNative) {
      await initNativeMap()
    } else {
      await initWebMap()
    }
  } catch (e) {
    console.error('Erreur init carte:', e)
    statusMsg.value = '⚠️ Erreur lors du chargement de la carte'
  }
})

onUnmounted(() => {
  if (isNative && nativeMap) nativeMap.destroy()
})
</script>

<style scoped>
.map-wrapper {
  position: relative;
  width: 100%;
  height: calc(100vh - 60px);
}

.map-container {
  width: 100%;
  height: 100%;
}

.route-panel {
  position: fixed;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  background: var(--bg-card);
  border: 1px solid var(--gold);
  border-radius: var(--radius);
  padding: 1rem 1.5rem;
  z-index: 9999;
  min-width: 260px;
  text-align: center;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
}

.route-panel h3 {
  color: var(--gold);
  font-size: 1rem;
  margin-bottom: 0.5rem;
}

.route-panel p {
  color: var(--text-muted);
  font-size: 0.875rem;
  margin: 0.25rem 0;
}

.route-panel button {
  margin-top: 0.75rem;
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text-muted);
  border-radius: 20px;
  padding: 0.25rem 0.9rem;
  font-size: 0.8rem;
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s;
}

.route-panel button:hover {
  border-color: var(--gold);
  color: var(--gold);
}

.map-status {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: var(--text-muted);
  font-size: 0.9rem;
  text-align: center;
  pointer-events: none;
}
</style>