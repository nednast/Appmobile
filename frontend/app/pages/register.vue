<script setup lang="ts">
const { register } = useAuth()

const form = reactive({
  firstname: '',
  lastname: '',
  email: '',
  password: '',
  password_confirmation: ''
})
const message = ref('')
const isError = ref(false)
const fieldErrors = ref<Record<string, string[]>>({})

const handleRegister = async () => {
  message.value = ''
  fieldErrors.value = {}
  const result = await register(
    form.firstname,
    form.lastname,
    form.email,
    form.password,
    form.password_confirmation
  )
  if (result.success) {
    navigateTo('/')
  } else {
    isError.value = true
    fieldErrors.value = result.errors
    message.value = 'Veuillez corriger les erreurs ci-dessous'
  }
}
</script>

<template>
  <div class="login-page">
    <div class="page-bg" />

    <div class="login-box">
      <div class="login-brand">
        <span class="login-brand-icon">✦</span>
        <h1>Inscription</h1>
        <div class="login-brand-divider"></div>
        <p class="login-subtitle">Créez votre compte pour commencer</p>
      </div>

      <form @submit.prevent="handleRegister" class="login-form">
        <div class="form-row">
          <div class="form-group">
            <label for="firstname">Prénom</label>
            <input
              v-model="form.firstname"
              id="firstname"
              type="text"
              placeholder="Prénom"
              autocomplete="given-name"
              required
            />
            <span v-if="fieldErrors.firstname" class="field-error">{{ fieldErrors.firstname[0] }}</span>
          </div>
          <div class="form-group">
            <label for="lastname">Nom</label>
            <input
              v-model="form.lastname"
              id="lastname"
              type="text"
              placeholder="Nom"
              autocomplete="family-name"
              required
            />
            <span v-if="fieldErrors.lastname" class="field-error">{{ fieldErrors.lastname[0] }}</span>
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input
            v-model="form.email"
            id="email"
            type="email"
            placeholder="votre@email.com"
            autocomplete="email"
            required
          />
          <span v-if="fieldErrors.email" class="field-error">{{ fieldErrors.email[0] }}</span>
        </div>

        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input
            v-model="form.password"
            id="password"
            type="password"
            placeholder="••••••••"
            autocomplete="new-password"
            required
          />
          <span v-if="fieldErrors.password" class="field-error">{{ fieldErrors.password[0] }}</span>
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirmer le mot de passe</label>
          <input
            v-model="form.password_confirmation"
            id="password_confirmation"
            type="password"
            placeholder="••••••••"
            autocomplete="new-password"
            required
          />
        </div>

        <button type="submit" class="btn btn-primary btn-full" style="margin-top: 0.5rem;">
          Créer mon compte
        </button>
      </form>

      <div v-if="message" :class="['alert', isError ? 'alert-error' : 'alert-success']">
        {{ message }}
      </div>

      <p class="login-footer">
        Déjà un compte ?
        <NuxtLink to="/login" class="login-footer-link">Se connecter</NuxtLink>
      </p>
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
  max-width: 420px;
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
  margin-bottom: 2rem;
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

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.field-error {
  font-size: 0.75rem;
  color: var(--rose-light);
  margin-top: 0.2rem;
  display: block;
}

.login-footer {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.82rem;
  color: var(--text-muted);
}

.login-footer-link {
  color: var(--gold);
  text-decoration: none;
  font-weight: 500;
}
.login-footer-link:hover { text-decoration: underline; }

@media (min-width: 480px) {
  .login-box { padding: 3rem 2.25rem; }
}
</style>
