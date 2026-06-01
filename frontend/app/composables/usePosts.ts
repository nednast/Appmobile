export const usePosts = () => {
  const posts = ref<any[]>([])
  const post = ref<any>(null)
  const currentPage = ref(1)
  const lastPage = ref(1)
  const totalPosts = ref(0)

  const { public: { APP_ENV, WEBAPI_URL, APPAPI_URL } } = useRuntimeConfig()
  const apiUrl = APP_ENV === 'mobile' ? APPAPI_URL : WEBAPI_URL
  const { token } = useAuth()

  const authHeaders = () => ({
    Authorization: `Bearer ${token.value}`
  })

  // ── Public ────────────────────────────────────────────────────
  const fetchPosts = async (page = 1) => {
    const res = await $fetch<any>(`${apiUrl}/api/posts?page=${page}`)
    if (res.data) {
      posts.value = res.data
      currentPage.value = res.current_page ?? 1
      lastPage.value = res.last_page ?? 1
      totalPosts.value = res.total ?? 0
    } else {
      posts.value = res
    }
  }

  const fetchPost = async (id: string | number) => {
    post.value = await $fetch(`${apiUrl}/api/posts/${id}`)
  }

  // ── Authentifié ───────────────────────────────────────────────
  const fetchUserPosts = async () => {
    const res = await $fetch<any>(`${apiUrl}/api/user/posts`, {
      headers: authHeaders()
    })
    posts.value = res.data ?? res
  }

  const fetchUserPost = async (id: string | number) => {
    post.value = await $fetch(`${apiUrl}/api/user/posts/${id}`, {
      headers: authHeaders()
    })
  }

  const createPost = async (data: FormData) => {
    const newPost = await $fetch<any>(`${apiUrl}/api/user/posts`, {
      method: 'POST',
      headers: authHeaders(),
      body: data
    })
    posts.value.unshift(newPost)
    return newPost
  }

  const updatePost = async (id: string | number, data: FormData) => {
    data.append('_method', 'PUT')
    const updated = await $fetch<any>(`${apiUrl}/api/user/posts/${id}`, {
      method: 'POST',
      headers: authHeaders(),
      body: data
    })
    const index = posts.value.findIndex(p => p.id === id)
    if (index !== -1) posts.value[index] = updated
    if (post.value?.id === id) post.value = updated
    return updated
  }

  const deletePost = async (id: string | number) => {
    await $fetch(`${apiUrl}/api/user/posts/${id}`, {
      method: 'DELETE',
      headers: authHeaders()
    })
    posts.value = posts.value.filter(p => p.id !== id)
    if (post.value?.id === id) post.value = null
  }

  // ── Likes ─────────────────────────────────────────────────────
  const toggleLike = async (postId: number): Promise<{ liked: boolean; likes_count: number }> => {
    return await $fetch(`${apiUrl}/api/posts/${postId}/like`, {
      method: 'POST',
      headers: authHeaders()
    })
  }

  // ── Comments ──────────────────────────────────────────────────
  const fetchComments = async (postId: number): Promise<any[]> => {
    return await $fetch(`${apiUrl}/api/posts/${postId}/comments`)
  }

  const addComment = async (postId: number, body: string): Promise<any> => {
    return await $fetch(`${apiUrl}/api/posts/${postId}/comments`, {
      method: 'POST',
      headers: { ...authHeaders(), 'Content-Type': 'application/json' },
      body: JSON.stringify({ body })
    })
  }

  const deleteComment = async (postId: number, commentId: number): Promise<void> => {
    await $fetch(`${apiUrl}/api/posts/${postId}/comments/${commentId}`, {
      method: 'DELETE',
      headers: authHeaders()
    })
  }

  return {
    posts, post,
    currentPage, lastPage, totalPosts,
    fetchPosts, fetchPost,
    fetchUserPosts, fetchUserPost,
    createPost, updatePost, deletePost,
    toggleLike,
    fetchComments, addComment, deleteComment,
  }
}
