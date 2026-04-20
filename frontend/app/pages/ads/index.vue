<template>
  <div class="ads-page">
    <h1 class="ads-page__title">Annonces à proximité</h1>

    <p v-if="locationError" class="ads-page__error">
      ⚠️ {{ locationError }}
    </p>
    <p v-else-if="!locationReady" class="ads-page__info">
      📡 Récupération de votre position...
    </p>

    <p v-if="loading" class="ads-page__info">Chargement des annonces...</p>
    <p v-else-if="error" class="ads-page__error">{{ error }}</p>

    <div v-else class="ads-page__grid">
      <CardAd v-for="ad in ads" :key="ad.id" :ad="ad" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAds } from '~/composables/useAds'
import type { Ad } from '~/composables/useAds'

const { fetchAds } = useAds()

const ads = ref<Ad[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const locationError = ref<string | null>(null)
const locationReady = ref(false)

async function loadAds(lat?: number, lng?: number) {
  loading.value = true
  error.value = null
  try {
    ads.value = await fetchAds(lat, lng)
  } catch (e: any) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

function getPosition(): Promise<GeolocationPosition> {
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
      reject(new Error('Géolocalisation non supportée par ce navigateur.'))
      return
    }
    navigator.geolocation.getCurrentPosition(resolve, reject, {
      enableHighAccuracy: true,
      timeout: 10000,
    })
  })
}

onMounted(async () => {
  try {
    const position = await getPosition()
    locationReady.value = true
    const { latitude, longitude } = position.coords
    await loadAds(latitude, longitude)
  } catch (e: any) {
    locationError.value = 'Position indisponible — affichage sans tri par distance.'
    locationReady.value = true
    await loadAds()
  }
})
</script>

<style scoped>
.ads-page {
  padding: 16px;
  max-width: 800px;
  margin: 0 auto;
}

.ads-page__title {
  font-size: 1.4rem;
  font-weight: 700;
  margin-bottom: 16px;
}

.ads-page__info {
  color: #666;
  margin-bottom: 12px;
}

.ads-page__error {
  color: #c0392b;
  margin-bottom: 12px;
}

.ads-page__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 16px;
}
</style>