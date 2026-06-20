<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '../api/client';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const jobs = ref([]);
const providers = ref([]);
const categories = ref([]);
const error = ref('');

const activeJobs = computed(() => jobs.value.filter((job) => !['completed', 'reviewed', 'rejected'].includes(job.status)));
const completedJobs = computed(() => jobs.value.filter((job) => ['completed', 'reviewed'].includes(job.status)));
const pendingCost = computed(() => jobs.value.filter((job) => job.final_cost && Number(job.final_cost_confirmed) !== 1));

async function load() {
  try {
    const [jobRes, providerRes, categoryRes] = await Promise.all([
      api.get('/jobs'),
      api.get('/providers'),
      api.get('/categories'),
    ]);
    jobs.value = jobRes.data.data;
    providers.value = providerRes.data.data;
    categories.value = categoryRes.data.data;
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
        <h1>Dashboard</h1>
        <p class="muted">Welcome back, {{ auth.user?.name }}. Today queue is ready.</p>
      </div>
      <RouterLink to="/bookings"><button>Open Bookings</button></RouterLink>
    </div>

    <p v-if="error" class="alert error">{{ error }}</p>

    <div class="grid cols-3">
      <div class="card metric">
        <span class="muted">Visible Providers</span>
        <strong>{{ providers.length }}</strong>
      </div>
      <div class="card metric">
        <span class="muted">Service Categories</span>
        <strong>{{ categories.length }}</strong>
      </div>
      <div class="card metric">
        <span class="muted">Active Jobs</span>
        <strong>{{ activeJobs.length }}</strong>
      </div>
    </div>

    <div class="grid cols-2" style="margin-top: 16px;">
      <div class="card">
        <h2>Next Action</h2>
        <div v-if="auth.isCustomer">
          <p>Browse verified providers, book a service, then confirm the final cost after completion.</p>
        </div>
        <div v-else-if="auth.isProvider">
          <p>Keep your profile current, accept suitable requests, and set the final cost before closing a job.</p>
          <RouterLink to="/profile"><button class="secondary">Edit Profile</button></RouterLink>
        </div>
        <div v-else>
          <p>Review provider KYC details, verify trusted providers, and keep service categories tidy.</p>
        </div>
      </div>

      <div class="card">
        <h2>Recent Jobs</h2>
        <div class="list">
          <article v-for="job in jobs.slice(0, 4)" :key="job.id" class="list-item">
            <strong>#{{ job.id }} {{ job.category_name }}</strong>
            <p class="muted">{{ job.status }} | {{ job.provider_name }}</p>
          </article>
          <p v-if="!jobs.length" class="muted">No jobs yet.</p>
        </div>
      </div>
    </div>

    <div class="card" style="margin-top: 16px;">
      <h2>Operations Snapshot</h2>
      <div class="grid cols-3">
        <span class="badge warning">{{ activeJobs.length }} active jobs</span>
        <span class="badge success">{{ completedJobs.length }} completed jobs</span>
        <span class="badge">{{ pendingCost.length }} waiting for cost confirmation</span>
      </div>
    </div>
  </section>
</template>
