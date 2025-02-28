import moment from 'moment'

function formattedTimestamp(timestamp, format = 'yyyy-MM-DD hh:mm:ss') {
  if (!timestamp) {
    return '';
  }

  if (timestamp.toString().length === 10) {
    timestamp = timestamp * 1000
  }

  return moment.utc(timestamp).format(format)
}

function calendarDateFormat(timestamp) {
  return formattedTimestamp(timestamp, 'MMMM D, YYYY')
}

export { formattedTimestamp, calendarDateFormat }
