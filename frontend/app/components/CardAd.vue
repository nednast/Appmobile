<template>
  <div class="card-ad">
    <img
      v-if="ad.image"
      :src="`${apiBase}/storage/${ad.image}`"
      :alt="ad.title"
      class="card-ad__image"
    />
    <div class="card-ad__body">
      <h3 class="card-ad__title">{{ ad.title }}</h3>
      <p class="card-ad__city">
        <svg class="card-ad__city-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
        {{ ad.city }}
      </p>
      <p v-if="ad.distance !== undefined" class="card-ad__distance">
        À {{ Math.round(ad.distance) }} km
      </p>
      <p class="card-ad__description">{{ ad.description }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Ad } from '../composables/useAds'

defineProps<{ ad: Ad }>()

const config = useRuntimeConfig()
const apiBase = config.public.APP_ENV === 'mobile'
  ? config.public.APPAPI_URL
  : config.public.WEBAPI_URL
</script>

<style scoped>
.card-ad {
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  background: var(--bg-card);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
  transition: border-color 0.2s, transform 0.15s;
}

.card-ad:hover {
  border-color: var(--gold);
  transform: translateY(-2px);
}

.card-ad__image {
  width: 100%;
  height: 180px;
  object-fit: cover;
}

.card-ad__body {
  padding: 12px;
}

.card-ad__title {
  font-size: 1rem;
  font-weight: 600;
  margin-bottom: 4px;
  color: var(--text-main);
}

.card-ad__city {
  color: var(--text-muted);
  font-size: 0.85rem;
  margin-bottom: 4px;
}

.card-ad__distance {
  color: var(--gold);
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 6px;
}

.card-ad__city {
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.card-ad__city-icon {
  width: 13px;
  height: 13px;
  flex-shrink: 0;
  color: var(--gold);
  opacity: 0.7;
}

.card-ad__description {
  font-size: 0.875rem;
  color: var(--text-muted);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>