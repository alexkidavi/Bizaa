<template>

  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
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
        Back to Invoices
      </button>
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
            {{ isEdit ? "Edit Invoice" : "New Invoice" }}
          </h1>
          <p class="text-slate-500 mt-1 text-sm">
            {{
              isEdit
                ? "Update invoice details below."
                : "Create a new invoice for a customer."
            }}
          </p>
        </div>
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
      <!-- LEFT: Form -->
      <div class="xl:col-span-2 space-y-5">
        <!-- Invoice Header -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="px-6 py-4 border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
              Invoice Details
            </h2>
          </div>
          <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
              <label class="field-label required">Customer</label>
              <Multiselect
                v-model="form.customer"
                :options="customers"
                track-by="id"
                label="name"
                placeholder="Select customer..."
                :class="{ 'ms-invalid': vErrors.customer }"
              />
              <p v-if="vErrors.customer" class="field-error">Customer is required</p>
            </div>

            <div>
              <label class="field-label">Invoice Number</label>
              <input
                v-model="form.invoice_number"
                type="text"
                class="field-input"
                placeholder="Auto-generated if blank"
              />
            </div>
            <div>
              <label class="field-label required">Issue Date</label>
              <input
                v-model="form.issue_date"
                type="date"
                class="field-input"
                :class="{ 'border-red-400': vErrors.issue_date }"
              />
              <p v-if="vErrors.issue_date" class="field-error">Issue date is required</p>
            </div>
            <div>
              <label class="field-label required">Due Date</label>
              <input
                v-model="form.due_date"
                type="date"
                class="field-input"
                :class="{ 'border-red-400': vErrors.due_date }"
              />
              <p v-if="vErrors.due_date" class="field-error">Due date is required</p>
            </div>
            <div>
              <label class="field-label">Branch</label>
              <Multiselect
                v-model="form.branch"
                :options="branches"
                track-by="id"
                label="name"
                placeholder="Select branch..."
              />
            </div>
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
                placeholder="Payment terms, delivery info, etc..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Line Items -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div
            class="px-6 py-4 border-b border-[#EEF5FB] flex items-center justify-between"
          >
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
              Line Items
            </h2>
            <span class="text-xs font-bold text-slate-400"
              >{{ form.items.length }} item{{ form.items.length !== 1 ? "s" : "" }}</span
            >
          </div>
          <div class="p-5">
            <!-- Column headers -->
            <div
              class="hidden md:grid gap-2 mb-2 px-1 text-[10px] font-bold uppercase tracking-widest text-slate-400"
              style="grid-template-columns: 2fr 1.2fr 80px 100px 80px 80px 40px"
            >
              <span>Product / Service</span><span>Description</span><span>Qty</span
              ><span>Unit Price</span><span>Disc %</span><span>Tax %</span><span></span>
            </div>

            <div class="space-y-3">
              <div
                v-for="(item, idx) in form.items"
                :key="item._key"
                class="grid gap-2 p-3 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl items-start"
                style="grid-template-columns: 2fr 1.2fr 80px 100px 80px 80px 40px"
              >
                <div>
                  <label class="md:hidden field-label">Product / Service</label>
                  <Multiselect
                    v-model="item.product"
                    :options="products"
                    track-by="id"
                    label="name"
                    placeholder="Select..."
                    :class="{ 'ms-invalid': item._error_product }"
                    @update:modelValue="onProductSelect(item)"
                  />
                  <p v-if="item._error_product" class="field-error">Required</p>
                </div>
                <div>
                  <label class="md:hidden field-label">Description</label>
                  <input
                    v-model="item.description"
                    type="text"
                    class="field-input text-xs"
                    placeholder="Optional description..."
                  />
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
                    min="0"
                    step="any"
                    class="field-input"
                    placeholder="0.00"
                    @input="recalc"
                  />
                  <p v-if="item._error_price" class="field-error">Required</p>
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
                <div class="flex items-start justify-end pt-1">
                  <button
                    @click="removeItem(idx)"
                    :disabled="form.items.length <= 1"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-red-400 hover:bg-red-50 hover:text-red-600 transition disabled:opacity-30"
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

                <!-- Line total -->
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
              @click="addItem"
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
              Add Item
            </button>
            <p v-if="vErrors.items" class="field-error mt-2">{{ vErrors.items }}</p>
          </div>
        </div>
      </div>

      <!-- RIGHT: Summary + Payment -->
      <div class="xl:col-span-1 space-y-5">
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="px-5 py-4 border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
              Summary
            </h2>
          </div>
          <div class="p-5 space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-slate-500 font-semibold">Subtotal</span
              ><span class="font-bold text-slate-800">{{
                formatCurrency(subtotal)
              }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500 font-semibold">Total Discount</span
              ><span class="font-bold text-red-500"
                >- {{ formatCurrency(totalDiscount) }}</span
              >
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500 font-semibold">Total Tax</span
              ><span class="font-bold text-amber-600"
                >+ {{ formatCurrency(totalTax) }}</span
              >
            </div>
            <div
              class="flex justify-between text-base font-extrabold pt-2 border-t border-[#D9EBF7]"
            >
              <span class="text-slate-800">Grand Total</span
              ><span class="text-[#229DE7]">{{ formatCurrency(grandTotal) }}</span>
            </div>
          </div>

          <!-- Payment -->
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
                  class="py-2 px-1 rounded-xl text-xs font-bold border transition text-center"
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
              <p v-if="vErrors.payment_method" class="field-error">Method is required</p>
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
                placeholder="e.g. Mpesa code"
              />
            </div>

            <div class="bg-[#F0F6FB] rounded-xl border border-[#D9EBF7] p-3 space-y-1.5">
              <div class="flex justify-between text-sm">
                <span class="text-slate-500 font-semibold">Amount Paid</span
                ><span class="font-bold text-emerald-600">{{
                  formatCurrency(form.payment_type === "none" ? 0 : form.paid_amount || 0)
                }}</span>
              </div>
              <div
                class="flex justify-between text-sm font-extrabold border-t border-[#D9EBF7] pt-1.5"
              >
                <span class="text-slate-700">Balance</span
                ><span :class="balance > 0 ? 'text-red-500' : 'text-emerald-600'">{{
                  formatCurrency(balance)
                }}</span>
              </div>
            </div>
          </div>

          <div class="px-5 pb-5">
            <button
              @click="submitInvoice"
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
              {{ submitting ? "Saving..." : isEdit ? "Update Invoice" : "Save Invoice" }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import Swal from "sweetalert2";
import InvoiceService from "@/services/InvoiceService";
import CustomerService from "@/services/CustomerService";
import BranchesService from "@/services/Branchesservice";
import ProductService from "@/services/ProductService";

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);

const pageLoading = ref(true);
const submitting = ref(false);
const customers = ref([]);
const branches = ref([]);
const products = ref([]);

const paymentTypes = [
  { label: "Full", value: "full" },
  { label: "Partial", value: "partial" },
  { label: "Invoice", value: "none" },
];

const makeItem = () => ({
  _key: Math.random().toString(36).slice(2),
  product: null,
  description: "",
  quantity: 1,
  unit_price: 0,
  discount: 0,
  tax: 0,
  _error_product: false,
  _error_qty: false,
  _error_price: false,
});

const form = reactive({
  customer: null,
  invoice_number: "",
  issue_date: new Date().toISOString().split("T")[0],
  due_date: "",
  branch: null,
  notes: "",
  payment_type: "none",
  paid_amount: 0,
  payment_method: "",
  payment_reference: "",
  items: [makeItem()],
});

const vErrors = reactive({
  customer: false,
  issue_date: false,
  due_date: false,
  items: "",
  paid_amount: "",
  payment_method: false,
});

/* ─── CALCULATIONS ────────────────────── */
const lineTotal = (item) => {
  const base = (Number(item.quantity) || 0) * (Number(item.unit_price) || 0);
  const afterDisc = base - (base * (Number(item.discount) || 0)) / 100;
  return afterDisc + (afterDisc * (Number(item.tax) || 0)) / 100;
};
const subtotal = computed(() =>
  form.items.reduce(
    (s, i) => s + (Number(i.quantity) || 0) * (Number(i.unit_price) || 0),
    0
  )
);
const totalDiscount = computed(() =>
  form.items.reduce((s, i) => {
    const b = (Number(i.quantity) || 0) * (Number(i.unit_price) || 0);
    return s + (b * (Number(i.discount) || 0)) / 100;
  }, 0)
);
const totalTax = computed(() =>
  form.items.reduce((s, i) => {
    const b = (Number(i.quantity) || 0) * (Number(i.unit_price) || 0);
    const ad = b - (b * (Number(i.discount) || 0)) / 100;
    return s + (ad * (Number(i.tax) || 0)) / 100;
  }, 0)
);
const grandTotal = computed(() => subtotal.value - totalDiscount.value + totalTax.value);
const balance = computed(
  () =>
    grandTotal.value - (form.payment_type === "none" ? 0 : Number(form.paid_amount) || 0)
);

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

const recalc = () => {
  if (form.payment_type === "full") form.paid_amount = grandTotal.value;
};
const addItem = () => form.items.push(makeItem());
const removeItem = (idx) => {
  if (form.items.length > 1) form.items.splice(idx, 1);
};
const onProductSelect = (item) => {
  if (item.product?.selling_price) item.unit_price = item.product.selling_price;
  recalc();
};

const formatCurrency = (val) =>
  new Intl.NumberFormat("en-KE", {
    style: "currency",
    currency: "KES",
    minimumFractionDigits: 2,
  }).format(Number(val ?? 0));

onMounted(async () => {
  pageLoading.value = true;
  try {
    const [custRes, branchRes, prodRes] = await Promise.all([
      CustomerService.getAll(),
      BranchesService.getAllBranches(),
      ProductService.getAll(),
    ]);
    customers.value = custRes?.data?.data ?? custRes?.data ?? [];
    branches.value = branchRes?.data?.data ?? branchRes?.data ?? branchRes ?? [];
    products.value = prodRes?.data?.data ?? prodRes?.data ?? [];

    if (isEdit.value) {
      const res = await InvoiceService.getById(route.params.id);
      const inv = res?.data?.data ?? res?.data ?? {};
      form.customer = inv.customer;
      form.invoice_number = inv.invoice_number;
      form.issue_date = inv.issue_date?.split("T")[0] ?? "";
      form.due_date = inv.due_date?.split("T")[0] ?? "";
      form.branch = inv.branch;
      form.notes = inv.notes ?? "";
      form.payment_type = inv.payment_type ?? "none";
      form.paid_amount = inv.paid_amount ?? 0;
      form.payment_method = inv.payment_method ?? "";
      form.payment_reference = inv.payment_reference ?? "";
      form.items = (inv.items || []).map((i) => ({
        _key: Math.random().toString(36).slice(2),
        product: i.product,
        description: i.description ?? "",
        quantity: i.quantity,
        unit_price: i.unit_price,
        discount: i.discount,
        tax: i.tax,
        _error_product: false,
        _error_qty: false,
        _error_price: false,
      }));
    }
  } catch (err) {
    console.error(err);
  } finally {
    setTimeout(() => {
      pageLoading.value = false;
    }, 400);
  }
});

const validate = () => {
  let valid = true;
  vErrors.customer = !form.customer;
  vErrors.issue_date = !form.issue_date;
  vErrors.due_date = !form.due_date;
  vErrors.items = "";
  vErrors.paid_amount = "";
  vErrors.payment_method = false;
  if (vErrors.customer || vErrors.issue_date || vErrors.due_date) valid = false;
  let itemsOk = true;
  form.items.forEach((item) => {
    item._error_product = !item.product;
    item._error_qty = !item.quantity || item.quantity <= 0;
    item._error_price =
      item.unit_price === "" || item.unit_price === null || item.unit_price === undefined;
    if (item._error_product || item._error_qty || item._error_price) itemsOk = false;
  });
  if (!itemsOk) {
    vErrors.items = "Fill all required item fields.";
    valid = false;
  }
  if (form.payment_type !== "none") {
    if (Number(form.paid_amount) > grandTotal.value) {
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

const submitInvoice = async () => {
  if (!validate()) {
    Swal.fire({
      icon: "warning",
      title: "Check your inputs",
      confirmButtonColor: "#229DE7",
    });
    return;
  }
  submitting.value = true;
  try {
    const payload = {
      customer_id: form.customer?.id,
      branch_id: form.branch?.id ?? null,
      invoice_number: form.invoice_number || null,
      issue_date: form.issue_date,
      due_date: form.due_date,
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
      items: form.items.map((i) => ({
        product_id: i.product?.id,
        description: i.description || null,
        quantity: i.quantity,
        unit_price: i.unit_price,
        discount: i.discount || 0,
        tax: i.tax || 0,
        line_total: lineTotal(i),
      })),
    };
    if (isEdit.value) {
      await InvoiceService.update(route.params.id, payload);
      Swal.fire({
        icon: "success",
        title: "Invoice Updated",
        confirmButtonColor: "#229DE7",
      }).then(() => router.push({ name: "InvoicesList" }));
    } else {
      await InvoiceService.create(payload);
      Swal.fire({
        icon: "success",
        title: "Invoice Created",
        confirmButtonColor: "#229DE7",
      }).then(() => router.push({ name: "InvoicesList" }));
    }
  } catch (err) {
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
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
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
  background: #f5faff;
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
  background: #f5faff !important;
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
