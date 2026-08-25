// services/brandService.js
import axios from "axios";

const API_URL = "/api/suppliers";

export default {

  getAll() {
    return axios.get(`${API_URL}/get`);
  },

  create(data) {
    return axios.post(`${API_URL}/register`, data);
  },

  update(id, data) {
    return axios.patch(`${API_URL}/${id}/update`, data);
  },

  delete(id) {
    return axios.delete(`${API_URL}/${id}/delete`);
  },
  getById(id) {
    return axios.get(`${API_URL}/${id}`);
  },
   getSales(id) {
    return axios.get(`${API_URL}/${id}/sales/`);
  },
getPayments: (id) => axios.get(`${API_URL}/${id}/payments`),
};