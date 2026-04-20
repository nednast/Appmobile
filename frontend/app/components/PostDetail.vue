<script setup>
import { Share } from '@capacitor/share'

const props = defineProps({
  post: { type: Object, default: null },
  apiUrl: { type: String, required: true },
  mode: { type: String, default: 'view' }
})

const emit = defineEmits(['save'])

const form = reactive({
  description: '',
  image: null
})

const previewUrl = ref(null)

watch(() => props.post, (p) => {
  if (p) form.description = p.description
}, { immediate: true })

const onFileChange = (e) => {
  const file = e.target?.files?.[0] ?? null
  form.image = file
  if (file) {
    previewUrl.value = URL.createObjectURL(file)
  }
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

const initials = computed(() => {
  const f = props.post?.user?.firstname?.[0] ?? ''
  const l = props.post?.user?.lastname?.[0] ?? ''
  return (f + l).toUpperCase()
})
</script>

<template>
  <!-- MODE VIEW -->
  <div v-if="mode === 'view' && post" class="detail">
    <div v-if="post.image" class="detail__media">
      <img
        :src="`${apiUrl}/storage/${post.image}`"
        class="detail__img"
        alt="Image"
      />
      <div class="detail__media-overlay" />
    </div>
    <div class="detail__media detail__media--empty" v-else>
      <span class="detail__media-icon">📝</span>
    </div>

    <div class="detail__body">
      <p class="detail__description">{{ post.description }}</p>

      <div class="detail__meta">
        <div class="detail__avatar">{{ initials }}</div>
        <div class="detail__author-info">
          <span class="detail__author-name">
            {{ post.user?.firstname }} {{ post.user?.lastname }}
          </span>
          <span class="detail__author-label">Auteur</span>
        </div>
        <button class="detail__share-btn" @click="sharePost">
          ↗ Partager
        </button>
      </div>
    </div>
  </div>

  <!-- MODE EDIT -->
  <div v-else-if="mode === 'edit'" class="edit-form">
    <div class="edit-form__field">
      <label class="edit-form__label">Description</label>
      <textarea
        v-model="form.description"
        class="edit-form__textarea"
        rows="5"
        placeholder="Description du post..."
      />
    </div>

    <div class="edit-form__field">
      <label class="edit-form__label">Image</label>
      <label class="edit-form__file-label">
        <span>{{ form.image ? form.image.name : 'Choisir une image' }}</span>
        <input type="file" accept="image/*" class="edit-form__file-input" @change="onFileChange" />
      </label>

      <div class="edit-form__preview" v-if="previewUrl || post?.image">
        <img
          :src="previewUrl ?? `${apiUrl}/storage/${post.image}`"
          class="edit-form__preview-img"
          alt="Aperçu"
        />
        <span v-if="previewUrl" class="edit-form__preview-badge">Nouvelle image</span>
      </div>
    </div>

    <button class="edit-form__submit" @click="handleSubmit">
      Enregistrer
    </button>
  </div>

  <div v-else class="detail__loading">Chargement...</div>
</template>

<style scoped>
/* ── VIEW ── */
.detail {
  border-radius: var(--radius);
  overflow: hidden;
  background: var(--bg-card);
  border: 1px solid var(--border);
}

.detail__media {
  width: 100%;
  height: 260px;
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, rgba(255,255,255,0.03), rgba(255,255,255,0.07));
}

.detail__media--empty {
  display: flex;
  align-items: center;
  justify-content: center;
}

.detail__media-icon {
  font-size: 3rem;
  opacity: 0.2;
}

.detail__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.detail__media-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.5) 0%, transparent 50%);
}

.detail__body {
  padding: 1.25rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.detail__description {
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-muted);
}

.detail__meta {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid var(--border);
}

.detail__avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--gold);
  color: #000;
  font-size: 0.7rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.detail__author-info {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
  flex: 1;
}

.detail__author-name {
  font-size: 0.85rem;
  font-weight: 600;
}

.detail__author-label {
  font-size: 0.7rem;
  color: var(--text-muted);
  opacity: 0.6;
}

.detail__share-btn {
  font-size: 0.75rem;
  font-family: var(--font-body);
  padding: 0.35rem 0.85rem;
  border-radius: 20px;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--gold);
  border-color: var(--gold);
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}

.detail__share-btn:hover {
  background: var(--gold);
  color: #000;
}

.detail__loading {
  color: var(--text-muted);
  font-size: 0.875rem;
  padding: 1rem 0;
}

/* ── EDIT ── */
.edit-form {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.edit-form__field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.edit-form__label {
  font-size: 0.8rem;
  color: var(--text-muted);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.edit-form__textarea {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 0.75rem 1rem;
  color: inherit;
  font-family: var(--font-body);
  font-size: 0.9rem;
  line-height: 1.6;
  resize: vertical;
  transition: border-color 0.2s;
  outline: none;
}

.edit-form__textarea:focus {
  border-color: var(--gold);
}

.edit-form__file-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: var(--bg-card);
  border: 1px dashed var(--border);
  border-radius: var(--radius);
  padding: 0.75rem 1rem;
  cursor: pointer;
  font-size: 0.85rem;
  color: var(--text-muted);
  transition: border-color 0.2s;
}

.edit-form__file-label:hover {
  border-color: var(--gold);
  color: var(--gold);
}

.edit-form__file-input {
  display: none;
}

.edit-form__preview {
  position: relative;
  border-radius: var(--radius);
  overflow: hidden;
  height: 160px;
}

.edit-form__preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.edit-form__preview-badge {
  position: absolute;
  top: 8px;
  right: 8px;
  background: var(--gold);
  color: #000;
  font-size: 0.65rem;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.edit-form__submit {
  font-family: var(--font-body);
  font-size: 0.875rem;
  padding: 0.75rem 1.5rem;
  border-radius: 20px;
  border: 1px solid var(--gold);
  background: transparent;
  color: var(--gold);
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  align-self: flex-start;
}

.edit-form__submit:hover {
  background: var(--gold);
  color: #000;
}
</style>