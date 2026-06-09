export function getNetworkSlug() {
  const param = new URLSearchParams(window.location.search).get('network')
  if (param) return param

  const hostname = window.location.hostname
  const parts = hostname.split('.')
  if (parts.length >= 3 && !parts[0].match(/^(localhost|127|10\.|172\.|192\.168)/)) {
    if (!parts[0].includes('-')) {
      return parts[0]
    }
  }

  return null
}
