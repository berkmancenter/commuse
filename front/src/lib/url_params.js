function objectToQueryParams(obj) {
  // Create an array of key-value pairs, and then map each pair to a URI component encoded string
  const params = Object.keys(obj).map(key => 
    `${encodeURIComponent(key)}=${encodeURIComponent(obj[key])}`
  )

  // Join the array into a single string with '&' delimiter
  return params.join('&')
}

export { objectToQueryParams }
