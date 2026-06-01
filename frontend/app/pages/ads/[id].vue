<script setup lang="ts">
import { useAds } from '~/composables/useAds'
import type { Ad } from '~/composables/useAds'

const route = useRoute()
const { fetchAd } = useAds()
const { public: { APP_ENV, WEBAPI_URL, APPAPI_URL } } = useRuntimeConfig()
const apiUrl = APP_ENV === 'mobile' ? APPAPI_URL : WEBAPI_URL

const ad = ref<Ad | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

onMounted(async () => {
  try {
    ad.value = await fetchAd(Number(route.params.id))
  } catch (e: any) {
    error.value = e.message
  } finally {
    loading.value = false
  }
})

const formattedDate = computed(() => {
  if (!ad.value) return ''
  return new Date(ad.value.created_at).toLocaleDateString('fr-FR', {
    day: 'numeric', month: 'long', year: 'numeric'
  })
})
</script>

<template>
  <div class="page-bg" />
  <div class="page-wrapper">

    <div class="page-header">
      <NuxtLink to="/ads" class="back-link">Retour</NuxtLink>
      <h1>Détail annonce</h1>
    </div>

    <p v-if="loading" class="loading">Chargement...</p>
    <p v-else-if="error" class="alert alert-error">{{ error }}</p>

    <div v-else-if="ad" class="ad-detail">

      <div v-if="ad.image" class="ad-detail__media">
        <img
          :src="`${apiUrl}/storage/${ad.image}`"
          :alt="ad.title"
          class="ad-detail__img"
        />
      </div>
      <div v-else class="ad-detail__media ad-detail__media--empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="ad-detail__empty-icon"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
      </div>

      <div class="ad-detail__body">

        <div class="ad-detail__header">
          <h2 class="ad-detail__title">{{ ad.title }}</h2>
          <div class="ad-detail__badges">
            <span class="ad-detail__city">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="ad-detail__icon"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
              {{ ad.city }}
            </span>
            <span v-if="ad.distance !== undefined" class="ad-detail__distance">
              À {{ Math.round(ad.distance) }} km
            </span>
          </div>
        </div>

        <p class="ad-detail__description">{{ ad.description }}</p>

        <div class="ad-detail__meta">
          <div class="ad-detail__author">
            <div class="ad-detail__avatar">
              {{ ad.user?.firstname?.[0] ?? '?' }}{{ ad.user?.lastname?.[0] ?? '' }}
            </div>
            <div>
              <p class="ad-detail__author-name">{{ ad.user?.firstname }} {{ ad.user?.lastname }}</p>
              <p class="ad-detail__author-date">Publié le {{ formattedDate }}</p>
            </div>
          </div>
        </div>

        <div class="ad-detail__map-link" v-if="ad.lat && ad.lng">
          <NuxtLink to="/map" class="btn btn-outline btn-full">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="btn-icon-sm"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
            Voir sur la carte
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.ad-detail {
  border-radius: var(--radius);
  overflow: hidden;
  background: var(--bg-card);
  border: 1px solid var(--border);
}

.ad-detail__media {
  width: 100%;
  height: 240px;
  overflow: hidden;
  background: linear-gradient(135deg, rgba(255,255,255,0.03), rgba(255,255,255,0.07));
}

.ad-detail__media--empty {
  display: flex;
  align-items: center;
  justify-content: center;
}

.ad-detail__empty-icon {
  width: 48px;
  height: 48px;
  color: rgba(201,168,76,0.25);
}

.ad-detail__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.ad-detail__body {
  padding: 1.25rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.ad-detail__header {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.ad-detail__title {
  font-size: 1.3rem;
  font-weight: 700;
  color: var(--text-main);
  line-height: 1.3;
}

.ad-detail__badges {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.ad-detail__city {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.85rem;
  color: var(--text-muted);
}

.ad-detail__icon {
  width: 13px;
  height: 13px;
  color: var(--gold);
  opacity: 0.7;
  flex-shrink: 0;
}

.ad-detail__distance {
  font-size: 0.85rem;
  color: var(--gold);
  font-weight: 500;
}

.ad-detail__description {
  font-size: 0.95rem;
  line-height: 1.7;
  color: var(--text-muted);
  border-top: 1px solid var(--border);
  padding-top: 1rem;
}

.ad-detail__meta {
  border-top: 1px solid var(--border);
  padding-top: 1rem;
}

.ad-detail__author {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.ad-detail__avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--gold);
  color: #000;
  font-size: 0.7rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  text-transform: uppercase;
}

.ad-detail__author-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-main);
}

.ad-detail__author-date {
  font-size: 0.75rem;
  color: var(--text-muted);
  margin-top: 0.1rem;
}

.ad-detail__map-link {
  padding-top: 0.5rem;
}

.btn-icon-sm {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
  margin-right: 0.4rem;
}
</style>
