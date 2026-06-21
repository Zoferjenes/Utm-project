<script setup>
import { onMounted, ref } from 'vue';
import api from '../api/client';

const categories = ref([]);
const providers = ref([]);
const q = ref('');
const selectedCategory = ref('');
const maxRate = ref('');
const error = ref('');

async function load() {
  error.value = '';
  try {
    const [categoryRes, providerRes] = await Promise.all([
      api.get('/categories'),
      api.get('/providers', {
        params: {
          q: q.value || undefined,
          category_id: selectedCategory.value || undefined,
          max_rate: maxRate.value || undefined,
        },
      }),
    ]);
    categories.value = categoryRes.data.data;
    providers.value = providerRes.data.data;
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

onMounted(load);
</script>

<template>
  <section>
    <div class="page-header">
      <div>
        <h1>Services</h1>
        <p class="muted">Browse verified local providers by category, rate, and location.</p>
      </div>
      <RouterLink to="/bookings"><button>Create Booking</button></RouterLink>
    </div>

    <p v-if="error" class="alert error">{{ error }}</p>

    <div class="card">
      <div class="form-grid">
        <div>
          <label>Search</label>
          <input v-model="q" placeholder="Skudai, plumber, cleaning" @keyup.enter="load" />
        </div>
        <div>
          <label>Category</label>
          <select v-model="selectedCategory" @change="load">
            <option value="">All categories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
        </div>
        <div>
          <label>Max Rate</label>
          <input v-model.number="maxRate" type="number" min="0" placeholder="60" @keyup.enter="load" />
        </div>
      </div>
      <p><button class="secondary" @click="load">Apply Filters</button></p>
    </div>

    <div class="grid cols-3" style="margin-top: 16px;">
      <div v-for="category in categories" :key="category.id" class="card metric">
        <span class="muted">{{ category.icon }}</span>
        <strong style="font-size: 22px;">{{ category.name }}</strong>
        <p>{{ category.description }}</p>
      </div>
    </div>

    <h2>Verified Providers</h2>
    <div class="grid cols-3">
      <article v-for="provider in providers" :key="provider.provider_id" class="card provider-card">
        <header>
          <img class="provider-photo" :src="provider.photo_url || '/provider-ali.svg'" :alt="provider.name" />
          <div>
            <strong>{{ provider.name }}</strong>
            <p class="muted">{{ provider.location }}</p>
          </div>
          <span class="badge success">Verified</span>
        </header>
        <p>{{ provider.bio }}</p>
        <p class="muted">{{ provider.categories || 'General services' }}</p>
        <div class="row">
          <span class="badge">RM{{ provider.base_rate }}</span>
          <span class="badge warning">{{ provider.rating_avg || 0 }} rating</span>
          <RouterLink to="/bookings"><button class="secondary compact">Book</button></RouterLink>
        </div>
      </article>
    </div>
  </section>
</template>
