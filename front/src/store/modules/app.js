import fetchIt from '@/lib/fetch_it'
import store2 from 'store2'
import { objectToQueryParams } from '@/lib/url_params'

const apiUrl = import.meta.env.VITE_API_URL


const state = {
  sideMenuStatus: false,
}

const mutations = {
  setSideMenuStatus(state, status) {
    state.sideMenuStatus = status
  },
}

const actions = {
  async fetchNews(context, params) {
    const response = await fetchIt(`${apiUrl}/api/news?${objectToQueryParams(params)}`)
    const data = await response

    return data
  },
  setSideMenuStatus(context, status) {
    context.commit('setSideMenuStatus', status)
    store2('commuse.side_menu_status', status)
  },
}

const getters = {}

function initLocalStorage() {
  if (store2('commuse.side_menu_status') === null) {
    store2('commuse.side_menu_status', false)
  }

  state.sideMenuStatus = store2('commuse.side_menu_status')
}

initLocalStorage()

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}
