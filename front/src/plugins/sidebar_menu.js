import { SidebarMenu } from 'vue-sidebar-menu'
import 'vue-sidebar-menu/dist/vue-sidebar-menu.css'

export default {
  install: (app, options) => {
    app.component('sidebar-menu', SidebarMenu)
  }
}
