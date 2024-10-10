import fetchIt from '@/lib/fetch_it'
import { objectToQueryParams } from '@/lib/url_params'

const apiUrl = import.meta.env.VITE_API_URL

const state = {
  activeAffiliationModalValue: {},
  profileDataAuditDataFetchController: null,
}

const mutations = {
  setActiveAffiliationModalValue(state, value) {
    state.activeAffiliationModalValue = value
  },
}

const actions = {
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
    context.state.profileDataAuditDataFetchController = new AbortController()

    const response = await fetchIt(`${apiUrl}/api/admin/profileDataAudit?${objectToQueryParams(params)}`, {
      method: 'GET',
      signal: context.state.profileDataAuditDataFetchController.signal,
    })

    const data = await response.json()

    return data
  },
  async fetchProfileDataAuditChangesFields(context) {
    const response = await fetchIt(`${apiUrl}/api/admin/profileDataAudit/getChangesFields`)
    const data = await response.json()

    return data
  },
  async saveInvitation(context, invitation) {
    const response = await fetchIt(`${apiUrl}/api/admin/invitations/upsert`, {
      method: 'POST',
      body: JSON.stringify({
        invitation: invitation,
      }),
    })

    return response
  },
  async deleteInvitations(context, invitations) {
    const response = await fetchIt(`${apiUrl}/api/admin/invitations/delete`, {
      method: 'POST',
      body: JSON.stringify({
        invitations: invitations,
      }),
    })

    return response
  },
  async deleteUsers(context, users) {
    const response = await fetchIt(`${apiUrl}/api/admin/users/delete`, {
      method: 'POST',
      body: JSON.stringify({
        users: users,
      }),
    })

    return response
  },
  async changeUserRole(context, data) {
    const response = await fetchIt(`${apiUrl}/api/admin/users/changeRole`, {
      method: 'POST',
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
      body: JSON.stringify(data),
    })

    return response
  },
  async importUsersFromCsv(context, file) {
    const formData = new FormData();
    formData.append('csv', file);
    const response = await fetchIt(`${apiUrl}/api/admin/users/importFromCsv`, {
      method: 'POST',
      body: formData,
    })

    return response
  },
  async saveCustomField(context, data) {
    const response = await fetchIt(`${apiUrl}/api/admin/customFields/upsert`, {
      method: 'POST',
      body: JSON.stringify({
        customField: data,
      }),
    })

    return response
  },
  async setReintakeStatus(context, data) {
    const response = await fetchIt(`${apiUrl}/api/admin/users/setReintakeStatus`, {
      method: 'POST',
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
      body: JSON.stringify(data),
    })

    return response
  },
  setActiveAffiliationModalValue(context, value) {
    context.commit('setActiveAffiliationModalValue', value)
  },
  async setActiveStatus(context, data) {
    const response = await fetchIt(`${apiUrl}/api/admin/users/setActiveStatus`, {
      method: 'POST',
      body: JSON.stringify(data),
    })

    return response
  },
  async createNewUser(context, data) {
    const response = await fetchIt(`${apiUrl}/api/admin/users/createNewUser`, {
      method: 'POST',
      body: JSON.stringify(data),
    })

    return response
  },
}

const getters = {}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters,
}
