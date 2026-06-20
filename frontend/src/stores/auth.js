import { defineStore } from 'pinia';
import axios from 'axios';

const baseURL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('fixit_token') || null,
    user: JSON.parse(localStorage.getItem('fixit_user') || 'null'),
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.token),
    isAdmin: (state) => state.user?.role === 'admin',
    isProvider: (state) => state.user?.role === 'provider',
    isCustomer: (state) => state.user?.role === 'customer',
  },

  actions: {
    async login(email, password) {
      const { data } = await axios.post(`${baseURL}/auth/login`, { email, password });
      this.token = data.access_token;
      this.user = data.user;
      localStorage.setItem('fixit_token', this.token);
      localStorage.setItem('fixit_user', JSON.stringify(this.user));
    },

    async register(payload) {
      await axios.post(`${baseURL}/auth/register`, payload);
      await this.login(payload.email, payload.password);
    },

    logout() {
      this.token = null;
      this.user = null;
      localStorage.removeItem('fixit_token');
      localStorage.removeItem('fixit_user');
    },
  },
});
