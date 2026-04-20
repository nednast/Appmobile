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
      <p class="card-ad__city">📍 {{ ad.city }}</p>
      <p v-if="ad.distance !== undefined" class="card-ad__distance">
        À {{ Math.round(ad.distance) }} km
      </p>
      <p class="card-ad__description">{{ ad.description }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Ad } from '~/composables/useAds'

defineProps<{ ad: Ad }>()

const config = useRuntimeConfig()
const apiBase = config.public.APP_ENV === 'mobile'
  ? config.public.APPAPI_URL
  : config.public.WEBAPI_URL
</script>

<style scoped>
.card-ad {
  border: 1px solid #ddd;
  border-radius: 10px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
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
}

.card-ad__city {
  color: #666;
  font-size: 0.85rem;
  margin-bottom: 4px;
}

.card-ad__distance {
  color: #1a73e8;
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 6px;
}

.card-ad__description {
  font-size: 0.875rem;
  color: #444;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>