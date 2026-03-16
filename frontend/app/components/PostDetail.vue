<script setup>
import { Share } from '@capacitor/share'

const props = defineProps({
  post: { type: Object, default: null },
  apiUrl: { type: String, required: true },
  mode: { type: String, default: 'view' } // 'view' | 'edit'
})

const emit = defineEmits(['save'])

// Formulaire local pour le mode edit
const form = reactive({
  description: '',
  image: null
})

watch(() => props.post, (p) => {
  if (p) form.description = p.description
}, { immediate: true })

const onFileChange = (e) => {
  const target = e.target
  form.image = target && target.files ? target.files[0] : null
}

const handleSubmit = () => {
  const data = new FormData()
  data.append('description', form.description)
  if (form.image) data.append('image', form.image)
  emit('save', data)
}

const sharePost = async () => {
  if (!props.post) return
  const url = `${props.apiUrl}/posts/${props.post.id}`
  try {
    await Share.share({
      title: `Post de ${props.post.user?.firstname}`,
      text: props.post.description,
      url
    })
  } catch {
    if (navigator.share) {
      await navigator.share({ url })
    } else {
      await navigator.clipboard.writeText(url)
      alert('Lien copié !')
    }
  }
}
</script>

<template>
  <!-- MODE VIEW -->
  <div v-if="mode === 'view' && post" class="flex flex-col gap-4">
    <img
      v-if="post.image"
      :src="`${apiUrl}/storage/${post.image}`"
      class="rounded-xl w-full max-h-96 object-cover"
      alt="Image"
    />
    <p class="text-xl">{{ post.description }}</p>
    <p class="text-gray-500">
      Par {{ post.user?.firstname }} {{ post.user?.lastname }}
    </p>
    <button class="text-green-500" @click="sharePost">Partager</button>
  </div>

  <!-- MODE EDIT -->
  <div v-else-if="mode === 'edit'" class="flex flex-col gap-4">
    <div>
      <label class="block text-sm font-medium mb-1">Description</label>
      <textarea
        v-model="form.description"
        class="w-full border rounded p-2"
        rows="4"
        placeholder="Description du post"
      />
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Image</label>
      <input type="file" accept="image/*" @change="onFileChange" />
      <img
        v-if="post?.image"
        :src="`${apiUrl}/storage/${post.image}`"
        class="mt-2 rounded h-32 object-cover"
        alt="Image actuelle"
      />
    </div>
    <button
      class="bg-blue-500 text-white px-4 py-2 rounded"
      @click="handleSubmit"
    >
      Enregistrer
    </button>
  </div>

  <p v-else class="text-gray-400">Chargement...</p>
</template>