function keysToCamelCase(obj) {
  if (Array.isArray(obj)) {
    return obj.map(keysToCamelCase)
  } else if (obj !== null && typeof obj === 'object') {
    return Object.keys(obj).reduce((acc, key) => {
      const camelCaseKey = key.replace(/_(\w)/g, (_, c) => c.toUpperCase())
      acc[camelCaseKey] = keysToCamelCase(obj[key])

      return acc
    }, {})
  }

  return obj
}

function keysToSnakeCase(obj) {
  if (Array.isArray(obj)) {
    return obj.map(keysToSnakeCase)
  } else if (obj !== null && typeof obj === 'object') {
    return Object.keys(obj).reduce((acc, key) => {
      const snakeCaseKey = key.replace(/[A-Z]/g, match => `_${match.toLowerCase()}`)
      acc[snakeCaseKey] = keysToSnakeCase(obj[key])
      return acc
    }, {})
  }
  return obj
}

export { keysToCamelCase, keysToSnakeCase }
