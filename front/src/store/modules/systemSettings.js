import fetchIt from '@/lib/fetch_it'

const apiUrl = import.meta.env.VITE_API_URL

const state = {
  systemSettings: [],
  publicSystemSettings: [],
}

const mutations = {
  setSystemSettings(state, settings) {
    state.systemSettings = settings
  },
  setPublicSystemSettings(state, settings) {
    state.publicSystemSettings = settings
  },
}

const actions = {
  async fetchSystemSettings(context) {
    const response = await fetchIt(`${apiUrl}/api/admin/systemSettings`)
    const data = await response.json()

    return data
  },
  async fetchPublicSystemSettings(context) {
    const response = await fetchIt(`${apiUrl}/api/admin/publicSystemSettings`)
    const data = await response.json()

    return data
  },
  async saveSystemSettings(context, settings) {
    const response = await fetchIt(`${apiUrl}/api/admin/systemSettings`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(settings),
    })

    return response
  },
  setSystemSettings(context, value) {
    context.commit('setSystemSettings', value)
  },
  setPublicSystemSettings(context, value) {
    context.commit('setPublicSystemSettings', value)
  },
}

const getters = {}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters,
}
