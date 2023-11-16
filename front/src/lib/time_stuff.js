import moment from 'moment'

function formattedTimestamp(timestamp) {
  if (!timestamp) {
    return '';
  }

  return moment(timestamp).format('yyyy-MM-DD hh:mm:ss')
}

export { formattedTimestamp }
