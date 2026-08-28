import api from "./api";

const BASE = "/loyalty";

export default {
  async getCustomers(params = {}) {
    const res = await api.get(`${BASE}/customers`, { params });
    return res.data;
  },
  async getHistory(customerId, params = {}) {
    const res = await api.get(`${BASE}/customers/${customerId}/history`, { params });
    return res.data;
  },
  async adjust(customerId, payload) {
    const res = await api.post(`${BASE}/customers/${customerId}/adjust`, payload);
    return res.data;
  },
  async previewRedeem(customerId, amount) {
    const res = await api.post(`${BASE}/preview-redeem`, { customer_id: customerId, amount });
    return res.data;
  },
};
