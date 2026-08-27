import api from "./api";

const BASE = "/hrm/payroll-periods";

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
  async calculate(id) {
    const res = await api.post(`${BASE}/${id}/calculate`);
    return res.data;
  },
  async cancel(id) {
    const res = await api.patch(`${BASE}/${id}/cancel`);
    return res.data;
  },
  async approve(id) {
    const res = await api.patch(`${BASE}/${id}/approve`);
    return res.data;
  },
  async finalize(id) {
    const res = await api.patch(`${BASE}/${id}/finalize`);
    return res.data;
  },
  async markPaid(id) {
    const res = await api.patch(`${BASE}/${id}/pay`);
    return res.data;
  },
  async toggleExclude(id, payslipId, excluded) {
    const res = await api.patch(`${BASE}/${id}/payslips/${payslipId}/exclude`, { excluded });
    return res.data;
  },
};
