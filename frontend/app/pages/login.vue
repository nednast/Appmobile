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
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem;
}

.login-box {
  width: 100%;
  max-width: 400px;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 2rem 1.5rem;
  box-shadow: var(--shadow);
}

.login-brand {
  text-align: center;
  margin-bottom: 2rem;
}

.login-brand-icon {
  display: inline-block;
  font-size: 2rem;
  color: var(--gold);
  margin-bottom: 0.5rem;
  animation: spin 8s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}

.login-subtitle {
  font-size: 0.85rem;
  color: var(--text-muted);
  margin-top: 0.4rem;
}

.login-form { display: flex; flex-direction: column; gap: 0.25rem; }

@media (min-width: 480px) {
  .login-box { padding: 2.5rem 2rem; }
}
</style>