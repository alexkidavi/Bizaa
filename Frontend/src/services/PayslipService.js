import api from "./api";

const BASE = "/hrm/payslips";

export default {
  async getById(id) {
    const res = await api.get(`${BASE}/${id}`);
    return res.data;
  },
  async downloadPdf(id) {
    const res = await api.get(`${BASE}/${id}/pdf`, { responseType: "blob" });
    return res.data;
  },
};
