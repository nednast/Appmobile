import { useRuntimeConfig } from '#app'

export interface Ad {
  id: number
  title: string
  description: string
  image: string | null
  city: string
  lat: number
  lng: number
  distance?: number
  user_id: number
  user?: {
    id: number
    firstname: string
    lastname: string
  }
  created_at: string
  updated_at: string
}

export function useAds() {
  const config = useRuntimeConfig()
  const baseUrl = config.public.APP_ENV === 'mobile'
    ? config.public.APPAPI_URL
    : config.public.WEBAPI_URL

  async function fetchAds(lat?: number, lng?: number): Promise<Ad[]> {
    let url = `${baseUrl}/api/ads`
    if (lat !== undefined && lng !== undefined) {
      url += `?lat=${lat}&lng=${lng}`
    }
    const res = await fetch(url)
    if (!res.ok) throw new Error('Erreur lors du chargement des annonces')
    return res.json()
  }

  async function fetchAd(id: number): Promise<Ad> {
    const res = await fetch(`${baseUrl}/api/ads/${id}`)
    if (!res.ok) throw new Error('Annonce introuvable')
    return res.json()
  }

  function authHeaders(token: string) {
    return { Authorization: `Bearer ${token}` }
  }

  async function fetchUserAds(token: string): Promise<{ data: Ad[]; current_page: number; last_page: number }> {
    const res = await fetch(`${baseUrl}/api/user/ads`, { headers: authHeaders(token) })
    if (!res.ok) throw new Error('Erreur chargement annonces')
    return res.json()
  }

  async function fetchUserAd(id: number, token: string): Promise<Ad> {
    const res = await fetch(`${baseUrl}/api/user/ads/${id}`, { headers: authHeaders(token) })
    if (!res.ok) throw new Error('Annonce introuvable')
    return res.json()
  }

  async function createAd(data: FormData, token: string): Promise<Ad> {
    const res = await fetch(`${baseUrl}/api/user/ads`, {
      method: 'POST',
      headers: authHeaders(token),
      body: data
    })
    if (!res.ok) {
      const err = await res.json()
      throw new Error(err.message ?? 'Erreur création')
    }
    return res.json()
  }

  async function updateAd(id: number, data: FormData, token: string): Promise<Ad> {
    data.append('_method', 'PUT')
    const res = await fetch(`${baseUrl}/api/user/ads/${id}`, {
      method: 'POST',
      headers: authHeaders(token),
      body: data
    })
    if (!res.ok) throw new Error('Erreur modification')
    return res.json()
  }

  async function deleteAd(id: number, token: string): Promise<void> {
    const res = await fetch(`${baseUrl}/api/user/ads/${id}`, {
      method: 'DELETE',
      headers: authHeaders(token)
    })
    if (!res.ok) throw new Error('Erreur suppression')
  }

  return { fetchAds, fetchAd, fetchUserAds, fetchUserAd, createAd, updateAd, deleteAd }
}