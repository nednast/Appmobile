<script setup lang="ts">
import { useAds } from '~/composables/useAds'
import type { Ad } from '~/composables/useAds'


const { fetchUserAds, deleteAd } = useAds()
const { token } = useAuth()
const { public: { APP_ENV, WEBAPI_URL, APPAPI_URL } } = useRuntimeConfig()
const apiUrl = APP_ENV === 'mobile' ? APPAPI_URL : WEBAPI_URL

const ads = ref<Ad[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const deletingId = ref<number | null>(null)

const loadAds = async () => {
  loading.value = true
  error.value = null
  try {
    const res = await fetchUserAds(token.value!)
    ads.value = res.data ?? res
  } catch (e: any) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

const handleDelete = async (id: number) => {
  if (!confirm('Supprimer cette annonce ?')) return
  deletingId.value = id
  try {
    await deleteAd(id, token.value!)
    ads.value = ads.value.filter(a => a.id !== id)
  } catch {
    alert('Erreur lors de la suppression')
  } finally {
    deletingId.value = null
  }
}

onMounted(loadAds)
</script>

<template>
  <div class="page-bg" />
  <div class="page-wrapper">

    <div class="page-header">
      <NuxtLink to="/account" class="back-link">Retour</NuxtLink>
      <h1>Mes annonces</h1>
      <NuxtLink to="/account/ads/new" class="btn btn-gold btn-sm">+ Nouvelle</NuxtLink>
    </div>

    <p v-if="loading" class="loading">Chargement...</p>
    <p v-else-if="error" class="alert alert-error">{{ error }}</p>
    <p v-else-if="ads.length === 0" class="loading">Aucune annonce pour le moment.</p>

    <div v-else class="ads-list">
      <div v-for="ad in ads" :key="ad.id" class="ad-row">
        <img
          v-if="ad.image"
          :src="`${apiUrl}/storage/${ad.image}`"
          :alt="ad.title"
          class="ad-row__img"
        />
        <div v-else class="ad-row__img-placeholder">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
        </div>
        <div class="ad-row__info">
          <p class="ad-row__title">{{ ad.title }}</p>
          <p class="ad-row__city">{{ ad.city }}</p>
          <p class="ad-row__desc">{{ ad.description }}</p>
        </div>
        <div class="ad-row__actions">
          <NuxtLink :to="`/account/ads/${ad.id}`" class="btn btn-outline btn-xs">Modifier</NuxtLink>
          <button
            class="btn btn-danger btn-xs"
            :disabled="deletingId === ad.id"
            @click="handleDelete(ad.id)"
          >
            {{ deletingId === ad.id ? '...' : 'Supprimer' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.ads-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.ad-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 0.75rem;
  transition: border-color 0.2s;
}
.ad-row:hover { border-color: var(--gold); }

.ad-row__img {
  width: 64px;
  height: 64px;
  border-radius: var(--radius-sm);
  object-fit: cover;
  flex-shrink: 0;
}

.ad-row__img-placeholder {
  width: 64px;
  height: 64px;
  border-radius: var(--radius-sm);
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: var(--gold);
  opacity: 0.4;
}
.ad-row__img-placeholder svg { width: 22px; height: 22px; }

.ad-row__info {
  flex: 1;
  min-width: 0;
}

.ad-row__title {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text-main);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.ad-row__city {
  font-size: 0.78rem;
  color: var(--gold);
  margin-top: 0.1rem;
}

.ad-row__desc {
  font-size: 0.78rem;
  color: var(--text-muted);
  margin-top: 0.15rem;
  display: -webkit-box;
  -webkit-line-clamp: 1;
  line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.ad-row__actions {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  flex-shrink: 0;
}

.btn-xs {
  font-size: 0.72rem;
  padding: 0.3rem 0.65rem;
  border-radius: 12px;
  white-space: nowrap;
}
</style>
