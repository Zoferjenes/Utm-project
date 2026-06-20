<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '../api/client';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const jobs = ref([]);
const categories = ref([]);
const providers = ref([]);
const finalCosts = ref({});
const reviews = ref({});
const error = ref('');
const ok = ref('');
const form = ref({
  provider_id: '',
  category_id: '',
  scheduled_at: '2026-06-24 10:00:00',
  address: 'Block A, Student Apartment, Skudai',
  description: 'Kitchen sink pipe is leaking.',
  total: 50,
});

const canCreate = computed(() => auth.isCustomer);
const canUpdate = computed(() => auth.isProvider || auth.isAdmin);

async function load() {
  error.value = '';
  try {
    const [jobRes, categoryRes, providerRes] = await Promise.all([
      api.get('/jobs'),
      api.get('/categories'),
      api.get('/providers'),
    ]);
    jobs.value = jobRes.data.data;
    jobs.value.forEach((job) => {
      if (finalCosts.value[job.id] === undefined) {
        finalCosts.value[job.id] = Number(job.final_cost || job.total || 0);
      }
      if (!reviews.value[job.id]) {
        reviews.value[job.id] = { rating: 5, comment: '' };
      }
    });
    categories.value = categoryRes.data.data;
    providers.value = providerRes.data.data;
    if (!form.value.provider_id && providers.value[0]) form.value.provider_id = providers.value[0].provider_id;
    if (!form.value.category_id && categories.value[0]) form.value.category_id = categories.value[0].id;
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

async function createJob() {
  ok.value = '';
  error.value = '';
  try {
    await api.post('/jobs', form.value);
    ok.value = 'Booking request created';
    await load();
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

async function updateStatus(job, status) {
  ok.value = '';
  error.value = '';
  try {
    await api.patch(`/jobs/${job.id}/status`, { status });
    ok.value = `Job #${job.id} updated to ${status}`;
    await load();
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

async function setFinalCost(job) {
  ok.value = '';
  error.value = '';
  try {
    await api.patch(`/jobs/${job.id}/cost`, { final_cost: finalCosts.value[job.id] });
    ok.value = `Final cost saved for job #${job.id}`;
    await load();
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

async function confirmCost(job) {
  ok.value = '';
  error.value = '';
  try {
    await api.patch(`/jobs/${job.id}/confirm-cost`);
    ok.value = `Final cost confirmed for job #${job.id}`;
    await load();
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

async function submitReview(job) {
  ok.value = '';
  error.value = '';
  try {
    await api.post('/reviews', { job_id: job.id, ...reviews.value[job.id] });
    ok.value = `Review submitted for job #${job.id}`;
    await load();
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

function badgeClass(status) {
  if (['completed', 'reviewed'].includes(status)) return 'success';
  if (status === 'rejected') return 'danger';
  return 'warning';
}

onMounted(load);
</script>

<template>
  <section>
    <div class="page-header">
      <div>
        <h1>Bookings</h1>
        <p class="muted">Customer requests, provider status updates, and admin monitoring.</p>
      </div>
      <button class="secondary" @click="load">Refresh</button>
    </div>

    <p v-if="error" class="alert error">{{ error }}</p>
    <p v-if="ok" class="alert ok">{{ ok }}</p>

    <div class="split">
      <div class="card">
        <h2>Job List</h2>
        <div class="list">
          <article v-for="job in jobs" :key="job.id" class="list-item job-card">
            <header>
              <div>
                <strong>#{{ job.id }} - {{ job.category_name }}</strong>
                <p class="muted">{{ job.customer_name }} -> {{ job.provider_name }}</p>
              </div>
              <span class="badge" :class="badgeClass(job.status)">{{ job.status }}</span>
            </header>
            <p>{{ job.description }}</p>
            <p class="muted">{{ job.address }} | {{ job.scheduled_at }} | estimate RM{{ job.total }}</p>
            <p v-if="job.final_cost" class="muted">
              final RM{{ job.final_cost }}
              <span class="badge" :class="Number(job.final_cost_confirmed) === 1 ? 'success' : 'warning'">
                {{ Number(job.final_cost_confirmed) === 1 ? 'confirmed' : 'awaiting confirmation' }}
              </span>
            </p>
            <div v-if="canUpdate" class="row">
              <button @click="updateStatus(job, 'accepted')">Accept</button>
              <button class="secondary" @click="updateStatus(job, 'in_progress')">In Progress</button>
              <button class="secondary" @click="updateStatus(job, 'completed')">Complete</button>
              <button class="danger" @click="updateStatus(job, 'rejected')">Reject</button>
            </div>
            <div v-if="canUpdate" class="inline-form">
              <input v-model.number="finalCosts[job.id]" type="number" min="0" />
              <button class="secondary" @click="setFinalCost(job)">Save Final Cost</button>
            </div>
            <div v-if="auth.isCustomer && job.final_cost && Number(job.final_cost_confirmed) !== 1 && ['completed', 'reviewed'].includes(job.status)" class="row">
              <button class="secondary" @click="confirmCost(job)">Confirm Final Cost</button>
            </div>
            <div v-if="auth.isCustomer && job.status === 'completed'" class="review-box">
              <label>Rating</label>
              <select v-model.number="reviews[job.id].rating">
                <option v-for="value in [5, 4, 3, 2, 1]" :key="value" :value="value">{{ value }}</option>
              </select>
              <label>Comment</label>
              <textarea v-model="reviews[job.id].comment" placeholder="Short review"></textarea>
              <button class="secondary" @click="submitReview(job)">Submit Review</button>
            </div>
          </article>
        </div>
      </div>

      <div class="card">
        <h2>Create Booking</h2>
        <p v-if="!canCreate" class="muted">Only customer accounts can create booking requests.</p>
        <template v-else>
          <label>Provider</label>
          <select v-model="form.provider_id">
            <option v-for="provider in providers" :key="provider.provider_id" :value="provider.provider_id">
              {{ provider.name }} - RM{{ provider.base_rate }}
            </option>
          </select>

          <label>Category</label>
          <select v-model="form.category_id">
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>

          <label>Scheduled At</label>
          <input v-model="form.scheduled_at" />

          <label>Address</label>
          <textarea v-model="form.address"></textarea>

          <label>Description</label>
          <textarea v-model="form.description"></textarea>

          <label>Estimated Total</label>
          <input v-model.number="form.total" type="number" min="0" />

          <p><button @click="createJob">Submit Booking</button></p>
        </template>
      </div>
    </div>
  </section>
</template>
