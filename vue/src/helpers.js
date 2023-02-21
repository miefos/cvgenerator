export function transformSubmitData(submitData, fieldParams) {
  Object.keys(submitData).forEach(key => {
    const theField = fieldParams.find(theField => theField.id === key)
    if (theField) {
      if (theField.type === 'json') {
        submitData[key] = JSON.stringify(submitData[key])
      } else {
        submitData[key] = submitData[key]
      }
    }
  })

  return submitData
}

export function isJsonString(str) {
  try {
    JSON.parse(str);
  } catch (e) {
    return false;
  }
  return true;
}