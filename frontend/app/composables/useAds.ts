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

  return { fetchAds, fetchAd }
}