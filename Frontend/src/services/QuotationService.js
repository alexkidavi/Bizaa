// services/QuotationService.js
import axios from "axios";

const API_URL = "/api/quotations";

export default {
  // Get all quotations (with filters/pagination)
  getAll(params = {}) {
    return axios.get(API_URL, { params });
  },

  // Get single quotation
  getById(id) {
    return axios.get(`${API_URL}/${id}`);
  },

  // Create quotation
  create(data) {
    return axios.post(API_URL, data);
  },

  // Update quotation
  update(id, data) {
    return axios.put(`${API_URL}/${id}`, data);
  },

  // Delete quotation
  delete(id) {
    return axios.delete(`${API_URL}/${id}`);
  },

  // Update status (e.g. draft, sent, accepted, rejected)
  updateStatus(id, status) {
    return axios.patch(`${API_URL}/${id}/status`, { status });
  },

  // Convert quotation → invoice
  convertToInvoice(id) {
    return axios.post(`${API_URL}/${id}/convert`);
  },
};