<script setup>
import { RouterLink, RouterView, useRouter } from 'vue-router';
import { useAuthStore } from './stores/auth';

const auth = useAuthStore();
const router = useRouter();

function logout() {
  auth.logout();
  router.push('/login');
}
</script>

<template>
  <div class="app-shell">
    <aside v-if="auth.isAuthenticated" class="sidebar">
      <div class="brand">
        <span class="brand-mark">F</span>
        <div>
          <strong>FixIt</strong>
          <small>Arcade CPAD</small>
        </div>
      </div>

      <nav>
        <RouterLink to="/">Dashboard</RouterLink>
        <RouterLink to="/services">Services</RouterLink>
        <RouterLink to="/bookings">Bookings</RouterLink>
        <RouterLink v-if="auth.isProvider" to="/profile">Provider Profile</RouterLink>
        <RouterLink v-if="auth.isAdmin" to="/admin">Admin</RouterLink>
      </nav>

      <div class="profile">
        <span>{{ auth.user?.name }}</span>
        <small>{{ auth.user?.role }}</small>
        <button class="ghost" @click="logout">Logout</button>
      </div>
    </aside>

    <main class="main">
      <RouterView />
    </main>
  </div>
</template>
