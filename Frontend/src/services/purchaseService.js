// services/PurchaseService.js
import axios from "axios";

const API_URL = "/api/purchases";

export default {
  getAll(params = {}) {
    return axios.get(API_URL, { params });
  },

  getById(id) {
    return axios.get(`${API_URL}/${id}`);
  },

  create(data) {
    return axios.post(API_URL, data);
  },

  update(id, data) {
    return axios.put(`${API_URL}/${id}`, data);
  },

  delete(id) {
    return axios.delete(`${API_URL}/${id}`);
  },

  getByProduct(productId, params = {}) {
    return axios.get(`${API_URL}/product/${productId}`, { params });
  },

  getBySupplier(supplierId, params = {}) {
    return axios.get(`${API_URL}/supplier/${supplierId}`, { params });
  },

  getByBranch(branchId, params = {}) {
    return axios.get(`${API_URL}/branch/${branchId}`, { params });
  },

  updatePayment(id, data) {
    return axios.post(`${API_URL}/${id}/payment`, data);
  },

  // ✅ FIXED ENDPOINTS
  getItems(id) {
    return axios.get(`${API_URL}/${id}/items`);
  },

  getPayments(id) {
    return axios.get(`${API_URL}/${id}/payments`);
  },

  recordPayment(id, data) {
    return axios.post(`${API_URL}/${id}/record-payments`, data);
  },
};