<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="mb-7">
      <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Resignations</h1>
      <p class="text-slate-500 mt-1 text-sm">Employee resignation workflow and exit records.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">{{ editingId ? "Edit Resignation" : "Submit Resignation" }}</h2>
            <button v-if="editingId" @click="resetForm" class="text-xs font-bold text-slate-400 hover:text-red-500 transition">Cancel</button>
          </div>
          <form @submit.prevent="save" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Employee</label>
              <select v-model="form.employee_id" class="field-input" :class="{ 'border-red-400': errors.employee_id }">
                <option value="" disabled>Select employee...</option>
                <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.full_name }} ({{ e.employee_number }})</option>
              </select>
              <p v-if="errors.employee_id" class="field-error">Select an employee</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label required">Resignation Date</label>
                <input v-model="form.resignation_date" type="date" class="field-input" :class="{ 'border-red-400': errors.resignation_date }" />
              </div>
              <div>
                <label class="field-label">Notice Date</label>
                <input v-model="form.notice_date" type="date" class="field-input" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label">Notice Period (days)</label>
                <input v-model="form.notice_period_days" type="number" min="0" class="field-input" />
              </div>
              <div>
                <label class="field-label">Expected Last Day</label>
                <input v-model="form.expected_last_working_day" type="date" class="field-input" />
              </div>
            </div>
            <div>
              <label class="field-label">Reason</label>
              <textarea v-model="form.reason" rows="2" class="field-input"></textarea>
            </div>
            <div>
              <label class="field-label">Employee Comments</label>
              <textarea v-model="form.employee_comments" rows="2" class="field-input"></textarea>
            </div>
            <div>
              <label class="field-label">Supporting Documents</label>
              <input type="file" multiple class="field-input" @change="onDocsChange" />
            </div>
            <button type="submit" :disabled="loading" class="cursor-pointer w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              {{ loading ? "Saving..." : editingId ? "Update" : "Submit Resignation" }}
            </button>
          </form>
        </div>
      </aside>

      <main class="lg:col-span-8">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB] flex flex-wrap gap-2">
            <button v-for="f in statusFilters" :key="f.value" @click="filterStatus = f.value; fetchResignations();" class="px-3.5 py-1.5 rounded-lg text-xs font-bold border transition" :class="filterStatus === f.value ? 'bg-[#229DE7] text-white border-[#229DE7]' : 'bg-white text-slate-500 border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7]'">
              {{ f.label }}
            </button>
          </div>
          <div v-if="tableLoading" class="p-6 space-y-3">
            <div v-for="n in 6" :key="n" class="h-12 bg-slate-100 animate-pulse rounded-xl"></div>
          </div>
          <div v-else-if="resignations.length === 0" class="py-20 text-center">
            <p class="font-bold text-slate-700">No resignations found</p>
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5">Employee</th>
                  <th class="px-5 py-3.5">Resignation Date</th>
                  <th class="px-5 py-3.5">Expected Last Day</th>
                  <th class="px-5 py-3.5">Status</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="r in resignations" :key="r.id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-4 text-sm font-bold text-slate-900">{{ r.employee?.full_name || r.employee?.first_name }}</td>
                  <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">{{ fmtDate(r.resignation_date) }}</td>
                  <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">{{ fmtDate(r.expected_last_working_day) }}</td>
                  <td class="px-5 py-4">
                    <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold border capitalize" :class="statusClass(r.status)">{{ r.status.replace("_", " ") }}</span>
                  </td>
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button v-if="!['completed', 'cancelled'].includes(r.status)" @click="openStatusModal(r)" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-[#EAF5FD] text-[#1682C4] hover:bg-[#229DE7] hover:text-white transition">
                        Update Status
                      </button>
                      <button @click="editResignation(r)" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                      </button>
                      <button @click="removeResignation(r)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition" title="Remove">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>

    <!-- STATUS MODAL -->
    <div v-if="statusModal.open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4" @click.self="statusModal.open = false">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-center justify-between">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Update Resignation Status</h2>
          <button @click="statusModal.open = false" class="text-slate-400 hover:text-red-500 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        <form @submit.prevent="submitStatus" class="p-6 space-y-4">
          <div>
            <label class="field-label required">New Status</label>
            <select v-model="statusForm.status" class="field-input">
              <option value="submitted">Submitted</option>
              <option value="under_review">Under Review</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <p v-if="statusForm.status === 'completed'" class="text-[10.5px] text-amber-600 mt-1.5">Marking this "Completed" sets the employee's status to Resigned.</p>
          </div>
          <div v-if="statusForm.status === 'completed'">
            <label class="field-label">Actual Last Working Day</label>
            <input v-model="statusForm.actual_last_working_day" type="date" class="field-input" />
          </div>
          <div>
            <label class="field-label">HR Comments</label>
            <textarea v-model="statusForm.hr_comments" rows="2" class="field-input"></textarea>
          </div>
          <div>
            <label class="field-label">Manager Comments</label>
            <textarea v-model="statusForm.manager_comments" rows="2" class="field-input"></textarea>
          </div>
          <button type="submit" :disabled="statusSaving" class="w-full py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
            {{ statusSaving ? "Saving..." : "Save Status" }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import Swal from "sweetalert2";
import ResignationService from "@/services/ResignationService";
import EmployeeProfileService from "@/services/EmployeeProfileService";

const tableLoading = ref(true);
const loading = ref(false);
const statusSaving = ref(false);
const editingId = ref(null);
const filterStatus = ref("");

const resignations = ref([]);
const employees = ref([]);

const form = reactive({
  employee_id: "",
  resignation_date: new Date().toISOString().slice(0, 10),
  notice_date: "",
  notice_period_days: "",
  expected_last_working_day: "",
  reason: "",
  employee_comments: "",
});
const docFiles = ref([]);
const errors = reactive({ employee_id: false, resignation_date: false });

const statusModal = reactive({ open: false, resignation: null });
const statusForm = reactive({ status: "submitted", actual_last_working_day: "", hr_comments: "", manager_comments: "" });

const statusFilters = [
  { label: "All", value: "" },
  { label: "Submitted", value: "submitted" },
  { label: "Under Review", value: "under_review" },
  { label: "Approved", value: "approved" },
  { label: "Completed", value: "completed" },
  { label: "Rejected", value: "rejected" },
  { label: "Cancelled", value: "cancelled" },
];

const STATUS_STYLES = {
  submitted: "bg-slate-100 text-slate-600 border-slate-200",
  under_review: "bg-amber-50 text-amber-700 border-amber-200",
  approved: "bg-sky-50 text-sky-700 border-sky-200",
  completed: "bg-emerald-50 text-emerald-700 border-emerald-200",
  rejected: "bg-red-50 text-red-600 border-red-200",
  cancelled: "bg-red-50 text-red-600 border-red-200",
};
const statusClass = (s) => STATUS_STYLES[s] || STATUS_STYLES.submitted;

const fmtDate = (d) => (d ? new Date(d).toLocaleDateString("en-KE", { day: "2-digit", month: "short", year: "numeric" }) : "—");
const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: "top-end", showConfirmButton: false, timer: 2200 });

const onDocsChange = (e) => (docFiles.value = Array.from(e.target.files));

const fetchResignations = async () => {
  tableLoading.value = true;
  try {
    const params = { per_page: 100 };
    if (filterStatus.value) params.status = filterStatus.value;
    const res = await ResignationService.getAll(params);
    resignations.value = res?.data ?? [];
  } finally {
    tableLoading.value = false;
  }
};

const validate = () => {
  errors.employee_id = !form.employee_id;
  errors.resignation_date = !form.resignation_date;
  return !errors.employee_id && !errors.resignation_date;
};

const save = async () => {
  if (!validate()) return;
  loading.value = true;
  try {
    const payload = { ...form };
    if (docFiles.value.length) payload.supporting_documents = docFiles.value;
    if (editingId.value) {
      await ResignationService.update(editingId.value, payload);
      toast("success", "Resignation Updated");
    } else {
      await ResignationService.create(payload);
      toast("success", "Resignation Submitted");
    }
    resetForm();
    await fetchResignations();
  } catch (err) {
    Swal.fire({ icon: "error", title: "Error", text: err?.response?.data?.message || "Something went wrong.", confirmButtonColor: "#229DE7" });
  } finally {
    loading.value = false;
  }
};

const editResignation = (r) => {
  editingId.value = r.id;
  form.employee_id = r.employee_id;
  form.resignation_date = r.resignation_date ? r.resignation_date.slice(0, 10) : "";
  form.notice_date = r.notice_date ? r.notice_date.slice(0, 10) : "";
  form.notice_period_days = r.notice_period_days || "";
  form.expected_last_working_day = r.expected_last_working_day ? r.expected_last_working_day.slice(0, 10) : "";
  form.reason = r.reason || "";
  form.employee_comments = r.employee_comments || "";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const removeResignation = async (r) => {
  const result = await Swal.fire({ title: "Remove this resignation record?", icon: "warning", showCancelButton: true, confirmButtonColor: "#229DE7", cancelButtonColor: "#f43f5e", confirmButtonText: "Yes, remove" });
  if (!result.isConfirmed) return;
  try {
    await ResignationService.remove(r.id);
    await fetchResignations();
    toast("success", "Resignation Removed");
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

const openStatusModal = (r) => {
  statusModal.resignation = r;
  statusModal.open = true;
  statusForm.status = r.status;
  statusForm.actual_last_working_day = "";
  statusForm.hr_comments = r.hr_comments || "";
  statusForm.manager_comments = r.manager_comments || "";
};

const submitStatus = async () => {
  statusSaving.value = true;
  try {
    await ResignationService.updateStatus(statusModal.resignation.id, { ...statusForm });
    statusModal.open = false;
    toast("success", "Status Updated");
    await fetchResignations();
  } catch (err) {
    Swal.fire({ icon: "error", title: "Error", text: err?.response?.data?.message || "Something went wrong.", confirmButtonColor: "#229DE7" });
  } finally {
    statusSaving.value = false;
  }
};

const resetForm = () => {
  editingId.value = null;
  docFiles.value = [];
  form.employee_id = "";
  form.resignation_date = new Date().toISOString().slice(0, 10);
  form.notice_date = "";
  form.notice_period_days = "";
  form.expected_last_working_day = "";
  form.reason = "";
  form.employee_comments = "";
  Object.keys(errors).forEach((k) => (errors[k] = false));
};

onMounted(async () => {
  const empRes = await EmployeeProfileService.getAll({ per_page: 500 });
  employees.value = empRes?.data ?? [];
  await fetchResignations();
});
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
.field-label.required::after { content: " *"; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color 0.15s, box-shadow 0.15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12); background: #fff; }
.field-error { font-size: 11px; font-weight: 600; color: #ef4444; margin-top: 4px; }
</style>
