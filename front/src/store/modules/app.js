import fetchIt from '@/lib/fetch_it'
import store2 from 'store2'

const apiUrl = import.meta.env.VITE_API_URL

const state = {
  news: [],
  people: [],
  sideMenuStatus: true,
}

const mutations = {
  setNews(state, news) {
    state.news = news
  },
  setPeople(state, people) {
    state.people = people
  },
  setSideMenuStatus(state, status) {
    state.sideMenuStatus = status
  },
}

const actions = {
  async fetchNews(context) {
    const response = await fetchIt(`${apiUrl}/api/news`)
    const data = await response.json()

    return data
  },
  async fetchPeople(context) {
    const response = await fetchIt(`${apiUrl}/api/people`)
    const data = await response.json()

    return data
  },
  setNews(context, news) {
    context.commit('setNews', news)
  },
  setPeople(context, people) {
    context.commit('setPeople', people)
  },
  setSideMenuStatus(context, status) {
    context.commit('setSideMenuStatus', status)
    store2('commuse.side_menu_status', status)
  },
}

const getters = {}

function initLocalStorage() {
  if (store2('commuse.side_menu_status') === null) {
    store2('commuse.side_menu_status', true)
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
