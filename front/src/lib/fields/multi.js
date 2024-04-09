function getMultiFieldValue(field, model) {
  const values = []

  if (!model.custom_fields) {
    return []
  }

  field?.child_fields?.forEach(childField => {
    const childFieldValues = model.custom_fields.filter((customField) => {
      return customField.machine_name === childField.machine_name && customField.parent_field_value_index !== null
    })

    childFieldValues.forEach((childFieldValue) => {
      if (values[childFieldValue.parent_field_value_index] === undefined) {
        values[childFieldValue.parent_field_value_index] = []
      }

      if (childField.input_type === 'tags') {
        const multiFieldChildValue = childFieldValue.value_json.filter(n => n)
        const multiFieldChildValueJoined = multiFieldChildValue.join(', ')

        if (multiFieldChildValueJoined !== '') {
          values[childFieldValue.parent_field_value_index].push(multiFieldChildValueJoined)
        }
      } else {
        if (childFieldValue.value) {
          values[childFieldValue.parent_field_value_index].push(childFieldValue.value)
        }
      }
    })
  })

  values.forEach((val, key) => {
    if (val.length === 0) {
      values.splice(key, 1)
    }
  })

  return values
}

export { getMultiFieldValue }
