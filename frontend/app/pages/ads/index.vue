<template>
  <div class="page-bg" />
  <div class="page-wrapper">

    <div class="page-header">
      <h1>Annonces à proximité</h1>
    </div>

    <!-- Géolocalisation refusée -->
    <div v-if="permissionDenied" class="geo-banner geo-banner--denied">
      <svg class="geo-banner__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
      <div>
        <p class="geo-banner__title">Géolocalisation refusée</p>
        <p class="geo-banner__text">
          Autorisez la géolocalisation dans les paramètres de votre navigateur
          puis rechargez la page pour trier par distance.
        </p>
      </div>
    </div>

    <!-- En attente de position -->
    <div v-else-if="!locationReady" class="geo-banner geo-banner--waiting">
      <svg class="geo-banner__icon geo-banner__icon--spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
      <p class="geo-banner__text">Récupération de votre position...</p>
    </div>

    <!-- Position approximative par IP -->
    <div v-else-if="locationError && locationError.includes('IP')" class="geo-banner geo-banner--ip">
      <svg class="geo-banner__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
      <p class="geo-banner__text">Tri approximatif basé sur votre adresse IP.</p>
    </div>

    <!-- Position indisponible avec retry -->
    <div v-else-if="locationError" class="geo-banner geo-banner--warn">
      <svg class="geo-banner__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      <div class="geo-banner__content">
        <p class="geo-banner__text">{{ locationError }}</p>
        <button class="btn btn-outline btn-sm geo-banner__retry" @click="retryLocation">
          Réessayer
        </button>
      </div>
    </div>

    <p v-if="loading" class="loading">Chargement des annonces...</p>
    <p v-else-if="fetchError" class="ads-status ads-status--error">{{ fetchError }}</p>
    <p v-else-if="ads.length === 0 && locationReady" class="loading">
      Aucune annonce pour le moment.
    </p>

    <div v-else class="ads-grid">
      <CardAd v-for="ad in ads" :key="ad.id" :ad="ad" />
    </div>

  </div>
</template>

<script setup lang="ts">
import { useAds } from '~/composables/useAds'
import type { Ad } from '~/composables/useAds'

const { fetchAds } = useAds()

const ads = ref<Ad[]>([])
const loading = ref(false)
const fetchError = ref<string | null>(null)
const locationError = ref<string | null>(null)
const locationReady = ref(false)
const permissionDenied = ref(false)

async function loadAds(lat?: number, lng?: number) {
  loading.value = true
  fetchError.value = null
  try {
    ads.value = await fetchAds(lat, lng)
  } catch (e: any) {
    fetchError.value = e.message
  } finally {
    loading.value = false
  }
}

async function getIpLocation(): Promise<{ lat: number; lng: number } | null> {
  try {
    const res = await fetch('https://ipapi.co/json/')
    const data = await res.json()
    if (data.latitude && data.longitude) {
      return { lat: data.latitude, lng: data.longitude }
    }
  } catch {}
  return null
}

async function requestLocation() {
  locationReady.value = false
  locationError.value = null
  permissionDenied.value = false

  // 1. Essai navigateur (permission refusée → pas de retry)
  if ('permissions' in navigator) {
    const perm = await navigator.permissions.query({ name: 'geolocation' })
    if (perm.state === 'denied') {
      permissionDenied.value = true
    }
  }

  if (!permissionDenied.value && navigator.geolocation) {
    const browserCoords = await new Promise<{ lat: number; lng: number } | null>((resolve) => {
      navigator.geolocation.getCurrentPosition(
        (pos) => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
        () => resolve(null),
        { enableHighAccuracy: false, timeout: 6000, maximumAge: 300000 }
      )
    })

    if (browserCoords) {
      locationReady.value = true
      await loadAds(browserCoords.lat, browserCoords.lng)
      return
    }
  }

  // 2. Fallback : géolocalisation par adresse IP (approximative)
  const ipCoords = await getIpLocation()
  if (ipCoords) {
    locationReady.value = true
    locationError.value = 'Position approximative (basée sur votre IP).'
    await loadAds(ipCoords.lat, ipCoords.lng)
    return
  }

  // 3. Rien ne fonctionne → annonces sans tri
  locationReady.value = true
  if (!permissionDenied.value) {
    locationError.value = 'Position indisponible — affichage sans tri par distance.'
  }
  await loadAds()
}

async function retryLocation() {
  ads.value = []
  await requestLocation()
}

onMounted(requestLocation)
</script>

<style scoped>
.ads-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 480px) {
  .ads-grid { grid-template-columns: 1fr 1fr; }
}

.ads-status {
  color: var(--text-muted);
  font-size: 0.875rem;
  margin-bottom: 1rem;
}
.ads-status--error { color: var(--rose-light); }

.geo-banner {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.9rem 1rem;
  border-radius: var(--radius-sm);
  margin-bottom: 1.25rem;
  border: 1px solid var(--border);
}

.geo-banner--denied {
  background: rgba(166, 95, 98, 0.1);
  border-color: rgba(166, 95, 98, 0.3);
}

.geo-banner--warn {
  background: rgba(203, 184, 136, 0.08);
  border-color: rgba(203, 184, 136, 0.25);
}

.geo-banner--ip {
  background: rgba(255, 255, 255, 0.03);
  border-color: var(--border);
}

.geo-banner--waiting {
  background: rgba(255, 255, 255, 0.03);
}

.geo-banner__icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  margin-top: 0.1rem;
  color: var(--text-muted);
}

.geo-banner--denied .geo-banner__icon { color: var(--rose-light); }
.geo-banner--warn .geo-banner__icon { color: var(--gold); }
.geo-banner--ip .geo-banner__icon { color: var(--gold); opacity: 0.7; }

@keyframes spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
.geo-banner__icon--spin { animation: spin 2s linear infinite; }

.geo-banner__content {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.geo-banner__title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--rose-light);
  margin-bottom: 0.2rem;
}

.geo-banner__text {
  font-size: 0.825rem;
  color: var(--text-muted);
  line-height: 1.5;
}

.geo-banner__retry {
  align-self: flex-start;
  margin-top: 0.25rem;
}
</style>
