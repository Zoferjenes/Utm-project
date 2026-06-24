import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const configuredBaseURL = import.meta.env.VITE_API_BASE_URL || '';
const localBaseURL = import.meta.env.DEV ? 'http://localhost:8000' : '';
const baseURL = (configuredBaseURL || localBaseURL).replace(/\/$/, '');

const api = axios.create({
  baseURL,
  timeout: 10000,
});

api.interceptors.request.use((config) => {
  const auth = useAuthStore();
  if (auth.token) {
    config.headers.Authorization = `Bearer ${auth.token}`;
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      useAuthStore().logout();
    }
    return Promise.reject(error);
  },
);

export default api;
