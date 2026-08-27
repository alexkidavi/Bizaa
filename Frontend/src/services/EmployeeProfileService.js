import api from "./api";

const BASE = "/hrm/employees";

const toFormData = (payload) => {
  const fd = new FormData();
  Object.entries(payload).forEach(([key, value]) => {
    if (value === null || value === undefined) return;
    if (key === "documents" && Array.isArray(value)) {
      value.forEach((file) => fd.append("documents[]", file));
      return;
    }
    fd.append(key, value);
  });
  return fd;
};

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
    const fd = toFormData(payload);
    const res = await api.post(BASE, fd, { headers: { "Content-Type": "multipart/form-data" } });
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
  async updateStatus(id, employment_status, reason) {
    const res = await api.patch(`${BASE}/${id}/status`, { employment_status, reason });
    return res.data;
  },
  async getHistory(id) {
    const res = await api.get(`${BASE}/${id}/history`);
    return res.data;
  },
  async removeDocument(id, path) {
    const res = await api.delete(`${BASE}/${id}/documents`, { data: { path } });
    return res.data;
  },
  async downloadDocument(id, path) {
    const res = await api.get(`${BASE}/${id}/documents/download`, { params: { path }, responseType: "blob" });
    return res.data;
  },
};
