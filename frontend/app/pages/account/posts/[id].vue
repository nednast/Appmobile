<script setup>
definePageMeta({ middleware: 'auth' })
const route = useRoute()
const { post, fetchUserPost, updatePost, createPost } = usePosts()
const { public: { APP_ENV, WEBAPI_URL, APPAPI_URL } } = useRuntimeConfig()
const apiUrl = APP_ENV === 'mobile' ? APPAPI_URL : WEBAPI_URL

const isNew = computed(() => route.params.id === 'new')

onMounted(async () => {
  if (!isNew.value) await fetchUserPost(route.params.id)
})

const handleSave = async (formData) => {
  if (isNew.value) {
    await createPost(formData)
  } else {
    await updatePost(route.params.id, formData)
  }
  navigateTo('/account/posts')
}
</script>

<template>
  <div class="page-bg" />
  <div class="page-wrapper">

    <NuxtLink to="/account/posts" class="back-link">Mes posts</NuxtLink>

    <h1 style="margin-bottom: 1.25rem;">
      {{ isNew ? 'Nouveau post' : 'Modifier le post' }}
    </h1>

    <PostDetail
      :post="post"
      :api-url="apiUrl"
      mode="edit"
      @save="handleSave"
    />

  </div>
</template>