<script setup lang="ts">
import { Share } from '@capacitor/share'

const props = defineProps({
  post: { type: Object, default: null },
  apiUrl: { type: String, required: true },
  mode: { type: String, default: 'view' }
})

const emit = defineEmits(['save'])

const { user } = useAuth()
const { toggleLike, fetchComments, addComment, deleteComment } = usePosts()
const { notifyLike, notifyComment } = useNotifications()

// ── Likes ─────────────────────────────────────────────────────
const liked = ref(false)
const likesCount = ref(0)

watch(() => props.post, (p) => {
  if (p) likesCount.value = p.likes_count ?? 0
}, { immediate: true })

const handleLike = async () => {
  if (!props.post) return
  const res = await toggleLike(props.post.id)
  liked.value = res.liked
  likesCount.value = res.likes_count
  if (res.liked && props.post.user_id !== user.value?.id) {
    await notifyLike(props.post.description)
  }
}

// ── Comments ──────────────────────────────────────────────────
const comments = ref<any[]>([])
const commentBody = ref('')
const commentsLoaded = ref(false)

const loadComments = async () => {
  if (!props.post) return
  comments.value = await fetchComments(props.post.id)
  commentsLoaded.value = true
}

const submitComment = async () => {
  if (!commentBody.value.trim() || !props.post) return
  const newComment = await addComment(props.post.id, commentBody.value.trim())
  comments.value.unshift(newComment)
  commentBody.value = ''
  if (props.post.user_id !== user.value?.id) {
    const author = `${user.value?.firstname ?? ''} ${user.value?.lastname ?? ''}`.trim()
    await notifyComment(author, props.post.description)
  }
}

const removeComment = async (commentId: number) => {
  if (!props.post) return
  await deleteComment(props.post.id, commentId)
  comments.value = comments.value.filter(c => c.id !== commentId)
}

onMounted(() => {
  if (props.mode === 'view') loadComments()
})

// ── Edit form ─────────────────────────────────────────────────
const form = reactive({ description: '', image: null })
const previewUrl = ref(null)

watch(() => props.post, (p) => {
  if (p) form.description = p.description
}, { immediate: true })

const onFileChange = (e) => {
  const file = e.target?.files?.[0] ?? null
  form.image = file
  if (file) previewUrl.value = URL.createObjectURL(file)
}

const handleSubmit = () => {
  const data = new FormData()
  data.append('description', form.description)
  if (form.image) data.append('image', form.image)
  emit('save', data)
}

// ── Share ─────────────────────────────────────────────────────
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
      await navigator.share({ title: `Post de ${props.post.user?.firstname}`, text: props.post.description, url })
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
      <svg class="detail__media-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2.5"/><path d="M7 8h10M7 12h10M7 16h6"/></svg>
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
        <button
          class="detail__like-btn"
          :class="{ 'detail__like-btn--liked': liked }"
          @click="handleLike"
        >
          <svg class="detail__btn-icon" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" :fill="liked ? 'currentColor' : 'none'"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
          {{ likesCount }}
        </button>
        <button class="detail__share-btn" @click="sharePost">
          <svg class="detail__btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
          Partager
        </button>
      </div>
    </div>

    <!-- Comments -->
    <div class="detail__comments">
      <h3 class="detail__comments-title">
        Commentaires
        <span class="detail__comments-count">{{ comments.length }}</span>
      </h3>

      <form class="detail__comment-form" @submit.prevent="submitComment">
        <input
          v-model="commentBody"
          type="text"
          class="detail__comment-input"
          placeholder="Ajouter un commentaire..."
        />
        <button type="submit" class="detail__comment-submit" :disabled="!commentBody.trim()">
          Envoyer
        </button>
      </form>

      <div v-if="!commentsLoaded" class="detail__comments-loading">
        Chargement...
      </div>
      <p v-else-if="comments.length === 0" class="detail__comments-empty">
        Aucun commentaire pour l'instant.
      </p>
      <div v-else class="detail__comment-list">
        <div v-for="c in comments" :key="c.id" class="detail__comment">
          <div class="detail__comment-avatar">
            {{ c.user?.firstname?.[0] ?? '?' }}{{ c.user?.lastname?.[0] ?? '' }}
          </div>
          <div class="detail__comment-body">
            <span class="detail__comment-author">
              {{ c.user?.firstname }} {{ c.user?.lastname }}
            </span>
            <p class="detail__comment-text">{{ c.body }}</p>
          </div>
          <button
            v-if="c.user_id === user?.id"
            class="detail__comment-delete"
            @click="removeComment(c.id)"
          >✕</button>
        </div>
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
  width: 48px;
  height: 48px;
  color: rgba(201,168,76,0.25);
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

.detail__btn-icon {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
}

.detail__like-btn {
  font-size: 0.8rem;
  font-family: var(--font-body);
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text-muted);
  cursor: pointer;
  transition: border-color 0.15s, color 0.15s;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}
.detail__like-btn--liked { color: #e05c6a; border-color: rgba(224,92,106,0.4); }
.detail__like-btn:hover  { color: #e05c6a; border-color: rgba(224,92,106,0.4); }

.detail__share-btn {
  font-size: 0.75rem;
  font-family: var(--font-body);
  padding: 0.35rem 0.85rem;
  border-radius: 20px;
  border: 1px solid var(--gold);
  background: transparent;
  color: var(--gold);
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}
.detail__share-btn:hover { background: var(--gold); color: #000; }

/* ── Comments ── */
.detail__comments {
  padding: 1.25rem 1rem;
  border-top: 1px solid var(--border);
}

.detail__comments-title {
  font-size: 0.85rem;
  font-family: var(--font-body);
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.detail__comments-count {
  background: rgba(203,184,136,0.15);
  color: var(--gold);
  border-radius: 99px;
  padding: 0.1rem 0.5rem;
  font-size: 0.7rem;
}

.detail__comment-form {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.detail__comment-input {
  flex: 1;
  padding: 0.55rem 0.85rem;
  border: 1.5px solid var(--border);
  border-radius: 20px;
  background: rgba(255,255,255,0.06);
  color: var(--text-main);
  font-family: var(--font-body);
  font-size: 0.875rem;
  outline: none;
  transition: border-color 0.2s;
}
.detail__comment-input:focus { border-color: var(--gold); }

.detail__comment-submit {
  padding: 0.55rem 1rem;
  border-radius: 20px;
  border: 1px solid var(--gold);
  background: transparent;
  color: var(--gold);
  font-family: var(--font-body);
  font-size: 0.8rem;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  white-space: nowrap;
}
.detail__comment-submit:hover:not(:disabled) { background: var(--gold); color: #000; }
.detail__comment-submit:disabled { opacity: 0.4; cursor: default; }

.detail__comments-loading,
.detail__comments-empty {
  font-size: 0.825rem;
  color: var(--text-muted);
  opacity: 0.6;
  padding: 0.5rem 0;
}

.detail__comment-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.detail__comment {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
}

.detail__comment-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: var(--bg-mid);
  border: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.6rem;
  font-weight: 700;
  color: var(--gold);
  flex-shrink: 0;
  text-transform: uppercase;
}

.detail__comment-body { flex: 1; }

.detail__comment-author {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-main);
  display: block;
  margin-bottom: 0.15rem;
}

.detail__comment-text {
  font-size: 0.85rem;
  color: var(--text-muted);
  line-height: 1.5;
}

.detail__comment-delete {
  background: transparent;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  font-size: 0.7rem;
  opacity: 0.4;
  padding: 0.2rem;
  transition: opacity 0.15s, color 0.15s;
  flex-shrink: 0;
}
.detail__comment-delete:hover { opacity: 1; color: var(--rose-light); }

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