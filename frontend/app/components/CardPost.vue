<script setup>
import { Share } from '@capacitor/share'

const props = defineProps({
  post: { type: Object, required: true },
  apiUrl: { type: String, required: true },
  showLink: { type: Boolean, default: true },
  editable: { type: Boolean, default: false }
})

const emit = defineEmits(['delete'])

const sharePost = async (post) => {
  const url = `${props.apiUrl}/posts/${post.id}`
  try {
    await Share.share({
      title: `Post de ${post.user?.firstname}`,
      text: post.description,
      url
    })
  } catch {
    // Fallback web
    if (navigator.share) {
      await navigator.share({ title: post.description, url })
    } else {
      await navigator.clipboard.writeText(url)
      alert('Lien copié !')
    }
  }
}
</script>

<template>
  <div class="border rounded-xl p-4 shadow flex flex-col gap-3 bg-white">
    <img
      v-if="post.image"
      :src="`${apiUrl}/storage/${post.image}`"
      class="rounded-lg w-full h-48 object-cover"
      alt="Image du post"
    />
    <p class="text-gray-700">{{ post.description }}</p>
    <p class="text-sm text-gray-400">
      Par {{ post.user?.firstname }} {{ post.user?.lastname }}
    </p>
    <div class="flex gap-2 mt-auto flex-wrap">
      <NuxtLink
        v-if="showLink"
        :to="`/posts/${post.id}`"
        class="text-blue-500 text-sm"
      >
        Voir le post
      </NuxtLink>
      <NuxtLink
        v-if="editable"
        :to="`/account/posts/${post.id}`"
        class="text-yellow-500 text-sm"
      >
        Modifier
      </NuxtLink>
      <button
        v-if="editable"
        class="text-red-500 text-sm"
        @click="emit('delete')"
      >
        Supprimer
      </button>
      <button class="text-green-500 text-sm ml-auto" @click="sharePost(post)">
        Partager
      </button>
    </div>
  </div>
</template>