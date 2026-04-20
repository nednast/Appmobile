<script setup>
const route = useRoute()
const { post, fetchPost } = usePosts()
const { public: { APP_ENV, WEBAPI_URL, APPAPI_URL } } = useRuntimeConfig()
const apiUrl = APP_ENV === 'mobile' ? APPAPI_URL : WEBAPI_URL

onMounted(() => fetchPost(route.params.id))
</script>

<template>
  <div class="page-bg" />
  <div class="page-wrapper">

    <NuxtLink to="/posts" class="back-link">Retour aux posts</NuxtLink>

    <p v-if="!post" class="loading">Chargement...</p>

    <PostDetail
      v-else
      :post="post"
      :api-url="apiUrl"
      mode="view"
    />

  </div>
</template>