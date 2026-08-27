import api from "./api";

const BASE = "/hrm/leave-balances";

export default {
  async getAll(params = {}) {
    const res = await api.get(BASE, { params });
    return res.data;
  },
  async ensureForYear(year) {
    const res = await api.get(`${BASE}/ensure`, { params: { year } });
    return res.data;
  },
  async getById(id) {
    const res = await api.get(`${BASE}/${id}`);
    return res.data;
  },
  async adjust(id, amount, reason) {
    const res = await api.patch(`${BASE}/${id}/adjust`, { amount, reason });
    return res.data;
  },
  async getHistory(id) {
    const res = await api.get(`${BASE}/${id}/history`);
    return res.data;
  },
};
