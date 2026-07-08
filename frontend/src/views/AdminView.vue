<script setup>
import { computed, onMounted, ref } from 'vue';
import api from '../api/client';

const providers = ref([]);
const categories = ref([]);
const jobs = ref([]);
const overview = ref({
  counts: {},
  status_breakdown: [],
  latest_jobs: [],
});
const activeTab = ref('overview');
const error = ref('');
const ok = ref('');
const busy = ref(false);
const categoryForm = ref({ name: '', description: '', icon: 'tool' });
const editingCategoryId = ref(null);

const tabs = [
  { id: 'overview', label: 'Overview', meta: 'Dashboard cards' },
  { id: 'providers', label: 'Providers', meta: 'Approve or reject' },
  { id: 'categories', label: 'Categories', meta: 'CRUD forms' },
  { id: 'safety', label: 'Safety', meta: 'Admin rules' },
];

const counts = computed(() => overview.value.counts || {});
const pendingProviders = computed(() => providers.value.filter((provider) => Number(provider.is_verified) !== 1));
const verifiedProviders = computed(() => providers.value.filter((provider) => Number(provider.is_verified) === 1));
const activeJobs = computed(() => jobs.value.filter((job) => !['completed', 'reviewed', 'rejected'].includes(job.status)));
const recentJobs = computed(() => overview.value.latest_jobs?.length ? overview.value.latest_jobs : jobs.value);

async function load() {
  error.value = '';
  busy.value = true;
  try {
    const [overviewRes, providerRes, categoryRes, jobRes] = await Promise.all([
      api.get('/admin/overview'),
      api.get('/admin/providers/pending'),
      api.get('/categories'),
      api.get('/jobs'),
    ]);
    overview.value = overviewRes.data.data;
    providers.value = providerRes.data.data;
    categories.value = categoryRes.data.data;
    jobs.value = jobRes.data.data;
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  } finally {
    busy.value = false;
  }
}

async function setVerified(provider, isVerified) {
  ok.value = '';
  error.value = '';
  try {
    await api.patch(`/admin/providers/${provider.id}/verify`, { is_verified: isVerified });
    ok.value = isVerified
      ? `${provider.name} approved and visible to customers`
      : `${provider.name} kept out of the verified marketplace`;
    await load();
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

function resetCategoryForm() {
  editingCategoryId.value = null;
  categoryForm.value = { name: '', description: '', icon: 'tool' };
}

function editCategory(category) {
  editingCategoryId.value = category.id;
  categoryForm.value = {
    name: category.name,
    description: category.description || '',
    icon: category.icon || 'tool',
  };
}

async function saveCategory() {
  ok.value = '';
  error.value = '';

  if (!categoryForm.value.name.trim()) {
    error.value = 'Category name is required';
    return;
  }

  try {
    if (editingCategoryId.value) {
      await api.patch(`/admin/categories/${editingCategoryId.value}`, categoryForm.value);
      ok.value = 'Category updated';
    } else {
      await api.post('/admin/categories', categoryForm.value);
      ok.value = 'Category created';
    }
    resetCategoryForm();
    await load();
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

async function deleteCategory(category) {
  if (!window.confirm(`Deactivate ${category.name}? Existing job history will remain.`)) return;

  ok.value = '';
  error.value = '';
  try {
    await api.delete(`/admin/categories/${category.id}`);
    ok.value = `${category.name} deactivated`;
    if (editingCategoryId.value === category.id) resetCategoryForm();
    await load();
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

function money(value) {
  return Number(value || 0).toFixed(2);
}

function statusClass(status) {
  if (['completed', 'reviewed'].includes(status)) return 'success';
  if (status === 'rejected') return 'danger';
  return 'warning';
}

onMounted(load);
</script>

<template>
  <section class="admin-portal">
    <div class="admin-workspace">
      <aside class="admin-panel">
        <div class="admin-brand">
          <span class="brand-mark">F</span>
          <div>
            <strong>FixIt Admin</strong>
            <small>Logged in as Admin</small>
          </div>
        </div>

        <nav class="admin-nav" aria-label="Admin sections">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            type="button"
            :class="{ active: activeTab === tab.id }"
            @click="activeTab = tab.id"
          >
            <span>{{ tab.label }}</span>
            <small>{{ tab.meta }}</small>
          </button>
        </nav>

        <div class="admin-panel-footer">
          <button class="secondary" :disabled="busy" @click="load">
            {{ busy ? 'Refreshing...' : 'Refresh Data' }}
          </button>
        </div>
      </aside>

      <div class="admin-content">
        <div class="page-header">
          <div>
            <h1>Admin Control</h1>
            <p class="muted">Verify providers, manage service categories, and monitor platform jobs.</p>
          </div>
          <span class="badge">System Settings</span>
        </div>

        <p v-if="error" class="alert error">{{ error }}</p>
        <p v-if="ok" class="alert ok">{{ ok }}</p>

        <section v-if="activeTab === 'overview'" class="admin-section">
          <div class="grid cols-3">
            <div class="card metric metric-accent blue">
              <span class="muted">Total Registered Users</span>
              <strong>{{ counts.total_users ?? 0 }}</strong>
            </div>
            <div class="card metric metric-accent orange">
              <span class="muted">Pending Providers</span>
              <strong>{{ counts.pending_providers ?? pendingProviders.length }}</strong>
            </div>
            <div class="card metric metric-accent green">
              <span class="muted">Active Marketplace Jobs</span>
              <strong>{{ counts.active_jobs ?? activeJobs.length }}</strong>
            </div>
          </div>

          <div class="grid cols-2 admin-overview-grid">
            <div class="card">
              <div class="section-title">
                <div>
                  <h2>Verify New Service Providers</h2>
                  <p class="muted">Pending KYC/profile review queue.</p>
                </div>
                <span class="badge warning">{{ pendingProviders.length }} pending</span>
              </div>
              <div class="table-wrap">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Provider Name</th>
                      <th>Requested Skill Category</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="provider in pendingProviders" :key="provider.id">
                      <td>
                        <strong>{{ provider.name }}</strong>
                        <p class="muted">{{ provider.location }} | RM{{ money(provider.base_rate) }}</p>
                      </td>
                      <td>{{ provider.categories || provider.bio }}</td>
                      <td><span class="badge warning">Pending Review</span></td>
                      <td>
                    <div v-if="Number(provider.is_verified) == 1" class="row">
                      <button class="compact" @click="setVerified(provider, true)">Approve</button>
                      <button class="danger compact" @click="setVerified(provider, false)">Reject</button>
                    </div>
                    <span v-else class="badge success">No action needed</span>
                  </td>
                    </tr>
                    <tr v-if="!pendingProviders.length">
                      <td colspan="4" class="empty-cell">All service providers verified.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <div class="card">
              <div class="section-title">
                <div>
                  <h2>Recent Platform Jobs</h2>
                  <p class="muted">Latest service activity from MySQL.</p>
                </div>
                <span class="badge">{{ jobs.length }} jobs</span>
              </div>
              <div class="table-wrap">
                <table class="data-table compact-table">
                  <thead>
                    <tr>
                      <th>Job</th>
                      <th>Status</th>
                      <th>Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="job in recentJobs.slice(0, 6)" :key="job.id">
                      <td>
                        <strong>#{{ job.id }} {{ job.category_name }}</strong>
                        <p class="muted">{{ job.customer_name }} -> {{ job.provider_name }}</p>
                      </td>
                      <td><span class="badge" :class="statusClass(job.status)">{{ job.status }}</span></td>
                      <td>RM{{ money(job.final_cost || job.total) }}</td>
                    </tr>
                    <tr v-if="!jobs.length">
                      <td colspan="3" class="empty-cell">No jobs yet.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="grid cols-3 admin-status-grid">
            <span
              v-for="item in overview.status_breakdown"
              :key="item.status"
              class="badge"
              :class="statusClass(item.status)"
            >
              {{ item.status }}: {{ item.total }}
            </span>
          </div>
        </section>

        <section v-if="activeTab === 'providers'" class="admin-section card">
          <div class="section-title">
            <div>
              <h2>Provider Verification</h2>
              <p class="muted">{{ verifiedProviders.length }} verified, {{ pendingProviders.length }} waiting.</p>
            </div>
          </div>

          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Provider</th>
                  <th>Service Profile</th>
                  <th>KYC</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="provider in providers" :key="provider.id">
                  <td>
                    <div class="row provider-cell">
                      <img class="provider-photo small" :src="provider.photo_url || '/provider-omar.svg'" :alt="provider.name" />
                      <div>
                        <strong>{{ provider.name }}</strong>
                        <p class="muted">{{ provider.email }}</p>
                      </div>
                    </div>
                  </td>
                  <td>{{ provider.location }} | RM{{ money(provider.base_rate) }}</td>
                  <td>{{ provider.kyc_doc_url || 'not uploaded' }}</td>
                  <td>
                    <span class="badge" :class="provider.is_verified == 1 ? 'success' : 'warning'">
                      {{ provider.is_verified == 1 ? 'Verified' : 'Pending' }}
                    </span>
                  </td>
                  <td>
                    <div v-if="Number(provider.is_verified) !== 1" class="row">
                      <button class="compact" @click="setVerified(provider, true)">Approve</button>
                      <button class="danger compact" @click="setVerified(provider, false)">Reject</button>
                    </div>
                    <span v-else class="badge success">No action needed</span>
                  </td>
                </tr>
                <tr v-if="!providers.length">
                  <td colspan="5" class="empty-cell">No provider profiles found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <section v-if="activeTab === 'categories'" class="admin-section">
          <div class="page-header compact-header">
            <div>
              <h2>Service Categories Management</h2>
              <p class="muted">Create, update, and deactivate active marketplace categories.</p>
            </div>
            <span class="badge">{{ categories.length }} active</span>
          </div>

          <div class="crud-layout">
            <div class="card">
              <h2>{{ editingCategoryId ? 'Edit Category' : 'Add New Category' }}</h2>
              <label>Category Name</label>
              <input v-model="categoryForm.name" placeholder="Aircon Servicing" />
              <label>Description</label>
              <textarea v-model="categoryForm.description" placeholder="Short category description"></textarea>
              <label>Icon Keyword</label>
              <input v-model="categoryForm.icon" placeholder="snowflake" />
              <div class="row form-actions">
                <button @click="saveCategory">{{ editingCategoryId ? 'Save Changes' : 'Save Category' }}</button>
                <button v-if="editingCategoryId" class="secondary" @click="resetCategoryForm">Cancel</button>
              </div>
            </div>

            <div class="card">
              <h2>Current Active Categories</h2>
              <div class="table-wrap">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Category Name</th>
                      <th>Description</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(category, index) in categories" :key="category.id">
                      <td>{{ index + 1 }}</td>
                      <td><strong>{{ category.name }}</strong></td>
                      <td>{{ category.description }}</td>
                      <td>
                        <div class="row">
                          <button class="secondary compact" @click="editCategory(category)">Edit</button>
                          <button class="danger compact" @click="deleteCategory(category)">Delete</button>
                        </div>
                      </td>
                    </tr>
                    <tr v-if="!categories.length">
                      <td colspan="4" class="empty-cell">No active categories.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </section>

        <section v-if="activeTab === 'safety'" class="admin-section grid cols-2">
          <div class="card">
            <h2>Safety Notes</h2>
            <ul>
              <li>Mock KYC document is required before verification.</li>
              <li>Providers can be unverified if suspicious.</li>
              <li>Admin can view all jobs for dispute handling.</li>
              <li>Job messages and timeline records help investigate disputes.</li>
            </ul>
          </div>

          <div class="card">
            <h2>Admin Rules</h2>
            <ul>
              <li>Approve only providers with a clear profile and KYC reference.</li>
              <li>Deactivate categories instead of deleting historical job data.</li>
              <li>Use job status and messages to resolve customer/provider issues.</li>
            </ul>
          </div>
        </section>
      </div>
    </div>
  </section>
</template>
