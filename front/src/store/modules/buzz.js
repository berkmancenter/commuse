import fetchIt from '@/lib/fetch_it'
import { objectToQueryParams } from '@/lib/url_params'

const apiUrl = import.meta.env.VITE_API_URL

const defaultItem = {
  id: null,
  content: '',
}

const state = {
  editorItem: JSON.parse(JSON.stringify(defaultItem)),
  items: [],
  fetchItemsController: null,
}

const mutations = {
  clearCurrentItem(state) {
    state.editorItem = JSON.parse(JSON.stringify(defaultItem))
  },
  setItems(state, items) {
    state.items = items
  },
  setCurrentItem(state, item) {
    state.editorItem = JSON.parse(JSON.stringify(item))
  },
  updateItems(state, newItems) {
    const currentItems = state.items
    newItems.forEach(newItem => {
      const index = currentItems.findIndex(item => item.id === newItem.id)
      if (index !== -1) {
        // If the item already exists, update it
        currentItems.splice(index, 1, newItem)
      } else {
        // If it's a new item, prepend it to the list
        currentItems.unshift(newItem)
      }
    })
  },
  removeItem(state, itemId) {
    const index = state.items.findIndex(item => item.id === itemId)
    state.items.splice(index, 1)
  },
}

const actions = {
  async fetchItems(context, params) {
    let url = `${apiUrl}/api/buzz?${objectToQueryParams(params)}`

    context.state.fetchItemsController = new AbortController()

    const response = await fetchIt(url, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      signal: context.state.fetchItemsController.signal,
    })

    const data = await response.json()

    return data
  },
  postMessage(context, itemData) {
    const response = fetchIt(`${apiUrl}/api/buzz/upsert`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(itemData),
    })

    return response
  },
  async deleteMessage(context, messageId) {
    const response = await fetchIt(`${apiUrl}/api/buzz/delete/${messageId}`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    })

    return response
  },
  clearCurrentItem(context) {
    context.commit('clearCurrentItem')
  },
  setCurrentItem(context, item) {
    context.commit('setCurrentItem', item)
  },
  updateItems(context, items) {
    context.commit('updateItems', items)
  },
  removeItem(context, itemId) {
    context.commit('removeItem', itemId)
  },
  clearItems(context) {
    context.commit('setItems', [])
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
