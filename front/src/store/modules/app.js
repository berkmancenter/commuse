import fetchIt from '@/lib/fetch_it'

const apiUrl = import.meta.env.VITE_API_URL

const state = {
  news: [],
  people: [],
}

const mutations = {
  setNews(state, news) {
    state.news = news
  },
  setPeople(state, people) {
    state.people = people
  },
}

const actions = {
  async fetchNews(context) {
    const response = await fetchIt(`${apiUrl}/news`)
    const data = await response.json()

    return data
  },
  async fetchPeople(context) {
    const response = await fetchIt(`${apiUrl}/people`)
    const data = await response.json()

    return data
  },
  setNews(context, news) {
    context.commit('setNews', news)
  },
  setPeople(context, people) {
    context.commit('setPeople', people)
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
