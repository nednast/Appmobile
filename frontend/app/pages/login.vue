<script setup lang="ts">
import { ref } from 'vue' // N'ayez pas peur d'importer ref
import './login.css'

// -- La logique n'a pas changé --
// On suppose que useAuth() est importé ou disponible globalement
// const { login } = useAuth()
const email = ref('')
const password = ref('')
const message = ref('')

const handleLogin = async () => {
  // const success = await login(email.value, password.value)
  const success = false // Temporairement pour tester, afin d'afficher le message

  if (success) {
    message.value = 'Connexion réussie !'
    // navigateTo('/')
  } else {
    message.value = 'Email ou mot de passe incorrect'
  }
}
</script>

<template>
  <div class="login-wrapper">
    <div class="animated-gradient-bg"></div>

    <div class="login-container">
      <h1>Connexion</h1>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="input-group">
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

        <div class="input-group">
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

        <button type="submit" class="login-button">Se connecter</button>
      </form>

      <p v-if="message" :class="['message', { 'error': message.includes('incorrect') }]">
        {{ message }}
      </p>
    </div>
  </div>
</template>

<!-- Стили вынесены в login.css -->