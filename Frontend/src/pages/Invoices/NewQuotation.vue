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
        Back to Quotations
      </button>
      <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
            {{ isEdit ? "Edit Quotation" : "New Quotation" }}
          </h1>
          <p class="text-slate-500 mt-1 text-sm">
            {{
              isEdit
                ? "Update quotation details."
                : "Create a new quotation for a customer."
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
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- LEFT: Form -->
      <div class="xl:col-span-2 space-y-5">
        <!-- Header -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="px-6 py-4 border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">
              Quotation Details
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
              <label class="field-label">Quotation Number</label>
              <input
                v-model="form.quotation_number"
                type="text"
                class="field-input"
                placeholder="Auto-generated if blank"
              />
            </div>
            <div>
              <label class="field-label required">Status</label>
              <select v-model="form.status" class="field-input">
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="accepted">Accepted</option>
                <option value="rejected">Rejected</option>
              </select>
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
              <label class="field-label required">Expiry Date</label>
              <input
                v-model="form.expiry_date"
                type="date"
                class="field-input"
                :class="{ 'border-red-400': vErrors.expiry_date }"
              />
              <p v-if="vErrors.expiry_date" class="field-error">
                Expiry date is required
              </p>
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
            <div>
              <label class="field-label">Currency</label>
              <select v-model="form.currency" class="field-input">
                <option value="KES">KES — Kenyan Shilling</option>
                <option value="USD">USD — US Dollar</option>
                <option value="EUR">EUR — Euro</option>
              </select>
            </div>
            <div class="sm:col-span-2">
              <label class="field-label"
                >Terms & Notes
                <span class="text-slate-400 font-normal text-[11px]"
                  >(Optional)</span
                ></label
              >
              <textarea
                v-model="form.notes"
                rows="3"
                class="field-input resize-none"
                placeholder="Payment terms, validity period, special conditions..."
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
                    placeholder="Optional..."
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

      <!-- RIGHT: Summary -->
      <div class="xl:col-span-1">
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

          <!-- Convert to Invoice shortcut (edit mode, accepted status) -->
          <div
            v-if="isEdit && form.status === 'accepted'"
            class="mx-5 mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-xl"
          >
            <p class="text-xs font-bold text-emerald-700 mb-2">✓ Quotation Accepted</p>
            <button
              @click="goConvert"
              class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition"
            >
              Convert to Invoice →
            </button>
          </div>

          <div class="px-5 pb-5">
            <button
              @click="submitQuotation"
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
              {{
                submitting ? "Saving..." : isEdit ? "Update Quotation" : "Save Quotation"
              }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import Swal from "sweetalert2";
import QuotationService from "@/services/QuotationService";
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

const makeItem = () => ({
  _key: Math.random().toString(36).slice(2),
  product: null,
  description: "",
  quantity: 1,
  unit_price: 0,
  discount: 0,
  tax: 0,
  _error_product: false,
});

const form = reactive({
  customer: null,
  quotation_number: "",
  status: "draft",
  issue_date: new Date().toISOString().split("T")[0],
  expiry_date: "",
  branch: null,
  currency: "KES",
  notes: "",
  items: [makeItem()],
});

const vErrors = reactive({
  customer: false,
  issue_date: false,
  expiry_date: false,
  items: "",
});

const lineTotal = (item) => {
  const b = (Number(item.quantity) || 0) * (Number(item.unit_price) || 0);
  const ad = b - (b * (Number(item.discount) || 0)) / 100;
  return ad + (ad * (Number(item.tax) || 0)) / 100;
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

const recalc = () => {};
const addItem = () => form.items.push(makeItem());
const removeItem = (idx) => {
  if (form.items.length > 1) form.items.splice(idx, 1);
};
const onProductSelect = (item) => {
  if (item.product?.selling_price) item.unit_price = item.product.selling_price;
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
    branches.value = branchRes?.data?.data ?? branchRes ?? [];
    products.value = prodRes?.data?.data ?? prodRes?.data ?? [];
    if (isEdit.value) {
      const res = await QuotationService.getById(route.params.id);
      const q = res?.data?.data ?? res?.data ?? {};
      form.customer = q.customer;
      form.quotation_number = q.quotation_number;
      form.status = q.status;
      form.issue_date = q.issue_date?.split("T")[0] ?? "";
      form.expiry_date = q.expiry_date?.split("T")[0] ?? "";
      form.branch = q.branch;
      form.currency = q.currency ?? "KES";
      form.notes = q.notes ?? "";
      form.items = (q.items || []).map((i) => ({
        _key: Math.random().toString(36).slice(2),
        product: i.product,
        description: i.description ?? "",
        quantity: i.quantity,
        unit_price: i.unit_price,
        discount: i.discount,
        tax: i.tax,
        _error_product: false,
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
  vErrors.expiry_date = !form.expiry_date;
  vErrors.items = "";
  if (vErrors.customer || vErrors.issue_date || vErrors.expiry_date) valid = false;
  let itemsOk = true;
  form.items.forEach((item) => {
    item._error_product = !item.product;
    if (item._error_product) itemsOk = false;
  });
  if (!itemsOk) {
    vErrors.items = "All items must have a product selected.";
    valid = false;
  }
  return valid;
};

const submitQuotation = async () => {
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
      quotation_number: form.quotation_number || null,
      status: form.status,
      issue_date: form.issue_date,
      expiry_date: form.expiry_date,
      currency: form.currency,
      notes: form.notes || null,
      subtotal: subtotal.value,
      total_discount: totalDiscount.value,
      total_tax: totalTax.value,
      grand_total: grandTotal.value,
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
      await QuotationService.update(route.params.id, payload);
      Swal.fire({
        icon: "success",
        title: "Quotation Updated",
        confirmButtonColor: "#229DE7",
      }).then(() => router.push({ name: "QuotationsList" }));
    } else {
      await QuotationService.create(payload);
      Swal.fire({
        icon: "success",
        title: "Quotation Created",
        confirmButtonColor: "#229DE7",
      }).then(() => router.push({ name: "QuotationsList" }));
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

const goConvert = async () => {
  const result = await Swal.fire({
    title: "Convert to Invoice?",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    confirmButtonText: "Convert",
  });
  if (!result.isConfirmed) return;
  try {
    const res = await QuotationService.convertToInvoice(route.params.id);
    const invoice = res?.data?.data ?? res?.data;
    Swal.fire({
      icon: "success",
      title: "Converted!",
      confirmButtonColor: "#229DE7",
    }).then(() => router.push({ name: "ViewInvoice", params: { id: invoice.id } }));
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Conversion Failed",
      text: err?.response?.data?.message || "Error.",
      confirmButtonColor: "#229DE7",
    });
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
.field-error {
  font-size: 11px;
  font-weight: 600;
  color: #ef4444;
  margin-top: 3px;
}
</style>
