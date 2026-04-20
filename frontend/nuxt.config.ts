export default defineNuxtConfig({
  css: ['~/assets/main.css'],
  ssr: false,
  compatibilityDate: '2025-07-15',

  nitro: {
    preset: 'static'
  },
  routeRules: {
  '/**': {
    headers: {
      'Permissions-Policy': 'geolocation=*'
    }
  }
},

  app: {
    baseURL: process.env.APP_ENV === 'mobile' ? './' : '/'  
  },

  runtimeConfig: {
    public: {
      APP_NAME: process.env.APP_NAME,
      APP_ENV: process.env.APP_ENV,
      WEBAPI_URL: process.env.WEBAPI_URL,
      APPAPI_URL: process.env.APPAPI_URL,
      GOOGLE_MAPS_API_KEY: process.env.GOOGLE_MAPS_API_KEY
    }
  },

  devtools: { enabled: false }
})