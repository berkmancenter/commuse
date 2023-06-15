// Additional libs
import './plugins/bulma'
import './plugins/pace'
import './plugins/hover_css'
import awn from './plugins/awesome-notifications'

import { createApp } from 'vue'
import router from './router/index'
import store from './store/index'
import App from './App.vue'

const app = createApp(App)
app.config.globalProperties = {
  environment: import.meta.env.VITE_ENVIRONMENT || 'development',
  awn: awn,
}
app.use(router)
app.use(store)
app.mount('#app')

const globals = app.config.globalProperties
export { globals }
