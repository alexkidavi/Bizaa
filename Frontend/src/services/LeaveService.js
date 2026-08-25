import api from "./api";

const BASE = "/hrm/leaves";

export default {
  async getAll(params = {}) {
    const res = await api.get(BASE, { params });
    return res.data;
  },
  async create(payload) {
    const res = await api.post(BASE, payload);
    return res.data;
  },
  async updateStatus(id, status, notes = null) {
    const res = await api.patch(`${BASE}/${id}/status`, { status, notes });
    return res.data;
  },
  async remove(id) {
    const res = await api.delete(`${BASE}/${id}`);
    return res.data;
  },
};
