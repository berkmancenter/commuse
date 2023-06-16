import { globals } from '@/main.js'

const environment = import.meta.env.VITE_ENVIRONMENT

const fetchIt = async (url, options = {}) => {
  if (environment === 'development') {
    options.credentials = 'include'
  }

  options.headers = {
    'Accept': 'application/json',
  }

  let response
  try {
    response = await fetch(url, options)
  } catch (error) {
    globals.awn.warning('Something went wrong, try again.')
  }

  const responsePromise = new Promise((resolve, reject) => {
    if (response) {
      resolve(response)
    }
  })

  return responsePromise
}

export default fetchIt
