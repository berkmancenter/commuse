import fetchIt from '@/lib/fetch_it'

const apiUrl = import.meta.env.VITE_API_URL

const state = {
  userProfile: {},
  currentUser: {
    id: null,
    admin: false,
    email: null,
  },
}

const mutations = {
  setUserProfile(state, profile) {
    state.userProfile = profile
  },
  setUserProfileStatus(state, profileStatus) {
    state.userProfile.public_profile = profileStatus.public_profile
  },
  setCurrentUser(state, currentUser) {
    state.currentUser = currentUser
  },
}

const actions = {
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
  setUserProfile(context, profile) {
    context.commit('setUserProfile', profile)
  },
  setUserProfileStatus(context, profileStatus) {
    context.commit('setUserProfileStatus', profileStatus)
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
}

const getters = {}

export default {
  namespaced: true,
  state,
  getters,
  actions,
  mutations,
}
