export const useAuth = () => {


  const user = useState<any>('user', () => null)
  const token = useState<string | null>('token', () => {
    if (process.client) {
      return localStorage.getItem('token')
    }
    return null
  })


  const { public: { APP_ENV, WEBAPI_URL, APPAPI_URL } } = useRuntimeConfig()


  const apiUrl = APP_ENV === 'mobile' ? APPAPI_URL : WEBAPI_URL


  const register = async (firstname: string, lastname: string, email: string, password: string, passwordConfirmation: string) => {
    try {
      const res = await fetch(`${apiUrl}/api/register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ firstname, lastname, email, password, password_confirmation: passwordConfirmation })
      })

      if (!res.ok) {
        const err = await res.json()
        return { success: false, errors: err.errors ?? {} }
      }

      const data = await res.json()
      token.value = data.token
      user.value = data.user
      localStorage.setItem('token', data.token)
      return { success: true, errors: {} }
    } catch (e) {
      return { success: false, errors: {} }
    }
  }

  const login = async (email: string, password: string) => {
    try {
      const res = await fetch(`${apiUrl}/api/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
      })


      if (!res.ok) throw new Error('Login failed')


      const data = await res.json()


      token.value = data.token
      user.value = data.user


      localStorage.setItem('token', data.token)


      return true


    } catch (e) {
      return false
    }
  }


  const fetchUser = async () => {
    if (!token.value) return


    const res = await fetch(`${apiUrl}/api/user`, {
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    })


    if (res.ok) {
      user.value = await res.json()
    }
  }


  const logout = async () => {
    await fetch(`${apiUrl}/api/logout`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token.value}`
      }
    })


    token.value = null
    user.value = null
    localStorage.removeItem('token')
    navigateTo('/login')
  }


  return {
    user,
    token,
    register,
    login,
    logout,
    fetchUser
  }
}
