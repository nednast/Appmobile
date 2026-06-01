<script setup lang="ts">
import { Capacitor } from '@capacitor/core'
import { Geolocation } from '@capacitor/geolocation'
import { Camera, CameraResultType, CameraSource } from '@capacitor/camera'
import { useAds } from '~/composables/useAds'


const route = useRoute()
const isNew = route.params.id === 'new'
const { fetchUserAd, createAd, updateAd } = useAds()
const { token } = useAuth()
const { public: { APP_ENV, WEBAPI_URL, APPAPI_URL } } = useRuntimeConfig()
const apiUrl = APP_ENV === 'mobile' ? APPAPI_URL : WEBAPI_URL

const form = reactive({
  title: '',
  description: '',
  city: '',
  lat: '',
  lng: '',
})
const imageFile = ref<File | null>(null)
const imagePreview = ref<string | null>(null)
const existingImage = ref<string | null>(null)

const loading = ref(!isNew)
const saving = ref(false)
const geoLoading = ref(false)
const message = ref('')
const isError = ref(false)

onMounted(async () => {
  if (!isNew) {
    try {
      const ad = await fetchUserAd(Number(route.params.id), token.value!)
      form.title = ad.title
      form.description = ad.description
      form.city = ad.city
      form.lat = String(ad.lat)
      form.lng = String(ad.lng)
      existingImage.value = ad.image ?? null
    } catch {
      message.value = 'Annonce introuvable'
      isError.value = true
    } finally {
      loading.value = false
    }
  }
})

const captureLocation = async () => {
  geoLoading.value = true
  message.value = ''
  try {
    if (Capacitor.isNativePlatform()) {
      await Geolocation.requestPermissions()
      const pos = await Geolocation.getCurrentPosition({ enableHighAccuracy: true, timeout: 10000 })
      form.lat = String(pos.coords.latitude)
      form.lng = String(pos.coords.longitude)
    } else {
      const pos = await new Promise<GeolocationPosition>((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(resolve, reject, {
          enableHighAccuracy: true, timeout: 10000
        })
      })
      form.lat = String(pos.coords.latitude)
      form.lng = String(pos.coords.longitude)
    }
  } catch {
    message.value = 'Position indisponible — saisissez les coordonnées manuellement'
    isError.value = true
  } finally {
    geoLoading.value = false
  }
}

const pickImage = async () => {
  if (Capacitor.isNativePlatform()) {
    try {
      const photo = await Camera.getPhoto({
        quality: 80,
        allowEditing: false,
        resultType: CameraResultType.DataUrl,
        source: CameraSource.Prompt
      })
      const blob = await (await fetch(photo.dataUrl!)).blob()
      imageFile.value = new File([blob], 'ad.jpg', { type: 'image/jpeg' })
      imagePreview.value = photo.dataUrl!
    } catch {}
  }
}

const onFileChange = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null
  imageFile.value = file
  if (file) imagePreview.value = URL.createObjectURL(file)
}

const handleSubmit = async () => {
  message.value = ''
  if (!form.title || !form.description || !form.city || !form.lat || !form.lng) {
    message.value = 'Tous les champs obligatoires doivent être remplis'
    isError.value = true
    return
  }
  saving.value = true
  const data = new FormData()
  data.append('title', form.title)
  data.append('description', form.description)
  data.append('city', form.city)
  data.append('lat', form.lat)
  data.append('lng', form.lng)
  if (imageFile.value) data.append('image', imageFile.value)

  try {
    if (isNew) {
      await createAd(data, token.value!)
    } else {
      await updateAd(Number(route.params.id), data, token.value!)
    }
    navigateTo('/account/ads')
  } catch (e: any) {
    isError.value = true
    message.value = e.message ?? 'Erreur lors de la sauvegarde'
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="page-bg" />
  <div class="page-wrapper">

    <div class="page-header">
      <NuxtLink to="/account/ads" class="back-link">Retour</NuxtLink>
      <h1>{{ isNew ? 'Nouvelle annonce' : 'Modifier l\'annonce' }}</h1>
    </div>

    <p v-if="loading" class="loading">Chargement...</p>

    <div v-else class="card">
      <form @submit.prevent="handleSubmit" class="ad-form">

        <div class="form-group">
          <label>Titre <span class="required">*</span></label>
          <input v-model="form.title" type="text" placeholder="Titre de l'annonce" required />
        </div>

        <div class="form-group">
          <label>Description <span class="required">*</span></label>
          <textarea v-model="form.description" rows="4" placeholder="Décrivez votre annonce..." required />
        </div>

        <div class="form-group">
          <label>Ville <span class="required">*</span></label>
          <input v-model="form.city" type="text" placeholder="Ville" required />
        </div>

        <!-- Géolocalisation -->
        <div class="form-group">
          <label>Localisation <span class="required">*</span></label>
          <button
            type="button"
            class="btn btn-outline btn-full geo-btn"
            :disabled="geoLoading"
            @click="captureLocation"
          >
            <svg v-if="!geoLoading" class="btn-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            <svg v-else class="btn-icon-sm spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            {{ geoLoading ? 'Localisation en cours...' : 'Utiliser ma position actuelle' }}
          </button>

          <div v-if="form.lat && form.lng" class="geo-coords">
            <svg class="geo-coords__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
            {{ parseFloat(form.lat).toFixed(5) }}, {{ parseFloat(form.lng).toFixed(5) }}
          </div>

          <div class="geo-manual">
            <input v-model="form.lat" type="number" step="any" placeholder="Latitude" class="geo-manual__input" />
            <input v-model="form.lng" type="number" step="any" placeholder="Longitude" class="geo-manual__input" />
          </div>
        </div>

        <!-- Image -->
        <div class="form-group">
          <label>Image (optionnel)</label>

          <!-- Mobile : Camera.getPhoto -->
          <button
            v-if="APP_ENV === 'mobile'"
            type="button"
            class="btn btn-outline btn-full"
            @click="pickImage"
          >
            <svg class="btn-icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/></svg>
            Prendre / Choisir une photo
          </button>

          <!-- Web : input file -->
          <label v-else class="file-label">
            <span>{{ imageFile ? imageFile.name : 'Choisir une image' }}</span>
            <input type="file" accept="image/*" style="display:none" @change="onFileChange" />
          </label>

          <div v-if="imagePreview || existingImage" class="img-preview">
            <img
              :src="imagePreview ?? `${apiUrl}/storage/${existingImage}`"
              alt="Aperçu"
              class="img-preview__img"
            />
            <span v-if="imagePreview" class="img-preview__badge">Nouvelle</span>
          </div>
        </div>

        <div v-if="message" :class="['alert', isError ? 'alert-error' : 'alert-success']">
          {{ message }}
        </div>

        <button type="submit" class="btn btn-primary btn-full" :disabled="saving">
          {{ saving ? 'Enregistrement...' : (isNew ? 'Publier l\'annonce' : 'Enregistrer les modifications') }}
        </button>

      </form>
    </div>

  </div>
</template>

<style scoped>
.ad-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.required {
  color: var(--rose-light);
}

.geo-btn {
  margin-bottom: 0.5rem;
}

.geo-coords {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  color: var(--gold);
  margin-bottom: 0.5rem;
  font-family: monospace;
}

.geo-coords__icon {
  width: 12px;
  height: 12px;
  flex-shrink: 0;
}

.geo-manual {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
}

.geo-manual__input {
  width: 100%;
}

.file-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: var(--bg-card);
  border: 1px dashed var(--border);
  border-radius: var(--radius);
  padding: 0.75rem 1rem;
  cursor: pointer;
  font-size: 0.85rem;
  color: var(--text-muted);
  transition: border-color 0.2s;
}
.file-label:hover { border-color: var(--gold); color: var(--gold); }

.img-preview {
  position: relative;
  border-radius: var(--radius);
  overflow: hidden;
  height: 160px;
  margin-top: 0.5rem;
}

.img-preview__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.img-preview__badge {
  position: absolute;
  top: 8px;
  right: 8px;
  background: var(--gold);
  color: #000;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 20px;
  text-transform: uppercase;
}

.btn-icon-sm {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
  margin-right: 0.4rem;
}

@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.spin { animation: spin 1.5s linear infinite; }
</style>
