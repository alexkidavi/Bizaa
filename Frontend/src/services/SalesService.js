// services/brandService.js
import axios from "axios";

const API_URL = "/api/sales";

export default {

getAll() {
  return axios.get(`${API_URL}/get`);
},

create(data) {
  return axios.post(`${API_URL}/pos-sale`, data);
},

update(id, data) {
  return axios.patch(`${API_URL}/${id}`, data);
},

delete(id) {
  return axios.delete(`${API_URL}/${id}/delete`);
},
 getByProduct(productId) {
    return axios.get(`${API_URL}/${productId}/product`);
  },
};