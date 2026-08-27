import api from "./api";

const BASE = "/hrm/payroll-settings";

export default {
  async get() {
    const res = await api.get(BASE);
    return res.data;
  },
  async update(payload) {
    const res = await api.put(BASE, payload);
    return res.data;
  },
};
