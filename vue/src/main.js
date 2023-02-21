import { createApp } from 'vue'
import App from './App.vue'
import PrimeVue from 'primevue/config'
import 'primevue/resources/themes/saga-blue/theme.css' /* theme */
import 'primevue/resources/primevue.min.css' /* core css */
import 'primeicons/primeicons.css' /* icons */
import './assets/main.scss'
import 'primeflex/primeflex.css'
import router from './router'
import store from './store'

document.addEventListener("DOMContentLoaded", function () {
  const rootElementId = "cv_generator"
  const data = JSON.parse(document.getElementById(rootElementId).dataset.js)
  const app = createApp(App, {...data})

  app.use(PrimeVue)
  app.use(store)
  app.use(router)
  app.mount('#' + rootElementId)
})