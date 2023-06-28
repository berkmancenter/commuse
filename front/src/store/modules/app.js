import fetchIt from '@/lib/fetch_it'
import store2 from 'store2'

const apiUrl = import.meta.env.VITE_API_URL

const defaultProfile = {
  firstName: '',
  lastName: '',
  shortBio: '',
  bio: '',
  continent: '',
  country: '',
  city: '',
  publicProfile: false,
  twitterUrl: '',
  linkedinUrl: '',
  mastodonUrl: '',
}

const state = {
  news: [],
  people: [],
  sideMenuStatus: false,
  userProfile: JSON.parse(JSON.stringify(defaultProfile)),
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
  setUserProfile(state, profile) {
    state.userProfile = profile
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
  async fetchProfile(context) {
    const response = await fetchIt(`${apiUrl}/api/users/current`)
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
  setUserProfile(context, profile) {
    context.commit('setUserProfile', profile)
  },
  async saveProfile(context, profileData) {
    const response = await fetchIt(`${apiUrl}/api/users/saveProfile`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(profileData),
    })

    return response
  },
  async uploadProfileImage(context, file) {
    const formData = new FormData();
    formData.append('image', file);
    const response = await fetchIt(`${apiUrl}/api/users/uploadProfileImage`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: formData,
    })

    return response
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
