import { createStore } from 'vuex'
import app from './modules/app'
import user from './modules/user'
import systemSettings from './modules/systemSettings'
import dataEditor from './modules/dataEditor'
import people from './modules/people'
import admin from './modules/admin'

export default createStore({
  modules: {
    app,
    user,
    systemSettings,
    dataEditor,
    people,
    admin,
  }
})
