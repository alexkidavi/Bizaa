<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Performance Reviews</h1>
        <p class="text-slate-500 mt-1 text-sm">Review cycles, ratings, and employee performance history.</p>
      </div>
      <div class="flex flex-wrap items-center gap-2">
        <button @click="openCyclesModal" class="px-4 py-2.5 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] transition">
          Manage Cycles
        </button>
        <button @click="openRatingsModal" class="px-4 py-2.5 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] transition">
          Manage Ratings
        </button>
        <router-link to="/hrm/performance/new" class="flex items-center gap-2 px-5 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98]">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
          New Review
        </router-link>
      </div>
    </div>

    <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-[#EEF5FB] flex flex-wrap items-center gap-3">
        <select v-model="filterCycle" class="text-sm bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl px-3 py-2 outline-none focus:border-[#229DE7]" @change="fetchReviews">
          <option value="">All Cycles</option>
          <option v-for="c in cycles" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
        <div class="flex flex-wrap items-center gap-2">
          <button v-for="f in statusFilters" :key="f.value" @click="filterStatus = f.value; fetchReviews();" class="px-3.5 py-1.5 rounded-lg text-xs font-bold border transition" :class="filterStatus === f.value ? 'bg-[#229DE7] text-white border-[#229DE7]' : 'bg-white text-slate-500 border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7]'">
            {{ f.label }}
          </button>
        </div>
      </div>

      <div v-if="tableLoading" class="p-6 space-y-3">
        <div v-for="n in 6" :key="n" class="h-12 bg-slate-100 animate-pulse rounded-xl"></div>
      </div>
      <div v-else-if="reviews.length === 0" class="py-20 text-center">
        <p class="font-bold text-slate-700">No performance reviews yet</p>
        <p class="text-sm text-slate-400 mt-1">Start a review cycle and record your first review.</p>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
              <th class="px-5 py-3.5">Employee</th>
              <th class="px-5 py-3.5">Cycle</th>
              <th class="px-5 py-3.5">Review Date</th>
              <th class="px-5 py-3.5">Reviewer</th>
              <th class="px-5 py-3.5 text-right">Score</th>
              <th class="px-5 py-3.5">Rating</th>
              <th class="px-5 py-3.5">Status</th>
              <th class="px-5 py-3.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#EEF5FB]">
            <tr v-for="r in reviews" :key="r.id" class="hover:bg-[#F5FAFF] transition-colors">
              <td class="px-5 py-4 text-sm font-bold text-slate-900">{{ r.employee?.full_name || r.employee?.first_name }}</td>
              <td class="px-5 py-4 text-xs text-slate-600">{{ r.performance_cycle?.name }}</td>
              <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">{{ fmtDate(r.review_date) }}</td>
              <td class="px-5 py-4 text-xs text-slate-500">{{ r.reviewer?.name || "—" }}</td>
              <td class="px-5 py-4 text-right text-xs font-bold text-slate-700">{{ r.overall_score ?? "—" }}</td>
              <td class="px-5 py-4 text-xs text-slate-600">{{ r.performance_rating?.name || "—" }}</td>
              <td class="px-5 py-4">
                <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold border capitalize" :class="statusClass(r.status)">{{ r.status }}</span>
              </td>
              <td class="px-5 py-4 text-right">
                <div class="flex justify-end gap-2">
                  <router-link :to="`/hrm/performance/${r.id}/edit`" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                  </router-link>
                  <button @click="removeReview(r)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition" title="Remove">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- CYCLES MODAL -->
    <div v-if="cyclesModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4" @click.self="cyclesModalOpen = false">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-center justify-between">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Review Cycles</h2>
          <button @click="cyclesModalOpen = false" class="text-slate-400 hover:text-red-500 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
          <form @submit.prevent="saveCycle" class="space-y-3 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl p-4">
            <div class="grid grid-cols-2 gap-3">
              <div class="col-span-2">
                <label class="field-label required">Name</label>
                <input v-model="cycleForm.name" type="text" class="field-input" placeholder="e.g. Q1 2026 Review" />
              </div>
              <div>
                <label class="field-label">Type</label>
                <select v-model="cycleForm.type" class="field-input">
                  <option value="monthly">Monthly</option>
                  <option value="quarterly">Quarterly</option>
                  <option value="biannual">Bi-Annual</option>
                  <option value="annual">Annual</option>
                  <option value="custom">Custom</option>
                </select>
              </div>
              <div>
                <label class="field-label">Status</label>
                <select v-model="cycleForm.status" class="field-input">
                  <option value="draft">Draft</option>
                  <option value="active">Active</option>
                  <option value="closed">Closed</option>
                </select>
              </div>
              <div>
                <label class="field-label required">Start Date</label>
                <input v-model="cycleForm.start_date" type="date" class="field-input" />
              </div>
              <div>
                <label class="field-label required">End Date</label>
                <input v-model="cycleForm.end_date" type="date" class="field-input" />
              </div>
            </div>
            <div class="flex gap-2">
              <button type="submit" class="flex-1 px-4 py-2 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl transition">{{ editingCycleId ? "Save" : "Add Cycle" }}</button>
              <button v-if="editingCycleId" type="button" @click="resetCycleForm" class="px-3 py-2 border border-[#D9EBF7] rounded-xl text-slate-500 text-sm font-bold">Cancel</button>
            </div>
          </form>
          <ul class="space-y-1.5">
            <li v-for="c in cycles" :key="c.id" class="flex items-center justify-between px-3 py-2 rounded-lg bg-white border border-[#D9EBF7]">
              <div>
                <span class="text-sm font-semibold text-slate-700">{{ c.name }}</span>
                <span class="text-[10.5px] text-slate-400 ml-2 capitalize">{{ c.type }} · {{ c.status }}</span>
              </div>
              <div class="flex gap-1">
                <button @click="editCycle(c)" class="text-[#229DE7] text-xs font-bold px-2">Edit</button>
                <button @click="removeCycle(c)" class="text-red-500 text-xs font-bold px-2">Delete</button>
              </div>
            </li>
            <li v-if="!cycles.length" class="text-sm text-slate-400 text-center py-4">No review cycles yet.</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- RATINGS MODAL -->
    <div v-if="ratingsModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4" @click.self="ratingsModalOpen = false">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-center justify-between">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Performance Ratings</h2>
          <button @click="ratingsModalOpen = false" class="text-slate-400 hover:text-red-500 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <form @submit.prevent="saveRating" class="space-y-3 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl p-4">
            <div class="grid grid-cols-3 gap-3">
              <div class="col-span-2">
                <label class="field-label required">Name</label>
                <input v-model="ratingForm.name" type="text" class="field-input" placeholder="e.g. Outstanding" />
              </div>
              <div>
                <label class="field-label">Score</label>
                <input v-model="ratingForm.score_value" type="number" step="0.1" min="0" class="field-input" />
              </div>
            </div>
            <div class="flex gap-2">
              <button type="submit" class="flex-1 px-4 py-2 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl transition">{{ editingRatingId ? "Save" : "Add Rating" }}</button>
              <button v-if="editingRatingId" type="button" @click="resetRatingForm" class="px-3 py-2 border border-[#D9EBF7] rounded-xl text-slate-500 text-sm font-bold">Cancel</button>
            </div>
          </form>
          <ul class="space-y-1.5 max-h-64 overflow-y-auto">
            <li v-for="r in ratings" :key="r.id" class="flex items-center justify-between px-3 py-2 rounded-lg bg-white border border-[#D9EBF7]">
              <span class="text-sm font-semibold text-slate-700">{{ r.name }} <span class="text-[10.5px] text-slate-400">({{ r.score_value ?? "—" }})</span></span>
              <div class="flex gap-1">
                <button @click="editRating(r)" class="text-[#229DE7] text-xs font-bold px-2">Edit</button>
                <button @click="removeRating(r)" class="text-red-500 text-xs font-bold px-2">Delete</button>
              </div>
            </li>
            <li v-if="!ratings.length" class="text-sm text-slate-400 text-center py-4">No ratings yet.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import Swal from "sweetalert2";
import PerformanceReviewService from "@/services/PerformanceReviewService";
import PerformanceCycleService from "@/services/PerformanceCycleService";
import PerformanceRatingService from "@/services/PerformanceRatingService";

const tableLoading = ref(true);
const reviews = ref([]);
const cycles = ref([]);
const ratings = ref([]);
const filterCycle = ref("");
const filterStatus = ref("");

const cyclesModalOpen = ref(false);
const ratingsModalOpen = ref(false);
const editingCycleId = ref(null);
const editingRatingId = ref(null);
const cycleForm = reactive({ name: "", type: "quarterly", start_date: "", end_date: "", status: "draft" });
const ratingForm = reactive({ name: "", score_value: "" });

const statusFilters = [
  { label: "All", value: "" },
  { label: "Draft", value: "draft" },
  { label: "Submitted", value: "submitted" },
  { label: "Acknowledged", value: "acknowledged" },
  { label: "Completed", value: "completed" },
];

const STATUS_STYLES = {
  draft: "bg-slate-100 text-slate-600 border-slate-200",
  submitted: "bg-amber-50 text-amber-700 border-amber-200",
  acknowledged: "bg-sky-50 text-sky-700 border-sky-200",
  completed: "bg-emerald-50 text-emerald-700 border-emerald-200",
};
const statusClass = (s) => STATUS_STYLES[s] || STATUS_STYLES.draft;

const fmtDate = (d) => (d ? new Date(d).toLocaleDateString("en-KE", { day: "2-digit", month: "short", year: "numeric" }) : "—");
const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: "top-end", showConfirmButton: false, timer: 2200 });

const fetchReviews = async () => {
  tableLoading.value = true;
  try {
    const params = { per_page: 100 };
    if (filterCycle.value) params.performance_cycle_id = filterCycle.value;
    if (filterStatus.value) params.status = filterStatus.value;
    const res = await PerformanceReviewService.getAll(params);
    reviews.value = res?.data ?? [];
  } finally {
    tableLoading.value = false;
  }
};

const fetchCycles = async () => {
  const res = await PerformanceCycleService.getAll();
  cycles.value = res?.data ?? [];
};

const fetchRatings = async () => {
  const res = await PerformanceRatingService.getAll();
  ratings.value = res?.data ?? [];
};

const removeReview = async (r) => {
  const result = await Swal.fire({ title: "Remove this review?", icon: "warning", showCancelButton: true, confirmButtonColor: "#229DE7", cancelButtonColor: "#f43f5e", confirmButtonText: "Yes, remove" });
  if (!result.isConfirmed) return;
  try {
    await PerformanceReviewService.remove(r.id);
    await fetchReviews();
    toast("success", "Review Removed");
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

const openCyclesModal = () => { cyclesModalOpen.value = true; resetCycleForm(); };
const openRatingsModal = () => { ratingsModalOpen.value = true; resetRatingForm(); };

const saveCycle = async () => {
  if (!cycleForm.name.trim() || !cycleForm.start_date || !cycleForm.end_date) return;
  try {
    if (editingCycleId.value) {
      await PerformanceCycleService.update(editingCycleId.value, { ...cycleForm });
    } else {
      await PerformanceCycleService.create({ ...cycleForm });
    }
    resetCycleForm();
    await fetchCycles();
  } catch (err) {
    Swal.fire({ icon: "error", title: "Error", text: err?.response?.data?.message || "Could not save cycle.", confirmButtonColor: "#229DE7" });
  }
};

const editCycle = (c) => {
  editingCycleId.value = c.id;
  cycleForm.name = c.name;
  cycleForm.type = c.type;
  cycleForm.start_date = c.start_date ? c.start_date.slice(0, 10) : "";
  cycleForm.end_date = c.end_date ? c.end_date.slice(0, 10) : "";
  cycleForm.status = c.status;
};

const removeCycle = async (c) => {
  const result = await Swal.fire({ title: `Delete "${c.name}"?`, icon: "warning", showCancelButton: true, confirmButtonColor: "#229DE7" });
  if (!result.isConfirmed) return;
  try {
    await PerformanceCycleService.remove(c.id);
    await fetchCycles();
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

const resetCycleForm = () => {
  editingCycleId.value = null;
  cycleForm.name = "";
  cycleForm.type = "quarterly";
  cycleForm.start_date = "";
  cycleForm.end_date = "";
  cycleForm.status = "draft";
};

const saveRating = async () => {
  if (!ratingForm.name.trim()) return;
  try {
    if (editingRatingId.value) {
      await PerformanceRatingService.update(editingRatingId.value, { ...ratingForm });
    } else {
      await PerformanceRatingService.create({ ...ratingForm });
    }
    resetRatingForm();
    await fetchRatings();
  } catch (err) {
    Swal.fire({ icon: "error", title: "Error", text: err?.response?.data?.message || "Could not save rating.", confirmButtonColor: "#229DE7" });
  }
};

const editRating = (r) => {
  editingRatingId.value = r.id;
  ratingForm.name = r.name;
  ratingForm.score_value = r.score_value ?? "";
};

const removeRating = async (r) => {
  const result = await Swal.fire({ title: `Delete "${r.name}"?`, icon: "warning", showCancelButton: true, confirmButtonColor: "#229DE7" });
  if (!result.isConfirmed) return;
  try {
    await PerformanceRatingService.remove(r.id);
    await fetchRatings();
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

const resetRatingForm = () => {
  editingRatingId.value = null;
  ratingForm.name = "";
  ratingForm.score_value = "";
};

onMounted(async () => {
  await Promise.all([fetchReviews(), fetchCycles(), fetchRatings()]);
});
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
.field-label.required::after { content: " *"; color: #ef4444; }
.field-input { width: 100%; padding: 8px 12px; background: #fff; border: 1.5px solid #d9ebf7; border-radius: 10px; font-size: 13px; color: #1e293b; outline: none; transition: border-color 0.15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; }
</style>
