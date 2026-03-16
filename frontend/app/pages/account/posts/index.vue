<script setup>
//definePageMeta({ middleware: 'auth' })
const { posts, fetchUserPosts, deletePost } = usePosts()
const { public: { APP_ENV, WEBAPI_URL, APPAPI_URL } } = useRuntimeConfig()
const apiUrl = APP_ENV === 'mobile' ? APPAPI_URL : WEBAPI_URL

onMounted(fetchUserPosts)
</script>

<template>
  <div class="page-bg" />
  <div class="page-wrapper">

    <div class="page-header">
      <NuxtLink to="/" class="back-link">Retour</NuxtLink>
      <h1>Mes posts</h1>
      <NuxtLink to="/account/posts/new" class="btn btn-gold btn-sm">+ Nouveau</NuxtLink>
    </div>

    <p v-if="!posts.length" class="loading">Aucun post pour le moment.</p>

    <div class="posts-grid">
      <CardPost
        v-for="p in posts"
        :key="p.id"
        :post="p"
        :api-url="apiUrl"
        :show-link="true"
        :editable="true"
        @delete="deletePost(p.id)"
      />
    </div>

  </div>
</template>