import api from "@/services/api";

const API_URL = "/reports";

const ReportsService = {
  closeDayReport(from, to) {
    return api.get(`${API_URL}/close-day`, {
      params: { from, to },
    });
  },

  itemsSaleReport(params) {
    return api.get(`${API_URL}/items-sale`, {
      params,
    });
  },

  salesByCustomer(params) {
    return api.get(`${API_URL}/sales-by-customer`, {
      params,
    });
  },

  salesByBranch(params) {
    return api.get(`${API_URL}/sales-by-branch`, {
      params,
    });
  },

  itemsLedger(params) {
    return api.get(`${API_URL}/items-ledger`, {
      params,
    });
  },

  customerBalances(search = null) {
    return api.get(`${API_URL}/customer-balances`, {
      params: { search },
    });
  },

  supplierBalances(search = null) {
    return api.get(`${API_URL}/supplier-balances`, {
      params: { search },
    });
  },
  salePayments(params) {
    return api.get(`${API_URL}/sale-payments`, {
      params,
    });
  },
purchasePayments(params) {
    return api.get(`${API_URL}/purchase-payments`, {
      params,
    });
  },
};

export default ReportsService;