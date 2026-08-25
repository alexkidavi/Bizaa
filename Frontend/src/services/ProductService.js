// services/productService.js
import axios from "axios";

const API_URL = "/api/products";

export default {
  getAll(params) {
    return axios.get(`${API_URL}/get`, { params });
  },

  getById(id) {
    return axios.get(`${API_URL}/${id}`);
  },

  create(data) {
    return axios.post(`${API_URL}/register`, data);
  },

  update(id, data) {
    return axios.patch(`${API_URL}/${id}`, data);
  },

  delete(id) {
    return axios.delete(`${API_URL}/${id}/delete`);
  },

  downloadImportTemplate() {
    return axios.get(`${API_URL}/import/template`, { responseType: "blob" });
  },

  importProducts(formData) {
    return axios.post(`${API_URL}/import`, formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
  }
};