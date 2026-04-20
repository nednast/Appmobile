<template>
  <div>
    <slot />
    <nav class="bottom-nav">
      <NuxtLink to="/" class="nav-item">
        <span class="nav-icon">🏠</span>
        Accueil
      </NuxtLink>
      <NuxtLink to="/posts" class="nav-item">
        <span class="nav-icon">📰</span>
        Posts
      </NuxtLink>
      <NuxtLink to="/account" class="nav-item">
        <span class="nav-icon">👤</span>
        Compte
      </NuxtLink>
    </nav>
  </div>
</template>