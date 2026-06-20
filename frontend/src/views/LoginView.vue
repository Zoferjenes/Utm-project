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
  <div class="auth-page">
    <div class="card auth-card">
      <h1>Arcade FixIt</h1>
      <p class="muted">Local home services marketplace for customer, provider, and admin workflows.</p>

      <p v-if="error" class="alert error">{{ error }}</p>

      <label>Email</label>
      <input v-model="email" type="email" autocomplete="email" />

      <label>Password</label>
      <input v-model="password" type="password" autocomplete="current-password" />

      <p><button :disabled="busy" @click="submit">{{ busy ? 'Signing in...' : 'Sign in' }}</button></p>

      <p class="muted">
        Demo:
        customer@fixit.test,
        provider@fixit.test,
        admin@fixit.test
        / password
      </p>

      <RouterLink to="/register">Create a test account</RouterLink>
    </div>
  </div>
</template>

