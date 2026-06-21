<script setup>
import { ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const email = ref('customer@fixit.test');
const password = ref('password');
const error = ref('');
const busy = ref(false);
const presets = [
  { label: 'Customer', email: 'customer@fixit.test' },
  { label: 'Provider', email: 'provider@fixit.test' },
  { label: 'Admin', email: 'admin@fixit.test' },
];

function usePreset(preset) {
  email.value = preset.email;
  password.value = 'password';
}

async function submit() {
  error.value = '';
  busy.value = true;
  try {
    await auth.login(email.value, password.value);
    router.push(route.query.redirect || '/');
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  } finally {
    busy.value = false;
  }
}
</script>

<template>
  <div class="auth-page secure-login">
    <div class="card auth-card login-card">
      <div class="login-brand">
        <span class="brand-mark">F</span>
        <div>
          <h1>FixIt Secure Login</h1>
          <p class="muted">Please sign in to your dashboard.</p>
        </div>
      </div>

      <p v-if="error" class="alert error">{{ error }}</p>

      <form @submit.prevent="submit">
        <label>Email Address</label>
        <input v-model="email" type="email" autocomplete="email" placeholder="admin@fixit.test" />

        <label>Password</label>
        <input v-model="password" type="password" autocomplete="current-password" placeholder="password" />

        <p><button class="login-btn" :disabled="busy" type="submit">{{ busy ? 'Signing in...' : 'Secure Login' }}</button></p>
      </form>

      <div class="demo-presets" aria-label="Demo accounts">
        <button
          v-for="preset in presets"
          :key="preset.email"
          class="secondary compact"
          type="button"
          @click="usePreset(preset)"
        >
          {{ preset.label }}
        </button>
      </div>

      <RouterLink to="/register">Create a test account</RouterLink>
    </div>
  </div>
</template>
