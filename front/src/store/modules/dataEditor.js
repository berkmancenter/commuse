import fetchIt from '@/lib/fetch_it'

const apiUrl = import.meta.env.VITE_API_URL

const state = {
  dataEditorFetchController: null,
  dataEditorSearchQuery: '',
}

const mutations = {
  setDataEditorFetchController(state, controller) {
    state.dataEditorFetchController = controller
  },
  setDataEditorSearchQuery(state, query) {
    state.dataEditorSearchQuery = query
  },
}

const actions = {
  async fetchDataEditorData(context) {
    context.commit('setDataEditorFetchController', new AbortController())

    const response = await fetchIt(`${apiUrl}/api/admin/dataEditor`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      signal: context.state.dataEditorFetchController.signal,
      body: JSON.stringify({
        q: context.state.dataEditorSearchQuery,
      }),
    })

    const data = await response.json()

    return data
  },
  async saveDataEditorItem(context, itemData) {
    const response = await fetchIt(`${apiUrl}/api/admin/dataEditor/saveItem`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(itemData),
    })

    return response
  },
  setDataEditorFetchController(context, controller) {
    context.commit('setDataEditorFetchController', controller)
  },
}

const getters = {}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}
