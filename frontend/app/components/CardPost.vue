<script setup>
import { Share } from '@capacitor/share'

const props = defineProps({
  post: { type: Object, required: true },
  apiUrl: { type: String, required: true },
  showLink: { type: Boolean, default: true },
  editable: { type: Boolean, default: false }
})

const emit = defineEmits(['delete'])

const { toggleLike } = usePosts()
const { user } = useAuth()
const { notifyLike } = useNotifications()

const liked = ref(false)
const likesCount = ref(props.post.likes_count ?? 0)

const handleLike = async () => {
  const res = await toggleLike(props.post.id)
  liked.value = res.liked
  likesCount.value = res.likes_count
  // Notification si le post appartient à un autre utilisateur
  if (res.liked && props.post.user_id !== user.value?.id) {
    await notifyLike(props.post.description)
  }
}

const sharePost = async (post) => {
  const url = `${props.apiUrl}/posts/${post.id}`
  try {
    await Share.share({
      title: `Post de ${post.user?.firstname}`,
      text: post.description,
      url
    })
  } catch {
    if (navigator.share) {
      await navigator.share({ title: post.description, url })
    } else {
      await navigator.clipboard.writeText(url)
      alert('Lien copié !')
    }
  }
}

const initials = computed(() => {
  const f = props.post.user?.firstname?.[0] ?? ''
  const l = props.post.user?.lastname?.[0] ?? ''
  return (f + l).toUpperCase()
})
</script>

<template>
  <div class="card">
    <!-- Image -->
    <div class="card__media" :class="{ 'card__media--empty': !post.image }">
      <img
        v-if="post.image"
        :src="`${apiUrl}/storage/${post.image}`"
        class="card__img"
        alt="Image du post"
      />
      <div v-else class="card__media-placeholder">
        <svg class="card__media-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2.5"/><path d="M7 8h10M7 12h10M7 16h6"/></svg>
      </div>
    </div>

    <!-- Body -->
    <div class="card__body">
      <p class="card__description">{{ post.description }}</p>

      <div class="card__meta">
        <div class="card__avatar">{{ initials }}</div>
        <span class="card__author">
          {{ post.user?.firstname }} {{ post.user?.lastname }}
        </span>
      </div>
    </div>

    <!-- Footer -->
    <div class="card__footer">
      <NuxtLink v-if="showLink" :to="`/posts/${post.id}`" class="card__btn card__btn--primary">
        Voir
      </NuxtLink>
      <NuxtLink v-if="editable" :to="`/account/posts/${post.id}`" class="card__btn card__btn--edit">
        Modifier
      </NuxtLink>
      <button v-if="editable" class="card__btn card__btn--danger" @click="emit('delete')">
        Supprimer
      </button>
      <button
        class="card__btn card__btn--like"
        :class="{ 'card__btn--liked': liked }"
        @click="handleLike"
      >
        <svg class="card__btn-icon" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" :fill="liked ? 'currentColor' : 'none'"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
        {{ likesCount }}
      </button>
      <button class="card__btn card__btn--share" @click="sharePost(post)">
        <svg class="card__btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
      </button>
    </div>
  </div>
</template>

<style scoped>
.card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: border-color 0.2s, transform 0.15s, box-shadow 0.2s;
}

.card:hover {
  border-color: var(--gold);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

/* Media */
.card__media {
  width: 100%;
  height: 160px;
  overflow: hidden;
  position: relative;
  background: var(--bg-card);
}

.card__media--empty {
  background: linear-gradient(135deg, rgba(61,44,44,0.9) 0%, rgba(74,53,53,0.6) 100%);
}
.card__media--empty::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: radial-gradient(circle at 1px 1px, rgba(201,168,76,0.08) 1px, transparent 0);
  background-size: 24px 24px;
}

.card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.4s ease;
}

.card:hover .card__img {
  transform: scale(1.04);
}

.card__media-placeholder {
  width: 100%; height: 100%;
  display: flex; align-items: center; justify-content: center;
  position: relative;
}
.card__media-icon {
  width: 36px; height: 36px;
  color: rgba(201,168,76,0.25);
}

/* Body */
.card__body {
  padding: 0.9rem 1rem 0.6rem;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.7rem;
}

.card__description {
  font-size: 0.875rem;
  color: var(--text-muted);
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card__meta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: auto;
}

.card__avatar {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: var(--gold);
  color: #000;
  font-size: 0.65rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.card__author {
  font-size: 0.75rem;
  color: var(--text-muted);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Footer */
.card__footer {
  padding: 0.6rem 1rem 0.9rem;
  display: flex;
  flex-wrap: wrap;      
  gap: 0.4rem;
  align-items: center;
  border-top: 1px solid var(--border);
}

.card__btn {
  font-size: 0.75rem;
  font-family: var(--font-body);
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  border: 1px solid var(--border);
  background: transparent;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.15s, border-color 0.15s, color 0.15s;
  color: var(--text-muted);
  display: inline-flex;
  align-items: center;
}

.card__btn--primary {
  border-color: var(--gold);
  color: var(--gold);
}
.card__btn--primary:hover {
  background: var(--gold);
  color: #000;
}

.card__btn--edit:hover {
  border-color: #a0a0ff;
  color: #a0a0ff;
}

.card__btn--danger:hover {
  border-color: var(--rose-light);
  color: var(--rose-light);
}

.card__btn-icon {
  width: 13px;
  height: 13px;
  flex-shrink: 0;
}

.card__btn--like { color: var(--text-muted); gap: 0.3rem; }
.card__btn--liked { color: #e05c6a; border-color: rgba(224,92,106,0.4); }
.card__btn--like:hover { border-color: rgba(224,92,106,0.4); color: #e05c6a; }

.card__btn--share {
  font-size: 1rem;
  padding: 0.3rem 0.6rem;
}
.card__btn--share:hover {
  border-color: var(--gold);
  color: var(--gold);
}
</style>