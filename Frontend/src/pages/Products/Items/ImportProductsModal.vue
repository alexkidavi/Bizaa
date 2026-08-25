<template>
  <div
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm"
    @click.self="close"
  >
    <div
      class="bg-white rounded-2xl shadow-2xl border border-[#D9EBF7] w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col"
    >
      <div class="h-[3px] bg-gradient-to-r from-[#229DE7] to-[#5BB8F5] flex-shrink-0"></div>

      <div class="p-6 overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-lg font-extrabold text-slate-900">Import Products</h2>
          <button
            @click="close"
            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Instructions -->
        <div class="bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl p-4 mb-5">
          <p class="text-xs font-bold uppercase tracking-widest text-[#1682C4] mb-2">
            How it works
          </p>
          <ol class="text-sm text-slate-600 space-y-1.5 list-decimal list-inside">
            <li>Download the template below.</li>
            <li>
              Fill in <strong>Product Name</strong>, <strong>Category</strong>,
              <strong>Unit</strong>, <strong>Buying Price</strong>,
              <strong>Selling Price</strong> and <strong>Opening Stock</strong> — all
              required. Category and Unit are dropdowns in the sheet; pick from the list
              so they match your existing records.
            </li>
            <li>Choose the branch the opening stock should be added to, below.</li>
            <li>Upload your completed file and click Import.</li>
          </ol>
          <p class="text-xs text-slate-400 mt-3">
            Up to 1,000 products per file — split larger catalogs into multiple imports.
          </p>
        </div>

        <!-- Download template -->
        <button
          @click="downloadTemplate"
          :disabled="downloading"
          class="w-full flex items-center justify-center gap-2 px-4 py-2.5 mb-5 text-sm font-bold text-[#229DE7] bg-white border-2 border-dashed border-[#B3DEFA] rounded-xl hover:bg-[#EAF5FD] transition disabled:opacity-50"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M16 10l-4 4-4-4M12 14V4" />
          </svg>
          {{ downloading ? "Preparing template..." : "Download Excel Template" }}
        </button>

        <!-- Branch -->
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">
          Branch to stock <span class="text-red-500">*</span>
        </label>
        <select
          v-model="branchId"
          class="w-full px-4 py-2.5 mb-4 bg-white border border-[#D9EBF7] rounded-xl text-sm text-slate-700 outline-none focus:border-[#229DE7] transition"
        >
          <option value="" disabled>Select a branch...</option>
          <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
        </select>

        <!-- File -->
        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">
          Completed file <span class="text-red-500">*</span>
        </label>
        <input
          ref="fileInput"
          type="file"
          accept=".xlsx,.xls,.csv"
          @change="onFileChange"
          class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-[#EAF5FD] file:text-[#1682C4] hover:file:bg-[#D9EBF7] border border-[#D9EBF7] rounded-xl px-3 py-2"
        />

        <!-- Results -->
        <div v-if="result" class="mt-5 border-t border-[#EEF5FB] pt-4">
          <div class="flex items-center gap-3 mb-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
              {{ result.imported }} imported
            </span>
            <span
              v-if="result.skipped"
              class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200"
            >
              {{ result.skipped }} skipped
            </span>
            <span
              v-if="result.errors?.length"
              class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600 border border-red-200"
            >
              {{ result.errors.length }} issue{{ result.errors.length === 1 ? "" : "s" }}
            </span>
          </div>
          <div v-if="result.errors?.length" class="max-h-40 overflow-y-auto bg-red-50/50 border border-red-100 rounded-xl p-3 space-y-1">
            <p v-for="(e, i) in result.errors" :key="i" class="text-xs text-red-700">
              <span v-if="e.row" class="font-bold">Row {{ e.row }}:</span> {{ e.message }}
            </p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3 mt-6">
          <button
            @click="close"
            class="flex-1 px-4 py-2.5 text-sm font-bold text-slate-600 bg-white border border-[#D9EBF7] rounded-xl hover:bg-slate-50 transition"
          >
            {{ result ? "Close" : "Cancel" }}
          </button>
          <button
            @click="submitImport"
            :disabled="!canSubmit || importing"
            class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-[#229DE7] hover:bg-[#1682C4] rounded-xl shadow-md shadow-[#229DE7]/30 transition disabled:opacity-40 disabled:cursor-not-allowed"
          >
            {{ importing ? "Importing..." : "Import" }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import Swal from "sweetalert2";
import ProductService from "@/services/ProductService";

const props = defineProps({
  branches: { type: Array, default: () => [] },
});

const emit = defineEmits(["close", "imported"]);

const branchId = ref("");
const file = ref(null);
const fileInput = ref(null);
const downloading = ref(false);
const importing = ref(false);
const result = ref(null);

const canSubmit = computed(() => branchId.value !== "" && file.value !== null);

const close = () => emit("close");

const onFileChange = (e) => {
  file.value = e.target.files[0] || null;
};

const downloadTemplate = async () => {
  downloading.value = true;
  try {
    const res = await ProductService.downloadImportTemplate();
    const url = window.URL.createObjectURL(new Blob([res.data]));
    const a = document.createElement("a");
    a.href = url;
    a.download = "product-import-template.xlsx";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
  } catch (err) {
    Swal.fire("Error", "Failed to download the template.", "error");
  } finally {
    downloading.value = false;
  }
};

const submitImport = async () => {
  if (!canSubmit.value) return;

  importing.value = true;
  result.value = null;

  try {
    const formData = new FormData();
    formData.append("file", file.value);
    formData.append("branch_id", branchId.value);

    const res = await ProductService.importProducts(formData);
    result.value = res.data;

    if (result.value.imported > 0) {
      emit("imported");
    }

    if (!result.value.errors?.length) {
      Swal.fire({
        icon: "success",
        title: "Import complete",
        text: `${result.value.imported} product(s) imported successfully.`,
        timer: 2200,
        showConfirmButton: false,
      });
    }
  } catch (err) {
    const message = err?.response?.data?.message || "Import failed. Please check your file and try again.";
    Swal.fire("Error", message, "error");
  } finally {
    importing.value = false;
  }
};
</script>
