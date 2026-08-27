<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex items-center gap-3 mb-7">
      <router-link to="/hrm/employees" class="w-9 h-9 rounded-xl flex items-center justify-center bg-white border border-[#D9EBF7] text-slate-500 hover:text-[#229DE7] hover:border-[#229DE7] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
      </router-link>
      <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Staff Profile</h1>
    </div>

    <div v-if="loading" class="max-w-4xl space-y-4">
      <div class="h-40 bg-slate-100 animate-pulse rounded-2xl"></div>
      <div class="h-64 bg-slate-100 animate-pulse rounded-2xl"></div>
    </div>

    <div v-else-if="employee" class="max-w-4xl space-y-6">
      <!-- HERO -->
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center gap-5">
          <img v-if="employee.photo" :src="photoUrl(employee.photo)" class="w-20 h-20 rounded-2xl object-cover flex-shrink-0" />
          <div v-else class="w-20 h-20 rounded-2xl bg-[#EAF5FD] text-[#1682C4] flex items-center justify-center font-extrabold text-2xl flex-shrink-0">
            {{ initials(employee.full_name) }}
          </div>
          <div class="flex-1">
            <div class="flex flex-wrap items-center gap-3">
              <h2 class="text-xl font-extrabold text-slate-900">{{ employee.full_name }}</h2>
              <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold border capitalize" :class="statusBadgeClass(employee.employment_status)">
                {{ (employee.employment_status || "").replace("_", " ") }}
              </span>
            </div>
            <p class="text-sm text-slate-500 mt-1">{{ employee.designation || "—" }} · {{ employee.department?.name || "Unassigned" }} · {{ employee.employee_number }}</p>
          </div>
          <div class="flex items-center gap-2">
            <select v-model="statusChange" class="text-sm bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl px-3 py-2 outline-none focus:border-[#229DE7]">
              <option value="" disabled>Change status...</option>
              <option v-for="s in statusOptions" :key="s" :value="s">{{ s.replace("_", " ") }}</option>
            </select>
            <button @click="applyStatusChange" :disabled="!statusChange" class="px-4 py-2 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl disabled:opacity-40 transition">Apply</button>
            <router-link :to="`/hrm/employees/${employee.id}/edit`" class="w-9 h-9 rounded-xl flex items-center justify-center bg-white border border-[#D9EBF7] text-[#229DE7] hover:border-[#229DE7] transition" title="Edit">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
              </svg>
            </router-link>
          </div>
        </div>
      </div>

      <!-- TABS -->
      <div class="flex gap-2">
        <button v-for="t in tabs" :key="t.key" @click="activeTab = t.key" class="px-4 py-2 rounded-xl text-sm font-bold border transition" :class="activeTab === t.key ? 'bg-[#229DE7] text-white border-[#229DE7] shadow-sm' : 'bg-white text-slate-500 border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7]'">
          {{ t.label }}
        </button>
      </div>

      <!-- OVERVIEW -->
      <div v-if="activeTab === 'overview'" class="space-y-6">
        <div class="card">
          <h3 class="card-title">Login &amp; Access</h3>
          <div class="card-body grid grid-cols-2 sm:grid-cols-3 gap-5">
            <Field label="Email" :value="employee.user?.email" />
            <Field label="Phone" :value="employee.user?.phone" />
            <Field label="Role" :value="employee.user?.role?.name" />
          </div>
        </div>
        <div class="card">
          <h3 class="card-title">Personal</h3>
          <div class="card-body grid grid-cols-2 sm:grid-cols-3 gap-5">
            <Field label="Gender" :value="employee.gender" capitalize />
            <Field label="Date of Birth" :value="fmtDate(employee.date_of_birth)" />
            <Field label="National ID" :value="employee.national_id" />
            <Field label="Address" :value="employee.address" class="col-span-2 sm:col-span-3" />
          </div>
        </div>
        <div class="card">
          <h3 class="card-title">Employment</h3>
          <div class="card-body grid grid-cols-2 sm:grid-cols-3 gap-5">
            <Field label="Employment Date" :value="fmtDate(employee.employment_date)" />
            <Field label="Type" :value="employee.employment_type" capitalize />
            <Field label="Branch" :value="employee.branch?.name" />
            <Field label="Manager" :value="employee.manager?.full_name" />
            <Field label="Basic Salary" :value="fmt(employee.basic_salary)" />
          </div>
        </div>
        <div class="card">
          <h3 class="card-title">Bank &amp; Statutory</h3>
          <div class="card-body grid grid-cols-2 sm:grid-cols-3 gap-5">
            <Field label="Bank" :value="employee.bank_name" />
            <Field label="Account No." :value="employee.bank_account_number" />
            <Field label="Bank Branch" :value="employee.bank_branch" />
            <Field label="KRA PIN" :value="employee.kra_pin" />
            <Field label="NSSF No." :value="employee.nssf_number" />
            <Field label="SHIF No." :value="employee.shif_number" />
          </div>
        </div>
        <div class="card">
          <h3 class="card-title">Emergency Contact</h3>
          <div class="card-body grid grid-cols-2 sm:grid-cols-3 gap-5">
            <Field label="Name" :value="employee.emergency_contact_name" />
            <Field label="Phone" :value="employee.emergency_contact_phone" />
            <Field label="Relationship" :value="employee.emergency_contact_relationship" />
          </div>
        </div>
        <div v-if="employee.documents?.length" class="card">
          <h3 class="card-title">Documents</h3>
          <div class="card-body space-y-2">
            <button v-for="doc in employee.documents" :key="doc.path" @click="download(doc)" class="w-full text-left text-sm text-slate-600 hover:text-[#229DE7] bg-[#F5FAFF] border border-[#D9EBF7] rounded-lg px-3 py-2 transition">
              {{ doc.name }}
            </button>
          </div>
        </div>
        <div v-if="employee.notes" class="card">
          <h3 class="card-title">Notes</h3>
          <div class="card-body text-sm text-slate-600">{{ employee.notes }}</div>
        </div>
      </div>

      <!-- PERFORMANCE -->
      <div v-else-if="activeTab === 'performance'" class="card">
        <h3 class="card-title flex items-center justify-between">
          Performance History
          <router-link :to="`/hrm/performance/new?employee_id=${employee.id}`" class="text-[10.5px] font-bold text-[#229DE7] hover:underline normal-case tracking-normal">+ New Review</router-link>
        </h3>
        <div class="card-body">
          <div v-if="!performanceReviews.length" class="text-center py-10 text-slate-400 text-sm">No performance reviews yet.</div>
          <div v-else class="space-y-3">
            <router-link
              v-for="r in performanceReviews"
              :key="r.id"
              :to="`/hrm/performance/${r.id}/edit`"
              class="flex items-center justify-between p-3 rounded-xl border border-[#D9EBF7] hover:border-[#229DE7] hover:bg-[#F5FAFF] transition"
            >
              <div>
                <p class="text-sm font-bold text-slate-900">{{ r.performance_cycle?.name }}</p>
                <p class="text-[10.5px] text-slate-400">{{ fmtDate(r.review_date) }} · {{ r.performance_rating?.name || "Not rated" }}</p>
              </div>
              <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold border capitalize" :class="reviewStatusClass(r.status)">{{ r.status }}</span>
            </router-link>
          </div>
        </div>
      </div>

      <!-- HISTORY -->
      <div v-else-if="activeTab === 'history'" class="card">
        <h3 class="card-title">Change History</h3>
        <div class="card-body">
          <div v-if="!history.length" class="text-center py-10 text-slate-400 text-sm">No changes recorded yet.</div>
          <ol v-else class="relative border-l-2 border-[#EAF5FD] ml-2 space-y-6">
            <li v-for="entry in history" :key="entry.id" class="ml-5">
              <span class="absolute -left-[7px] w-3 h-3 rounded-full bg-[#229DE7] border-2 border-white"></span>
              <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs font-extrabold text-slate-900 capitalize">{{ entry.action.replace("_", " ") }}</span>
                <span class="text-[10.5px] text-slate-400">by {{ entry.user?.name || "System" }} · {{ fmtDateTime(entry.created_at) }}</span>
              </div>
              <p v-if="entry.reason" class="text-xs text-slate-500 mt-1 italic">"{{ entry.reason }}"</p>
              <ul v-if="fieldChanges(entry).length" class="mt-2 space-y-1">
                <li v-for="c in fieldChanges(entry)" :key="c.field" class="text-xs text-slate-500">
                  <span class="font-semibold capitalize">{{ c.field }}:</span>
                  {{ c.from ?? "—" }} → {{ c.to ?? "—" }}
                </li>
              </ul>
            </li>
          </ol>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, h } from "vue";
import { useRoute } from "vue-router";
import Swal from "sweetalert2";
import EmployeeProfileService from "@/services/EmployeeProfileService";
import PerformanceReviewService from "@/services/PerformanceReviewService";

const Field = (props, { slots }) =>
  h("div", { class: props.class }, [
    h("p", { class: "text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-1" }, props.label),
    h("p", { class: "text-sm font-semibold text-slate-800" + (props.capitalize ? " capitalize" : "") }, props.value || "—"),
  ]);

const route = useRoute();
const loading = ref(true);
const employee = ref(null);
const history = ref([]);
const performanceReviews = ref([]);
const activeTab = ref("overview");
const statusChange = ref("");

const BASE_URL = import.meta.env.VITE_APP_URL ?? "";
const photoUrl = (path) => `${BASE_URL}/storage/${path}`;

const tabs = [
  { key: "overview", label: "Overview" },
  { key: "performance", label: "Performance" },
  { key: "history", label: "History" },
];

const REVIEW_STATUS_STYLES = {
  draft: "bg-slate-100 text-slate-600 border-slate-200",
  submitted: "bg-amber-50 text-amber-700 border-amber-200",
  acknowledged: "bg-sky-50 text-sky-700 border-sky-200",
  completed: "bg-emerald-50 text-emerald-700 border-emerald-200",
};
const reviewStatusClass = (s) => REVIEW_STATUS_STYLES[s] || REVIEW_STATUS_STYLES.draft;

const statusOptions = ["active", "on_leave", "suspended", "resigned", "terminated", "retired"];

const STATUS_STYLES = {
  active: "bg-emerald-50 text-emerald-700 border-emerald-200",
  on_leave: "bg-amber-50 text-amber-700 border-amber-200",
  suspended: "bg-orange-50 text-orange-700 border-orange-200",
  resigned: "bg-slate-100 text-slate-500 border-slate-200",
  terminated: "bg-red-50 text-red-600 border-red-200",
  retired: "bg-violet-50 text-violet-700 border-violet-200",
};
const statusBadgeClass = (s) => STATUS_STYLES[s] || "bg-slate-100 text-slate-500 border-slate-200";

const initials = (name) => (name || "?").split(" ").slice(0, 2).map((w) => w[0]).join("").toUpperCase();
const fmt = (v) => `KES ${Number(v || 0).toLocaleString("en-KE", { minimumFractionDigits: 2 })}`;
const fmtDate = (d) => (d ? new Date(d).toLocaleDateString("en-KE", { day: "2-digit", month: "short", year: "numeric" }) : "—");
const fmtDateTime = (d) => (d ? new Date(d).toLocaleString("en-KE", { day: "2-digit", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit" }) : "—");

// Only "updated"/"status_changed" logs carry [old, new] pairs worth
// rendering — "created" logs the full initial field set as plain values,
// which would otherwise dump ~30 mostly-empty rows into the timeline.
const fieldChanges = (entry) => {
  if (entry.action === "created") return [];
  return Object.entries(entry.changes || {})
    .filter(([, pair]) => Array.isArray(pair))
    .map(([field, pair]) => ({ field: field.replace(/_/g, " "), from: pair[0], to: pair[1] }));
};

const download = async (doc) => {
  const blob = await EmployeeProfileService.downloadDocument(employee.value.id, doc.path);
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = doc.name;
  a.click();
  URL.revokeObjectURL(url);
};

const applyStatusChange = async () => {
  if (!statusChange.value) return;
  const { value: reason, isConfirmed } = await Swal.fire({
    title: `Change status to "${statusChange.value.replace("_", " ")}"?`,
    input: "text",
    inputLabel: "Reason (optional)",
    inputPlaceholder: "e.g. Approved resignation",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
  });
  if (!isConfirmed) return;
  try {
    const res = await EmployeeProfileService.updateStatus(employee.value.id, statusChange.value, reason || undefined);
    employee.value = res?.data ?? employee.value;
    statusChange.value = "";
    await loadHistory();
    Swal.fire({ icon: "success", title: "Status updated", toast: true, position: "top-end", showConfirmButton: false, timer: 2000 });
  } catch (err) {
    Swal.fire({ icon: "error", title: "Error", text: err?.response?.data?.message || "Something went wrong.", confirmButtonColor: "#229DE7" });
  }
};

const loadHistory = async () => {
  const res = await EmployeeProfileService.getHistory(route.params.id);
  history.value = res?.data ?? [];
};

const loadPerformanceReviews = async () => {
  const res = await PerformanceReviewService.getAll({ employee_id: route.params.id, per_page: 100 });
  performanceReviews.value = res?.data ?? [];
};

onMounted(async () => {
  try {
    const res = await EmployeeProfileService.getById(route.params.id);
    employee.value = res?.data ?? null;
    await Promise.all([loadHistory(), loadPerformanceReviews()]);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.card {
  background: #fff;
  border: 1px solid #d9ebf7;
  border-radius: 16px;
  overflow: hidden;
}
.card-title {
  padding: 14px 24px;
  font-weight: 800;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #1682c4;
  border-bottom: 1px solid #eef5fb;
}
.card-body {
  padding: 20px 24px;
}
</style>
