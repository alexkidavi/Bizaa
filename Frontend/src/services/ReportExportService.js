import api from "@/services/api";

const API_URL = "/reports/export";

export default {
  create(reportType, format, params) {
    return api.post(API_URL, { report_type: reportType, format, params });
  },
  status(id) {
    return api.get(`${API_URL}/${id}`);
  },
  download(id) {
    return api.get(`${API_URL}/${id}/download`, { responseType: "blob" });
  },
};
