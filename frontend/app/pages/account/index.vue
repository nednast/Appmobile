<script setup>
import { Camera, CameraResultType, CameraSource } from '@capacitor/camera'

const { public: { APP_ENV, WEBAPI_URL, APPAPI_URL } } = useRuntimeConfig()
const apiUrl = APP_ENV === 'mobile' ? APPAPI_URL : WEBAPI_URL
const { user, token, fetchUser } = useAuth()

const profileMsg = ref('')
const profileError = ref(false)
const passwordMsg = ref('')
const passwordError = ref(false)

const form = reactive({ firstname: '', lastname: '', email: '' })

onMounted(() => {
  if (user.value) {
    form.firstname = user.value.firstname
    form.lastname  = user.value.lastname
    form.email     = user.value.email
  }
})

const updateProfile = async () => {
  profileMsg.value = ''
  const res = await fetch(`${apiUrl}/api/user`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token.value}` },
    body: JSON.stringify(form)
  })
  profileError.value = !res.ok
  profileMsg.value = res.ok ? 'Profil mis à jour ✓' : 'Erreur lors de la mise à jour'
  if (res.ok) await fetchUser()
}

const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' })

const updatePassword = async () => {
  passwordMsg.value = ''
  const res = await fetch(`${apiUrl}/api/user/password`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${token.value}` },
    body: JSON.stringify(passwordForm)
  })
  passwordError.value = !res.ok
  passwordMsg.value = res.ok ? 'Mot de passe modifié ✓' : 'Mot de passe actuel incorrect'
}

const uploadAvatar = async (event) => {
  const file = event.target.files[0]
  const formData = new FormData()
  formData.append('avatar', file)
  await fetch(`${apiUrl}/api/user/avatar`, {
    method: 'POST',
    headers: { Authorization: `Bearer ${token.value}` },
    body: formData
  })
  await fetchUser()
}

const takePicture = async () => {
  const photo = await Camera.getPhoto({
    quality: 80,
    allowEditing: false,
    resultType: CameraResultType.DataUrl,
    source: CameraSource.Camera
  })
  const blob = await (await fetch(photo.dataUrl)).blob()
  const formData = new FormData()
  formData.append('avatar', blob, 'avatar.jpg')
  await fetch(`${apiUrl}/api/user/avatar`, {
    method: 'POST',
    headers: { Authorization: `Bearer ${token.value}` },
    body: formData
  })
  await fetchUser()
}
</script>

<template>
  <div class="page-bg" />
  <div class="page-wrapper">

    <!-- Header -->
    <div class="page-header">
      <NuxtLink to="/" class="back-link">Retour</NuxtLink>
      <h1>Mon compte</h1>
    </div>

    <!-- Avatar -->
    <div class="card">
      <div class="avatar-row">
        <img
          v-if="user?.avatar"
          :src="`${apiUrl}/storage/${user.avatar}`"
          class="avatar avatar-lg"
          alt="Avatar"
        />
        <div v-else class="avatar-placeholder avatar-lg">
          {{ user?.firstname?.[0] ?? '?' }}
        </div>
        <div>
          <p class="avatar-name">{{ user?.firstname }} {{ user?.lastname }}</p>
          <p class="avatar-email">{{ user?.email }}</p>
        </div>
      </div>

      <hr class="divider" />

      <!-- Web -->
      <div v-if="APP_ENV !== 'mobile'">
        <label class="btn btn-outline btn-sm avatar-upload-label">
          📷 Changer l'avatar
          <input type="file" accept="image/*" @change="uploadAvatar" style="display:none;" />
        </label>
      </div>
      <!-- Mobile -->
      <div v-else>
        <button class="btn btn-outline btn-sm" @click="takePicture">
          📷 Prendre une photo
        </button>
      </div>
    </div>

    <!-- Profil -->
    <div class="card">
      <h2>Informations personnelles</h2>
      <form @submit.prevent="updateProfile">
        <div class="form-group">
          <label>Prénom</label>
          <input v-model="form.firstname" type="text" placeholder="Prénom" />
        </div>
        <div class="form-group">
          <label>Nom</label>
          <input v-model="form.lastname" type="text" placeholder="Nom" />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" placeholder="Email" autocomplete="email" />
        </div>
        <button type="submit" class="btn btn-primary btn-full">Mettre à jour le profil</button>
        <div v-if="profileMsg" :class="['alert', profileError ? 'alert-error' : 'alert-success']">
          {{ profileMsg }}
        </div>
      </form>
    </div>

    <!-- Mot de passe -->
    <div class="card">
      <h2>Modifier le mot de passe</h2>
      <form @submit.prevent="updatePassword">
        <div class="form-group">
          <label>Mot de passe actuel</label>
          <input v-model="passwordForm.current_password" type="password" autocomplete="current-password" />
        </div>
        <div class="form-group">
          <label>Nouveau mot de passe</label>
          <input v-model="passwordForm.password" type="password" autocomplete="new-password" />
        </div>
        <div class="form-group">
          <label>Confirmer le mot de passe</label>
          <input v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" />
        </div>
        <button type="submit" class="btn btn-primary btn-full">Modifier le mot de passe</button>
        <div v-if="passwordMsg" :class="['alert', passwordError ? 'alert-error' : 'alert-success']">
          {{ passwordMsg }}
        </div>
      </form>
    </div>

  </div>
</template>

<style scoped>
.avatar-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.25rem;
}

.avatar-lg {
  width: 64px;
  height: 64px;
  font-size: 1.4rem;
  flex-shrink: 0;
}

.avatar-name {
  font-size: 0.95rem;
  color: var(--text-main);
  font-weight: 500;
}

.avatar-email {
  font-size: 0.8rem;
  color: var(--text-muted);
  margin-top: 0.15rem;
}

.avatar-upload-label {
  cursor: pointer;
  display: inline-flex;
}
</style>