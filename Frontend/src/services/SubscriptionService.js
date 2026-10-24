import axios from "axios";

const BASE = "/api/subscription";

export default {
  getPlans() {
    return axios.get(`${BASE}/plans`);
  },

  getCurrent() {
    return axios.get(`${BASE}/current`);
  },

  getHistory() {
    return axios.get(`${BASE}/history`);
  },

  initiateStk(data) {
    return axios.post(`${BASE}/initiate-stk`, data);
  },

  checkStkStatus(checkoutRequestId) {
    return axios.get("/api/mpesa/check-status", {
      params: { checkout_request_id: checkoutRequestId },
    });
  },
  renewSubscription(id, status) {
    return axios.get("/renew", {
      params: { plan_id: id, status: status },
    });
  },
  // Submit offline M-Pesa payment confirmation
  submitOfflinePayment(data) {
    return axios.post(`${BASE}/offline-payment`, data);
  },
};