import fetchIt from '@/lib/fetch_it'
import store2 from 'store2'

const apiUrl = import.meta.env.VITE_API_URL

const defaultTagRange = {
  from: '',
  to: '',
  tags: '',
}

const state = {
  news: [],
  people: [],
  peopleMarkReload: false,
  sideMenuStatus: false,
  userProfile: {},
  currentUser: {
    id: null,
    admin: false,
    email: null,
  },
  peopleSearchTerm: '',
  peopleFilters: [],
  peopleActiveFilters: {},
  peopleFetchController: null,
  dataEditorFetchController: null,
  dataEditorSearchQuery: '',
}

const mutations = {
  setNews(state, news) {
    state.news = news
  },
  setPeople(state, people) {
    state.people = people
  },
  setPeopleMarkReload(state, value) {
    state.peopleMarkReload = value
  },
  setSideMenuStatus(state, status) {
    state.sideMenuStatus = status
  },
  setUserProfile(state, profile) {
    state.userProfile = profile
  },
  setCurrentUser(state, currentUser) {
    state.currentUser = currentUser
  },
  addTag(state, data) {
    if (!state.userProfile[data.key]) {
      state.userProfile[data.key] = []
    }

    state.userProfile[data.key].push(data.newOption)
  },
  addTagRange(state, data) {
    if (!state.userProfile[data.key]) {
      state.userProfile[data.key] = {}
    }

    if (!state.userProfile[data.key]['tags']) {
      state.userProfile[data.key]['tags'] = []
    }

    state.userProfile[data.key]['tags'](data.newOption)
  },
  addEmptyTagRangeItem(state, machineName) {
    if (!state.userProfile[machineName]) {
      state.userProfile[machineName] = []
    }

    state.userProfile[machineName].push(JSON.parse(JSON.stringify(defaultTagRange)))
  },
  addEmptyMultiItem(state, machineName) {
    if (!state.userProfile[machineName]) {
      state.userProfile[machineName] = []
    }

    state.userProfile[machineName].push({})
  },
  removeTagRangeItem(state, data) {
    state.userProfile[data.machineName].splice(data.index, 1);
  },
  removeMultiItem(state, data) {
    state.userProfile[data.machineName].splice(data.index, 1);
  },
}

const actions = {
  async fetchNews(context) {
    const response = await fetchIt(`${apiUrl}/api/news`)
    const data = await response.json()

    return data
  },
  async fetchPeople(context) {
    context.state.peopleFetchController = new AbortController()

    const response = await fetchIt(`${apiUrl}/api/people`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      signal: context.state.peopleFetchController.signal,
      body: JSON.stringify({
        q: context.state.peopleSearchTerm,
        filters: context.state.peopleActiveFilters,
      }),
    })

    const data = await response.json()

    return data
  },
  async fetchCurrentUser(context) {
    const response = await fetchIt(`${apiUrl}/api/users/current`)
    const data = await response.json()

    return data
  },
  async fetchProfile(context) {
    const response = await fetchIt(`${apiUrl}/api/users/currentProfile`)
    const data = await response.json()

    return data
  },
  async fetchProfileStructure(context) {
    const response = await fetchIt(`${apiUrl}/api/users/profileStructure`)
    const data = await response.json()

    return data
  },
  async fetchPeopleFilters(context) {
    const response = await fetchIt(`${apiUrl}/api/people/filters`)
    const data = await response.json()

    return data
  },
  async fetchInterests(context) {
    const response = await fetchIt(`${apiUrl}/api/people/interests`)
    const data = await response.json()

    return data
  },
  async fetchPerson(context, id) {
    const response = await fetchIt(`${apiUrl}/api/people/${id}`)
    const data = await response.json()

    return data
  },
  async fetchInvitations(context, id) {
    const response = await fetchIt(`${apiUrl}/api/admin/invitations`)
    const data = await response.json()

    return data
  },
  async fetchUsers(context, id) {
    const response = await fetchIt(`${apiUrl}/api/admin/users`)
    const data = await response.json()

    return data
  },
  async fetchCustomFields(context, id) {
    const response = await fetchIt(`${apiUrl}/api/admin/customFields`)
    const data = await response.json()

    return data
  },
  async fetchDataEditorData(context, id) {
    context.state.dataEditorFetchController = new AbortController()

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
  setNews(context, news) {
    context.commit('setNews', news)
  },
  setPeople(context, people) {
    context.commit('setPeople', people)
  },
  setPeopleMarkReload(context, value) {
    context.commit('setPeopleMarkReload', value)
  },
  setSideMenuStatus(context, status) {
    context.commit('setSideMenuStatus', status)
    store2('commuse.side_menu_status', status)
  },
  setUserProfile(context, profile) {
    context.commit('setUserProfile', profile)
  },
  setCurrentUser(context, currentUser) {
    context.commit('setCurrentUser', currentUser)
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
  addTag(context, data) {
    context.commit('addTag', data)
  },
  async saveInvitation(context, invitation) {
    const response = await fetchIt(`${apiUrl}/api/admin/invitations/upsert`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        invitation: invitation,
      }),
    })

    return response
  },
  async deleteInvitations(context, invitations) {
    const response = await fetchIt(`${apiUrl}/api/admin/invitations/delete`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        invitations: invitations,
      }),
    })

    return response
  },
  async deleteUsers(context, users) {
    const response = await fetchIt(`${apiUrl}/api/admin/users/delete`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        users: users,
      }),
    })

    return response
  },
  async changeUserRole(context, data) {
    const response = await fetchIt(`${apiUrl}/api/admin/users/changeRole`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        users: data.users,
        role: data.role,
      }),
    })

    return response
  },
  addEmptyTagRangeItem(context, machineName) {
    context.commit('addEmptyTagRangeItem', machineName)
  },
  addEmptyMultiItem(context, machineName) {
    context.commit('addEmptyMultiItem', machineName)
  },
  removeTagRangeItem(context, data) {
    context.commit('removeTagRangeItem', data)
  },
  removeMultiItem(context, data) {
    context.commit('removeMultiItem', data)
  },
  async importUsersFromCsv(context, file) {
    const formData = new FormData();
    formData.append('csv', file);
    const response = await fetchIt(`${apiUrl}/api/admin/users/importFromCsv`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: formData,
    })

    return response
  },
  async changePassword(context, data) {
    const formData = new FormData();
    formData.append('password', data.password);
    formData.append('password_confirm', data.password_confirm);

    const response = await fetchIt(`${apiUrl}/changePassword`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: formData,
    })

    return response
  },
  async saveCustomField(context, data) {
    const response = await fetchIt(`${apiUrl}/api/admin/customFields/upsert`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        customField: data,
      }),
    })

    return response
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
