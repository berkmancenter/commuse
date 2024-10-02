import fetchIt from '@/lib/fetch_it'

const apiUrl = import.meta.env.VITE_API_URL

const state = {
  itemContent: '',
  items: [],
}

const mutations = {
  clearCurrentItem(state) {
    state.itemContent = ''
  },
  setItems(state, items) {
    state.items = items
  },
}

const actions = {
  async fetchItems(context) {
    const response = await fetchIt(`${apiUrl}/api/buzz`, {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    })

    const data = await response.json()

    return data
  },
  async postMessage(context, itemData) {
    const response = await fetchIt(`${apiUrl}/api/buzz/upsert`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(itemData),
    })

    return response
  },
  clearCurrentItem(context) {
    context.commit('clearCurrentItem')
  },
  setItems(context, items) {
    context.commit('setItems', items)
  }
}

const getters = {}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}
