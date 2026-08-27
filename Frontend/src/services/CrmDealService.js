import api from "./api";

const BASE = "/crm/deals";

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
  async moveStage(id, pipelineStageId) {
    const res = await api.patch(`${BASE}/${id}/move-stage`, { pipeline_stage_id: pipelineStageId });
    return res.data;
  },
  async markWon(id) {
    const res = await api.patch(`${BASE}/${id}/mark-won`);
    return res.data;
  },
  async markLost(id, reason) {
    const res = await api.patch(`${BASE}/${id}/mark-lost`, { reason });
    return res.data;
  },
  async linkQuotation(id, quotationId) {
    const res = await api.post(`${BASE}/${id}/link-quotation`, { quotation_id: quotationId });
    return res.data;
  },
  async linkSale(id, saleId) {
    const res = await api.post(`${BASE}/${id}/link-sale`, { sale_id: saleId });
    return res.data;
  },
};
