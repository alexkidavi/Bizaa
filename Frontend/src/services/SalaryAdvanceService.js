import api from "./api";

const BASE = "/hrm/salary-advances";

export default {
  async getAll(params = {}) {
    const res = await api.get(BASE, { params });
    return res.data;
  },
  async getById(id) {
    const res = await api.get(`${BASE}/${id}`);
    return res.data;
  },
  async create(payload) {
    const res = await api.post(BASE, payload);
    return res.data;
  },
  async remove(id) {
    const res = await api.delete(`${BASE}/${id}`);
    return res.data;
  },
  async approve(id) {
    const res = await api.patch(`${BASE}/${id}/approve`);
    return res.data;
  },
  async reject(id, notes = null) {
    const res = await api.patch(`${BASE}/${id}/reject`, { notes });
    return res.data;
  },
  async cancel(id) {
    const res = await api.patch(`${BASE}/${id}/cancel`);
    return res.data;
  },
};
