<template>
  <div class="map-page">
    <div class="map-page__header">
      <NuxtLink to="/" class="back-link">Retour</NuxtLink>
      <h1>Carte</h1>
    </div>
    <MapView />
  </div>
</template>

<script setup lang="ts">
useHead({
  bodyAttrs: { class: 'map-body' }
})
</script>

<style>
body.map-body {
  background: transparent !important;
}
body.map-body .page-bg {
  display: none;
}
</style>

<style scoped>
.map-page {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: transparent;
}

.map-page__header {
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  background: transparent;
}
</style>