<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Awards</h1>
        <p class="text-slate-500 mt-1 text-sm">Recognize and track employee awards.</p>
      </div>
      <button @click="openTypesModal" class="px-4 py-2.5 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] transition">
        Manage Award Types
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">{{ editingId ? "Edit Award" : "Record Award" }}</h2>
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
              <label class="field-label required">Award Type</label>
              <select v-model="form.award_type_id" class="field-input" :class="{ 'border-red-400': errors.award_type_id }">
                <option value="" disabled>Select type...</option>
                <option v-for="t in awardTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
              </select>
              <p v-if="errors.award_type_id" class="field-error">Select an award type</p>
            </div>
            <div>
              <label class="field-label required">Award Date</label>
              <input v-model="form.award_date" type="date" class="field-input" :class="{ 'border-red-400': errors.award_date }" />
            </div>
            <div>
              <label class="field-label required">Reason / Citation</label>
              <input v-model="form.reason" type="text" class="field-input" :class="{ 'border-red-400': errors.reason }" placeholder="e.g. Outstanding sales performance" />
            </div>
            <div>
              <label class="field-label">Monetary Value (KES)</label>
              <input v-model="form.monetary_value" type="number" min="0" step="0.01" class="field-input" />
            </div>
            <div>
              <label class="field-label">Description</label>
              <textarea v-model="form.description" rows="2" class="field-input"></textarea>
            </div>
            <div>
              <label class="field-label">Supporting Document</label>
              <input type="file" class="field-input" @change="onDocChange" />
            </div>
            <div>
              <label class="field-label">Notes</label>
              <textarea v-model="form.notes" rows="2" class="field-input"></textarea>
            </div>
            <button type="submit" :disabled="loading" class="cursor-pointer w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              {{ loading ? "Saving..." : editingId ? "Update Award" : "Record Award" }}
            </button>
          </form>
        </div>
      </aside>

      <main class="lg:col-span-8">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div v-if="tableLoading" class="p-6 space-y-3">
            <div v-for="n in 6" :key="n" class="h-12 bg-slate-100 animate-pulse rounded-xl"></div>
          </div>
          <div v-else-if="awards.length === 0" class="py-20 text-center">
            <p class="font-bold text-slate-700">No awards recorded yet</p>
            <p class="text-sm text-slate-400 mt-1">Recognize your first employee achievement.</p>
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5">Employee</th>
                  <th class="px-5 py-3.5">Type</th>
                  <th class="px-5 py-3.5">Date</th>
                  <th class="px-5 py-3.5">Reason</th>
                  <th class="px-5 py-3.5 text-right">Value</th>
                  <th class="px-5 py-3.5">Status</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="a in awards" :key="a.id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-4 text-sm font-bold text-slate-900">{{ a.employee?.full_name || a.employee?.first_name }}</td>
                  <td class="px-5 py-4 text-xs text-slate-600">{{ a.award_type?.name || a.awardType?.name }}</td>
                  <td class="px-5 py-4 text-xs text-slate-500 whitespace-nowrap">{{ fmtDate(a.award_date) }}</td>
                  <td class="px-5 py-4 text-xs text-slate-500">{{ a.reason }}</td>
                  <td class="px-5 py-4 text-right text-xs font-bold text-emerald-600">{{ a.monetary_value ? fmt(a.monetary_value) : "—" }}</td>
                  <td class="px-5 py-4">
                    <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold border capitalize" :class="a.status === 'issued' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'">
                      {{ a.status }}
                    </span>
                  </td>
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button v-if="(a.supporting_document)" @click="download(a)" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:bg-slate-100 transition" title="Download document">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                      </button>
                      <button @click="editAward(a)" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                      </button>
                      <button @click="removeAward(a)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition" title="Remove">
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
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Award Types</h2>
          <button @click="typesModalOpen = false" class="text-slate-400 hover:text-red-500 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        <div class="p-6 space-y-4">
          <form @submit.prevent="saveType" class="flex gap-2">
            <input v-model="typeForm.name" type="text" class="field-input flex-1" :placeholder="editingTypeId ? 'Update type name' : 'e.g. Employee of the Month'" />
            <button type="submit" class="px-4 py-2 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl transition">{{ editingTypeId ? "Save" : "Add" }}</button>
            <button v-if="editingTypeId" type="button" @click="editingTypeId = null; typeForm.name = '';" class="px-3 py-2 border border-[#D9EBF7] rounded-xl text-slate-500 text-sm font-bold">×</button>
          </form>
          <ul class="space-y-1.5 max-h-64 overflow-y-auto">
            <li v-for="t in awardTypes" :key="t.id" class="flex items-center justify-between px-3 py-2 rounded-lg bg-[#F5FAFF] border border-[#D9EBF7]">
              <span class="text-sm font-semibold text-slate-700">{{ t.name }}</span>
              <div class="flex gap-1">
                <button @click="editingTypeId = t.id; typeForm.name = t.name;" class="text-[#229DE7] text-xs font-bold px-2">Edit</button>
                <button @click="removeType(t)" class="text-red-500 text-xs font-bold px-2">Delete</button>
              </div>
            </li>
            <li v-if="!awardTypes.length" class="text-sm text-slate-400 text-center py-4">No award types yet.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import Swal from "sweetalert2";
import AwardService from "@/services/AwardService";
import AwardTypeService from "@/services/AwardTypeService";
import EmployeeProfileService from "@/services/EmployeeProfileService";

const tableLoading = ref(true);
const loading = ref(false);
const editingId = ref(null);
const typesModalOpen = ref(false);
const editingTypeId = ref(null);

const awards = ref([]);
const awardTypes = ref([]);
const employees = ref([]);

const form = reactive({
  employee_id: "",
  award_type_id: "",
  award_date: new Date().toISOString().slice(0, 10),
  reason: "",
  monetary_value: "",
  description: "",
  notes: "",
});
const docFile = ref(null);
const errors = reactive({ employee_id: false, award_type_id: false, award_date: false, reason: false });
const typeForm = reactive({ name: "" });

const fmt = (v) => `KES ${Number(v || 0).toLocaleString("en-KE", { minimumFractionDigits: 2 })}`;
const fmtDate = (d) => (d ? new Date(d).toLocaleDateString("en-KE", { day: "2-digit", month: "short", year: "numeric" }) : "—");
const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: "top-end", showConfirmButton: false, timer: 2200 });

const onDocChange = (e) => (docFile.value = e.target.files[0] || null);

const fetchAwards = async () => {
  tableLoading.value = true;
  try {
    const res = await AwardService.getAll({ per_page: 100 });
    awards.value = res?.data ?? [];
  } finally {
    tableLoading.value = false;
  }
};

const fetchAwardTypes = async () => {
  const res = await AwardTypeService.getAll();
  awardTypes.value = res?.data ?? [];
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
      await AwardTypeService.update(editingTypeId.value, { name: typeForm.name });
    } else {
      await AwardTypeService.create({ name: typeForm.name });
    }
    editingTypeId.value = null;
    typeForm.name = "";
    await fetchAwardTypes();
  } catch (err) {
    Swal.fire({ icon: "error", title: "Error", text: err?.response?.data?.message || "Could not save type.", confirmButtonColor: "#229DE7" });
  }
};

const removeType = async (t) => {
  const result = await Swal.fire({ title: `Delete "${t.name}"?`, icon: "warning", showCancelButton: true, confirmButtonColor: "#229DE7" });
  if (!result.isConfirmed) return;
  try {
    await AwardTypeService.remove(t.id);
    await fetchAwardTypes();
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

const validate = () => {
  errors.employee_id = !form.employee_id;
  errors.award_type_id = !form.award_type_id;
  errors.award_date = !form.award_date;
  errors.reason = !form.reason.trim();
  return !errors.employee_id && !errors.award_type_id && !errors.award_date && !errors.reason;
};

const save = async () => {
  if (!validate()) return;
  loading.value = true;
  try {
    const payload = { ...form };
    if (docFile.value) payload.supporting_document = docFile.value;
    if (editingId.value) {
      await AwardService.update(editingId.value, payload);
      toast("success", "Award Updated");
    } else {
      await AwardService.create(payload);
      toast("success", "Award Recorded");
    }
    resetForm();
    await fetchAwards();
  } catch (err) {
    Swal.fire({ icon: "error", title: "Error", text: err?.response?.data?.message || "Something went wrong.", confirmButtonColor: "#229DE7" });
  } finally {
    loading.value = false;
  }
};

const editAward = (a) => {
  editingId.value = a.id;
  form.employee_id = a.employee_id;
  form.award_type_id = a.award_type_id;
  form.award_date = a.award_date ? a.award_date.slice(0, 10) : "";
  form.reason = a.reason || "";
  form.monetary_value = a.monetary_value || "";
  form.description = a.description || "";
  form.notes = a.notes || "";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const removeAward = async (a) => {
  const result = await Swal.fire({ title: "Remove this award?", icon: "warning", showCancelButton: true, confirmButtonColor: "#229DE7", cancelButtonColor: "#f43f5e", confirmButtonText: "Yes, remove" });
  if (!result.isConfirmed) return;
  try {
    await AwardService.remove(a.id);
    await fetchAwards();
    toast("success", "Award Removed");
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

const download = async (a) => {
  const blob = await AwardService.downloadDocument(a.id);
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = `award-${a.id}-document`;
  link.click();
  URL.revokeObjectURL(url);
};

const resetForm = () => {
  editingId.value = null;
  docFile.value = null;
  form.employee_id = "";
  form.award_type_id = "";
  form.award_date = new Date().toISOString().slice(0, 10);
  form.reason = "";
  form.monetary_value = "";
  form.description = "";
  form.notes = "";
  Object.keys(errors).forEach((k) => (errors[k] = false));
};

onMounted(async () => {
  const empRes = await EmployeeProfileService.getAll({ per_page: 500 });
  employees.value = empRes?.data ?? [];
  await Promise.all([fetchAwards(), fetchAwardTypes()]);
});
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
.field-label.required::after { content: " *"; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color 0.15s, box-shadow 0.15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12); background: #fff; }
.field-error { font-size: 11px; font-weight: 600; color: #ef4444; margin-top: 4px; }
</style>
