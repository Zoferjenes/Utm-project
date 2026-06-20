<script setup>
import { onMounted, ref } from 'vue';
import api from '../api/client';

const providers = ref([]);
const categories = ref([]);
const jobs = ref([]);
const error = ref('');
const ok = ref('');
const categoryForm = ref({ name: '', description: '', icon: 'tool' });

async function load() {
  error.value = '';
  try {
    const [providerRes, categoryRes, jobRes] = await Promise.all([
      api.get('/admin/providers/pending'),
      api.get('/categories'),
      api.get('/jobs'),
    ]);
    providers.value = providerRes.data.data;
    categories.value = categoryRes.data.data;
    jobs.value = jobRes.data.data;
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

async function setVerified(provider, isVerified) {
  ok.value = '';
  error.value = '';
  try {
    await api.patch(`/admin/providers/${provider.id}/verify`, { is_verified: isVerified });
    ok.value = `${provider.name} verification updated`;
    await load();
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

async function createCategory() {
  ok.value = '';
  error.value = '';
  try {
    await api.post('/admin/categories', categoryForm.value);
    ok.value = 'Category created';
    categoryForm.value = { name: '', description: '', icon: 'tool' };
    await load();
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
        <h1>Admin Control</h1>
        <p class="muted">Verify providers, manage service categories, and monitor platform jobs.</p>
      </div>
      <button class="secondary" @click="load">Refresh</button>
    </div>

    <p v-if="error" class="alert error">{{ error }}</p>
    <p v-if="ok" class="alert ok">{{ ok }}</p>

    <div class="grid cols-3">
      <div class="card metric">
        <span class="muted">Providers</span>
        <strong>{{ providers.length }}</strong>
      </div>
      <div class="card metric">
        <span class="muted">Categories</span>
        <strong>{{ categories.length }}</strong>
      </div>
      <div class="card metric">
        <span class="muted">Jobs</span>
        <strong>{{ jobs.length }}</strong>
      </div>
    </div>

    <div class="split" style="margin-top: 16px;">
      <div class="card">
        <h2>Provider Verification</h2>
        <div class="list">
          <article v-for="provider in providers" :key="provider.id" class="list-item">
            <header class="row" style="justify-content: space-between;">
              <div class="row">
                <img class="provider-photo small" :src="provider.photo_url || '/provider-omar.svg'" :alt="provider.name" />
                <div>
                  <strong>{{ provider.name }}</strong>
                  <p class="muted">{{ provider.location }} | RM{{ provider.base_rate }}</p>
                </div>
              </div>
              <span class="badge" :class="provider.is_verified == 1 ? 'success' : 'warning'">
                {{ provider.is_verified == 1 ? 'Verified' : 'Pending' }}
              </span>
            </header>
            <p>{{ provider.bio }}</p>
            <p class="muted">KYC: {{ provider.kyc_doc_url || 'not uploaded' }}</p>
            <div class="row">
              <button @click="setVerified(provider, true)">Verify</button>
              <button class="danger" @click="setVerified(provider, false)">Unverify</button>
            </div>
          </article>
        </div>
      </div>

      <div class="grid">
        <div class="card">
          <h2>Add Category</h2>
          <label>Name</label>
          <input v-model="categoryForm.name" placeholder="Painting" />
          <label>Description</label>
          <textarea v-model="categoryForm.description" placeholder="Short category description"></textarea>
          <label>Icon keyword</label>
          <input v-model="categoryForm.icon" placeholder="paint" />
          <p><button @click="createCategory">Add Category</button></p>
        </div>

        <div class="card">
          <h2>Safety Notes</h2>
          <ul>
            <li>Mock KYC document is required before verification.</li>
            <li>Providers can be unverified if suspicious.</li>
            <li>Admin can view all jobs for dispute handling.</li>
          </ul>
        </div>
      </div>
    </div>
  </section>
</template>
