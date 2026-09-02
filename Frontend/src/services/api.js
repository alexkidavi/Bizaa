// services/api.js
import axios from "axios";
import { attachSessionHandling } from "./attachSessionHandling.js";
import { API_BASE_URL } from "@/config";

const api = axios.create({
  baseURL: API_BASE_URL,
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
});

attachSessionHandling(api);

export default api;


