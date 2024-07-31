import fetchIt from '@/lib/fetch_it'
import store2 from 'store2'
import { objectToQueryParams } from '@/lib/url_params'
import { ref } from 'vue'

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
  systemSettings: [],
  systemSettingsValues: [],
  setActiveAffiliationModalValue: {},
  publicSystemSettings: [],
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
  setUserProfileStatus(state, profileStatus) {
    state.userProfile.public_profile = profileStatus.public_profile
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
  addEmptyTagRangeItem(state, destination) {
    if (!destination['store'][destination['key']]) {
      destination['store'][destination['key']] = []
    }

    destination['store'][destination['key']].push(JSON.parse(JSON.stringify(defaultTagRange)))
  },
  addEmptyMultiItem(state, destination) {
    if (!destination['store'][destination['key']]) {
      destination['store'][destination['key']] = []
    }

    destination['store'][destination['key']].push({})
  },
  removeTagRangeItem(state, data) {
    state.userProfile[data.machineName].splice(data.index, 1)
  },
  removeMultiItem(state, data) {
    state.userProfile[data.machineName].splice(data.index, 1)
  },
  setSystemSettings(state, settings) {
    state.systemSettings = settings
  },
  setActiveAffiliationModalValue(state, value) {
    state.setActiveAffiliationModalValue = value
  },
  setPublicSystemSettings(state, settings) {
    state.publicSystemSettings = settings
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
  async fetchProfile(context, id) {
    const response = await fetchIt(`${apiUrl}/api/users/profile/${id}`)
    const data = await response.json()

    return data
  },
  async fetchProfileStatus(context) {
    const response = await fetchIt(`${apiUrl}/api/users/profileStatus`)
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

    return response
  },
  async fetchInvitations(context) {
    const response = await fetchIt(`${apiUrl}/api/admin/invitations`)
    const data = await response.json()

    return data
  },
  async fetchUsers(context) {
    const response = await fetchIt(`${apiUrl}/api/admin/users`)
    const data = await response.json()

    return data
  },
  async fetchCustomFields(context) {
    const response = await fetchIt(`${apiUrl}/api/admin/customFields`)
    const data = await response.json()

    return data
  },
  async fetchProfileDataAuditData(context, params) {
    const response = await fetchIt(`${apiUrl}/api/admin/profileDataAudit?${objectToQueryParams(params)}`)
    const data = await response.json()

    return data
  },
  async fetchDataEditorData(context) {
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
  setUserProfileStatus(context, profileStatus) {
    context.commit('setUserProfileStatus', profileStatus)
  },
  setCurrentUser(context, currentUser) {
    context.commit('setCurrentUser', currentUser)
  },
  setActiveAffiliationModalValue(context, value) {
    context.commit('setActiveAffiliationModalValue', value)
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
  async uploadProfileImage(context, data) {
    const formData = new FormData();
    formData.append('image', data.file);
    const response = await fetchIt(`${apiUrl}/api/users/uploadProfileImage/${data.id}`, {
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
  async setActiveAffiliation(context, data) {
    const response = await fetchIt(`${apiUrl}/api/admin/users/setActiveAffiliation`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    })

    return response
  },
  addEmptyTagRangeItem(context, destination) {
    context.commit('addEmptyTagRangeItem', destination)
  },
  addEmptyMultiItem(context, destination) {
    context.commit('addEmptyMultiItem', destination)
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
  async setReintakeStatus(context, data) {
    const response = await fetchIt(`${apiUrl}/api/admin/users/setReintakeStatus`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        users: data.users,
        status: data.status,
      }),
    })

    return response
  },
  async processProfileAuditRecord(context, data) {
    const response = await fetchIt(`${apiUrl}/api/admin/profileDataAudit/process`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
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
