<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex items-center gap-3 mb-7">
      <router-link to="/hrm/performance" class="w-9 h-9 rounded-xl flex items-center justify-center bg-white border border-[#D9EBF7] text-slate-500 hover:text-[#229DE7] hover:border-[#229DE7] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
      </router-link>
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ isEdit ? "Edit Performance Review" : "New Performance Review" }}</h1>
        <p class="text-slate-500 mt-1 text-sm">Goals, assessment, and rating for this review cycle.</p>
      </div>
    </div>

    <div v-if="loading" class="max-w-4xl space-y-4">
      <div v-for="n in 3" :key="n" class="h-40 bg-slate-100 animate-pulse rounded-2xl"></div>
    </div>

    <form v-else @submit.prevent="save" class="max-w-4xl space-y-6">
      <section class="card">
        <h2 class="card-title">Review Details</h2>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="field-label required">Employee</label>
            <select v-model="form.employee_id" class="field-input" :class="{ 'border-red-400': errors.employee_id }">
              <option value="" disabled>Select employee...</option>
              <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.full_name }} ({{ e.employee_number }})</option>
            </select>
            <p v-if="errors.employee_id" class="field-error">Select an employee</p>
          </div>
          <div>
            <label class="field-label required">Review Cycle</label>
            <select v-model="form.performance_cycle_id" class="field-input" :class="{ 'border-red-400': errors.performance_cycle_id }">
              <option value="" disabled>Select cycle...</option>
              <option v-for="c in cycles" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <p v-if="errors.performance_cycle_id" class="field-error">Select a review cycle</p>
          </div>
          <div>
            <label class="field-label required">Review Date</label>
            <input v-model="form.review_date" type="date" class="field-input" :class="{ 'border-red-400': errors.review_date }" />
          </div>
          <div>
            <label class="field-label">Status</label>
            <select v-model="form.status" class="field-input">
              <option value="draft">Draft</option>
              <option value="submitted">Submitted</option>
              <option value="acknowledged">Acknowledged</option>
              <option value="completed">Completed</option>
            </select>
          </div>
        </div>
      </section>

      <section class="card">
        <h2 class="card-title">Goals &amp; KPIs</h2>
        <div class="card-body space-y-4">
          <div>
            <label class="field-label">Goals</label>
            <textarea v-model="form.goals" rows="3" class="field-input"></textarea>
          </div>
          <div>
            <label class="field-label">KPIs</label>
            <textarea v-model="form.kpis" rows="3" class="field-input"></textarea>
          </div>
          <div>
            <label class="field-label">Competencies</label>
            <textarea v-model="form.competencies" rows="3" class="field-input"></textarea>
          </div>
        </div>
      </section>

      <section class="card">
        <h2 class="card-title">Assessment</h2>
        <div class="card-body space-y-4">
          <div>
            <label class="field-label">Self-Assessment</label>
            <textarea v-model="form.self_assessment" rows="3" class="field-input"></textarea>
          </div>
          <div>
            <label class="field-label">Manager Assessment</label>
            <textarea v-model="form.manager_assessment" rows="3" class="field-input"></textarea>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="field-label">Overall Score</label>
              <input v-model="form.overall_score" type="number" step="0.1" min="0" max="99.9" class="field-input" />
            </div>
            <div>
              <label class="field-label">Rating</label>
              <select v-model="form.performance_rating_id" class="field-input">
                <option value="">Select rating...</option>
                <option v-for="r in ratings" :key="r.id" :value="r.id">{{ r.name }}</option>
              </select>
            </div>
          </div>
        </div>
      </section>

      <section class="card">
        <h2 class="card-title">Outcomes</h2>
        <div class="card-body space-y-4">
          <div>
            <label class="field-label">Strengths</label>
            <textarea v-model="form.strengths" rows="2" class="field-input"></textarea>
          </div>
          <div>
            <label class="field-label">Areas for Improvement</label>
            <textarea v-model="form.areas_for_improvement" rows="2" class="field-input"></textarea>
          </div>
          <div>
            <label class="field-label">Development Plan</label>
            <textarea v-model="form.development_plan" rows="2" class="field-input"></textarea>
          </div>
          <div>
            <label class="field-label">Comments</label>
            <textarea v-model="form.comments" rows="2" class="field-input"></textarea>
          </div>
        </div>
      </section>

      <div class="flex items-center justify-end gap-3">
        <router-link to="/hrm/performance" class="px-5 py-2.5 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-500 hover:border-red-300 hover:text-red-500 hover:bg-red-50 transition">Cancel</router-link>
        <button type="submit" :disabled="saving" class="flex items-center justify-center gap-2 px-8 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] disabled:opacity-60">
          {{ saving ? "Saving..." : isEdit ? "Update Review" : "Create Review" }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import Swal from "sweetalert2";
import PerformanceReviewService from "@/services/PerformanceReviewService";
import PerformanceCycleService from "@/services/PerformanceCycleService";
import PerformanceRatingService from "@/services/PerformanceRatingService";
import EmployeeProfileService from "@/services/EmployeeProfileService";

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const reviewId = computed(() => route.params.id);

const loading = ref(true);
const saving = ref(false);
const employees = ref([]);
const cycles = ref([]);
const ratings = ref([]);

const form = reactive({
  employee_id: "",
  performance_cycle_id: "",
  review_date: new Date().toISOString().slice(0, 10),
  status: "draft",
  goals: "",
  kpis: "",
  competencies: "",
  self_assessment: "",
  manager_assessment: "",
  overall_score: "",
  performance_rating_id: "",
  strengths: "",
  areas_for_improvement: "",
  development_plan: "",
  comments: "",
});

const errors = reactive({ employee_id: false, performance_cycle_id: false, review_date: false });

const validate = () => {
  errors.employee_id = !form.employee_id;
  errors.performance_cycle_id = !form.performance_cycle_id;
  errors.review_date = !form.review_date;
  return !errors.employee_id && !errors.performance_cycle_id && !errors.review_date;
};

const save = async () => {
  if (!validate()) return;
  saving.value = true;
  try {
    if (isEdit.value) {
      await PerformanceReviewService.update(reviewId.value, { ...form });
    } else {
      await PerformanceReviewService.create({ ...form });
    }
    Swal.fire({ icon: "success", title: isEdit.value ? "Review updated" : "Review created", confirmButtonColor: "#229DE7" });
    router.push("/hrm/performance");
  } catch (err) {
    const res = err?.response;
    if (res?.status === 422 && res.data?.errors) {
      Swal.fire({ icon: "error", title: "Validation Error", text: Object.values(res.data.errors)[0]?.[0] || "Please check the form.", confirmButtonColor: "#229DE7" });
      return;
    }
    Swal.fire({ icon: "error", title: "Error", text: res?.data?.message || "Something went wrong.", confirmButtonColor: "#229DE7" });
  } finally {
    saving.value = false;
  }
};

const populateForm = (r) => {
  Object.keys(form).forEach((key) => {
    if (key in r && r[key] !== null) form[key] = r[key];
  });
  form.review_date = r.review_date ? r.review_date.slice(0, 10) : "";
  form.performance_rating_id = r.performance_rating_id ?? "";
};

onMounted(async () => {
  try {
    const [empRes, cycleRes, ratingRes] = await Promise.all([
      EmployeeProfileService.getAll({ per_page: 500 }),
      PerformanceCycleService.getAll(),
      PerformanceRatingService.getAll({ active_only: 1 }),
    ]);
    employees.value = empRes?.data ?? [];
    cycles.value = cycleRes?.data ?? [];
    ratings.value = ratingRes?.data ?? [];

    if (isEdit.value) {
      const res = await PerformanceReviewService.getById(reviewId.value);
      populateForm(res?.data ?? {});
    } else if (route.query.employee_id) {
      form.employee_id = Number(route.query.employee_id);
    }
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.card { background: #fff; border: 1px solid #d9ebf7; border-radius: 16px; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03); overflow: hidden; }
.card-title { padding: 16px 24px; font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em; color: #1682c4; border-bottom: 1px solid #eef5fb; }
.card-body { padding: 20px 24px; }
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
.field-label.required::after { content: " *"; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color 0.15s, box-shadow 0.15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12); background: #fff; }
.field-error { font-size: 11px; font-weight: 600; color: #ef4444; margin-top: 4px; }
</style>
