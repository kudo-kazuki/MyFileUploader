import { createApp } from 'vue'
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import '@/scss/style.scss'
import 'animate.css'
import App from '@/App.vue'
import router from '@/router'
import ElementPlus from 'element-plus'
import ja from 'element-plus/es/locale/lang/ja'

const app = createApp(App)
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)

app.use(pinia)
app.use(router)
app.use(ElementPlus, { locale: ja })
app.mount('#app')
