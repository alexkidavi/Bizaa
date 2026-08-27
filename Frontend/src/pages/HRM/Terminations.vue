<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Terminations</h1>
        <p class="text-slate-500 mt-1 text-sm">Employee termination records and approvals.</p>
      </div>
      <button @click="openTypesModal" class="px-4 py-2.5 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] transition">
        Manage Termination Types
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">{{ editingId ? "Edit Termination" : "Record Termination" }}</h2>
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
            <div>
              <label class="field-label required">Termination Type</label>
              <select v-model="form.termination_type_id" class="field-input" :class="{ 'border-red-400': errors.termination_type_id }">
                <option value="" disabled>Select type...</option>
                <option v-for="t in terminationTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
              </select>
              <p v-if="errors.termination_type_id" class="field-error">Select a termination type</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label required">Termination Date</label>
                <input v-model="form.termination_date" type="date" class="field-input" :class="{ 'border-red-400': errors.termination_date }" />
              </div>
              <div>
                <label class="field-label required">Effective Date</label>
                <input v-model="form.effective_date" type="date" class="field-input" :class="{ 'border-red-400': errors.effective_date }" />
              </div>
            </div>
            <div>
              <label class="field-label required">Reason</label>
              <input v-model="form.reason" type="text" class="field-input" :class="{ 'border-red-400': errors.reason }" placeholder="e.g. Position made redundant" />
            </div>
            <div>
              <label class="field-label">Detailed Explanation</label>
              <textarea v-model="form.detailed_explanation" rows="2" class="field-input"></textarea>
            </div>
            <div class="flex items-center gap-2">
              <input id="notice_given" v-model="form.notice_given" type="checkbox" class="w-4 h-4 rounded border-[#D9EBF7] text-[#229DE7]" />
              <label for="notice_given" class="text-sm font-semibold text-slate-600">Notice was given</label>
            </div>
            <div v-if="form.notice_given">
              <label class="field-label">Notice Period (days)</label>
              <input v-model="form.notice_period_days" type="number" min="0" class="field-input" />
            </div>
            <div>
              <label class="field-label">Supporting Documents</label>
              <input type="file" multiple class="field-input" @change="onDocsChange" />
            </div>
            <button type="submit" :disabled="loading" class="cursor-pointer w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              {{ loading ? "Saving..." : editingId ? "Update" : "Record Termination" }}
            </button>
          </form>
        </div>
      </aside>

      <main class="lg:col-span-8">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB] flex flex-wrap gap-2">
            <button v-for="f in statusFilters" :key="f.value" @click="filterStatus = f.value; fetchTerminations();" class="px-3.5 py-1.5 rounded-lg text-xs font-bold border transition" :class="filterStatus === f.value ? 'bg-[#229DE7] text-white border-[#229DE7]' : 'bg-white text-slate-500 border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7]'">
              {{ f.label }}
            </button>
          </div>
          <div v-if="tableLoading" class="p-6 space-y-3">
            <div v-for="n in 6" :key="n" class="h-12 bg-slate-100 animate-pulse rounded-xl"></div>
          </div>
          <div v-else-if="terminations.length === 0" class="py-20 text-center">
            <p class="font-bold text-slate-700">No terminations recorded</p>
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5">Employee</th>
                  <th class="px-5 py-3.5">Type</th>
                  <th class="px-5 py-3.5">Effective Date</th>
                  <th class="px-5 py-3.5 text-right">Settlement</th>
                  <th class="px-5 py-3.5">Status</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="t in terminations" :key="t.id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-4 text-sm font-bold text-slate-900">{{ t.employee?.full_name || t.employee?.first_name }}</td>
                  <td class="px-5 py-4 text-xs text-slate-600">{{ t.termination_type?.name }}</td>
                  <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">{{ fmtDate(t.effective_date) }}</td>
                  <td class="px-5 py-4 text-right text-xs font-bold text-slate-700">{{ t.final_settlement_amount ? fmt(t.final_settlement_amount) : "—" }}</td>
                  <td class="px-5 py-4">
                    <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold border capitalize" :class="statusClass(t.status)">{{ t.status }}</span>
                  </td>
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button v-if="!['completed', 'cancelled'].includes(t.status)" @click="openStatusModal(t)" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-[#EAF5FD] text-[#1682C4] hover:bg-[#229DE7] hover:text-white transition">
                        Update Status
                      </button>
                      <button @click="editTermination(t)" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                      </button>
                      <button @click="removeTermination(t)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition" title="Remove">
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

    <!-- TYPES MODAL -->
    <div v-if="typesModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4" @click.self="typesModalOpen = false">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-center justify-between">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Termination Types</h2>
          <button @click="typesModalOpen = false" class="text-slate-400 hover:text-red-500 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <form @submit.prevent="saveType" class="flex gap-2">
            <input v-model="typeForm.name" type="text" class="field-input flex-1" :placeholder="editingTypeId ? 'Update type name' : 'e.g. Redundancy'" />
            <button type="submit" class="px-4 py-2 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl transition">{{ editingTypeId ? "Save" : "Add" }}</button>
            <button v-if="editingTypeId" type="button" @click="editingTypeId = null; typeForm.name = '';" class="px-3 py-2 border border-[#D9EBF7] rounded-xl text-slate-500 text-sm font-bold">×</button>
          </form>
          <ul class="space-y-1.5 max-h-64 overflow-y-auto">
            <li v-for="t in terminationTypes" :key="t.id" class="flex items-center justify-between px-3 py-2 rounded-lg bg-[#F5FAFF] border border-[#D9EBF7]">
              <span class="text-sm font-semibold text-slate-700">{{ t.name }}</span>
              <div class="flex gap-1">
                <button @click="editingTypeId = t.id; typeForm.name = t.name;" class="text-[#229DE7] text-xs font-bold px-2">Edit</button>
                <button @click="removeType(t)" class="text-red-500 text-xs font-bold px-2">Delete</button>
              </div>
            </li>
            <li v-if="!terminationTypes.length" class="text-sm text-slate-400 text-center py-4">No termination types yet.</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- STATUS MODAL -->
    <div v-if="statusModal.open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4" @click.self="statusModal.open = false">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-center justify-between">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Update Termination Status</h2>
          <button @click="statusModal.open = false" class="text-slate-400 hover:text-red-500 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        <form @submit.prevent="submitStatus" class="p-6 space-y-4">
          <div>
            <label class="field-label required">New Status</label>
            <select v-model="statusForm.status" class="field-input">
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <p v-if="statusForm.status === 'completed'" class="text-[10.5px] text-amber-600 mt-1.5">Marking this "Completed" sets the employee's status to Terminated.</p>
          </div>
          <div v-if="statusForm.status === 'completed'" class="grid grid-cols-2 gap-3">
            <div>
              <label class="field-label">Final Settlement (KES)</label>
              <input v-model="statusForm.final_settlement_amount" type="number" min="0" step="0.01" class="field-input" />
            </div>
            <div>
              <label class="field-label">Settlement Notes</label>
              <input v-model="statusForm.final_settlement_notes" type="text" class="field-input" />
            </div>
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
import TerminationService from "@/services/TerminationService";
import TerminationTypeService from "@/services/TerminationTypeService";
import EmployeeProfileService from "@/services/EmployeeProfileService";

const tableLoading = ref(true);
const loading = ref(false);
const statusSaving = ref(false);
const editingId = ref(null);
const typesModalOpen = ref(false);
const editingTypeId = ref(null);
const filterStatus = ref("");

const terminations = ref([]);
const terminationTypes = ref([]);
const employees = ref([]);

const form = reactive({
  employee_id: "",
  termination_type_id: "",
  termination_date: new Date().toISOString().slice(0, 10),
  effective_date: "",
  reason: "",
  detailed_explanation: "",
  notice_given: false,
  notice_period_days: "",
});
const docFiles = ref([]);
const errors = reactive({ employee_id: false, termination_type_id: false, termination_date: false, effective_date: false, reason: false });
const typeForm = reactive({ name: "" });

const statusModal = reactive({ open: false, termination: null });
const statusForm = reactive({ status: "pending", final_settlement_amount: "", final_settlement_notes: "", hr_comments: "", manager_comments: "" });

const statusFilters = [
  { label: "All", value: "" },
  { label: "Pending", value: "pending" },
  { label: "Approved", value: "approved" },
  { label: "Completed", value: "completed" },
  { label: "Cancelled", value: "cancelled" },
];

const STATUS_STYLES = {
  pending: "bg-slate-100 text-slate-600 border-slate-200",
  approved: "bg-sky-50 text-sky-700 border-sky-200",
  completed: "bg-emerald-50 text-emerald-700 border-emerald-200",
  cancelled: "bg-red-50 text-red-600 border-red-200",
};
const statusClass = (s) => STATUS_STYLES[s] || STATUS_STYLES.pending;

const fmt = (v) => `KES ${Number(v || 0).toLocaleString("en-KE", { minimumFractionDigits: 2 })}`;
const fmtDate = (d) => (d ? new Date(d).toLocaleDateString("en-KE", { day: "2-digit", month: "short", year: "numeric" }) : "—");
const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: "top-end", showConfirmButton: false, timer: 2200 });

const onDocsChange = (e) => (docFiles.value = Array.from(e.target.files));

const fetchTerminations = async () => {
  tableLoading.value = true;
  try {
    const params = { per_page: 100 };
    if (filterStatus.value) params.status = filterStatus.value;
    const res = await TerminationService.getAll(params);
    terminations.value = res?.data ?? [];
  } finally {
    tableLoading.value = false;
  }
};

const fetchTerminationTypes = async () => {
  const res = await TerminationTypeService.getAll();
  terminationTypes.value = res?.data ?? [];
};

const openTypesModal = () => {
  typesModalOpen.value = true;
  editingTypeId.value = null;
  typeForm.name = "";
};

const saveType = async () => {
  if (!typeForm.name.trim()) return;
  try {
    if (editingTypeId.value) {
      await TerminationTypeService.update(editingTypeId.value, { name: typeForm.name });
    } else {
      await TerminationTypeService.create({ name: typeForm.name });
    }
    editingTypeId.value = null;
    typeForm.name = "";
    await fetchTerminationTypes();
  } catch (err) {
    Swal.fire({ icon: "error", title: "Error", text: err?.response?.data?.message || "Could not save type.", confirmButtonColor: "#229DE7" });
  }
};

const removeType = async (t) => {
  const result = await Swal.fire({ title: `Delete "${t.name}"?`, icon: "warning", showCancelButton: true, confirmButtonColor: "#229DE7" });
  if (!result.isConfirmed) return;
  try {
    await TerminationTypeService.remove(t.id);
    await fetchTerminationTypes();
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

const validate = () => {
  errors.employee_id = !form.employee_id;
  errors.termination_type_id = !form.termination_type_id;
  errors.termination_date = !form.termination_date;
  errors.effective_date = !form.effective_date;
  errors.reason = !form.reason.trim();
  return !errors.employee_id && !errors.termination_type_id && !errors.termination_date && !errors.effective_date && !errors.reason;
};

const save = async () => {
  if (!validate()) return;
  loading.value = true;
  try {
    const payload = { ...form };
    if (docFiles.value.length) payload.supporting_documents = docFiles.value;
    if (editingId.value) {
      await TerminationService.update(editingId.value, payload);
      toast("success", "Termination Updated");
    } else {
      await TerminationService.create(payload);
      toast("success", "Termination Recorded");
    }
    resetForm();
    await fetchTerminations();
  } catch (err) {
    Swal.fire({ icon: "error", title: "Error", text: err?.response?.data?.message || "Something went wrong.", confirmButtonColor: "#229DE7" });
  } finally {
    loading.value = false;
  }
};

const editTermination = (t) => {
  editingId.value = t.id;
  form.employee_id = t.employee_id;
  form.termination_type_id = t.termination_type_id;
  form.termination_date = t.termination_date ? t.termination_date.slice(0, 10) : "";
  form.effective_date = t.effective_date ? t.effective_date.slice(0, 10) : "";
  form.reason = t.reason || "";
  form.detailed_explanation = t.detailed_explanation || "";
  form.notice_given = !!t.notice_given;
  form.notice_period_days = t.notice_period_days || "";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const removeTermination = async (t) => {
  const result = await Swal.fire({ title: "Remove this termination record?", icon: "warning", showCancelButton: true, confirmButtonColor: "#229DE7", cancelButtonColor: "#f43f5e", confirmButtonText: "Yes, remove" });
  if (!result.isConfirmed) return;
  try {
    await TerminationService.remove(t.id);
    await fetchTerminations();
    toast("success", "Termination Removed");
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

const openStatusModal = (t) => {
  statusModal.termination = t;
  statusModal.open = true;
  statusForm.status = t.status;
  statusForm.final_settlement_amount = t.final_settlement_amount || "";
  statusForm.final_settlement_notes = t.final_settlement_notes || "";
  statusForm.hr_comments = t.hr_comments || "";
  statusForm.manager_comments = t.manager_comments || "";
};

const submitStatus = async () => {
  statusSaving.value = true;
  try {
    await TerminationService.updateStatus(statusModal.termination.id, { ...statusForm });
    statusModal.open = false;
    toast("success", "Status Updated");
    await fetchTerminations();
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
  form.termination_type_id = "";
  form.termination_date = new Date().toISOString().slice(0, 10);
  form.effective_date = "";
  form.reason = "";
  form.detailed_explanation = "";
  form.notice_given = false;
  form.notice_period_days = "";
  Object.keys(errors).forEach((k) => (errors[k] = false));
};

onMounted(async () => {
  const empRes = await EmployeeProfileService.getAll({ per_page: 500 });
  employees.value = empRes?.data ?? [];
  await Promise.all([fetchTerminations(), fetchTerminationTypes()]);
});
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
.field-label.required::after { content: " *"; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color 0.15s, box-shadow 0.15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12); background: #fff; }
.field-error { font-size: 11px; font-weight: 600; color: #ef4444; margin-top: 4px; }
</style>
