async function waitUntil(condition) {
  return await new Promise(resolve => {
    const interval = setInterval(() => {
      if (condition()) {
        resolve()
        clearInterval(interval)
      }
    }, 10)
  })
}

export { waitUntil }
