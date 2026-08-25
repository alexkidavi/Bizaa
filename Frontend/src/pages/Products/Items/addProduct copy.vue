<template>
  <div class="p-6 bg-slate-50 min-h-screen">
    <!-- HEADER -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-slate-800">Add Product</h1>
      <p class="text-sm text-slate-500">Professional POS Product Setup</p>
    </div>

    <form
      @submit.prevent="submitProduct"
      class="bg-white rounded-2xl shadow-sm p-6 space-y-10"
    >
      <!-- BASIC -->
      <section class="section">
        <h2 class="section-title">Basic Information</h2>

        <div class="grid md:grid-cols-3 gap-6">
          <div>
            <label class="label required">Product Name</label>
            <input v-model="form.name" class="input" />
            <p v-if="errors.name" class="error-text">{{ errors.name }}</p>
          </div>

          <div>
            <label class="label required">Product Code</label>
            <div class="flex gap-2">
              <input v-model="form.product_code" class="input" />
              <button type="button" @click="generateProductCode" class="icon-btn">
                🔄
              </button>
            </div>
            <p v-if="errors.product_code" class="error-text">{{ errors.product_code }}</p>
          </div>

          <div>
            <label class="label">
              Barcode <span class="optional">(Optional)</span>
            </label>
            <input v-model="form.barcode" class="input" />
          </div>

          <!-- CATEGORY -->
          <div>
            <label class="label required">Category</label>
            <select v-model="form.category_id" class="input">
              <option value="">Select</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">
                {{ c.name }}
              </option>
            </select>
            <p v-if="errors.category_id" class="error-text">{{ errors.category_id }}</p>
          </div>

          <!-- BRAND -->
          <div>
            <label class="label"> Brand <span class="optional">(Optional)</span> </label>
            <select v-model="form.brand_id" class="input">
              <option value="">Select</option>
              <option v-for="b in brands" :key="b.id" :value="b.id">
                {{ b.name }}
              </option>
            </select>
          </div>

          <!-- UNIT -->
          <div>
            <label class="label required">Unit</label>
            <select v-model="form.unit_id" class="input">
              <option value="">Select</option>
              <option v-for="u in units" :key="u.id" :value="u.id">
                {{ u.name }}
              </option>
            </select>
            <p v-if="errors.unit_id" class="error-text">{{ errors.unit_id }}</p>
          </div>

          <!-- COLOR -->
          <div>
            <label class="label"> Color <span class="optional">(Optional)</span> </label>
            <select v-model="form.color" class="input">
              <option value="">Select</option>
              <option v-for="c in colors" :key="c" :value="c">{{ c }}</option>
            </select>
          </div>
        </div>
      </section>

      <!-- PRICING -->
      <section class="section">
        <h2 class="section-title">Pricing</h2>

        <div class="grid md:grid-cols-4 gap-6">
          <div>
            <label class="label required">Buying Price</label>
            <input type="number" v-model.number="form.buying_price" class="input" />
            <p v-if="errors.buying_price" class="error-text">{{ errors.buying_price }}</p>
          </div>

          <div>
            <label class="label">Profit %</label>
            <input type="number" v-model.number="profitPercent" class="input" />
          </div>

          <div>
            <label class="label required">Selling Price</label>
            <input type="number" v-model.number="form.selling_price" class="input" />
            <p v-if="errors.selling_price" class="error-text">
              {{ errors.selling_price }}
            </p>
          </div>

          <div>
            <label class="label">Tax Type</label>
            <select v-model="form.tax_type" class="input">
              <option value="vat">VAT</option>
              <option value="non_vat">Non VAT</option>
            </select>
          </div>
        </div>
      </section>

      <!-- INVENTORY -->
      <section class="section">
        <h2 class="section-title">Inventory & Branches</h2>

        <div class="grid md:grid-cols-4 gap-6">
          <div>
            <label class="label"
              >Opening Stock <span class="optional">(Optional)</span></label
            >
            <input type="number" v-model.number="form.opening_stock" class="input" />
          </div>

          <div>
            <label class="label"
              >Reorder Level <span class="optional">(Optional)</span></label
            >
            <input
              type="number"
              v-model.number="form.reorder_level"
              class="input"
              value="10"
            />
          </div>

          <div>
            <label class="label"
              >Wholesale Price <span class="optional">(Optional)</span></label
            >
            <input type="number" v-model.number="form.wholesale_price" class="input" />
          </div>

          <div>
            <label class="label required">Branches</label>
            <Multiselect
              v-model="form.branch_ids"
              :options="branches"
              :multiple="true"
              track-by="id"
              label="name"
            />
            <p v-if="errors.branch_ids" class="error-text">{{ errors.branch_ids }}</p>
          </div>
        </div>

        <div class="flex gap-8 mt-4">
          <label class="checkbox">
            <input type="checkbox" v-model="form.for_sale" />
            <span>For Sale</span>
          </label>

          <label class="checkbox">
            <input type="checkbox" v-model="form.for_purchase" />
            <span>For Purchase</span>
          </label>
        </div>
      </section>

      <!-- IMAGE -->
      <section>
        <label class="label"
          >Product Image <span class="optional">(Optional)</span></label
        >

        <div
          class="upload-box"
          @dragover.prevent
          @drop.prevent="handleDrop"
          @click="fileInput.click()"
        >
          <p>Drag & Drop or Click to Upload</p>
          <input type="file" ref="fileInput" class="hidden" @change="handleImage" />
        </div>

        <img v-if="form.imagePreview" :src="form.imagePreview" class="preview" />
      </section>

      <!-- VARIANTS -->
      <section class="section">
        <div class="flex justify-between items-center">
          <h2 class="section-title">Variants</h2>
          <button type="button" @click="addVariant" class="add-btn">+ Add Variant</button>
        </div>

        <div v-for="(v, i) in form.variants" :key="i" class="variant-row">
          <div class="grid md:grid-cols-5 gap-4">
            <div>
              <label class="label required">Name</label>
              <input v-model="v.name" class="input" />
            </div>

            <div>
              <label class="label required">SKU</label>
              <div class="flex gap-2">
                <input v-model="v.code" class="input" />
                <button type="button" @click="generateVariantCode(i)" class="icon-btn">
                  🔄
                </button>
              </div>
            </div>

            <div>
              <label class="label required">Buying Price</label>
              <input type="number" v-model.number="v.buying_price" class="input" />
            </div>

            <div>
              <label class="label required">Selling Price</label>
              <input type="number" v-model.number="v.selling_price" class="input" />
            </div>

            <div>
              <label class="label required">Stock</label>
              <input type="number" v-model.number="v.stock" class="input" />
            </div>
          </div>
        </div>
      </section>

      <!-- SUBMIT -->
      <button class="submit-btn" :disabled="loading">
        {{ loading ? "Saving..." : "Save Product" }}
      </button>
    </form>
  </div>
</template>
<script setup>
import { ref, watch, onMounted } from "vue";
import Swal from "sweetalert2";
import Multiselect from "vue-multiselect";

import ProductService from "@/services/ProductService";
import CategoryService from "@/services/CategoryService";
import BrandService from "@/services/brandService";
import UnitService from "@/services/UnitService";
import BranchesService from "@/services/Branchesservice";

/* ------------------------
   CONSTANTS
------------------------ */
const colors = ["Red", "Blue", "Green", "Black", "White"];

/* ------------------------
   DEFAULT FORM
------------------------ */
const getDefaultForm = () => ({
  name: "",
  product_code: "",
  barcode: "",
  category_id: "",
  brand_id: "",
  unit_id: "",
  color: "",
  branch_ids: [],
  buying_price: 0,
  selling_price: 0,
  tax_type: "vat",
  wholesale_price: 0,
  opening_stock: 0,
  reorder_level: 0,
  for_sale: true,
  for_purchase: true,
  image: null,
  imagePreview: null,
  variants: [],
});

/* ------------------------
   STATE
------------------------ */
const form = ref(getDefaultForm());
const errors = ref({});
const loading = ref(false);
const profitPercent = ref(0);

const categories = ref([]);
const brands = ref([]);
const units = ref([]);
const branches = ref([]);

/* ------------------------
   LOAD DATA
------------------------ */
onMounted(async () => {
  try {
    const [catRes, brandRes, unitRes, branchRes] = await Promise.all([
      CategoryService.getAll(),
      BrandService.getAll(),
      UnitService.getAll(),
      BranchesService.getAllBranches(),
    ]);

    categories.value = catRes?.data || [];
    brands.value = brandRes?.data || [];
    units.value = unitRes?.data || [];
    branches.value = branchRes || [];
  } catch (err) {
    console.error("Failed to load dropdown data", err);
  }
});

/* ------------------------
   PROFIT CALCULATION
------------------------ */
watch([profitPercent, () => form.value.buying_price], () => {
  const buy = Number(form.value.buying_price || 0);
  const profit = Number(profitPercent.value || 0);

  if (buy > 0) {
    form.value.selling_price = +(buy + (buy * profit) / 100).toFixed(2);
  }
});

/* ------------------------
   IMAGE HANDLING
------------------------ */
const fileInput = ref(null);

const setImage = (file) => {
  if (!file) return;
  form.value.image = file;
  form.value.imagePreview = URL.createObjectURL(file);
};

const handleImage = (e) => setImage(e.target.files[0]);
const handleDrop = (e) => setImage(e.dataTransfer.files[0]);

/* ------------------------
   SKU GENERATION
------------------------ */
const generateProductCode = () => {
  form.value.product_code = "PR" + Date.now().toString().slice(-6);
};

const generateVariantCode = (i) => {
  form.value.variants[i].code = "PR" + Date.now().toString().slice(-6);
};

/* ------------------------
   VARIANTS
------------------------ */
const addVariant = () => {
  form.value.variants.push({
    name: "",
    code: "",
    buying_price: 0,
    selling_price: 0,
    stock: 0,
  });
};

/* ------------------------
   VALIDATION
------------------------ */
const validate = () => {
  const e = {};
  let ok = true;

  if (!form.value.name) (e.name = "Required"), (ok = false);
  if (!form.value.product_code) (e.product_code = "Required"), (ok = false);
  if (!form.value.category_id) (e.category_id = "Required"), (ok = false);
  if (!form.value.unit_id) (e.unit_id = "Required"), (ok = false);
  if (!form.value.branch_ids.length) (e.branch_ids = "Required"), (ok = false);

  errors.value = e;
  return ok;
};

/* ------------------------
   RESET FORM
------------------------ */
const resetForm = () => {
  form.value = getDefaultForm();
  profitPercent.value = 0;
  errors.value = {};

  if (fileInput.value) {
    fileInput.value.value = null;
  }
};

/* ------------------------
   SUBMIT
------------------------ */
const submitProduct = async () => {
  loading.value = true;

  try {
    if (!validate()) {
      await Swal.fire("Error", "Fix validation errors", "error");
      return;
    }

    const payload = {
      ...form.value,

      branch_ids: (form.value.branch_ids || [])
        .map((b) => (typeof b === "object" ? b.id : b))
        .map(Number)
        .filter((id) => !isNaN(id)),

      buying_price: Number(form.value.buying_price),
      selling_price: Number(form.value.selling_price),

      variants: (form.value.variants || []).map((v) => ({
        name: v.name,
        code: v.code,
        buying_price: Number(v.buying_price),
        selling_price: Number(v.selling_price),
        stock: Number(v.stock),
      })),
    };

    await ProductService.create(payload);

    await Swal.fire("Success", "Product created successfully", "success");

    resetForm();
  } catch (err) {
    console.error(err);
    await Swal.fire("Error", "Failed to create product", "error");
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.label {
  font-weight: 600;
  font-size: 13px;
}
.required::after {
  content: " *";
  color: red;
}
.optional {
  color: #56be78;
  font-size: 12px;
}
.input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 8px;
}
.section {
  border-bottom: 1px solid #eee;
  padding-bottom: 12px;
}
.section-title {
  font-weight: 600;
}
.submit-btn {
  width: 100%;
  padding: 12px;
  background: #229de7;
  color: #fff;
}
.variant-row {
  border: 1px solid #eee;
  padding: 10px;
  border-radius: 10px;
}
.upload-box {
  border: 1px dashed #ccc;
  padding: 20px;
  text-align: center;
  cursor: pointer;
}
.preview {
  height: 100px;
  margin-top: 10px;
  border-radius: 8px;
}
.icon-btn {
  background: #e0f2fe;
  padding: 5px;
  border-radius: 6px;
}
.add-btn {
  color: #229de7;
  font-weight: bold;
}
</style>
