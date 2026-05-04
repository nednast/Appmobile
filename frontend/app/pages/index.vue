<script setup>
const { user, logout } = useAuth()
const { public: { APP_ENV, WEBAPI_URL, APPAPI_URL } } = useRuntimeConfig()
const apiUrl = APP_ENV === 'mobile' ? APPAPI_URL : WEBAPI_URL
</script>

<template>
  <div class="page-bg" />
  <div class="dash-page">

    <!-- Hero -->
    <div class="dash-hero">
      <div class="dash-hero__orb dash-hero__orb--1" />
      <div class="dash-hero__orb dash-hero__orb--2" />
      <div class="dash-hero__content">
        <div class="dash-hero__avatar">
          <img v-if="user?.avatar" :src="`${apiUrl}/storage/${user.avatar}`" alt="avatar" />
          <span v-else class="dash-hero__initials">{{ user?.firstname?.[0] ?? '?' }}</span>
        </div>
        <div class="dash-hero__info">
          <p class="dash-hero__greeting">Bienvenue,</p>
          <h1 class="dash-hero__name">{{ user?.firstname }} {{ user?.lastname }}</h1>
          <p class="dash-hero__email">{{ user?.email }}</p>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="dash-content">

      <p class="dash-label">Naviguer</p>
      <div class="dash-list">
        <NuxtLink to="/posts" class="dash-row">
          <div class="dash-row__icon dash-row__icon--blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2.5"/><path d="M7 8h10M7 12h10M7 16h6"/></svg>
          </div>
          <div class="dash-row__body">
            <p class="dash-row__title">Tous les posts</p>
            <p class="dash-row__sub">Fil d'actualité</p>
          </div>
          <svg class="dash-row__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </NuxtLink>

        <NuxtLink to="/ads" class="dash-row">
          <div class="dash-row__icon dash-row__icon--gold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
          </div>
          <div class="dash-row__body">
            <p class="dash-row__title">Annonces</p>
            <p class="dash-row__sub">Près de vous</p>
          </div>
          <svg class="dash-row__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </NuxtLink>

        <NuxtLink to="/map" class="dash-row">
          <div class="dash-row__icon dash-row__icon--green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
          </div>
          <div class="dash-row__body">
            <p class="dash-row__title">Carte</p>
            <p class="dash-row__sub">Explorer la zone</p>
          </div>
          <svg class="dash-row__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </NuxtLink>
      </div>

      <p class="dash-label">Mon compte</p>
      <div class="dash-list">
        <NuxtLink to="/account/posts" class="dash-row">
          <div class="dash-row__icon dash-row__icon--rose">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          </div>
          <div class="dash-row__body">
            <p class="dash-row__title">Mes posts</p>
            <p class="dash-row__sub">Créer · Modifier</p>
          </div>
          <svg class="dash-row__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </NuxtLink>

        <NuxtLink to="/account" class="dash-row">
          <div class="dash-row__icon dash-row__icon--purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
          </div>
          <div class="dash-row__body">
            <p class="dash-row__title">Mon profil</p>
            <p class="dash-row__sub">Infos · Avatar · Mot de passe</p>
          </div>
          <svg class="dash-row__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
        </NuxtLink>
      </div>

      <button class="dash-logout" @click="logout()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Se déconnecter
      </button>

    </div>
  </div>
</template>

<style scoped>
.dash-page {
  min-height: 100dvh;
  padding-bottom: 5.5rem;
}

/* ── Hero ── */
.dash-hero {
  position: relative;
  overflow: hidden;
  padding: 3.5rem 1.5rem 2.5rem;
  background: linear-gradient(145deg, rgba(74,53,53,0.9) 0%, rgba(61,44,44,0.7) 100%);
  border-bottom: 1px solid rgba(201,168,76,0.15);
}

.dash-hero__orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(60px);
  pointer-events: none;
}
.dash-hero__orb--1 {
  width: 220px; height: 220px;
  background: radial-gradient(circle, rgba(201,168,76,0.18) 0%, transparent 70%);
  top: -60px; right: -40px;
  animation: orbFloat 8s ease-in-out infinite;
}
.dash-hero__orb--2 {
  width: 160px; height: 160px;
  background: radial-gradient(circle, rgba(168,90,93,0.15) 0%, transparent 70%);
  bottom: -40px; left: -20px;
  animation: orbFloat 11s ease-in-out infinite reverse;
}

@keyframes orbFloat {
  0%,100% { transform: translate(0,0); }
  50%      { transform: translate(15px, -15px); }
}

.dash-hero__content {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  position: relative;
  z-index: 1;
  animation: fadeInUp 0.5s ease both;
}

.dash-hero__avatar {
  width: 72px; height: 72px;
  border-radius: 50%;
  border: 2px solid rgba(201,168,76,0.5);
  box-shadow: 0 0 0 4px rgba(201,168,76,0.08), 0 8px 24px rgba(0,0,0,0.4);
  overflow: hidden;
  flex-shrink: 0;
  background: linear-gradient(135deg, #573e3e, #3d2c2c);
  display: flex; align-items: center; justify-content: center;
}
.dash-hero__avatar img { width: 100%; height: 100%; object-fit: cover; }
.dash-hero__initials { font-size: 1.6rem; font-weight: 600; color: var(--gold); }

.dash-hero__greeting {
  font-size: 0.75rem;
  color: rgba(201,168,76,0.7);
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 0.15rem;
}
.dash-hero__name {
  font-family: var(--font-title);
  font-size: 1.5rem;
  color: var(--gold-bright);
  line-height: 1.15;
  margin-bottom: 0.2rem;
}
.dash-hero__email {
  font-size: 0.75rem;
  color: var(--text-muted);
  opacity: 0.7;
}

/* ── Content ── */
.dash-content {
  padding: 1.5rem 1.1rem;
  max-width: 640px;
  margin: 0 auto;
}

.dash-label {
  font-size: 0.7rem;
  font-weight: 500;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--text-muted);
  opacity: 0.6;
  margin-bottom: 0.6rem;
  padding-left: 0.25rem;
}

.dash-list {
  background: rgba(74,53,53,0.45);
  backdrop-filter: blur(16px);
  border: 1px solid rgba(201,168,76,0.1);
  border-top-color: rgba(201,168,76,0.2);
  border-radius: var(--radius);
  overflow: hidden;
  margin-bottom: 1.5rem;
  animation: fadeInUp 0.5s ease both;
}

.dash-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.1rem;
  text-decoration: none;
  color: inherit;
  border-bottom: 1px solid rgba(201,168,76,0.06);
  transition: background var(--transition);
  cursor: pointer;
}
.dash-row:last-child { border-bottom: none; }
.dash-row:active { background: rgba(255,255,255,0.04); }

.dash-row__icon {
  width: 40px; height: 40px;
  border-radius: 11px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.dash-row__icon svg { width: 20px; height: 20px; }

.dash-row__icon--blue   { background: rgba(96,165,250,0.15); color: #93c5fd; }
.dash-row__icon--gold   { background: rgba(201,168,76,0.15); color: var(--gold); }
.dash-row__icon--green  { background: rgba(74,222,128,0.12); color: #86efac; }
.dash-row__icon--rose   { background: rgba(168,90,93,0.18);  color: var(--rose-light); }
.dash-row__icon--purple { background: rgba(167,139,250,0.15); color: #c4b5fd; }

.dash-row__body { flex: 1; }
.dash-row__title {
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--text-main);
}
.dash-row__sub {
  font-size: 0.75rem;
  color: var(--text-muted);
  margin-top: 0.1rem;
}

.dash-row__arrow {
  width: 16px; height: 16px;
  color: var(--text-muted);
  opacity: 0.4;
  flex-shrink: 0;
}

/* ── Logout ── */
.dash-logout {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  width: 100%;
  padding: 0.9rem;
  background: transparent;
  border: 1px solid rgba(168,90,93,0.25);
  border-radius: var(--radius);
  color: var(--rose-light);
  font-family: var(--font-body);
  font-size: 0.875rem;
  letter-spacing: 0.03em;
  cursor: pointer;
  transition: background var(--transition), border-color var(--transition);
  animation: fadeInUp 0.5s 0.2s ease both;
}
.dash-logout svg { width: 18px; height: 18px; }
.dash-logout:hover {
  background: rgba(168,90,93,0.08);
  border-color: rgba(168,90,93,0.5);
}
</style>
