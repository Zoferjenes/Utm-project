import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'dashboard', component: () => import('../views/DashboardView.vue'), meta: { requiresAuth: true } },
    { path: '/services', name: 'services', component: () => import('../views/ServicesView.vue'), meta: { requiresAuth: true } },
    { path: '/bookings', name: 'bookings', component: () => import('../views/BookingsView.vue'), meta: { requiresAuth: true } },
    { path: '/profile', name: 'profile', component: () => import('../views/ProfileView.vue'), meta: { requiresAuth: true, roles: ['provider'] } },
    { path: '/admin', name: 'admin', component: () => import('../views/AdminView.vue'), meta: { requiresAuth: true, roles: ['admin'] } },
    { path: '/login', name: 'login', component: () => import('../views/LoginView.vue') },
    { path: '/register', name: 'register', component: () => import('../views/RegisterView.vue') },
  ],
});

router.beforeEach((to) => {
  const auth = useAuthStore();

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } };
  }

  if (to.meta.roles && !to.meta.roles.includes(auth.user?.role)) {
    return { name: 'dashboard' };
  }
});

export default router;
