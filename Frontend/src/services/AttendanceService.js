import api from "./api";

const BASE = "/hrm/attendance";

export default {
  async getAll(params = {}) {
    const res = await api.get(BASE, { params });
    return res.data;
  },
  async record(payload) {
    const res = await api.post(BASE, payload);
    return res.data;
  },
  async remove(id) {
    const res = await api.delete(`${BASE}/${id}`);
    return res.data;
  },
};
