<script setup>
const { posts, fetchPosts } = usePosts()
const { public: { APP_ENV, WEBAPI_URL, APPAPI_URL } } = useRuntimeConfig()
const apiUrl = APP_ENV === 'mobile' ? APPAPI_URL : WEBAPI_URL

onMounted(fetchPosts)
</script>

<template>
  <div class="page-bg" />
  <div class="page-wrapper">

    <div class="page-header">
      <NuxtLink to="/" class="back-link">Retour</NuxtLink>
      <h1>Tous les posts</h1>
    </div>

    <p v-if="!posts.length" class="loading">Chargement...</p>

    <div class="posts-grid">
      <CardPost
        v-for="p in posts"
        :key="p.id"
        :post="p"
        :api-url="apiUrl"
        :show-link="true"
      />
    </div>

  </div>
</template>