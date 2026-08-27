<template>
  <div class="pos-root" @keydown.esc="closeAllModals">
    <PosTopBar
      :branches="branches"
      :model-branch="activeBranch"
      :cashier-name="cashierName"
      :current-time="currentTime"
      :held-count="heldOrders.length"
      @update:model-branch="onBranchChange"
      @open-held="showHeldOrders = true"
      @close-pos="$router.push('/dashboard')"
      @navigate="handleNavigation"
    />
    <div class="pos-body">
      <!-- Mobile tab switcher -->
      <div class="mobile-tabs">
        <button
          @click="mobileTab = 'products'"
          class="mobile-tab"
          :class="{ active: mobileTab === 'products' }"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M4 6h16M4 10h16M4 14h16M4 18h16"
            />
          </svg>
          Products
        </button>
        <button
          @click="mobileTab = 'cart'"
          class="mobile-tab"
          :class="{ active: mobileTab === 'cart' }"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
            />
          </svg>
          Cart
          <span v-if="cart.length > 0" class="cart-badge">{{ cart.length }}</span>
        </button>
      </div>

      <ProductPanel
        ref="productPanel"
        class="pos-panel"
        :class="{ 'mobile-hidden': mobileTab !== 'products' }"
        :products="products"
        :loading="productsLoading"
        :categories="categories"
        :active-cat="activeCat"
        :active-cat-name="activeCatName"
        :total-products="totalProducts"
        :current-page="productPage"
        :total-pages="totalProductPages"
        @add-to-cart="onAddToCart"
        @add-first="addFirstResult"
        @set-category="setCategory"
        @search-change="onSearchChange"
        @page-change="onPageChange"
        @focus-search="focusSearch"
      />

      <CartPanel
        class="pos-panel"
        :class="{ 'mobile-hidden': mobileTab !== 'cart' }"
        :cart="cart"
        :sell-by-price="sellByPrice"
        :selected-customer="selectedCustomer"
        :order-ref="orderRef"
        :subtotal="subtotal"
        :discount-amount="discountAmount"
        :tax-amount="taxAmount"
        :grand-total="grandTotal"
        :discount-type="discountType"
        :global-discount="globalDiscount"
        :apply-tax="applyTax"
        :selected-payment="selectedPayment"
        :cash-tendered="cashTendered"
        :change="change"
        :processing="processing"
        :held-count="heldOrders.length"
        @open-customer="showCustomerModal = true"
        @new-order="newOrder"
        @hold-order="holdOrder"
        @remove="removeFromCart"
        @increase-qty="increaseQty"
        @decrease-qty="decreaseQty"
        @set-qty="setQty"
        @toggle-discount="toggleItemDiscount"
        @set-item-discount="setItemDiscount"
        @set-item-note="setItemNote"
        @update:discount-type="discountType = $event"
        @update:global-discount="globalDiscount = $event"
        @update:apply-tax="applyTax = $event"
        @update:selected-payment="selectedPayment = $event"
        @update:cash-tendered="
          cashTendered = $event;
          calcChange();
        "
        @process-payment="processPayment"
        @print-quote="printQuote"
        @open-held="showHeldOrders = true"
        @clear-cart="clearCart"
        @toggle-sell-by-price="sellByPrice = !sellByPrice"
      />
    </div>

    <PosModals
      ref="posModalsRef"
      :show-customer="showCustomerModal"
      :show-held="showHeldOrders"
      :show-receipt="showReceipt"
      :selected-customer="selectedCustomer"
      :customers="customers"
      :held-orders="heldOrders"
      :receipt-data="receiptData"
      :time-remaining="timeRemaining"
      @close-customer="showCustomerModal = false"
      @select-customer="selectCustomer"
      @close-held="showHeldOrders = false"
      @resume-order="resumeOrder"
      @delete-held="deleteHeldOrder"
      @close-receipt="closeReceipt"
      @print-receipt="printReceipt"
      @email-receipt="emailReceipt"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import SalesService from "@/services/SaleService";
import ProductService from "@/services/ProductService";
import CustomerService from "@/services/CustomerService";
import CategoryService from "@/services/categoryService";
import BranchesService from "@/services/Branchesservice";

import PosTopBar from "./pos-components/PosTopBar.vue";
import ProductPanel from "./pos-components/ProductPanel.vue";
import CartPanel from "./pos-components/CartPanel.vue";
import PosModals from "./pos-components/PosModals.vue";
import { useHeldOrders } from "./pos-components/useHeldOrders.js";

const router = useRouter();

/* ── MOBILE TAB ──────────────────────── */
const mobileTab = ref("products"); // 'products' | 'cart'

/* ── TOP BAR ─────────────────────────── */
const branches = ref([]);
const activeBranch = ref("");
const cashierName = ref("Demo Account");
const currentTime = ref("");
let clockTimer = null;
const handleNavigation = (page) => {
  const routes = {
    dashboard: "/dashboard",
    sales: "/sales/list",
    payments: "/reports/transactions",
    customers: "/customers/list",
  };

  if (routes[page]) {
    router.push(routes[page]);
  }
};
const activeBranchName = computed(
  () =>
    branches.value.find((b) => String(b.id) === String(activeBranch.value))?.name ||
    "Main Branch"
);
const updateClock = () => {
  currentTime.value = new Date().toLocaleTimeString("en-KE", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
  });
};
const onBranchChange = async (id) => {
  if (String(id) === String(activeBranch.value)) return;

  // Cart lines are a snapshot of the branch they were added under
  // (product.current_stock, pricing) — carrying them across a branch
  // switch would silently show stale stock/prices for the new branch.
  // Clear (with confirmation) rather than leave them stale.
  if (cart.value.length > 0) {
    const r = await Swal.fire({
      title: "Switch branch?",
      text: "Your cart has items priced and stocked for the current branch. Switching branches will clear the cart so stock and pricing stay accurate.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Switch & Clear Cart",
      confirmButtonColor: "#229DE7",
      cancelButtonColor: "#94a3b8",
    });
    if (!r.isConfirmed) return; // activeBranch unchanged — the <select> snaps back via its reactive :value binding
    cart.value = [];
    globalDiscount.value = 0;
  }

  activeBranch.value = id;
  productPage.value = 1;
  fetchProducts();
};

/* ── PRODUCTS ────────────────────────── */
const products = ref([]);
const productsLoading = ref(false);
const productSearch = ref("");
const categories = ref([]);
const activeCat = ref(null);
const productPage = ref(1);
const productPerPage = 20;
const totalProducts = ref(0);
const productPanel = ref(null);

const totalProductPages = computed(() =>
  Math.max(1, Math.ceil(totalProducts.value / productPerPage))
);
const activeCatName = computed(
  () => categories.value.find((c) => c.id === activeCat.value)?.name || ""
);
const hasActiveQuery = computed(
  () => productSearch.value.trim().length > 0 || activeCat.value !== null
);

const fetchProducts = async () => {
  if (!hasActiveQuery.value) {
    products.value = [];
    totalProducts.value = 0;
    return;
  }
  productsLoading.value = true;
  try {
    const params = { per_page: productPerPage, page: productPage.value };
    if (activeBranch.value) params.branch_id = activeBranch.value;
    if (productSearch.value.trim()) params.search = productSearch.value.trim();
    if (activeCat.value !== null) params.category_id = activeCat.value;
    const res = await ProductService.getAll(params);
    const data = res.data?.data || res.data || res;
    products.value = Array.isArray(data) ? data : [];
    totalProducts.value =
      res.data?.total || res.data?.meta?.total || products.value.length;
  } catch {
    products.value = [];
    totalProducts.value = 0;
  } finally {
    productsLoading.value = false;
  }
};

const onSearchChange = (query) => {
  productSearch.value = query;
  productPage.value = 1;
  fetchProducts();
};
const setCategory = (cat) => {
  const newId = cat ? cat.id : null;
  if (activeCat.value === newId) {
    activeCat.value = null;
    products.value = [];
    totalProducts.value = 0;
    return;
  }
  activeCat.value = newId;
  productPage.value = 1;
  fetchProducts();
};
const onPageChange = (page) => {
  productPage.value = page;
  fetchProducts();
};
const focusSearch = () => productPanel.value?.focusInput();
const addFirstResult = () => {
  if (products.value.length > 0 && products.value[0].current_stock > 0) {
    addToCart(products.value[0]);
    productPanel.value?.clearSearch();
    productSearch.value = "";
    if (activeCat.value === null) {
      products.value = [];
      totalProducts.value = 0;
    } else fetchProducts();
  }
};

/* ── SELL-BY-PRICE MODE ──────────────── */
const sellByPrice = ref(false);

/* ── CART ────────────────────────────── */
const cart = ref([]);
const orderRef = ref(generateOrderRef());
const selectedCustomer = ref(null);

function generateOrderRef() {
  return "ORD-" + Date.now().toString().slice(-6);
}

const addToCart = async (product) => {
  if (sellByPrice.value) {
    const { value: amountStr, isConfirmed } = await Swal.fire({
      title: "Enter Amount (KES)",
      html: `<div style="font-size:13px;color:#64748b;margin-bottom:8px"><b>${product.name}</b><br>Unit price: KES ${Number(product.selling_price).toLocaleString()}</div>`,
      input: "number",
      inputAttributes: { min: "1", step: "any", placeholder: "e.g. 50" },
      inputLabel: "Amount to sell for",
      showCancelButton: true,
      confirmButtonText: "Add to Cart",
      confirmButtonColor: "#f97316",
      cancelButtonColor: "#94a3b8",
      inputValidator: (v) => (!v || Number(v) <= 0 ? "Enter a valid amount" : null),
    });
    if (!isConfirmed || !amountStr) return;
    const amount = parseFloat(amountStr);
    const qty = product.selling_price > 0 ? amount / product.selling_price : 1;
    if (qty > Number(product.current_stock ?? Infinity)) {
      stockLimitReached(product);
      return;
    }
    const item = {
      ...product,
      qty,
      enteredPrice: amount,
      discount: 0,
      note: "",
      cart_key: Date.now() + Math.random(),
      _new: false,
      _showDiscount: false,
    };
    cart.value.push(item);
    flash(item);
    return;
  }

  const existing = cart.value.find((i) => i.id === product.id && !i.enteredPrice);
  const limit = Number(product.current_stock ?? Infinity);
  if (existing) {
    if (existing.qty + 1 > limit) {
      stockLimitReached(product);
      return;
    }
    existing.qty++;
    flash(existing);
    return;
  }
  if (limit < 1) {
    stockLimitReached(product);
    return;
  }
  const item = {
    ...product,
    qty: 1,
    enteredPrice: null,
    discount: 0,
    note: "",
    cart_key: Date.now() + Math.random(),
    _new: false,
    _showDiscount: false,
  };
  cart.value.push(item);
  flash(item);
};
const flash = (item) => {
  item._new = true;
  setTimeout(() => {
    item._new = false;
  }, 600);
};

// Client-side check only, for immediate feedback — the backend
// (createPosSale, under a row lock) is the real source of truth and
// re-validates against the actual database stock at checkout regardless.
const stockLimitReached = (product) => {
  Swal.fire({
    icon: "warning",
    title: "Stock limit reached",
    text: `Only ${Number(product.current_stock ?? 0)} unit(s) of ${product.name} are available.`,
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 2200,
    timerProgressBar: true,
  });
};

// On mobile, switch to cart tab after adding a product
const onAddToCart = (product) => {
  addToCart(product);
  if (window.innerWidth < 768) mobileTab.value = "cart";
};
const removeFromCart = (index) => cart.value.splice(index, 1);
const clearCart = () => {
  cart.value = [];
  globalDiscount.value = 0;
};
const increaseQty = (index) => {
  const i = cart.value[index];
  if (i.enteredPrice != null) return; // sell-by-price lines are fixed
  const limit = Number(i.current_stock ?? Infinity);
  if (i.qty + 1 > limit) {
    stockLimitReached(i);
    return;
  }
  i.qty++;
};
const decreaseQty = (index) => {
  const i = cart.value[index];
  if (i.enteredPrice != null) return; // sell-by-price lines: delete only
  if (i.qty > 1) i.qty--;
  else removeFromCart(index);
};
const setQty = (index, val) => {
  const i = cart.value[index];
  if (i.enteredPrice != null) return;
  const q = parseFloat(val) || 1;
  const limit = Number(i.current_stock ?? Infinity);
  if (q > limit) {
    stockLimitReached(i);
    i.qty = Math.max(0.001, limit);
    return;
  }
  i.qty = Math.max(0.001, q);
};
const toggleItemDiscount = (index) => {
  cart.value[index]._showDiscount = !cart.value[index]._showDiscount;
};
const setItemDiscount = (index, val) => {
  cart.value[index].discount = Math.max(0, Math.min(100, val));
};
const setItemNote = (index, val) => {
  cart.value[index].note = val;
};

/* ── TOTALS ──────────────────────────── */
const globalDiscount = ref(0);
const discountType = ref("percent");
const applyTax = ref(false);

const lineTotal = (item) => {
  if (item.enteredPrice != null) return item.enteredPrice;
  return Math.round(item.selling_price * item.qty * (1 - (item.discount || 0) / 100));
};
const subtotal = computed(() => cart.value.reduce((s, i) => s + lineTotal(i), 0));
const discountAmount = computed(() =>
  discountType.value === "percent"
    ? Math.round(subtotal.value * (globalDiscount.value / 100))
    : Math.min(globalDiscount.value, subtotal.value)
);
const afterDiscount = computed(() => subtotal.value - discountAmount.value);
const taxAmount = computed(() =>
  applyTax.value ? Math.round(afterDiscount.value * 0.16) : 0
);
const grandTotal = computed(() => afterDiscount.value + taxAmount.value);

/* ── PAYMENT ─────────────────────────── */
const selectedPayment = ref("cash");
const cashTendered = ref(0);
const change = ref(0);
const processing = ref(false);

const calcChange = () => {
  change.value = Math.max(0, cashTendered.value - grandTotal.value);
};

const processPayment = async (mpesaMeta = null) => {
  if (cart.value.length === 0) return;
  if (selectedPayment.value === "cash" && cashTendered.value < grandTotal.value) {
    Swal.fire({
      icon: "warning",
      title: "Insufficient Cash",
      text: "Cash tendered is less than the total amount.",
      confirmButtonColor: "#229DE7",
    });
    return;
  }
  processing.value = true;
  try {
    const payload = {
      branch_id: activeBranch.value,
      customer_id: selectedCustomer.value?.id || null,
      reference: orderRef.value,
      payment_method: selectedPayment.value,
      items: cart.value.map((i) => ({
        product_id: i.id,
        quantity: i.qty,
        unit_price: i.selling_price,
        discount: i.discount || 0,
        note: i.note || "",
        ...(i.enteredPrice != null ? { sub_total: i.enteredPrice } : {}),
      })),
      subtotal: subtotal.value,
      discount: discountAmount.value,
      tax: taxAmount.value,
      total: grandTotal.value,
      cash_tendered: cashTendered.value,
      change: change.value,
      // Include M-Pesa transaction details if provided
      ...(mpesaMeta && {
        mpesa_code: mpesaMeta.mpesa_meta?.code,
        mpesa_phone: mpesaMeta.mpesa_meta?.phone,
        mpesa_method: mpesaMeta.mpesa_meta?.method,
      }),
    };
    const res = await SalesService.create(payload);
    const createdSale = res?.data?.data ?? res?.data ?? {};
    receiptData.value = {
      ref: orderRef.value,
      date: new Date().toLocaleString("en-KE"),
      branch: activeBranchName.value,
      cashier: cashierName.value,
      items: [...cart.value],
      subtotal: subtotal.value,
      discount: discountAmount.value,
      tax: taxAmount.value,
      total: grandTotal.value,
      paid: grandTotal.value,
      change: 0,
      paymentMethod: mpesaMeta
        ? `M-Pesa${mpesaMeta.mpesa_meta?.code ? " · " + mpesaMeta.mpesa_meta.code : ""}`
        : {
            cash: "Cash",
            mpesa: "M-Pesa",
            card: "Card",
            credit: "Credit",
            split: "Split",
          }[selectedPayment.value] || "Cash",
      customer: selectedCustomer.value?.name || null,
      // DigiTax/eTIMS: etims_url only arrives once KRA finishes processing
      // (delivered later via callback); offline_url is available right away
      // and is what the receipt QR shows until etims_url upgrades it.
      etimsUrl: createdSale.etims_url || null,
      offlineUrl: createdSale.offline_url || null,
      kraSerial: createdSale.kra_serial_number || null,
    };
    showReceipt.value = true;
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Sale Failed",
      text: err?.response?.data?.message || "Something went wrong.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    processing.value = false;
  }
};

/* ── HOLD / RESUME ───────────────────── */
const {
  heldOrders,
  holdOrder: _holdOrder,
  resumeOrder: _resumeOrder,
  deleteHeldOrder,
  timeRemaining,
} = useHeldOrders();

const showHeldOrders = ref(false);

const holdOrder = () => {
  if (cart.value.length === 0) return;
  _holdOrder({
    ref: orderRef.value,
    customer: selectedCustomer.value,
    items: [...cart.value],
    subtotal: subtotal.value,
    total: grandTotal.value,
  });
  Swal.fire({
    icon: "success",
    title: "Order On Hold",
    text: `Order ${orderRef.value} has been held for 6 hours.`,
    timer: 1800,
    showConfirmButton: false,
  });
  newOrder();
};

const resumeOrder = (index) => {
  if (cart.value.length > 0) holdOrder(); // hold current cart first
  const order = _resumeOrder(index);
  cart.value = [...order.items];
  selectedCustomer.value = order.customer;
  orderRef.value = order.ref;
  showHeldOrders.value = false;
};
const newOrder = () => {
  cart.value = [];
  selectedCustomer.value = null;
  orderRef.value = generateOrderRef();
  cashTendered.value = 0;
  change.value = 0;
  globalDiscount.value = 0;
  selectedPayment.value = "cash";
};

/* ── RECEIPT ─────────────────────────── */
const showReceipt = ref(false);
const receiptData = ref(null);
const closeReceipt = () => {
  showReceipt.value = false;
  newOrder();
  if (hasActiveQuery.value) fetchProducts();
};
const emailReceipt = () =>
  Swal.fire({
    icon: "info",
    title: "Email Receipt",
    text: "Coming soon.",
    confirmButtonColor: "#229DE7",
  });
const posModalsRef = ref(null);
const printReceipt = () => posModalsRef.value?.printReceipt();
const printQuote = () => window.print();

/* ── CUSTOMERS ───────────────────────── */
const showCustomerModal = ref(false);
const customers = ref([]);
const selectCustomer = (c) => {
  selectedCustomer.value = c;
  showCustomerModal.value = false;
};

/* ── LIFECYCLE ───────────────────────── */
const closeAllModals = () => {
  showCustomerModal.value = false;
  showHeldOrders.value = false;
};
const handleKeyboard = (e) => {
  if (e.key === "F2") {
    e.preventDefault();
    focusSearch();
  }
};

onMounted(async () => {
  updateClock();
  clockTimer = setInterval(updateClock, 1000);
  window.addEventListener("keydown", handleKeyboard);
  try {
    const [branchRes, catRes, custRes] = await Promise.all([
      BranchesService.getAllBranches(),
      CategoryService.getAll(),
      CustomerService.getAll().catch(() => ({ data: [] })),
    ]);
    const rawBranches = branchRes.data ?? branchRes;
    branches.value = Array.isArray(rawBranches) ? rawBranches : rawBranches?.data ?? [];
    if (branches.value.length > 0) activeBranch.value = branches.value[0].id;
    const rawCats = catRes.data?.data || catRes.data || catRes;
    categories.value = Array.isArray(rawCats) ? rawCats : [];
    const rawCust = custRes.data?.data || custRes.data || custRes;
    customers.value = Array.isArray(rawCust) ? rawCust : [];
  } catch {
    /* silently continue */
  }
  focusSearch();
});

onBeforeUnmount(() => {
  clearInterval(clockTimer);
  window.removeEventListener("keydown", handleKeyboard);
});
</script>

<style scoped>
.pos-root {
  display: flex;
  flex-direction: column;
  height: 100vh;
  background: #f0f6fb;
  font-family: "Segoe UI", system-ui, sans-serif;
  overflow: hidden;
}

/* ── Mobile tab bar — only visible on small screens ── */
.mobile-tabs {
  display: none;
  flex-shrink: 0;
}

/* ── Body ── */
.pos-body {
  display: grid;
  grid-template-columns: 1fr 380px;
  grid-template-rows: 1fr;
  flex: 1;
  min-height: 0;
  overflow: hidden;
}

/* On desktop panels are always visible */
.pos-panel {
  display: flex;
  flex-direction: column;
  min-height: 0;
}

/* ── Tablet: narrow the cart ── */
@media (max-width: 1024px) {
  .pos-body {
    grid-template-columns: 1fr 320px;
  }
}

/* ── Mobile: stack panels, show tab bar ── */
@media (max-width: 767px) {
  .pos-body {
    display: flex;
    flex-direction: column;
    position: relative;
  }

  .mobile-tabs {
    display: flex;
    background: white;
    border-bottom: 1px solid #eef5fb;
    flex-shrink: 0;
  }

  .mobile-tab {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 8px;
    font-size: 13px;
    font-weight: 700;
    color: #94a3b8;
    border-bottom: 2px solid transparent;
    transition: all 0.15s;
    background: none;
    position: relative;
  }
  .mobile-tab:hover {
    color: #229de7;
  }
  .mobile-tab.active {
    color: #229de7;
    border-bottom-color: #229de7;
    background: #f5faff;
  }

  .cart-badge {
    position: absolute;
    top: 6px;
    right: calc(50% - 28px);
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #ef4444;
    color: white;
    font-size: 10px;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* Each panel takes full height of the body */
  .pos-panel {
    position: absolute;
    inset: 0;
    transition: opacity 0.2s, transform 0.2s;
  }

  .mobile-hidden {
    opacity: 0;
    pointer-events: none;
    transform: translateX(8px);
  }

  .mobile-hidden.pos-panel:first-of-type {
    transform: translateX(-8px);
  }
}
</style>
