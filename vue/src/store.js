import Vuex from 'vuex';
import axios from "axios";
import {isJsonString, transformSubmitData} from "./helpers";

const store = new Vuex.Store({
  state: {
    nonce: {},
    response: null,
    waitingForResponse: false,
    responseReceivedRecently: false,
    responseReceivedRecentlyTimeoutId: false,
    lastButtonPressedId: null,
    userHasVideo: false,
    errors: {},
    formData: {}, // this is the form data that will be sent to the server, oncreate it is set dynamically
  },
  mutations: {
    updateFormField(state, { sectionId, field, value, innerField, innerFieldRowId}) {
      if (innerField) {
        state.formData[sectionId][field.id][innerFieldRowId][innerField.id] = value;
        return;
      }
      state.formData[sectionId][field.id] = value;
    },
    setNonce(state, {nonceName, nonceValue}) {
      state.nonce = {
        nonceName: nonceName,
        nonceValue: nonceValue
      };
    },
    setResponse(state, response) {
      state.response = response;
    }
  },
  actions: {
    setLastButtonPressed({state}, lastButtonPressedId) {
      state.lastButtonPressedId = lastButtonPressedId
    },
    successfulResponse({state,commit}) {
      return state.response?.data?.status === 'ok'
    },
    updateFormData({commit}, formData) {
      commit('updateFormField', formData);
    },
    setWaitingForResponse({state, commit}, waitingForResponse) {
      state.waitingForResponse = waitingForResponse
    },
    setStatusReceivedResponse({state, commit}) {
      clearInterval(state.responseReceivedRecentlyTimeoutId)
      state.waitingForResponse = false
      state.responseReceivedRecently = true
      state.responseReceivedRecentlyTimeoutId = setInterval(() => {
        if (state.responseReceivedRecently) {
          state.responseReceivedRecently = false
          clearInterval(state.responseReceivedRecentlyTimeoutId)
        }
      }, 5000)
    },
    saveForm({state,commit,dispatch}, {apiUrl, formName, fieldParameters}) {
      return new Promise((resolve, reject) => {
        // reset response
        state.response = null

        // transform data
        let data = transformSubmitData({...state.formData[formName]}, fieldParameters)

        // add nonce
        data = {...data, [state.nonce.nonceName]: state.nonce.nonceValue}

        // send data
        dispatch('setWaitingForResponse', true);
        axios.post(apiUrl, data)
          .then(response => {
            commit('setResponse', response)
            resolve(response)
          })
          .catch(error => console.log("Error updating"))
          .finally(() => {
            dispatch('setStatusReceivedResponse')
          })
      })
    },
    setNonce({commit}, nonce) {
      commit('setNonce', nonce);
    },
    addFormSection({state,commit}, sectionName) {
      state.formData[sectionName] = {};
    },
    addInitialFieldData({state,commit}, {sectionId, field, fieldValue}) {
      if (field.type === 'yesno') {
          state.formData[sectionId][field.id] = Number.parseInt(fieldValue)
      } else if (field.type === 'json') {
        if (fieldValue.length === 0) {
          state.formData[sectionId][field.id] = [];
        } else if (isJsonString(fieldValue)) {
          state.formData[sectionId][field.id] = JSON.parse(fieldValue)
        } else {
          state.formData[sectionId][field.id] = [];
          console.log("Error parsing json string");
        }
      } else {
        state.formData[sectionId][field.id] = fieldValue;
      }
    },
    addNewRow({state,commit}, {sectionId, field, rowValue}) {
      state.formData[sectionId][field.id].push(rowValue);
    },
    removeRow({state,commit}, {sectionId, field, rowId}) {
      state.formData[sectionId][field.id].splice(rowId, 1);
    },
    getSectionFieldValues({state,commit}, {sectionId}) {
      return state.formData[sectionId];
    },
    validateForm({state,commit,dispatch}, {fieldsValues, fieldParameters}) {
      let errors = {};
      fieldParameters.forEach(field => {
        if (field.hasOwnProperty("validation")) {
          const validation = field.validation
          validation.forEach(validationItem => {
            if (validationItem === "required") {
              if (fieldsValues[field.id] === undefined || fieldsValues[field.id].length <= 0) {
                errors[field.id] = field.label + " is required."
              }
            }
          })
        }
      })

      return new Promise((resolve, reject) => {
        if (Object.keys(errors).length > 0) {
          state.errors = errors
          reject(errors)
        } else {
          state.errors = {}
          resolve()
        }
      })
    },
    uploadVideo({state,commit,dispatch}, {apiUrl, theFile}) {
      let formData = new FormData();
      formData.append('video', theFile);

      return new Promise((resolve, reject) => {
        dispatch('setWaitingForResponse', true);
        axios.post(apiUrl, formData)
         .then(response => {
            commit('setResponse', response)
            dispatch('setUserHasVideo', true)
            resolve(response)
          })
         .catch(error => {
           console.log("Error updating")
           reject(error)
         })
         .finally(() => {
            dispatch('setStatusReceivedResponse')
          })
      })
    },
    removeVideo({state,commit,dispatch}, apiUrl) {
      return new Promise((resolve, reject) => {
        dispatch('setWaitingForResponse', true);
        axios.delete(apiUrl)
        .then(response => {
            commit('setResponse', response)
            dispatch('setUserHasVideo', false)
            resolve(response)
          })
        .catch(error => console.log("Error updating"))
        .finally(() => {
            dispatch('setStatusReceivedResponse')
          })
      })
    },
    setUserHasVideo({state,commit}, userHasVideo) {
      state.userHasVideo = userHasVideo;
    }
    // resetResponseAndErrors({state,commit}) {
    //   state.response = null
    //   state.errors = {}
    //
    //   state.waitingForResponse = false
    //   state.responseReceivedRecently = false
    //   state.lastButtonPressedId = null
    // }
  },
});

export default store;