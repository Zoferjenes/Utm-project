<script setup>
import { onMounted, ref } from 'vue';
import api from '../api/client';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const categories = ref([]);
const selectedCategories = ref([]);
const kycFile = ref(null);
const error = ref('');
const ok = ref('');
const form = ref({
  bio: 'Reliable home service provider for UTM student areas.',
  location: 'Taman Universiti, Skudai',
  base_rate: 50,
  photo_url: '/provider-ali.svg',
  kyc_doc_url: 'mock-kyc/provider-profile.pdf',
});

async function load() {
  error.value = '';
  try {
    const [categoryRes, profileRes] = await Promise.all([
      api.get('/categories'),
      api.get('/providers/profile'),
    ]);
    categories.value = categoryRes.data.data;
    selectedCategories.value = profileRes.data.selected_categories || [];

    if (profileRes.data.data) {
      form.value = {
        bio: profileRes.data.data.bio,
        location: profileRes.data.data.location,
        base_rate: Number(profileRes.data.data.base_rate),
        photo_url: profileRes.data.data.photo_url || '/provider-ali.svg',
        kyc_doc_url: profileRes.data.data.kyc_doc_url || 'mock-kyc/provider-profile.pdf',
      };
    }
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

async function save() {
  ok.value = '';
  error.value = '';
  try {
    await api.post('/providers/profile', {
      ...form.value,
      category_ids: selectedCategories.value.map(Number),
    });
    ok.value = 'Provider profile saved';
    await load();
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

function setKycFile(event) {
  kycFile.value = event.target.files?.[0] || null;
}

async function uploadKyc() {
  ok.value = '';
  error.value = '';
  if (!kycFile.value) {
    error.value = 'Choose a KYC file first';
    return;
  }

  const data = new FormData();
  data.append('document', kycFile.value);

  try {
    const response = await api.post('/providers/kyc', data, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    form.value.kyc_doc_url = response.data.kyc_doc_url;
    ok.value = 'KYC document uploaded';
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
        <h1>Provider Profile</h1>
        <p class="muted">Manage public provider details, categories, rate, photo, and KYC reference.</p>
      </div>
      <button class="secondary" @click="load">Refresh</button>
    </div>

    <p v-if="error" class="alert error">{{ error }}</p>
    <p v-if="ok" class="alert ok">{{ ok }}</p>

    <div class="split">
      <div class="card">
        <h2>Profile Details</h2>
        <label>Bio</label>
        <textarea v-model="form.bio"></textarea>

        <label>Location</label>
        <input v-model="form.location" />

        <label>Base Rate (RM)</label>
        <input v-model.number="form.base_rate" type="number" min="0" />

        <label>Photo URL</label>
        <input v-model="form.photo_url" />

        <label>Mock KYC Reference</label>
        <input v-model="form.kyc_doc_url" />

        <p><button @click="save">Save Profile</button></p>

        <div class="upload-panel">
          <label>KYC Upload</label>
          <input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="setKycFile" />
          <button class="secondary" @click="uploadKyc">Upload KYC</button>
        </div>
      </div>

      <div class="card">
        <h2>Service Categories</h2>
        <div class="checkbox-list">
          <label v-for="category in categories" :key="category.id">
            <input v-model="selectedCategories" type="checkbox" :value="category.id" />
            <span>{{ category.name }}</span>
          </label>
        </div>

        <h2>Public Preview</h2>
        <article class="list-item provider-preview">
          <img class="provider-photo" :src="form.photo_url || '/provider-ali.svg'" alt="Provider preview" />
          <div>
            <strong>{{ auth.user?.name }}</strong>
            <p class="muted">{{ form.location }}</p>
            <p>{{ form.bio }}</p>
            <span class="badge">RM{{ form.base_rate }}</span>
            <span class="badge warning">Admin controls public visibility</span>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>
