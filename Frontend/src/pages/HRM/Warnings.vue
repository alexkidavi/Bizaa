<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Warnings</h1>
        <p class="text-slate-500 mt-1 text-sm">Disciplinary warnings and employee history.</p>
      </div>
      <button @click="openTypesModal" class="px-4 py-2.5 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] transition">
        Manage Warning Types
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">{{ editingId ? "Edit Warning" : "Issue Warning" }}</h2>
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
              <label class="field-label required">Warning Type</label>
              <select v-model="form.warning_type_id" class="field-input" :class="{ 'border-red-400': errors.warning_type_id }">
                <option value="" disabled>Select type...</option>
                <option v-for="t in warningTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
              </select>
              <p v-if="errors.warning_type_id" class="field-error">Select a warning type</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label required">Date</label>
                <input v-model="form.warning_date" type="date" class="field-input" :class="{ 'border-red-400': errors.warning_date }" />
              </div>
              <div>
                <label class="field-label">Severity</label>
                <select v-model="form.severity" class="field-input">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
            </div>
            <div>
              <label class="field-label required">Reason</label>
              <input v-model="form.reason" type="text" class="field-input" :class="{ 'border-red-400': errors.reason }" placeholder="e.g. Repeated late arrival" />
            </div>
            <div>
              <label class="field-label">Description</label>
              <textarea v-model="form.description" rows="2" class="field-input"></textarea>
            </div>
            <div>
              <label class="field-label">Employee Response</label>
              <textarea v-model="form.employee_response" rows="2" class="field-input"></textarea>
            </div>
            <div>
              <label class="field-label">Follow-up Date</label>
              <input v-model="form.follow_up_date" type="date" class="field-input" />
            </div>
            <div>
              <label class="field-label">Status</label>
              <select v-model="form.status" class="field-input">
                <option value="active">Active</option>
                <option value="acknowledged">Acknowledged</option>
                <option value="resolved">Resolved</option>
              </select>
            </div>
            <div>
              <label class="field-label">Supporting Documents</label>
              <input type="file" multiple class="field-input" @change="onDocsChange" />
            </div>
            <div>
              <label class="field-label">Notes</label>
              <textarea v-model="form.notes" rows="2" class="field-input"></textarea>
            </div>
            <button type="submit" :disabled="loading" class="cursor-pointer w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              {{ loading ? "Saving..." : editingId ? "Update Warning" : "Issue Warning" }}
            </button>
          </form>
        </div>
      </aside>

      <main class="lg:col-span-8">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div v-if="tableLoading" class="p-6 space-y-3">
            <div v-for="n in 6" :key="n" class="h-12 bg-slate-100 animate-pulse rounded-xl"></div>
          </div>
          <div v-else-if="warnings.length === 0" class="py-20 text-center">
            <p class="font-bold text-slate-700">No warnings recorded</p>
            <p class="text-sm text-slate-400 mt-1">Disciplinary history will appear here.</p>
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5">Employee</th>
                  <th class="px-5 py-3.5">Type</th>
                  <th class="px-5 py-3.5">Date</th>
                  <th class="px-5 py-3.5">Severity</th>
                  <th class="px-5 py-3.5">Reason</th>
                  <th class="px-5 py-3.5">Status</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="w in warnings" :key="w.id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-4 text-sm font-bold text-slate-900">{{ w.employee?.full_name || w.employee?.first_name }}</td>
                  <td class="px-5 py-4 text-xs text-slate-600">{{ w.warning_type?.name }}</td>
                  <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">{{ fmtDate(w.warning_date) }}</td>
                  <td class="px-5 py-4">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border capitalize" :class="severityClass(w.severity)">{{ w.severity }}</span>
                  </td>
                  <td class="px-5 py-4 text-xs text-slate-500">{{ w.reason }}</td>
                  <td class="px-5 py-4">
                    <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold border capitalize" :class="statusClass(w.status)">{{ w.status }}</span>
                  </td>
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button @click="editWarning(w)" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                      </button>
                      <button @click="removeWarning(w)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition" title="Remove">
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
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Warning Types</h2>
          <button @click="typesModalOpen = false" class="text-slate-400 hover:text-red-500 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <form @submit.prevent="saveType" class="flex gap-2">
            <input v-model="typeForm.name" type="text" class="field-input flex-1" :placeholder="editingTypeId ? 'Update type name' : 'e.g. Verbal Warning'" />
            <button type="submit" class="px-4 py-2 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl transition">{{ editingTypeId ? "Save" : "Add" }}</button>
            <button v-if="editingTypeId" type="button" @click="editingTypeId = null; typeForm.name = '';" class="px-3 py-2 border border-[#D9EBF7] rounded-xl text-slate-500 text-sm font-bold">×</button>
          </form>
          <ul class="space-y-1.5 max-h-64 overflow-y-auto">
            <li v-for="t in warningTypes" :key="t.id" class="flex items-center justify-between px-3 py-2 rounded-lg bg-[#F5FAFF] border border-[#D9EBF7]">
              <span class="text-sm font-semibold text-slate-700">{{ t.name }}</span>
              <div class="flex gap-1">
                <button @click="editingTypeId = t.id; typeForm.name = t.name;" class="text-[#229DE7] text-xs font-bold px-2">Edit</button>
                <button @click="removeType(t)" class="text-red-500 text-xs font-bold px-2">Delete</button>
              </div>
            </li>
            <li v-if="!warningTypes.length" class="text-sm text-slate-400 text-center py-4">No warning types yet.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import Swal from "sweetalert2";
import WarningService from "@/services/WarningService";
import WarningTypeService from "@/services/WarningTypeService";
import EmployeeProfileService from "@/services/EmployeeProfileService";

const tableLoading = ref(true);
const loading = ref(false);
const editingId = ref(null);
const typesModalOpen = ref(false);
const editingTypeId = ref(null);

const warnings = ref([]);
const warningTypes = ref([]);
const employees = ref([]);

const form = reactive({
  employee_id: "",
  warning_type_id: "",
  warning_date: new Date().toISOString().slice(0, 10),
  severity: "low",
  reason: "",
  description: "",
  employee_response: "",
  follow_up_date: "",
  status: "active",
  notes: "",
});
const docFiles = ref([]);
const errors = reactive({ employee_id: false, warning_type_id: false, warning_date: false, reason: false });
const typeForm = reactive({ name: "" });

const SEVERITY_STYLES = { low: "bg-slate-100 text-slate-600 border-slate-200", medium: "bg-amber-50 text-amber-700 border-amber-200", high: "bg-red-50 text-red-600 border-red-200" };
const STATUS_STYLES = { active: "bg-red-50 text-red-600 border-red-200", acknowledged: "bg-amber-50 text-amber-700 border-amber-200", resolved: "bg-emerald-50 text-emerald-700 border-emerald-200" };
const severityClass = (s) => SEVERITY_STYLES[s] || SEVERITY_STYLES.low;
const statusClass = (s) => STATUS_STYLES[s] || STATUS_STYLES.active;

const fmtDate = (d) => (d ? new Date(d).toLocaleDateString("en-KE", { day: "2-digit", month: "short", year: "numeric" }) : "—");
const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: "top-end", showConfirmButton: false, timer: 2200 });

const onDocsChange = (e) => (docFiles.value = Array.from(e.target.files));

const fetchWarnings = async () => {
  tableLoading.value = true;
  try {
    const res = await WarningService.getAll({ per_page: 100 });
    warnings.value = res?.data ?? [];
  } finally {
    tableLoading.value = false;
  }
};

const fetchWarningTypes = async () => {
  const res = await WarningTypeService.getAll();
  warningTypes.value = res?.data ?? [];
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
      await WarningTypeService.update(editingTypeId.value, { name: typeForm.name });
    } else {
      await WarningTypeService.create({ name: typeForm.name });
    }
    editingTypeId.value = null;
    typeForm.name = "";
    await fetchWarningTypes();
  } catch (err) {
    Swal.fire({ icon: "error", title: "Error", text: err?.response?.data?.message || "Could not save type.", confirmButtonColor: "#229DE7" });
  }
};

const removeType = async (t) => {
  const result = await Swal.fire({ title: `Delete "${t.name}"?`, icon: "warning", showCancelButton: true, confirmButtonColor: "#229DE7" });
  if (!result.isConfirmed) return;
  try {
    await WarningTypeService.remove(t.id);
    await fetchWarningTypes();
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

const validate = () => {
  errors.employee_id = !form.employee_id;
  errors.warning_type_id = !form.warning_type_id;
  errors.warning_date = !form.warning_date;
  errors.reason = !form.reason.trim();
  return !errors.employee_id && !errors.warning_type_id && !errors.warning_date && !errors.reason;
};

const save = async () => {
  if (!validate()) return;
  loading.value = true;
  try {
    const payload = { ...form };
    if (docFiles.value.length) payload.supporting_documents = docFiles.value;
    if (editingId.value) {
      await WarningService.update(editingId.value, payload);
      toast("success", "Warning Updated");
    } else {
      await WarningService.create(payload);
      toast("success", "Warning Issued");
    }
    resetForm();
    await fetchWarnings();
  } catch (err) {
    Swal.fire({ icon: "error", title: "Error", text: err?.response?.data?.message || "Something went wrong.", confirmButtonColor: "#229DE7" });
  } finally {
    loading.value = false;
  }
};

const editWarning = (w) => {
  editingId.value = w.id;
  form.employee_id = w.employee_id;
  form.warning_type_id = w.warning_type_id;
  form.warning_date = w.warning_date ? w.warning_date.slice(0, 10) : "";
  form.severity = w.severity || "low";
  form.reason = w.reason || "";
  form.description = w.description || "";
  form.employee_response = w.employee_response || "";
  form.follow_up_date = w.follow_up_date ? w.follow_up_date.slice(0, 10) : "";
  form.status = w.status || "active";
  form.notes = w.notes || "";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const removeWarning = async (w) => {
  const result = await Swal.fire({ title: "Remove this warning?", icon: "warning", showCancelButton: true, confirmButtonColor: "#229DE7", cancelButtonColor: "#f43f5e", confirmButtonText: "Yes, remove" });
  if (!result.isConfirmed) return;
  try {
    await WarningService.remove(w.id);
    await fetchWarnings();
    toast("success", "Warning Removed");
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

const resetForm = () => {
  editingId.value = null;
  docFiles.value = [];
  form.employee_id = "";
  form.warning_type_id = "";
  form.warning_date = new Date().toISOString().slice(0, 10);
  form.severity = "low";
  form.reason = "";
  form.description = "";
  form.employee_response = "";
  form.follow_up_date = "";
  form.status = "active";
  form.notes = "";
  Object.keys(errors).forEach((k) => (errors[k] = false));
};

onMounted(async () => {
  const empRes = await EmployeeProfileService.getAll({ per_page: 500 });
  employees.value = empRes?.data ?? [];
  await Promise.all([fetchWarnings(), fetchWarningTypes()]);
});
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
.field-label.required::after { content: " *"; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color 0.15s, box-shadow 0.15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12); background: #fff; }
.field-error { font-size: 11px; font-weight: 600; color: #ef4444; margin-top: 4px; }
</style>
