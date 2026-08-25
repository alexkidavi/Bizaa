import axios from "axios";

const API_URL = "/api/stocks";

export default {
  // Get all stocks
  getAll(params = {}) {
    return axios.get(`${API_URL}/get`, { params });
  },

  // Get stock per product
  getByProduct(productId) {
    return axios.get(`${API_URL}/product/${productId}`);
  },

  // Get single stock take (BY ID)
  getById(id) {
    return axios.get(`${API_URL}/show/${id}`);
  },

  // Get stock take items (BY STOCK TAKE ID)
  getItems(id, params = {}) {
    return axios.get(`${API_URL}/items/${id}`, { params });
  },


  // Adjust stock (add/remove)
  adjust(productId, data) {
    return axios.post(`${API_URL}/adjust/${productId}`, data);
  },

  // Create (REGISTER stock)
  create(data) {
    return axios.post(`${API_URL}/register`, data);
  },

  // Update stock
  update(id, data) {
    return axios.patch(`${API_URL}/${id}`, data);
  },

  // Delete stock
  delete(id) {
    return axios.delete(`${API_URL}/${id}/delete`);
  },
  saveCounts(stockTakeId, data) {
  return axios.post(`${API_URL}/save-counts/${stockTakeId}`, data);
},
  transferStock(data) {
  return axios.post(`${API_URL}/transfer`, data);
},

searchBranchProducts(params) {
  // alert(params);
  return axios.get(`${API_URL}/branch-products`, { params });
},

 getRecent() {
  return axios.get(`${API_URL}/stock-transfers`);
}
};