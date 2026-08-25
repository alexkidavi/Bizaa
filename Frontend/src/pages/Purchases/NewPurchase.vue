<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- BACK + TITLE -->
    <div class="mb-6">
      <button
        @click="$router.back()"
        class="inline-flex items-center gap-2 text-sm font-bold text-[#229DE7] hover:text-[#1682C4] mb-4 transition group"
      >
        <svg
          class="w-4 h-4 group-hover:-translate-x-1 transition-transform"
          fill="none"
          stroke="currentColor"
          stroke-width="2.5"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Purchases
      </button>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
            New Purchase
          </h1>
          <p class="text-slate-500 mt-1 text-sm">
            Create a purchase order for one or multiple branches.
          </p>
        </div>
        <!-- Grand total pill -->
        <div
          class="hidden md:flex items-center gap-3 bg-white border border-[#D9EBF7] rounded-2xl px-5 py-3 shadow-sm"
        >
          <div class="text-right">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
              Grand Total
            </p>
            <p class="text-xl font-extrabold text-[#229DE7]">
              {{ formatCurrency(grandTotal) }}
            </p>
          </div>
          <div class="w-px h-10 bg-[#D9EBF7]"></div>
          <div class="text-right">
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
              Balance
            </p>
            <p
              class="text-xl font-extrabold"
              :class="balance > 0 ? 'text-red-500' : 'text-emerald-600'"
            >
              {{ formatCurrency(balance) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- ══════════ LEFT COLUMN ══════════ -->
      <div class="xl:col-span-2 space-y-5">
        <!-- ── SECTION 1: Purchase Header ── -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="px-6 py-4 border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
              Purchase Details
            </h2>
          </div>

          <!-- Skeleton while loading -->
          <div v-if="formLoading" class="p-6 space-y-3">
            <div
              v-for="n in 4"
              :key="n"
              class="h-11 bg-slate-50 animate-pulse rounded-xl"
            ></div>
          </div>

          <div v-else class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Supplier -->
            <div class="sm:col-span-2">
              <label class="field-label required">Supplier</label>
              <Multiselect
                v-model="form.supplier"
                :options="suppliers"
                track-by="id"
                label="name"
                placeholder="Select supplier..."
                :class="{ 'ms-invalid': vErrors.supplier }"
              />
              <p v-if="vErrors.supplier" class="field-error">Supplier is required</p>
            </div>

            <!-- Branches -->
            <div class="sm:col-span-2">
              <label class="field-label required">
                Branches
                <span class="text-slate-400 font-normal text-[11px] ml-1"
                  >(Each branch gets its own item list)</span
                >
              </label>
              <Multiselect
                v-model="form.branches"
                :options="branches"
                :multiple="true"
                track-by="id"
                label="name"
                placeholder="Select one or more branches..."
                :class="{ 'ms-invalid': vErrors.branches }"
                @update:modelValue="syncBranchItems"
              />
              <p v-if="vErrors.branches" class="field-error">
                Select at least one branch
              </p>
            </div>

            <!-- Reference -->
            <div>
              <label class="field-label">Purchase Reference</label>
              <input
                v-model="form.reference"
                type="text"
                class="field-input"
                placeholder="e.g. PO-2024-001 (auto if blank)"
              />
            </div>

            <!-- Purchase Date -->
            <div>
              <label class="field-label required">Purchase Date</label>
              <input
                v-model="form.purchase_date"
                type="date"
                class="field-input"
                :class="{ 'border-red-400': vErrors.purchase_date }"
              />
              <p v-if="vErrors.purchase_date" class="field-error">Date is required</p>
            </div>

            <!-- Notes -->
            <div class="sm:col-span-2">
              <label class="field-label"
                >Notes
                <span class="text-slate-400 font-normal text-[11px]"
                  >(Optional)</span
                ></label
              >
              <textarea
                v-model="form.notes"
                rows="2"
                class="field-input resize-none"
                placeholder="Additional notes..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- ── SECTION 2: Items per Branch ── -->
        <div
          v-if="form.branches.length > 0"
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div
            class="px-6 py-4 border-b border-[#EEF5FB] flex items-center justify-between"
          >
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
              Items per Branch
            </h2>
            <span class="text-xs text-slate-400 font-semibold">
              {{ form.branches.length }} branch{{ form.branches.length > 1 ? "es" : "" }}
              selected
            </span>
          </div>

          <!-- Branch tabs -->
          <div class="flex gap-0 border-b border-[#EEF5FB] overflow-x-auto">
            <button
              v-for="branch in form.branches"
              :key="branch.id"
              @click="activeBranchTab = branch.id"
              class="px-5 py-3 text-sm font-bold whitespace-nowrap border-b-2 transition-all"
              :class="
                activeBranchTab === branch.id
                  ? 'border-[#229DE7] text-[#229DE7] bg-[#EAF5FD]'
                  : 'border-transparent text-slate-500 hover:text-[#229DE7] hover:bg-[#F5FAFF]'
              "
            >
              <span class="flex items-center gap-2">
                <span
                  class="w-5 h-5 rounded-md bg-[#229DE7] text-white text-[9px] font-extrabold flex items-center justify-center flex-shrink-0"
                >
                  {{ (branchItems[branch.id] || []).length }}
                </span>
                {{ branch.name }}
              </span>
            </button>
          </div>

          <!-- Items for active branch -->
          <div
            v-for="branch in form.branches"
            :key="`items-${branch.id}`"
            v-show="activeBranchTab === branch.id"
            class="p-5"
          >
            <!-- Items table header -->
            <div
              class="hidden md:grid items-center gap-2 mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 px-1"
              style="grid-template-columns: 2fr 80px 100px 80px 80px 110px 110px 40px"
            >
              <span>Product</span><span>Qty</span><span>Unit Price</span>
              <span>Disc %</span><span>Tax %</span><span>Expiry</span>
              <span>Batch/Lot</span><span></span>
            </div>

            <!-- Item rows -->
            <div class="space-y-2">
              <div
                v-for="(item, idx) in branchItems[branch.id]"
                :key="item._key"
                class="grid items-start gap-2 p-3 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl"
                style="grid-template-columns: 2fr 80px 100px 80px 80px 110px 110px 40px"
              >
                <div>
                  <label class="md:hidden field-label">Product</label>
                  <Multiselect
                    v-model="item.product"
                    :options="products"
                    track-by="id"
                    label="name"
                    placeholder="Select product..."
                    :class="{ 'ms-invalid': item._error_product }"
                    @update:modelValue="onProductSelect(item)"
                  />
                  <p v-if="item._error_product" class="field-error">Required</p>
                </div>
                <div>
                  <label class="md:hidden field-label">Qty</label>
                  <input
                    v-model.number="item.quantity"
                    type="number"
                    min="0.01"
                    step="any"
                    class="field-input text-center"
                    placeholder="1"
                    @input="recalc"
                  />
                  <p v-if="item._error_qty" class="field-error">Required</p>
                </div>
                <div>
                  <label class="md:hidden field-label">Unit Price</label>
                  <input
                    v-model.number="item.unit_price"
                    type="number"
                    min="0.01"
                    step="any"
                    class="field-input"
                    placeholder="0.00"
                    @input="recalc"
                  />
                  <p v-if="item._error_price" class="field-error">
                    Price must be greater than 0
                  </p>
                </div>
                <div>
                  <label class="md:hidden field-label">Disc %</label>
                  <input
                    v-model.number="item.discount"
                    type="number"
                    min="0"
                    max="100"
                    step="any"
                    class="field-input text-center"
                    placeholder="0"
                    @input="recalc"
                  />
                </div>
                <div>
                  <label class="md:hidden field-label">Tax %</label>
                  <input
                    v-model.number="item.tax"
                    type="number"
                    min="0"
                    max="100"
                    step="any"
                    class="field-input text-center"
                    placeholder="0"
                    @input="recalc"
                  />
                </div>
                <div>
                  <label class="md:hidden field-label">Expiry</label>
                  <input
                    v-model="item.expiry_date"
                    type="date"
                    class="field-input text-xs"
                  />
                </div>
                <div>
                  <label class="md:hidden field-label">Batch/Lot</label>
                  <input
                    v-model="item.batch_number"
                    type="text"
                    class="field-input text-xs"
                    placeholder="Batch no."
                  />
                </div>
                <div class="flex items-start justify-end pt-1">
                  <button
                    @click="removeItem(branch.id, idx)"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-red-400 hover:bg-red-50 hover:text-red-600 transition"
                    :disabled="(branchItems[branch.id] || []).length <= 1"
                  >
                    <svg
                      class="w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2.5"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12"
                      />
                    </svg>
                  </button>
                </div>
                <div
                  class="col-span-full flex justify-end items-center gap-2 pt-1 border-t border-[#D9EBF7] mt-1"
                >
                  <span
                    class="text-[10.5px] text-slate-400 font-bold uppercase tracking-wide"
                    >Line Total:</span
                  >
                  <span class="text-sm font-extrabold text-slate-800">{{
                    formatCurrency(lineTotal(item))
                  }}</span>
                </div>
              </div>
            </div>

            <button
              @click="addItem(branch.id)"
              class="mt-3 w-full flex items-center justify-center gap-2 py-2.5 border-2 border-dashed border-[#B3DEFA] rounded-xl text-sm font-bold text-[#229DE7] hover:bg-[#EAF5FD] hover:border-[#229DE7] transition"
            >
              <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
              </svg>
              Add Item to {{ branch.name }}
            </button>

            <div class="mt-3 flex justify-end">
              <div
                class="bg-[#EAF5FD] border border-[#B3DEFA] rounded-xl px-5 py-2.5 flex gap-6"
              >
                <div class="text-right">
                  <p
                    class="text-[10px] font-bold uppercase tracking-widest text-[#1682C4]"
                  >
                    Branch Subtotal
                  </p>
                  <p class="text-base font-extrabold text-slate-800">
                    {{ formatCurrency(branchSubtotal(branch.id)) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <p v-if="vErrors.items" class="px-6 pb-4 field-error">{{ vErrors.items }}</p>
        </div>

        <!-- Prompt to select branches -->
        <div
          v-else
          class="bg-white border-2 border-dashed border-[#B3DEFA] rounded-2xl p-10 text-center"
        >
          <div
            class="w-14 h-14 rounded-full bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center mx-auto mb-3"
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
                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
              />
            </svg>
          </div>
          <p class="font-bold text-slate-700">Select branches above</p>
          <p class="text-xs text-slate-400 mt-1">
            Each branch will get its own item list.
          </p>
        </div>
      </div>

      <!-- ══════════ RIGHT COLUMN ══════════ -->
      <div class="xl:col-span-1 space-y-5">
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="px-5 py-4 border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
              Order Summary
            </h2>
          </div>
          <div class="p-5 space-y-2.5">
            <div
              v-for="branch in form.branches"
              :key="`sum-${branch.id}`"
              class="flex items-center justify-between py-1.5 border-b border-[#EEF5FB]"
            >
              <span class="text-xs font-bold text-slate-500 truncate max-w-[150px]">{{
                branch.name
              }}</span>
              <span class="text-sm font-extrabold text-slate-700">{{
                formatCurrency(branchSubtotal(branch.id))
              }}</span>
            </div>

            <div
              v-if="form.branches.length === 0"
              class="text-xs text-slate-400 italic text-center py-3"
            >
              No branches selected
            </div>

            <div class="pt-1 space-y-1.5">
              <div class="flex justify-between text-sm">
                <span class="text-slate-500 font-semibold">Subtotal</span>
                <span class="font-bold text-slate-800">{{
                  formatCurrency(subtotal)
                }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-slate-500 font-semibold">Total Discount</span>
                <span class="font-bold text-red-500"
                  >- {{ formatCurrency(totalDiscount) }}</span
                >
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-slate-500 font-semibold">Total Tax</span>
                <span class="font-bold text-amber-600"
                  >+ {{ formatCurrency(totalTax) }}</span
                >
              </div>
              <div
                class="flex justify-between text-base font-extrabold pt-2 border-t border-[#D9EBF7]"
              >
                <span class="text-slate-800">Grand Total</span>
                <span class="text-[#229DE7]">{{ formatCurrency(grandTotal) }}</span>
              </div>
            </div>
          </div>

          <!-- Payment Section -->
          <div class="border-t border-[#EEF5FB] px-5 py-4 space-y-4">
            <h3 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
              Payment
            </h3>

            <div>
              <label class="field-label required">Payment Type</label>
              <div class="grid grid-cols-3 gap-2">
                <button
                  v-for="pt in paymentTypes"
                  :key="pt.value"
                  type="button"
                  @click="
                    form.payment_type = pt.value;
                    if (pt.value === 'full') form.paid_amount = grandTotal;
                    if (pt.value === 'none') form.paid_amount = 0;
                  "
                  class="py-2 px-2 rounded-xl text-xs font-bold border transition text-center"
                  :class="
                    form.payment_type === pt.value
                      ? 'bg-[#229DE7] text-white border-[#229DE7] shadow-sm'
                      : 'bg-white text-slate-500 border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7]'
                  "
                >
                  {{ pt.label }}
                </button>
              </div>
            </div>

            <div v-if="form.payment_type !== 'none'">
              <label class="field-label required">Amount Paid (KES)</label>
              <input
                v-model.number="form.paid_amount"
                type="number"
                min="0"
                :max="grandTotal"
                step="any"
                class="field-input"
                :class="{ 'border-red-400': vErrors.paid_amount }"
                placeholder="0.00"
                :readonly="form.payment_type === 'full'"
                @input="recalc"
              />
              <p v-if="vErrors.paid_amount" class="field-error">
                {{ vErrors.paid_amount }}
              </p>
            </div>

            <div v-if="form.payment_type !== 'none'">
              <label class="field-label required">Payment Method</label>
              <select
                v-model="form.payment_method"
                class="field-input"
                :class="{ 'border-red-400': vErrors.payment_method }"
              >
                <option value="" disabled>Select method...</option>
                <option value="cash">Cash</option>
                <option value="mpesa">M-Pesa</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="cheque">Cheque</option>
                <option value="credit">Credit</option>
              </select>
              <p v-if="vErrors.payment_method" class="field-error">
                Payment method required
              </p>
            </div>

            <div v-if="form.payment_type !== 'none'">
              <label class="field-label"
                >Payment Reference
                <span class="text-slate-400 font-normal text-[11px]"
                  >(Optional)</span
                ></label
              >
              <input
                v-model="form.payment_reference"
                type="text"
                class="field-input"
                placeholder="e.g. Mpesa code, cheque no."
              />
            </div>

            <div class="bg-[#F0F6FB] rounded-xl border border-[#D9EBF7] p-3 space-y-1.5">
              <div class="flex justify-between text-sm">
                <span class="text-slate-500 font-semibold">Amount Paid</span>
                <span class="font-bold text-emerald-600">
                  {{
                    formatCurrency(
                      form.payment_type === "none" ? 0 : form.paid_amount || 0
                    )
                  }}
                </span>
              </div>
              <div
                class="flex justify-between text-sm font-extrabold border-t border-[#D9EBF7] pt-1.5"
              >
                <span class="text-slate-700">Balance</span>
                <span :class="balance > 0 ? 'text-red-500' : 'text-emerald-600'">{{
                  formatCurrency(balance)
                }}</span>
              </div>
            </div>
          </div>

          <!-- Submit -->
          <div class="px-5 pb-5">
            <button
              @click="submitPurchase"
              :disabled="submitting"
              class="w-full flex items-center justify-center gap-2 py-3.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] disabled:opacity-60"
            >
              <svg
                v-if="submitting"
                class="animate-spin h-4 w-4 text-white"
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
              <svg
                v-else
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
              </svg>
              {{ submitting ? "Saving Purchase..." : "Save Purchase" }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from "vue";
import { useRouter } from "vue-router";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import Swal from "sweetalert2";
import PurchaseService from "@/services/purchaseService";
import SupplierService from "@/services/SupplierService";
import BranchesService from "@/services/Branchesservice";
import ProductService from "@/services/ProductService";

const router = useRouter();

/* ─── STATE ───────────────────────────── */
const formLoading = ref(false);
const submitting = ref(false);

const suppliers = ref([]);
const branches = ref([]);
const products = ref([]);

const branchItems = reactive({});
const activeBranchTab = ref(null);

const paymentTypes = [
  { label: "Full", value: "full" },
  { label: "Partial", value: "partial" },
  { label: "Invoice", value: "none" },
];

const form = reactive({
  supplier: null,
  branches: [],
  reference: "",
  purchase_date: new Date().toISOString().split("T")[0],
  notes: "",
  payment_type: "full",
  paid_amount: 0,
  payment_method: "",
  payment_reference: "",
});

const vErrors = reactive({
  supplier: false,
  branches: false,
  purchase_date: false,
  items: "",
  paid_amount: "",
  payment_method: false,
});

/* ─── ITEM HELPERS ────────────────────── */
const makeItem = () => ({
  _key: Math.random().toString(36).slice(2),
  product: null,
  quantity: 1,
  unit_price: "",
  discount: 0,
  tax: 0,
  expiry_date: "",
  batch_number: "",
  _error_product: false,
  _error_qty: false,
  _error_price: false,
});

const syncBranchItems = (newBranches) => {
  newBranches.forEach((b) => {
    if (!branchItems[b.id]) branchItems[b.id] = [makeItem()];
  });
  Object.keys(branchItems).forEach((id) => {
    if (!newBranches.find((b) => String(b.id) === String(id))) delete branchItems[id];
  });
  if (
    newBranches.length > 0 &&
    !newBranches.find((b) => b.id === activeBranchTab.value)
  ) {
    activeBranchTab.value = newBranches[0].id;
  }
  recalc();
};

const addItem = (branchId) => {
  if (!branchItems[branchId]) branchItems[branchId] = [];
  branchItems[branchId].push(makeItem());
};

const removeItem = (branchId, idx) => {
  if ((branchItems[branchId] || []).length <= 1) return;
  branchItems[branchId].splice(idx, 1);
  recalc();
};

const onProductSelect = (item) => {
  if (item.product?.purchase_price) item.unit_price = item.product.purchase_price;
  recalc();
};

/* ─── CALCULATIONS ────────────────────── */
const lineTotal = (item) => {
  const qty = Number(item.quantity) || 0;
  const price = Number(item.unit_price) || 0;
  const disc = Number(item.discount) || 0;
  const tax = Number(item.tax) || 0;
  const base = qty * price;
  const afterDiscount = base - (base * disc) / 100;
  return afterDiscount + (afterDiscount * tax) / 100;
};

const branchSubtotal = (branchId) =>
  (branchItems[branchId] || []).reduce((sum, item) => sum + lineTotal(item), 0);

const subtotal = computed(() =>
  form.branches.reduce(
    (sum, b) =>
      sum +
      (branchItems[b.id] || []).reduce((s, item) => {
        return s + (Number(item.quantity) || 0) * (Number(item.unit_price) || 0);
      }, 0),
    0
  )
);

const totalDiscount = computed(() =>
  form.branches.reduce(
    (sum, b) =>
      sum +
      (branchItems[b.id] || []).reduce((s, item) => {
        const base = (Number(item.quantity) || 0) * (Number(item.unit_price) || 0);
        return s + (base * (Number(item.discount) || 0)) / 100;
      }, 0),
    0
  )
);

const totalTax = computed(() =>
  form.branches.reduce(
    (sum, b) =>
      sum +
      (branchItems[b.id] || []).reduce((s, item) => {
        const base = (Number(item.quantity) || 0) * (Number(item.unit_price) || 0);
        const afterDiscount = base - (base * (Number(item.discount) || 0)) / 100;
        return s + (afterDiscount * (Number(item.tax) || 0)) / 100;
      }, 0),
    0
  )
);

const grandTotal = computed(() => subtotal.value - totalDiscount.value + totalTax.value);
const balance = computed(() => {
  const paid = form.payment_type === "none" ? 0 : Number(form.paid_amount) || 0;
  return grandTotal.value - paid;
});

const recalc = () => {
  if (form.payment_type === "full") form.paid_amount = grandTotal.value;
};

watch(grandTotal, (val) => {
  if (form.payment_type === "full") form.paid_amount = val;
});

watch(
  () => form.payment_type,
  (val) => {
    if (val === "full") form.paid_amount = grandTotal.value;
    if (val === "none") form.paid_amount = 0;
  }
);

/* ─── HELPERS ─────────────────────────── */
const formatCurrency = (val) =>
  new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
    minimumFractionDigits: 2,
  }).format(Number(val ?? 0));

/* ─── FETCH ───────────────────────────── */
onMounted(async () => {
  formLoading.value = true;
  try {
    const [suppliersRes, branchesRes, productsRes] = await Promise.all([
      SupplierService.getAll(),
      BranchesService.getAllBranches(),
      ProductService.getAll({ per_page: 9999 }),
    ]);
    suppliers.value =
      suppliersRes?.data?.data ?? suppliersRes?.data ?? suppliersRes ?? [];
    branches.value = branchesRes?.data?.data ?? branchesRes?.data ?? branchesRes ?? [];
    products.value = productsRes?.data?.data ?? productsRes?.data ?? productsRes ?? [];
  } catch (err) {
    console.error(err);
    Swal.fire("Error", "Could not load required data.", "error");
  } finally {
    formLoading.value = false;
  }
});

/* ─── VALIDATE ────────────────────────── */
const validate = () => {
  let valid = true;
  vErrors.supplier = !form.supplier;
  vErrors.branches = form.branches.length === 0;
  vErrors.purchase_date = !form.purchase_date;
  vErrors.items = "";
  vErrors.paid_amount = "";
  vErrors.payment_method = false;

  if (vErrors.supplier || vErrors.branches || vErrors.purchase_date) valid = false;

  let itemsValid = true;
  form.branches.forEach((b) => {
    const items = branchItems[b.id] || [];
    if (items.length === 0) {
      itemsValid = false;
      return;
    }
    items.forEach((item) => {
      item._error_product = !item.product;
      item._error_qty = !item.quantity || item.quantity <= 0;
      item._error_price =
        item.unit_price === "" ||
        item.unit_price === null ||
        item.unit_price === undefined ||
        Number(item.unit_price) <= 0;
      if (item._error_product || item._error_qty || item._error_price) itemsValid = false;
    });
  });

  if (!itemsValid) {
    vErrors.items = "Please fill all required item fields in every branch.";
    valid = false;
  }

  if (form.payment_type !== "none") {
    if (!form.paid_amount && form.paid_amount !== 0) {
      vErrors.paid_amount = "Amount paid is required";
      valid = false;
    } else if (Number(form.paid_amount) > grandTotal.value) {
      vErrors.paid_amount = "Cannot exceed grand total";
      valid = false;
    }
    if (!form.payment_method) {
      vErrors.payment_method = true;
      valid = false;
    }
  }

  return valid;
};

/* ─── SUBMIT ──────────────────────────── */
const submitPurchase = async () => {
  if (!validate()) {
    Swal.fire({
      icon: "warning",
      title: "Check your inputs",
      text: "Please fix the errors before saving.",
      confirmButtonColor: "#229DE7",
    });
    return;
  }
  submitting.value = true;
  try {
    const branchPayloads = form.branches.map((b) => ({
      branch_id: b.id,
      items: (branchItems[b.id] || []).map((item) => ({
        product_id: item.product?.id,
        quantity: item.quantity,
        unit_price: item.unit_price,
        discount: item.discount || 0,
        tax: item.tax || 0,
        expiry_date: item.expiry_date || null,
        batch_number: item.batch_number || null,
        line_total: lineTotal(item),
      })),
      subtotal: branchSubtotal(b.id),
    }));

    const payload = {
      supplier_id: form.supplier?.id,
      reference: form.reference || null,
      purchase_date: form.purchase_date,
      notes: form.notes || null,
      subtotal: subtotal.value,
      total_discount: totalDiscount.value,
      total_tax: totalTax.value,
      grand_total: grandTotal.value,
      payment_type: form.payment_type,
      paid_amount: form.payment_type === "none" ? 0 : form.paid_amount || 0,
      payment_method: form.payment_type !== "none" ? form.payment_method : null,
      payment_reference: form.payment_reference || null,
      payment_status:
        form.payment_type === "none"
          ? "unpaid"
          : form.paid_amount >= grandTotal.value
          ? "paid"
          : "partial",
      balance: balance.value,
      branches: branchPayloads,
    };

    await PurchaseService.create(payload);
    Swal.fire({
      icon: "success",
      title: "Purchase Saved!",
      text: "Purchase recorded successfully.",
      confirmButtonColor: "#229DE7",
    }).then(() => router.push({ name: "PurchasesList" }));
  } catch (err) {
    console.error(err);
    Swal.fire({
      icon: "error",
      title: "Save Failed",
      text: err?.response?.data?.message || "An error occurred.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    submitting.value = false;
  }
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
  padding: 9px 12px;
  background: #fff;
  border: 1.5px solid #d9ebf7;
  border-radius: 10px;
  font-size: 13px;
  color: #1e293b;
  outline: none;
  transition: border-color 0.15s, box-shadow 0.15s;
  font-family: inherit;
}
.field-input:focus {
  border-color: #229de7;
  box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12);
}
.field-input:read-only {
  background: #f0f6fb;
  cursor: not-allowed;
}
.field-error {
  font-size: 11px;
  font-weight: 600;
  color: #ef4444;
  margin-top: 3px;
}
</style>

<style>
.multiselect__tags {
  background: #fff !important;
  border: 1.5px solid #d9ebf7 !important;
  border-radius: 10px !important;
  padding: 5px 12px !important;
  min-height: 40px !important;
  font-size: 13px !important;
}
.ms-invalid .multiselect__tags {
  border-color: #f87171 !important;
  background: #fff1f2 !important;
}
.multiselect__tags:focus-within {
  border-color: #229de7 !important;
  box-shadow: 0 0 0 3px rgba(34, 157, 231, 0.12) !important;
}
.multiselect__tag {
  background: #229de7 !important;
  border-radius: 7px !important;
  font-size: 11.5px !important;
  font-weight: 600 !important;
}
.multiselect__tag-icon::after {
  color: #fff !important;
}
.multiselect__tag-icon:hover {
  background: #1682c4 !important;
}
.multiselect__option--highlight {
  background: #229de7 !important;
}
.multiselect__option--selected {
  background: #eaf5fd !important;
  color: #1682c4 !important;
  font-weight: 700 !important;
}
.multiselect__placeholder {
  color: #94a3b8 !important;
  font-size: 13px !important;
}
.multiselect__input {
  background: transparent !important;
  font-size: 13px !important;
}
</style>
