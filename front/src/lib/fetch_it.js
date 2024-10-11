import { globals } from '@/main.js'

const environment = import.meta.env.VITE_ENVIRONMENT

const fetchIt = async (url, options = {}) => {
  if (environment === 'development') {
    options.credentials = 'include'
  }

  options.headers = {
    'Accept': 'application/json',
    ...options.headers,
  }

  try {
    const response = await fetch(url, options)

    if (response.redirected && response.url.includes('/login')) {
      window.location.href = response.url

      return Promise.reject('Redirecting to login')
    }

    if (!response.ok) {
      globals.awn.warning('Something went wrong, try again.')

      return Promise.reject('Fetch failed with status ' + response.status)
    }

    const data = await response.json().catch(() => {
      globals.awn.warning('Invalid JSON response.')

      return Promise.reject('Invalid JSON response')
    })

    return Promise.resolve(data)
  } catch (error) {
    if (error.name !== 'AbortError') {
      globals.awn.warning('Something went wrong, try again.')
    }

    return Promise.reject(error)
  }
}

export default fetchIt
