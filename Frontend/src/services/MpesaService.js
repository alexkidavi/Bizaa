import axios from "axios";

const API_URL = "/api/mpesa";

export default {
  // STK Push
  async initiateSTK(payload) {
    return await axios.post(`${API_URL}/stk`, payload);
  },

  // ✅ Check STK Status
  async checkSTKStatus(payload) {
    // payload: { checkout_request_id }
    return await axios.post(`${API_URL}/stk-status`, payload);
  },

  // Recent confirmed payments for this business (manual-match "Sync" tab)
  async getRecentPayments(params) {
    return await axios.get(`${API_URL}/recent-payments`, { params });
  },
};