import fetchIt from '@/lib/fetch_it'

const apiUrl = import.meta.env.VITE_API_URL

const state = {
  people: [],
  peopleMarkReload: false,
  peopleSearchTerm: '',
  peopleFilters: [],
  peopleActiveFilters: {},
  peopleFetchController: null,
}

const mutations = {
  setPeople(state, people) {
    state.people = people
  },
  setPeopleMarkReload(state, value) {
    state.peopleMarkReload = value
  },
  setPeopleActiveFilters(state, filters) {
    state.peopleActiveFilters = filters
  },
  setPeopleSearchTerm(state, term) {
    state.peopleSearchTerm = term
  },
}

const actions = {
  async fetchPeople(context) {
    if (context.state.peopleFetchController) {
      context.state.peopleFetchController.abort()
    }

    context.state.peopleFetchController = new AbortController()

    const response = await fetchIt(`${apiUrl}/api/people`, {
      method: 'POST',
      signal: context.state.peopleFetchController.signal,
      body: JSON.stringify({
        q: context.state.peopleSearchTerm,
        filters: context.state.peopleActiveFilters,
      }),
    })

    const data = await response.json()

    return data
  },
  async fetchPeopleFilters(context) {
    const response = await fetchIt(`${apiUrl}/api/people/filters`)
    const data = await response.json()

    return data
  },
  async fetchPerson(context, id) {
    const response = await fetchIt(`${apiUrl}/api/people/${id}`)

    return response
  },
  setPeople(context, people) {
    context.commit('setPeople', people)
  },
  setPeopleMarkReload(context, value) {
    context.commit('setPeopleMarkReload', value)
  },
  setPeopleActiveFilters(context, filters) {
    context.commit('setPeopleActiveFilters', filters)
  },
  setPeopleSearchTerm(context, term) {
    context.commit('setPeopleSearchTerm', term)
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
