// Additional libs
import './plugins/bulma'
import './plugins/hover_css'
import awn from './plugins/awesome_notifications'
import store2 from 'store2'
import mitt from './plugins/mitt'
import './plugins/multiselect'
import './plugins/loading_css'
import './plugins/datepicker'
import floating from './plugins/floating_vue'

import { createApp } from 'vue'
import router from './router/index'
import store from './store/index'
import App from './App.vue'

const app = createApp(App)
app.config.globalProperties = {
  environment: import.meta.env.VITE_ENVIRONMENT || 'development',
  colors: {
    main: '#890309',
  },
  awn: awn,
  store2: store2,
  mitt: mitt,
}
app.use(router)
app.use(store)
app.use(floating)
app.mount('#app')

const globals = app.config.globalProperties
export { globals }
