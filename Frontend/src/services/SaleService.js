// services/brandService.js
import axios from "axios";

const API_URL = "/api/sales";

export default {

getAll(params) {
  return axios.get(`${API_URL}/get-sales`, {
    params
  });
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
  getById(id) {
    return axios.get(`${API_URL}/${id}/get-sale`);
  },
getPayments(id) {
  return axios.get(`${API_URL}/sale-payments/${id}`);
},
recordPayment(id, data) {
  return axios.post(`${API_URL}/${id}/record-payment`, data);
},
// `id` here is the sale's plain numeric id (sale.id from getById's
// response), not the encrypted route id getById() itself takes — see
// SaleController::loadSaleForReceipt on the backend.
emailReceipt(id, email) {
  return axios.post(`${API_URL}/${id}/email-receipt`, email ? { email } : {});
},
};