import { Capacitor } from '@capacitor/core'
import { LocalNotifications } from '@capacitor/local-notifications'

export const useNotifications = () => {
  const requestPermission = async () => {
    if (Capacitor.isNativePlatform()) {
      await LocalNotifications.requestPermissions()
    } else if ('Notification' in window) {
      await Notification.requestPermission()
    }
  }

  const send = async (title: string, body: string) => {
    if (Capacitor.isNativePlatform()) {
      await LocalNotifications.schedule({
        notifications: [{
          title,
          body,
          id: Date.now(),
          schedule: { at: new Date(Date.now() + 500) },
          smallIcon: 'ic_stat_icon_config_sample',
        }]
      })
    } else if ('Notification' in window && Notification.permission === 'granted') {
      new Notification(title, { body })
    }
  }

  const notifyLike = (postDescription: string) =>
    send('Nouveau like 👍', `Quelqu'un a aimé : "${postDescription.slice(0, 60)}"`)

  const notifyComment = (authorName: string, postDescription: string) =>
    send('Nouveau commentaire 💬', `${authorName} a commenté votre post`)

  return { requestPermission, send, notifyLike, notifyComment }
}
