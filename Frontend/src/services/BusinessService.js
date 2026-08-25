// services/businessService.js
import axios from "axios";

const API_URL = "/api/business";

export default {
  get() {
    return axios.get(`${API_URL}`);
  },

  update(data) {
    const isFormData = data instanceof FormData;

    return axios.post(`${API_URL}/update`, data, {
      headers: isFormData ? { "Content-Type": "multipart/form-data" } : {},
    });
  },

  updateTax(data) {
    return axios.post(`${API_URL}/tax`, data);
  },

  updateMpesa(data) {
    return axios.post(`${API_URL}/mpesa`, data);
  },

  updateDigitax(data) {
    return axios.post(`${API_URL}/digitax`, data);
  },

  // Fetch-on-reveal: returns a masked preview only, never the full secret.
  revealSecret(field) {
    return axios.get(`${API_URL}/secret-preview`, { params: { field } });
  },
};
