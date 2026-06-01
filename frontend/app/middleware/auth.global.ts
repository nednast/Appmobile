const PUBLIC_ROUTES = ['/login', '/register']

export default defineNuxtRouteMiddleware((to) => {
  if (process.client) {
    const token = localStorage.getItem('token')
    const isPublic = PUBLIC_ROUTES.includes(to.path)

    if (!token && !isPublic) {
      return navigateTo('/login')
    }

    if (token && isPublic) {
      return navigateTo('/')
    }
  }
})
