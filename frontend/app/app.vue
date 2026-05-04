<script setup>
const { token, fetchUser, user } = useAuth()
const { public: { APP_ENV, WEBAPI_URL, APPAPI_URL } } = useRuntimeConfig()
const apiUrl = APP_ENV === 'mobile' ? APPAPI_URL : WEBAPI_URL
const { requestPermission, notifyLike, notifyComment } = useNotifications()

const prevState = {}

const pollNotifications = async () => {
  if (!token.value || !user.value) return

  try {
    const res = await fetch(`${apiUrl}/api/user/posts`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    if (!res.ok) return

    const data = await res.json()
    const posts = data.data ?? data

    for (const post of posts) {
      const prev = prevState[post.id]
      if (prev) {
        if (post.likes_count > prev.likes) {
          await notifyLike(post.description)
        }
        if (post.comments_count > prev.comments) {
          await notifyComment('Quelqu\'un', post.description)
        }
      }
      prevState[post.id] = {
        likes:    post.likes_count    ?? 0,
        comments: post.comments_count ?? 0,
      }
    }
  } catch {}
}

onMounted(async () => {
  if (token.value) {
    await fetchUser()
    await requestPermission()
    await pollNotifications()
    setInterval(pollNotifications, 10000)
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
