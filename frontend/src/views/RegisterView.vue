<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const error = ref('');
const busy = ref(false);
const form = ref({
  name: '',
  email: '',
  password: '',
  phone: '',
  role: 'customer',
});

async function submit() {
  error.value = '';
  busy.value = true;
  try {
    await auth.register(form.value);
    router.push('/');
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
      <h1>Create Account</h1>
      <p class="muted">Register as a customer or service provider.</p>

      <p v-if="error" class="alert error">{{ error }}</p>

      <label>Name</label>
      <input v-model="form.name" />

      <label>Email</label>
      <input v-model="form.email" type="email" />

      <label>Password</label>
      <input v-model="form.password" type="password" />

      <label>Phone</label>
      <input v-model="form.phone" />

      <label>Role</label>
      <select v-model="form.role">
        <option value="customer">Customer</option>
        <option value="provider">Provider</option>
      </select>

      <p><button :disabled="busy" @click="submit">{{ busy ? 'Creating...' : 'Create account' }}</button></p>
      <RouterLink to="/login">Back to login</RouterLink>
    </div>
  </div>
</template>

