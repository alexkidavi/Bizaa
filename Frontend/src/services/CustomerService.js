// services/brandService.js
import axios from "axios";

const API_URL = "/api/customers";

export default {

  getAll() {
    return axios.get(`${API_URL}/get`);
  },

  // For the POS customer picker only — returns a small, plain-integer-id
  // result set (capped server-side, searched server-side), not the whole
  // customer table. Never use getAll() for a picker/search UI.
  searchForPos(search = "") {
    return axios.get(`${API_URL}/pos-search`, { params: { search } });
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