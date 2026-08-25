<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Expenses</h1>
        <p class="text-slate-500 mt-1 text-sm">Track and manage all business expenses.</p>
      </div>
    </div>

    <!-- SUMMARY CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4 shadow-sm">
        <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">
          Today
        </p>
        <p class="text-xl font-extrabold text-slate-900">{{ fmt(summary.today) }}</p>
      </div>
      <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4 shadow-sm">
        <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">
          This Month
        </p>
        <p class="text-xl font-extrabold text-[#229DE7]">{{ fmt(summary.this_month) }}</p>
      </div>
      <div class="bg-white border border-[#D9EBF7] rounded-2xl p-4 shadow-sm">
        <p class="text-[10.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">
          All Time
        </p>
        <p class="text-xl font-extrabold text-slate-900">{{ fmt(summary.total) }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- FORM -->
      <aside class="lg:col-span-4">
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div
            class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]"
          >
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
              {{ editingId ? "Edit Expense" : "Record Expense" }}
            </h2>
            <button
              v-if="editingId"
              @click="resetForm"
              class="text-xs font-bold text-slate-400 hover:text-red-500 transition"
            >
              Cancel
            </button>
          </div>
          <form @submit.prevent="save" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Category</label>
              <select
                v-model="form.expense_category_id"
                class="field-input"
                :class="{ 'border-red-400': errors.category }"
              >
                <option value="" disabled>Select category...</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">
                  {{ c.name }}
                </option>
              </select>
              <p v-if="errors.category" class="field-error">Category is required</p>
            </div>
            <div>
              <label class="field-label required">Amount</label>
              <input
                v-model="form.amount"
                type="number"
                min="0.01"
                step="0.01"
                class="field-input"
                :class="{ 'border-red-400': errors.amount }"
                placeholder="0.00"
              />
              <p v-if="errors.amount" class="field-error">Enter a valid amount</p>
            </div>
            <div>
              <label class="field-label required">Date</label>
              <input
                v-model="form.expense_date"
                type="date"
                class="field-input"
                :class="{ 'border-red-400': errors.date }"
              />
              <p v-if="errors.date" class="field-error">Date is required</p>
            </div>
            <div>
              <label class="field-label">Payment Method</label>
              <select v-model="form.payment_method" class="field-input">
                <option value="cash">Cash</option>
                <option value="bank">Bank Transfer</option>
                <option value="mobile">Mobile Money</option>
              </select>
            </div>
            <div>
              <label class="field-label">Description</label>
              <input
                v-model="form.description"
                type="text"
                class="field-input"
                placeholder="Optional notes"
              />
            </div>
            <div>
              <label class="field-label">Reference #</label>
              <input
                v-model="form.reference"
                type="text"
                class="field-input"
                placeholder="e.g. Receipt no."
              />
            </div>
            <button
              type="submit"
              :disabled="loading"
              class="cursor-pointer w-full flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60"
            >
              <svg
                v-if="loading"
                class="cursor-pointer animate-spin h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
              </svg>
              {{
                loading ? "Saving..." : editingId ? "Update Expense" : "Record Expense"
              }}
            </button>
          </form>
        </div>
      </aside>

      <!-- TABLE -->
      <main class="lg:col-span-8">
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="px-6 py-4 border-b border-[#EEF5FB] flex flex-wrap items-center gap-3"
          >
            <select
              v-model="filterCategory"
              class="text-sm bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl px-3 py-2 outline-none focus:border-[#229DE7]"
            >
              <option value="">All Categories</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">
                {{ c.name }}
              </option>
            </select>
            <input
              v-model="filterFrom"
              type="date"
              class="text-sm bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl px-3 py-2 outline-none focus:border-[#229DE7]"
            />
            <input
              v-model="filterTo"
              type="date"
              class="text-sm bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl px-3 py-2 outline-none focus:border-[#229DE7]"
            />
            <button
              @click="fetchExpenses"
              class="px-4 py-2 bg-[#229DE7] text-white text-sm font-bold rounded-xl hover:bg-[#1682C4] transition"
            >
              Filter
            </button>
          </div>

          <div v-if="tableLoading" class="p-6 space-y-3">
            <div
              v-for="n in 6"
              :key="n"
              class="h-10 bg-slate-100 animate-pulse rounded-xl"
            ></div>
          </div>

          <div v-else-if="expenses.length === 0" class="py-20 text-center">
            <div
              class="w-14 h-14 rounded-full bg-[#EAF5FD] flex items-center justify-center mx-auto mb-4"
            >
              <svg
                class="w-7 h-7 text-[#229DE7]"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
            <p class="font-bold text-slate-700">No expenses found</p>
            <p class="text-sm text-slate-400 mt-1">Record your first expense.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest"
                >
                  <th class="px-5 py-3.5">#</th>
                  <th class="px-5 py-3.5">Date</th>
                  <th class="px-5 py-3.5">Category</th>
                  <th class="px-5 py-3.5">Description</th>
                  <th class="px-5 py-3.5">Method</th>
                  <th class="px-5 py-3.5 text-right">Amount</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(exp, i) in expenses"
                  :key="exp.id"
                  class="hover:bg-[#F5FAFF] transition-colors"
                >
                  <td class="px-5 py-4 text-xs font-bold text-slate-400">
                    {{ (currentPage - 1) * 50 + i + 1 }}
                  </td>
                  <td
                    class="px-5 py-4 text-xs text-slate-600 font-semibold whitespace-nowrap"
                  >
                    {{ fmtDate(exp.expense_date) }}
                  </td>
                  <td class="px-5 py-4">
                    <span
                      class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#EAF5FD] text-[#1682C4]"
                      >{{ exp.category?.name || "—" }}</span
                    >
                  </td>
                  <td class="px-5 py-4 text-xs text-slate-500">
                    {{ exp.description || "—" }}
                  </td>
                  <td class="px-5 py-4">
                    <span class="text-xs font-semibold capitalize text-slate-600">{{
                      exp.payment_method
                    }}</span>
                  </td>
                  <td class="px-5 py-4 text-right font-bold text-sm text-slate-900">
                    {{ fmt(exp.amount) }}
                  </td>
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button
                        @click="editExpense(exp)"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition"
                      >
                        <svg
                          class="w-4 h-4"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="2.2"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                          />
                        </svg>
                      </button>
                      <button
                        @click="removeExpense(exp.id)"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition"
                      >
                        <svg
                          class="w-4 h-4"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="2.2"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                          />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- PAGINATION -->
          <div
            v-if="totalPages > 1"
            class="px-6 py-4 border-t border-[#EEF5FB] flex items-center justify-between gap-3"
          >
            <span class="text-sm text-slate-500 font-semibold"
              >Page {{ currentPage }} of {{ totalPages }}</span
            >
            <div class="flex gap-2">
              <button
                :disabled="currentPage === 1"
                @click="
                  currentPage--;
                  fetchExpenses();
                "
                class="px-4 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] disabled:opacity-40 transition"
              >
                ← Prev
              </button>
              <button
                :disabled="currentPage >= totalPages"
                @click="
                  currentPage++;
                  fetchExpenses();
                "
                class="px-4 py-2 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#229DE7] hover:text-[#229DE7] disabled:opacity-40 transition"
              >
                Next →
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import Swal from "sweetalert2";
import ExpenseCategoryService from "@/services/ExpenseCategoryService";
import ExpenseService from "@/services/ExpenseService";

const tableLoading = ref(true);
const loading = ref(false);
const editingId = ref(null);

const categories = ref([]);
const expenses = ref([]);
const summary = ref({ today: 0, this_month: 0, total: 0 });

const currentPage = ref(1);
const totalPages = ref(1);

const filterCategory = ref("");
const filterFrom = ref("");
const filterTo = ref("");

const form = reactive({
  expense_category_id: "",
  amount: "",
  expense_date: new Date().toISOString().slice(0, 10),
  payment_method: "cash",
  description: "",
  reference: "",
});
const errors = reactive({ category: false, amount: false, date: false });

const fmt = (v) =>
  `KES ${Number(v || 0).toLocaleString("en-KE", { minimumFractionDigits: 2 })}`;
const fmtDate = (d) =>
  d
    ? new Date(d).toLocaleDateString("en-KE", {
        day: "2-digit",
        month: "short",
        year: "numeric",
      })
    : "—";
const toast = (icon, title) =>
  Swal.fire({
    icon,
    title,
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2500,
    timerProgressBar: true,
  });

const fetchExpenses = async () => {
  tableLoading.value = true;
  try {
    const params = { page: currentPage.value, per_page: 50 };
    if (filterCategory.value) params.category_id = filterCategory.value;
    if (filterFrom.value) params.from = filterFrom.value;
    if (filterTo.value) params.to = filterTo.value;
    const res = await ExpenseService.getAll(params);
    expenses.value = res?.data ?? [];
    totalPages.value = res?.last_page ?? 1;
  } finally {
    tableLoading.value = false;
  }
};

const fetchSummary = async () => {
  try {
    const res = await ExpenseService.getSummary();
    summary.value = res?.data ?? summary.value;
  } catch {}
};

onMounted(async () => {
  const catsRes = await ExpenseCategoryService.getAll();

  await Promise.all([fetchExpenses(), fetchSummary()]);

  categories.value = catsRes.data || [];
});
const validate = () => {
  errors.category = !form.expense_category_id;
  errors.amount = !form.amount || Number(form.amount) <= 0;
  errors.date = !form.expense_date;
  return !errors.category && !errors.amount && !errors.date;
};

const save = async () => {
  if (!validate()) return;
  loading.value = true;
  try {
    if (editingId.value) {
      await ExpenseService.update(editingId.value, form);
      toast("success", "Expense Updated");
    } else {
      await ExpenseService.create(form);
      toast("success", "Expense Recorded");
    }
    resetForm();
    await Promise.all([fetchExpenses(), fetchSummary()]);
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: err?.response?.data?.message || "Something went wrong",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    loading.value = false;
  }
};

const editExpense = (exp) => {
  editingId.value = exp.id;
  form.expense_category_id = exp.expense_category_id;
  form.amount = exp.amount;
  form.expense_date = exp.expense_date ? exp.expense_date.slice(0, 10) : "";
  form.payment_method = exp.payment_method || "cash";
  form.description = exp.description || "";
  form.reference = exp.reference || "";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const removeExpense = async (id) => {
  const result = await Swal.fire({
    title: "Delete this expense?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: "Yes, delete",
  });
  if (!result.isConfirmed) return;
  try {
    await ExpenseService.remove(id);
    await Promise.all([fetchExpenses(), fetchSummary()]);
    toast("success", "Expense Deleted");
  } catch {
    Swal.fire({ icon: "error", title: "Delete Failed", confirmButtonColor: "#229DE7" });
  }
};

const resetForm = () => {
  editingId.value = null;
  form.expense_category_id = "";
  form.amount = "";
  form.expense_date = new Date().toISOString().slice(0, 10);
  form.payment_method = "cash";
  form.description = "";
  form.reference = "";
  Object.keys(errors).forEach((k) => (errors[k] = false));
};
</script>

<style scoped>
.field-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 6px;
}
.field-label.required::after {
  content: " *";
  color: #ef4444;
}
.field-input {
  width: 100%;
  padding: 10px 14px;
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 12px;
  font-size: 13.5px;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
  font-family: inherit;
}
.field-input:focus {
  border-color: #229de7;
  box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12);
  background: #fff;
}
.field-error {
  font-size: 11px;
  font-weight: 600;
  color: #ef4444;
  margin-top: 4px;
}
</style>
