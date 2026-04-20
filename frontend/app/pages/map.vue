<template>
  <div class="page-bg" />
  <div class="map-page">
    <div class="map-page__header">
      <NuxtLink to="/" class="back-link">Retour</NuxtLink>
      <h1>Carte</h1>
    </div>
    <MapView />
  </div>
</template>

<style scoped>
.map-page {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.map-page__header {
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}
</style>