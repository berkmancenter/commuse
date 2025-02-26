import fetchIt from '@/lib/fetch_it'

const apiUrl = import.meta.env.VITE_API_URL

const state = {
  meetings: [],
}

const mutations = {
  setMeetings(state, meetings) {
    state.meetings = meetings
  },
}

const actions = {
  async fetchMeetings(context) {
    const response = await fetchIt(`${apiUrl}/api/zoom_scheduler`)
    const data = await response

    return data
  },
  async createMeeting(context, payload) {
    const response = await fetchIt(`${apiUrl}/api/zoom_scheduler`, {
      method: 'POST',
      body: JSON.stringify(payload),
    })

    return response
  },
  setMeetings(context, value) {
    context.commit('setMeetings', value)
  }
}

const getters = {}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters,
}
