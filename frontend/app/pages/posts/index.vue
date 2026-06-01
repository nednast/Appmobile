<script setup>
const { posts, currentPage, lastPage, fetchPosts } = usePosts()
const { public: { APP_ENV, WEBAPI_URL, APPAPI_URL } } = useRuntimeConfig()
const apiUrl = APP_ENV === 'mobile' ? APPAPI_URL : WEBAPI_URL
const loading = ref(false)

const loadPage = async (page) => {
  loading.value = true
  await fetchPosts(page)
  loading.value = false
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => loadPage(1))
</script>

<template>
  <div class="page-bg" />
  <div class="page-wrapper">

    <div class="page-header">
      <NuxtLink to="/" class="back-link">Retour</NuxtLink>
      <h1>Tous les posts</h1>
    </div>

    <p v-if="loading" class="loading">Chargement...</p>

    <div v-else>
      <p v-if="!posts.length" class="loading">Aucun post pour le moment.</p>

      <div class="posts-grid">
        <CardPost
          v-for="p in posts"
          :key="p.id"
          :post="p"
          :api-url="apiUrl"
          :show-link="true"
        />
      </div>

      <!-- Pagination -->
      <div v-if="lastPage > 1" class="pagination">
        <button
          class="pagination__btn"
          :disabled="currentPage <= 1"
          @click="loadPage(currentPage - 1)"
        >
          ← Précédent
        </button>

        <div class="pagination__pages">
          <button
            v-for="p in lastPage"
            :key="p"
            class="pagination__page"
            :class="{ 'pagination__page--active': p === currentPage }"
            @click="loadPage(p)"
          >
            {{ p }}
          </button>
        </div>

        <button
          class="pagination__btn"
          :disabled="currentPage >= lastPage"
          @click="loadPage(currentPage + 1)"
        >
          Suivant →
        </button>
      </div>
    </div>

  </div>
</template>

<style scoped>
.posts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 1rem;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  margin-top: 2rem;
  flex-wrap: wrap;
}

.pagination__btn {
  padding: 0.45rem 1rem;
  border-radius: 20px;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text-muted);
  font-family: var(--font-body);
  font-size: 0.82rem;
  cursor: pointer;
  transition: border-color 0.2s, color 0.2s;
}
.pagination__btn:hover:not(:disabled) { border-color: var(--gold); color: var(--gold); }
.pagination__btn:disabled { opacity: 0.3; cursor: default; }

.pagination__pages {
  display: flex;
  gap: 0.3rem;
}

.pagination__page {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text-muted);
  font-family: var(--font-body);
  font-size: 0.82rem;
  cursor: pointer;
  transition: all 0.15s;
  display: flex;
  align-items: center;
  justify-content: center;
}
.pagination__page:hover { border-color: var(--gold); color: var(--gold); }
.pagination__page--active {
  background: var(--gold);
  border-color: var(--gold);
  color: #000;
  font-weight: 700;
}
</style>