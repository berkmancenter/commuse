import moment from 'moment'

function formattedTimestamp(timestamp) {
  if (!timestamp) {
    return '';
  }

  return moment(timestamp).format('yyyy-MM-DD hh:mm:ss')
}

function calendarDateFormat(timestamp) {
  if (!timestamp) {
    return '';
  }

  if (timestamp.toString().length === 10) {
    timestamp = timestamp * 1000
  }

  return moment.utc(new Date(timestamp)).format('MMMM D, YYYY')
}

export { formattedTimestamp, calendarDateFormat }
