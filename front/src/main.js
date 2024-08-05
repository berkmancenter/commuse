// Additional libs
import './plugins/bulma'
import './plugins/hover_css'
import awn from './plugins/awesome_notifications'
import store2 from 'store2'
import mitt from './plugins/mitt'
import './plugins/multiselect'
import './plugins/loading_css'
import datepicker from './plugins/datepicker'
import floating from './plugins/floating_vue'
import vfm from './plugins/vfm'
import CKEditor from './plugins/ckeditor'
import paginate from './plugins/paginate'

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
  awn,
  store2,
  mitt,
  store,
}
app.use(router)
app.use(store)
app.use(floating)
app.use(vfm)
app.use(CKEditor)
app.use(datepicker)
app.use(paginate)
app.mount('#app')

const globals = app.config.globalProperties
export { globals, app }
