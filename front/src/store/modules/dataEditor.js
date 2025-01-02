import fetchIt from '@/lib/fetch_it'

const apiUrl = import.meta.env.VITE_API_URL

const state = {
  dataEditorFetchController: null,
  dataEditorSearchQuery: '',
}

const mutations = {
  setDataEditorSearchQuery(state, query) {
    state.dataEditorSearchQuery = query
  },
}

const actions = {
  async fetchDataEditorData(context) {
    if (context.state.dataEditorFetchController) {
      context.state.dataEditorFetchController.abort()
    }

    context.state.dataEditorFetchController = new AbortController()

    const response = await fetchIt(`${apiUrl}/api/admin/dataEditor`, {
      method: 'POST',
      signal: context.state.dataEditorFetchController.signal,
      body: JSON.stringify({
        q: context.state.dataEditorSearchQuery,
      }),
    })

    const data = await response

    return data
  },
  async saveDataEditorItem(context, itemData) {
    const response = await fetchIt(`${apiUrl}/api/admin/dataEditor/saveItem`, {
      method: 'POST',
      body: JSON.stringify(itemData),
    })

    return response
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
