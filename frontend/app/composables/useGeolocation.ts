import { Capacitor } from '@capacitor/core'
import { Geolocation } from '@capacitor/geolocation'

export const useGeolocation = () => {
  const coords = ref<{ lat: number; lng: number } | null>(null)
  const error = ref<string | null>(null)

  const requestPosition = async () => {
    try {
      if (Capacitor.isNativePlatform()) {
        // Android — Capacitor
        await Geolocation.requestPermissions()
        const pos = await Geolocation.getCurrentPosition({
          enableHighAccuracy: true,
          timeout: 10000
        })
        coords.value = {
          lat: pos.coords.latitude,
          lng: pos.coords.longitude
        }
      } else {
        const pos = await new Promise<GeolocationPosition>((resolve, reject) => {
          navigator.geolocation.getCurrentPosition(resolve, reject, {
            enableHighAccuracy: true,
            timeout: 10000
          })
        })
        coords.value = {
          lat: pos.coords.latitude,
          lng: pos.coords.longitude
        }
      }
    } catch (e: any) {
      error.value = e?.message ?? 'Position indisponible'
    }
  }

  return { coords, error, requestPosition }
}