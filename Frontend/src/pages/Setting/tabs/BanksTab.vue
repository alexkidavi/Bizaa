<template>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <aside class="lg:col-span-4">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">{{ editingId ? "Edit Bank" : "Add Bank" }}</h2>
          <button v-if="editingId" @click="resetForm" class="text-xs font-bold text-slate-400 hover:text-red-500 transition">Cancel</button>
        </div>
        <form @submit.prevent="save" class="p-6 space-y-4">
          <div>
            <label class="field-label required">Bank Name</label>
            <input v-model="form.name" type="text" class="field-input" :class="{ 'border-red-400': errors.name }" placeholder="e.g. Equity Bank" />
            <p v-if="errors.name" class="field-error">Bank name is required</p>
          </div>
          <div>
            <label class="field-label">Bank Code</label>
            <input v-model="form.code" type="text" class="field-input" placeholder="e.g. 68" />
          </div>
          <div class="flex items-center gap-2">
            <input id="bank_active" v-model="form.is_active" type="checkbox" class="w-4 h-4 rounded border-[#D9EBF7] text-[#229DE7]" />
            <label for="bank_active" class="text-sm font-semibold text-slate-600">Active (selectable for staff)</label>
          </div>
          <button type="submit" :disabled="loading" class="cursor-pointer w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
            {{ loading ? "Saving..." : editingId ? "Update Bank" : "Add Bank" }}
          </button>
        </form>
      </div>
    </aside>

    <main class="lg:col-span-8">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div v-if="tableLoading" class="p-6 space-y-3">
          <div v-for="n in 6" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div>
        </div>
        <div v-else-if="banks.length === 0" class="py-20 text-center">
          <p class="font-bold text-slate-700">No banks configured</p>
          <p class="text-sm text-slate-400 mt-1">Add a bank to make it selectable on staff profiles.</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                <th class="px-5 py-3.5">Bank Name</th>
                <th class="px-5 py-3.5">Code</th>
                <th class="px-5 py-3.5">Status</th>
                <th class="px-5 py-3.5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF5FB]">
              <tr v-for="b in banks" :key="b.id" class="hover:bg-[#F5FAFF] transition-colors">
                <td class="px-5 py-4 text-sm font-bold text-slate-900">{{ b.name }}</td>
                <td class="px-5 py-4 text-xs text-slate-500">{{ b.code || "—" }}</td>
                <td class="px-5 py-4">
                  <span class="px-2.5 py-1 rounded-full text-[10.5px] font-bold border" :class="b.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'">
                    {{ b.is_active ? "Active" : "Inactive" }}
                  </span>
                </td>
                <td class="px-5 py-4 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="editBank(b)" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition" title="Edit">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </button>
                    <button @click="removeBank(b)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition" title="Remove">
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
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import Swal from "sweetalert2";
import BankService from "@/services/BankService";

const tableLoading = ref(true);
const loading = ref(false);
const editingId = ref(null);
const banks = ref([]);

const form = reactive({ name: "", code: "", is_active: true });
const errors = reactive({ name: false });

const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: "top-end", showConfirmButton: false, timer: 2200 });

const fetchBanks = async () => {
  tableLoading.value = true;
  try {
    const res = await BankService.getAll();
    banks.value = res?.data ?? [];
  } finally {
    tableLoading.value = false;
  }
};

const save = async () => {
  errors.name = !form.name.trim();
  if (errors.name) return;
  loading.value = true;
  try {
    if (editingId.value) {
      await BankService.update(editingId.value, { ...form });
      toast("success", "Bank Updated");
    } else {
      await BankService.create({ ...form });
      toast("success", "Bank Added");
    }
    resetForm();
    await fetchBanks();
  } catch (err) {
    Swal.fire({ icon: "error", title: "Error", text: err?.response?.data?.message || "Could not save bank.", confirmButtonColor: "#229DE7" });
  } finally {
    loading.value = false;
  }
};

const editBank = (b) => {
  editingId.value = b.id;
  form.name = b.name;
  form.code = b.code || "";
  form.is_active = !!b.is_active;
};

const removeBank = async (b) => {
  const result = await Swal.fire({ title: `Remove "${b.name}"?`, icon: "warning", showCancelButton: true, confirmButtonColor: "#229DE7", cancelButtonColor: "#f43f5e", confirmButtonText: "Yes, remove" });
  if (!result.isConfirmed) return;
  try {
    await BankService.remove(b.id);
    await fetchBanks();
    toast("success", "Bank Removed");
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

const resetForm = () => {
  editingId.value = null;
  form.name = "";
  form.code = "";
  form.is_active = true;
  errors.name = false;
};

onMounted(fetchBanks);
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
.field-label.required::after { content: " *"; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color 0.15s, box-shadow 0.15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12); background: #fff; }
.field-error { font-size: 11px; font-weight: 600; color: #ef4444; margin-top: 4px; }
</style>
