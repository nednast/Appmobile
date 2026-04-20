<script setup>
const { token, fetchUser } = useAuth()

onMounted(async () => {
  if (token.value) {
    await fetchUser()
  } else {
    navigateTo('/login')
  }
})
</script>

<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>