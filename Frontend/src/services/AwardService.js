import api from "./api";

const BASE = "/hrm/awards";

const toFormData = (payload) => {
  const fd = new FormData();
  Object.entries(payload).forEach(([key, value]) => {
    if (value === null || value === undefined || value === "") return;
    fd.append(key, value);
  });
  return fd;
};

export default {
  async getAll(params = {}) {
    const res = await api.get(BASE, { params });
    return res.data;
  },
  async create(payload) {
    const res = await api.post(BASE, toFormData(payload), { headers: { "Content-Type": "multipart/form-data" } });
    return res.data;
  },
  async update(id, payload) {
    const fd = toFormData(payload);
    fd.append("_method", "PUT");
    const res = await api.post(`${BASE}/${id}`, fd, { headers: { "Content-Type": "multipart/form-data" } });
    return res.data;
  },
  async remove(id) {
    const res = await api.delete(`${BASE}/${id}`);
    return res.data;
  },
  async downloadDocument(id) {
    const res = await api.get(`${BASE}/${id}/document`, { responseType: "blob" });
    return res.data;
  },
};
