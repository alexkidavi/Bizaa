import api from "./api";

const BASE = "/crm/contacts";

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
  async update(id, payload) {
    const res = await api.put(`${BASE}/${id}`, payload);
    return res.data;
  },
  async remove(id) {
    const res = await api.delete(`${BASE}/${id}`);
    return res.data;
  },
  async convertToCustomer(id) {
    const res = await api.post(`${BASE}/${id}/convert-to-customer`);
    return res.data;
  },
};
