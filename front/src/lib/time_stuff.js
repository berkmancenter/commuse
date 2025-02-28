import moment from 'moment'
import { getTimeZones } from '@vvo/tzdb'

function formattedTimestamp(timestamp, format = 'yyyy-MM-DD hh:mm:ss') {
  if (!timestamp) {
    return ''
  }

  if (timestamp.toString().length === 10) {
    timestamp = timestamp * 1000
  }

  return moment.utc(timestamp).format(format)
}

function calendarDateFormat(timestamp) {
  return formattedTimestamp(timestamp, 'MMMM D, YYYY')
}

function formattedTimezones() {
  return getTimeZones().map((tz) => {
    return {
      key: tz.name,
      label: tz.currentTimeFormat,
    }
  })
}

export {
  formattedTimestamp,
  calendarDateFormat,
  formattedTimezones,
}
