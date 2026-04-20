<script setup>
const { user, logout } = useAuth()
</script>

<template>
  <div class="page-bg" />
  <div class="page-wrapper">

    <!-- Header -->
    <div class="page-header">
      <div>
        <h1>Dashboard</h1>
        <p v-if="user" style="font-size:0.85rem; margin-top:0.2rem;">
          Bonjour, <strong style="color:var(--gold)">{{ user.firstname }}</strong> 👋
        </p>
      </div>
      <div v-if="user?.avatar">
        <img :src="user.avatar" class="avatar" alt="avatar" />
      </div>
      <div v-else class="avatar-placeholder">{{ user?.firstname?.[0] ?? '?' }}</div>
    </div>

    <!-- Quick links -->
    <div class="dashboard-grid">
      <NuxtLink to="/posts" class="dash-card">
        <span class="dash-icon">📰</span>
        <span class="dash-label">Tous les posts</span>
      </NuxtLink>
      <NuxtLink to="/account/posts" class="dash-card">
        <span class="dash-icon">✏️</span>
        <span class="dash-label">Mes posts</span>
      </NuxtLink>
      <NuxtLink to="/account" class="dash-card">
        <span class="dash-icon">👤</span>
        <span class="dash-label">Mon compte</span>
      </NuxtLink>
      <NuxtLink to="/ads" class="dash-card">
      <span class="dash-icon">📍</span>
      <span class="dash-label">Annonces</span>
    </NuxtLink>
      <button class="dash-card dash-card--danger" @click="logout()">
        <span class="dash-icon">🚪</span>
        <span class="dash-label">Déconnexion</span>
      </button>
    </div>

  </div>
</template>

<style scoped>
.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.85rem;
}

.dash-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.5rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.6rem;
  text-decoration: none;
  cursor: pointer;
  transition: border-color 0.2s, transform 0.15s;
  font-family: var(--font-body);
  color: inherit;
  width: 100%;
}

.dash-card:active { transform: scale(0.97); }
.dash-card:hover  { border-color: var(--gold); }

.dash-icon  { font-size: 1.8rem; line-height: 1; }
.dash-label { font-size: 0.8rem; color: var(--text-muted); text-align: center; }

.dash-card--danger:hover { border-color: var(--rose-light); }
.dash-card--danger .dash-label { color: var(--rose-light); }
</style>