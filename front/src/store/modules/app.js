import fetchIt from '@/lib/fetch_it'

const apiUrl = import.meta.env.VITE_API_URL

const state = {
  news: [],
}

const mutations = {
  setNews(state, news) {
    state.news = news
  },
}

const actions = {
  async fetchNews(context) {
    const response = await fetchIt(`${apiUrl}/news`)
    const data = await response.json()

    return data
  },
  setNews(context, news) {
    context.commit('setNews', news)
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
