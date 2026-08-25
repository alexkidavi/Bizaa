import api from "./api";

const BASE = "/hrm/payroll";

export default {
  async getAll(params = {}) {
    const res = await api.get(BASE, { params });
    return res.data;
  },
  async generate(month, year) {
    const res = await api.post(`${BASE}/generate`, { month, year });
    return res.data;
  },
  async update(id, payload) {
    const res = await api.patch(`${BASE}/${id}`, payload);
    return res.data;
  },
  async markPaid(id) {
    const res = await api.patch(`${BASE}/${id}/pay`);
    return res.data;
  },
  async remove(id) {
    const res = await api.delete(`${BASE}/${id}`);
    return res.data;
  },
};
