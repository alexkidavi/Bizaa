import axios from "axios";

const API_URL = "/api/digitax";

export default {
  getPackageUnits() {
    return axios.get(`${API_URL}/package-units`);
  },

  getQuantityUnits() {
    return axios.get(`${API_URL}/quantity-units`);
  },

  searchItemClasses(search) {
    return axios.get(`${API_URL}/item-classes`, { params: { search } });
  },
};
