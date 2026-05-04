<script setup lang="ts">
import { ref } from 'vue'

const { login } = useAuth()
const email = ref('')
const password = ref('')
const message = ref('')
const isError = ref(false)

const handleLogin = async () => {
  message.value = ''
  const success = await login(email.value, password.value)
  if (success) {
    message.value = 'Connexion réussie !'
    isError.value = false
    navigateTo('/')
  } else {
    message.value = 'Email ou mot de passe incorrect'
    isError.value = true
  }
}
</script>

<template>
  <div class="login-page">
    <div class="page-bg" />

    <div class="login-box">
      <!-- Logo / titre -->
      <div class="login-brand">
        <span class="login-brand-icon">✦</span>
        <h1>Connexion</h1>
        <div class="login-brand-divider"></div>
        <p class="login-subtitle">Bienvenue, connectez-vous pour continuer</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            v-model="email"
            id="email"
            type="email"
            placeholder="votre@email.com"
            autocomplete="email"
            required
          />
        </div>

        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input
            v-model="password"
            id="password"
            type="password"
            placeholder="••••••••"
            autocomplete="current-password"
            required
          />
        </div>

        <button type="submit" class="btn btn-primary btn-full" style="margin-top: 0.5rem;">
          Se connecter
        </button>
      </form>

      <div v-if="message" :class="['alert', isError ? 'alert-error' : 'alert-success']">
        {{ message }}
      </div>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  min-height: 100dvh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
}

.login-box {
  width: 100%;
  max-width: 400px;
  background: rgba(42,31,31,0.80);
  backdrop-filter: blur(32px) saturate(150%);
  -webkit-backdrop-filter: blur(32px) saturate(150%);
  border: 1px solid rgba(201,168,76,0.15);
  border-top-color: rgba(201,168,76,0.35);
  border-radius: var(--radius);
  padding: 2.5rem 1.75rem;
  box-shadow:
    0 32px 80px rgba(0,0,0,0.7),
    0 0 0 1px rgba(255,255,255,0.02) inset,
    0 0 80px rgba(201,168,76,0.04);
}

.login-brand {
  text-align: center;
  margin-bottom: 2.5rem;
}

.login-brand-icon {
  display: block;
  font-size: 2.4rem;
  color: var(--gold);
  margin-bottom: 1rem;
  animation: glow-pulse 3s ease-in-out infinite;
  filter: drop-shadow(0 0 12px rgba(201,168,76,0.5));
}

@keyframes glow-pulse {
  0%, 100% { filter: drop-shadow(0 0 8px rgba(201,168,76,0.4)); transform: scale(1); }
  50%       { filter: drop-shadow(0 0 20px rgba(201,168,76,0.7)); transform: scale(1.08); }
}

/* Ligne décorative sous le titre */
.login-brand h1 {
  font-size: clamp(1.8rem, 7vw, 2.4rem);
  margin-bottom: 0.5rem;
}

.login-brand-divider {
  width: 40px;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--gold), transparent);
  margin: 0.9rem auto 0.7rem;
}

.login-subtitle {
  font-size: 0.82rem;
  color: var(--text-muted);
  letter-spacing: 0.03em;
}

.login-form { display: flex; flex-direction: column; gap: 0.3rem; }

@media (min-width: 480px) {
  .login-box { padding: 3rem 2.25rem; }
}
</style>