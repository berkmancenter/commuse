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

    // Redirect if login is required
    if (response.redirected && response.url.includes('/login')) {
      window.location.href = response.url
      return Promise.reject('Redirecting to login')
    }

    // Check for non-200 responses
    if (!response.ok) {
      globals.awn.warning('Something went wrong, try again.')
      return Promise.reject('Fetch failed with status ' + response.status)
    }

    // Use .text() and then manually parse JSON to handle incomplete/broken responses better
    const text = await response.text()
    
    let data
    try {
      data = JSON.parse(text)
    } catch (err) {
      globals.awn.warning('Invalid JSON response.')
      return Promise.reject('Invalid JSON response')
    }

    return Promise.resolve(data)
  } catch (error) {
    // Ignore AbortError and show no warning, since it's intentional
    if (error.name === 'AbortError') {
      return Promise.reject('Fetch aborted')
    }

    // Handle other errors
    globals.awn.warning('Something went wrong, try again.')
    return Promise.reject(error)
  }
}

export default fetchIt
