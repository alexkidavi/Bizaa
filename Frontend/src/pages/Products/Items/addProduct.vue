<template>
  <div class="min-h-screen bg-[#F0F6FB] font-sans text-slate-900">
    <div class="px-6 pt-6 pb-10 max-w-6xl mx-auto">
      <!-- BACK -->
      <button
        @click="router.back()"
        class="cursor-pointer flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-[#229DE7] transition mb-5 group"
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
        Back to Products
      </button>

      <!-- HEADER -->
      <div class="flex items-center justify-between mb-7">
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
            {{ isEdit ? "Edit Product" : "Add Product" }}
          </h1>
          <p class="text-slate-500 mt-1 text-sm">
            {{
              isEdit
                ? "Update product details, pricing, and inventory."
                : "Set up a new product for your inventory."
            }}
          </p>
        </div>
        <div v-if="isEdit && form.name" class="hidden sm:flex items-center gap-3">
          <div
            class="w-10 h-10 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-lg"
          >
            {{ form.name.charAt(0).toUpperCase() }}
          </div>
          <div>
            <div class="font-extrabold text-sm text-slate-900">{{ form.name }}</div>
            <div class="font-mono text-xs text-slate-400">{{ form.product_code }}</div>
          </div>
        </div>
      </div>

      <!-- LOADING SKELETON -->
      <div v-if="pageLoading" class="space-y-4">
        <div
          v-for="n in 4"
          :key="n"
          class="h-40 bg-white rounded-2xl animate-pulse border border-[#D9EBF7]"
        ></div>
      </div>

      <!-- FORM -->
      <form v-else @submit.prevent="submitProduct" class="space-y-5">
        <!-- ── SECTION: BASIC INFORMATION ── -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="p-6">
            <h2
              class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4] mb-5"
            >
              Basic Information
            </h2>
            <div class="grid md:grid-cols-3 gap-5">
              <div>
                <label class="field-label required">Product Name</label>
                <input
                  v-model="form.name"
                  class="field-input"
                  :class="{ 'border-red-400 focus:border-red-500': errors.name }"
                  placeholder="e.g. Coca Cola 500ml"
                />
                <p v-if="errors.name" class="field-error">{{ errors.name }}</p>
              </div>

              <div>
                <label class="field-label required">Product Code</label>
                <div class="flex gap-2">
                  <input
                    v-model="form.product_code"
                    class="field-input flex-1"
                    :class="{ 'border-red-400': errors.product_code }"
                    placeholder="e.g. PR001234"
                  />
                  <button
                    type="button"
                    @click="generateProductCode"
                    class="cursor-pointer w-10 h-10 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] text-[#229DE7] flex items-center justify-center hover:bg-[#D9EBF7] transition flex-shrink-0"
                    title="Generate Code"
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
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                      />
                    </svg>
                  </button>
                </div>
                <p v-if="errors.product_code" class="field-error">
                  {{ errors.product_code }}
                </p>
              </div>

              <div>
                <label class="field-label"
                  >Barcode
                  <span class="text-slate-400 font-normal text-[11px]"
                    >(Optional)</span
                  ></label
                >
                <input
                  v-model="form.barcode"
                  class="field-input"
                  placeholder="Scan or enter barcode"
                />
              </div>

              <div>
                <label class="field-label required">Category</label>
                <select
                  v-model="form.category_id"
                  class="field-input"
                  :class="{ 'border-red-400': errors.category_id }"
                >
                  <option value="">Select category</option>
                  <option v-for="c in categories" :key="c.id" :value="c.id">
                    {{ c.name }}
                  </option>
                </select>
                <p v-if="errors.category_id" class="field-error">
                  {{ errors.category_id }}
                </p>
              </div>

              <div>
                <label class="field-label"
                  >Brand
                  <span class="text-slate-400 font-normal text-[11px]"
                    >(Optional)</span
                  ></label
                >
                <select v-model="form.brand_id" class="field-input">
                  <option value="">Select brand</option>
                  <option v-for="b in brands" :key="b.id" :value="b.id">
                    {{ b.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="field-label required">Unit</label>
                <select
                  v-model="form.unit_id"
                  class="field-input"
                  :class="{ 'border-red-400': errors.unit_id }"
                >
                  <option value="">Select unit</option>
                  <option v-for="u in units" :key="u.id" :value="u.id">
                    {{ u.name }}
                  </option>
                </select>
                <p v-if="errors.unit_id" class="field-error">{{ errors.unit_id }}</p>
              </div>

              <div>
                <label class="field-label"
                  >Color
                  <span class="text-slate-400 font-normal text-[11px]"
                    >(Optional)</span
                  ></label
                >
                <select v-model="form.color" class="field-input">
                  <option value="">Select color</option>
                  <option v-for="c in colors" :key="c" :value="c">{{ c }}</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- ── SECTION: PRICING ── -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="p-6">
            <h2
              class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4] mb-5"
            >
              Pricing
            </h2>

            <!-- Live margin preview -->
            <div
              v-if="form.buying_price > 0 && form.selling_price > 0"
              class="mb-5 flex items-center gap-3 p-3 bg-[#EAF5FD] border border-[#B3DEFA] rounded-xl"
            >
              <svg
                class="w-4 h-4 text-[#229DE7] flex-shrink-0"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                />
              </svg>
              <span class="text-xs font-bold text-[#1682C4]">
                Margin: {{ computedMargin }}% &nbsp;·&nbsp; Profit per unit: KES
                {{
                  computedProfit.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                  })
                }}
              </span>
            </div>

            <div class="grid md:grid-cols-4 gap-5">
              <div>
                <label class="field-label required">Buying Price (KES)</label>
                <input
                  type="number"
                  step="0.01"
                  v-model.number="form.buying_price"
                  class="field-input"
                  :class="{ 'border-red-400': errors.buying_price }"
                  min="0"
                  placeholder="0.00"
                />
                <p v-if="errors.buying_price" class="field-error">
                  {{ errors.buying_price }}
                </p>
              </div>

              <div>
                <label class="field-label">Profit %</label>
                <input
                  type="number"
                  step="0.01"
                  v-model.number="profitPercent"
                  class="field-input"
                  min="0"
                  placeholder="0"
                />
                <p class="text-[10.5px] text-slate-400 mt-1 font-semibold">
                  Auto-calculates selling price
                </p>
              </div>

              <div>
                <label class="field-label required">Selling Price (KES)</label>
                <input
                  type="number"
                  step="0.01"
                  v-model.number="form.selling_price"
                  class="field-input"
                  :class="{ 'border-red-400': errors.selling_price }"
                  min="0"
                  placeholder="0.00"
                />
                <p v-if="errors.selling_price" class="field-error">
                  {{ errors.selling_price }}
                </p>
              </div>

              <div>
                <label class="field-label"
                  >Wholesale Price
                  <span class="text-slate-400 font-normal text-[11px]"
                    >(Optional)</span
                  ></label
                >
                <input
                  type="number"
                  step="0.01"
                  v-model.number="form.wholesale_price"
                  class="field-input"
                  min="0"
                  placeholder="0.00"
                />
              </div>

              <div>
                <label class="field-label">Tax Type</label>
                <select v-model="form.tax_type" class="field-input">
                  <option value="vat">VAT</option>
                  <option value="non_vat">Non VAT</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- ── SECTION: KRA eTIMS (DIGITAX) ── -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="p-6">
            <h2
              class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4] mb-1"
            >
              KRA eTIMS (DigiTax)
              <span
                class="text-slate-400 font-normal text-[11px] normal-case tracking-normal"
                >(Optional)</span
              >
            </h2>
            <p class="text-xs text-slate-400 mb-5">
              Only used if KRA eTIMS is enabled under Settings → KRA eTIMS. Leave blank to
              skip tax compliance registration for this product.
            </p>
            <div class="grid md:grid-cols-3 gap-5">
              <div>
                <label class="field-label">Item Classification Code</label>
                <div class="relative">
                  <input
                    v-model="form.item_class_code"
                    class="field-input pr-11"
                    placeholder="e.g. 99010000"
                  />
                  <button
                    type="button"
                    @click="showItemClassPicker = true"
                    title="Browse KRA classification codes"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 w-7 h-7 rounded-lg bg-[#EAF5FD] border border-[#B3DEFA] text-[#229DE7] flex items-center justify-center hover:bg-[#D9EBF7] transition"
                  >
                    <svg
                      class="w-3.5 h-3.5"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2.2"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"
                      />
                    </svg>
                  </button>
                </div>
                <p class="text-[10.5px] text-slate-400 mt-1.5">
                  Click the icon to search the official KRA classification table, or type
                  a code directly.
                </p>
              </div>

              <div>
                <label class="field-label">Item Type</label>
                <select v-model="form.item_type_code" class="field-input">
                  <option value="1">Raw Material</option>
                  <option value="2">Finished Product</option>
                  <option value="3">Service</option>
                </select>
              </div>

              <div>
                <label class="field-label">eTIMS Tax Rate</label>
                <select v-model="form.tax_type_code" class="field-input">
                  <option value="">
                    Use Tax Type above (VAT → 16%, Non VAT → Non-VAT)
                  </option>
                  <option value="A">A — Exempt</option>
                  <option value="B">B — 16%</option>
                  <option value="C">C — Zero Rated (0%)</option>
                  <option value="D">D — Non-VAT</option>
                  <option value="E">E — 8%</option>
                </select>
              </div>

              <div>
                <label class="field-label">Packaging Unit Code</label>
                <select v-model="form.package_unit_code" class="field-input">
                  <option value="">Select packaging unit</option>
                  <option v-for="u in packageUnits" :key="u.code" :value="u.code">
                    {{ u.code }} — {{ u.label }}
                  </option>
                </select>
              </div>

              <div>
                <label class="field-label">Quantity Unit Code</label>
                <select v-model="form.quantity_unit_code" class="field-input">
                  <option value="">Select quantity unit</option>
                  <option v-for="u in quantityUnits" :key="u.code" :value="u.code">
                    {{ u.code }} — {{ u.label }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- ── SECTION: INVENTORY ── -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="p-6">
            <h2
              class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4] mb-5"
            >
              Inventory & Branches
            </h2>
            <div class="grid md:grid-cols-4 gap-5 mb-5">
              <div>
                <label class="field-label"
                  >Opening Stock
                  <span class="text-slate-400 font-normal text-[11px]"
                    >(Optional)</span
                  ></label
                >
                <input
                  type="number"
                  step="0.01"
                  v-model.number="form.opening_stock"
                  class="field-input"
                  min="0"
                  placeholder="0"
                />
              </div>

              <div>
                <label class="field-label"
                  >Reorder Level
                  <span class="text-slate-400 font-normal text-[11px]"
                    >(Optional)</span
                  ></label
                >
                <input
                  type="number"
                  v-model.number="form.reorder_level"
                  class="field-input"
                  min="0"
                  placeholder="10"
                />
              </div>

              <div class="md:col-span-2">
                <label class="field-label required">Branches</label>
                <Multiselect
                  v-model="form.branch_ids"
                  :options="branches"
                  :multiple="true"
                  track-by="id"
                  label="name"
                  placeholder="Select branches"
                  :class="{ 'multiselect--error': errors.branch_ids }"
                />
                <p v-if="errors.branch_ids" class="field-error">
                  {{ errors.branch_ids }}
                </p>
              </div>
            </div>

            <div class="flex gap-6">
              <label class="flex items-center gap-2.5 cursor-pointer group">
                <div
                  class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all"
                  :class="
                    form.for_sale
                      ? 'bg-[#229DE7] border-[#229DE7]'
                      : 'border-slate-300 group-hover:border-[#B3DEFA]'
                  "
                  @click="form.for_sale = !form.for_sale"
                >
                  <svg
                    v-if="form.for_sale"
                    class="w-3 h-3 text-white"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="3"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </div>
                <span class="text-sm font-semibold text-slate-700">For Sale</span>
              </label>

              <label class="flex items-center gap-2.5 cursor-pointer group">
                <div
                  class="w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all"
                  :class="
                    form.for_purchase
                      ? 'bg-[#229DE7] border-[#229DE7]'
                      : 'border-slate-300 group-hover:border-[#B3DEFA]'
                  "
                  @click="form.for_purchase = !form.for_purchase"
                >
                  <svg
                    v-if="form.for_purchase"
                    class="w-3 h-3 text-white"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="3"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </div>
                <span class="text-sm font-semibold text-slate-700">For Purchase</span>
              </label>
            </div>
          </div>
        </div>

        <!-- ── SECTION: IMAGE ── -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="p-6">
            <h2
              class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4] mb-5"
            >
              Product Image
              <span
                class="text-slate-400 font-normal text-[11px] normal-case tracking-normal"
                >(Optional)</span
              >
            </h2>

            <div class="flex flex-col sm:flex-row gap-5 items-start">
              <!-- Drop zone -->
              <div
                class="flex-1 border-2 border-dashed border-[#B3DEFA] rounded-2xl p-8 text-center cursor-pointer hover:border-[#229DE7] hover:bg-[#F5FAFF] transition group"
                @dragover.prevent
                @drop.prevent="handleDrop"
                @click="fileInput.click()"
              >
                <div
                  class="w-12 h-12 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform"
                >
                  <svg
                    class="w-6 h-6 text-[#229DE7]"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M8 8l4-4 4 4M12 4v12"
                    />
                  </svg>
                </div>
                <p class="text-sm font-bold text-slate-700">
                  Drag & drop or click to upload
                </p>
                <p class="text-xs text-slate-400 mt-1">PNG, JPG, WEBP up to 5MB</p>
                <input
                  type="file"
                  ref="fileInput"
                  class="hidden"
                  accept="image/*"
                  @change="handleImage"
                />
              </div>

              <!-- Preview -->
              <div v-if="form.imagePreview" class="relative flex-shrink-0">
                <img
                  :src="form.imagePreview"
                  class="w-32 h-32 object-cover rounded-2xl border-2 border-[#B3DEFA] shadow-sm"
                />
                <button
                  type="button"
                  @click="clearImage"
                  class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-red-500 text-white flex items-center justify-center shadow-md hover:bg-red-600 transition"
                >
                  <svg
                    class="w-3 h-3"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="3"
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
            </div>
          </div>
        </div>

        <!-- ── SECTION: VARIANTS ── -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="p-6">
            <div class="flex items-center justify-between mb-5">
              <div>
                <h2
                  class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]"
                >
                  Variants
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                  Add sizes, colors, or other variants
                </p>
              </div>
              <button
                type="button"
                @click="addVariant"
                class="cursor-pointer flex items-center gap-2 px-4 py-2 bg-[#EAF5FD] border border-[#B3DEFA] text-[#229DE7] text-sm font-bold rounded-xl hover:bg-[#D9EBF7] transition"
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
                    d="M12 5v14M5 12h14"
                  />
                </svg>
                Add Variant
              </button>
            </div>

            <div
              v-if="form.variants.length === 0"
              class="py-10 text-center border-2 border-dashed border-[#D9EBF7] rounded-xl"
            >
              <p class="text-sm text-slate-400 font-semibold">
                No variants yet. Click "Add Variant" to get started.
              </p>
            </div>

            <div class="space-y-3">
              <div
                v-for="(v, i) in form.variants"
                :key="i"
                class="border border-[#D9EBF7] rounded-xl p-4 bg-[#F5FAFF] relative group"
              >
                <button
                  type="button"
                  @click="removeVariant(i)"
                  class="cursor-pointer absolute top-3 right-3 w-7 h-7 rounded-lg bg-white border border-red-200 text-red-400 flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-50 transition"
                >
                  <svg
                    class="w-3.5 h-3.5"
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

                <div class="grid md:grid-cols-5 gap-4">
                  <div>
                    <label class="field-label required">Name</label>
                    <input
                      v-model="v.name"
                      class="field-input"
                      placeholder="e.g. Large"
                    />
                  </div>
                  <div>
                    <label class="field-label required">SKU</label>
                    <div class="flex gap-2">
                      <input
                        v-model="v.code"
                        class="field-input flex-1"
                        placeholder="SKU-001"
                      />
                      <button
                        type="button"
                        @click="generateVariantCode(i)"
                        class="cursor-pointer w-10 h-10 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] text-[#229DE7] flex items-center justify-center hover:bg-[#D9EBF7] transition flex-shrink-0"
                      >
                        <svg
                          class="w-3.5 h-3.5"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="2.2"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                          />
                        </svg>
                      </button>
                    </div>
                  </div>
                  <div>
                    <label class="field-label required">Buying Price</label>
                    <input
                      type="number"
                      step="0.01"
                      v-model.number="v.buying_price"
                      class="field-input"
                      min="0"
                      placeholder="0.00"
                    />
                  </div>
                  <div>
                    <label class="field-label required">Selling Price</label>
                    <input
                      type="number"
                      step="0.01"
                      v-model.number="v.selling_price"
                      class="field-input"
                      min="0"
                      placeholder="0.00"
                    />
                  </div>
                  <div>
                    <label class="field-label required">Stock</label>
                    <input
                      type="number"
                      step="0.01"
                      v-model.number="v.stock"
                      class="field-input"
                      min="0"
                      placeholder="0"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ── SUBMIT ── -->
        <div class="flex gap-3 pt-1">
          <button
            type="button"
            @click="router.back()"
            class="px-6 py-3 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#B3DEFA] hover:text-[#229DE7] hover:bg-[#EAF5FD] transition shadow-sm"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="cursor-pointer flex-1 flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.99] disabled:opacity-60"
          >
            <svg
              v-if="loading"
              class="w-4 h-4 animate-spin"
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
            {{ loading ? "Saving..." : isEdit ? "Update Product" : "Create Product" }}
          </button>
        </div>
      </form>
    </div>

    <ItemClassPickerModal
      v-if="showItemClassPicker"
      @close="showItemClassPicker = false"
      @select="(code) => (form.item_class_code = code)"
    />
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import Swal from "sweetalert2";
import Multiselect from "vue-multiselect";
import ItemClassPickerModal from "./ItemClassPickerModal.vue";

import ProductService from "@/services/ProductService";
import CategoryService from "@/services/categoryService";
import BrandService from "@/services/brandService";
import UnitService from "@/services/unitService";
import BranchesService from "@/services/Branchesservice";
import DigitaxService from "@/services/DigitaxService";

const router = useRouter();
const route = useRoute();

/* ─── MODE ──────────────────────────────── */
const isEdit = computed(() => !!route.params.id);
const productId = computed(() => route.params.id);

/* ─── CONSTANTS ─────────────────────────── */
const colors = [
  "Red",
  "Blue",
  "Green",
  "Black",
  "White",
  "Yellow",
  "Orange",
  "Purple",
  "Pink",
  "Grey",
];

/* ─── DEFAULT FORM ──────────────────────── */
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
  reorder_level: 10,
  for_sale: true,
  for_purchase: true,
  image: null,
  imagePreview: null,
  variants: [],
  item_class_code: "",
  item_type_code: "2",
  tax_type_code: "",
  package_unit_code: "",
  quantity_unit_code: "",
});

/* ─── STATE ─────────────────────────────── */
const form = ref(getDefaultForm());
const errors = ref({});
const loading = ref(false);
const pageLoading = ref(false);
const profitPercent = ref(0);

const categories = ref([]);
const brands = ref([]);
const units = ref([]);
const branches = ref([]);
const packageUnits = ref([]);
const quantityUnits = ref([]);
const showItemClassPicker = ref(false);

/* ─── COMPUTED MARGIN ───────────────────── */
const computedMargin = computed(() => {
  const sell = Number(form.value.selling_price || 0);
  const buy = Number(form.value.buying_price || 0);
  if (sell <= 0) return 0;
  return Math.round(((sell - buy) / sell) * 100);
});

const computedProfit = computed(() => {
  return Math.max(
    0,
    Number(form.value.selling_price || 0) - Number(form.value.buying_price || 0)
  );
});

/* ─── LOAD DROPDOWNS ────────────────────── */
const loadDropdowns = async () => {
  try {
    const [
      catRes,
      brandRes,
      unitRes,
      branchRes,
      packageUnitRes,
      quantityUnitRes,
    ] = await Promise.all([
      CategoryService.getAll(),
      BrandService.getAll(),
      UnitService.getAll(),
      BranchesService.getAllBranches(),
      DigitaxService.getPackageUnits(),
      DigitaxService.getQuantityUnits(),
    ]);
    categories.value = catRes?.data || [];
    brands.value = brandRes?.data || [];
    units.value = unitRes?.data || [];
    branches.value = branchRes || [];
    packageUnits.value = packageUnitRes?.data || [];
    quantityUnits.value = quantityUnitRes?.data || [];
  } catch (err) {
    console.error("Failed to load dropdown data", err);
  }
};

/* ─── LOAD PRODUCT FOR EDIT ─────────────── */
const loadProduct = async () => {
  if (!isEdit.value) return;
  pageLoading.value = true;
  try {
    const res = await ProductService.getById(productId.value);
    const p = res.data;

    form.value = {
      name: p.name || "",
      product_code: p.product_code || "",
      barcode: p.barcode || "",
      category_id: p.category_id || "",
      brand_id: p.brand_id || "",
      unit_id: p.unit_id || "",
      color: p.color || "",
      branch_ids: p.branches || [], // expects array of {id, name} objects
      buying_price: Number(p.buying_price) || 0,
      selling_price: Number(p.selling_price) || 0,
      tax_type: p.tax_type || "vat",
      wholesale_price: Number(p.wholesale_price) || 0,
      opening_stock: Number(p.opening_stock) || 0,
      reorder_level: Number(p.reorder_level) || 10,
      for_sale: p.for_sale ?? true,
      for_purchase: p.for_purchase ?? true,
      image: null,
      imagePreview: p.image_url || null,
      item_class_code: p.item_class_code || "",
      item_type_code: p.item_type_code || "2",
      tax_type_code: p.tax_type_code || "",
      package_unit_code: p.package_unit_code || "",
      quantity_unit_code: p.quantity_unit_code || "",
      variants: (p.variants || []).map((v) => ({
        id: v.id,
        name: v.name,
        code: v.code,
        buying_price: Number(v.buying_price),
        selling_price: Number(v.selling_price),
        stock: Number(v.stock),
      })),
    };

    // Sync profit percent
    if (form.value.buying_price > 0) {
      profitPercent.value = Math.round(
        ((form.value.selling_price - form.value.buying_price) / form.value.buying_price) *
          100
      );
    }
  } catch (err) {
    console.error("Failed to load product", err);
    await Swal.fire("Error", "Could not load product details.", "error");
    router.push("/products/list");
  } finally {
    pageLoading.value = false;
  }
};

onMounted(async () => {
  await loadDropdowns();
  await loadProduct();
});

/* ─── PROFIT % → SELLING PRICE ──────────── */
watch([profitPercent, () => form.value.buying_price], () => {
  const buy = Number(form.value.buying_price || 0);
  const profit = Number(profitPercent.value || 0);
  if (buy > 0 && profit > 0) {
    form.value.selling_price = +(buy + (buy * profit) / 100).toFixed(2);
  }
});

/* ─── IMAGE HANDLING ────────────────────── */
const fileInput = ref(null);

const setImage = (file) => {
  if (!file) return;
  form.value.image = file;
  form.value.imagePreview = URL.createObjectURL(file);
};

const handleImage = (e) => setImage(e.target.files[0]);
const handleDrop = (e) => setImage(e.dataTransfer.files[0]);
const clearImage = () => {
  form.value.image = null;
  form.value.imagePreview = null;
  if (fileInput.value) fileInput.value.value = null;
};

/* ─── CODE GENERATION ───────────────────── */
const generateProductCode = () => {
  form.value.product_code = "PR" + Date.now().toString().slice(-6);
};

const generateVariantCode = (i) => {
  form.value.variants[i].code = "VR" + Date.now().toString().slice(-6);
};

/* ─── VARIANTS ──────────────────────────── */
const addVariant = () => {
  form.value.variants.push({
    name: "",
    code: "",
    buying_price: 0,
    selling_price: 0,
    stock: 0,
  });
};

const removeVariant = (i) => {
  form.value.variants.splice(i, 1);
};

/* ─── VALIDATION ────────────────────────── */
const validate = () => {
  const e = {};
  if (!form.value.name) e.name = "Product name is required";
  if (!form.value.product_code) e.product_code = "Product code is required";
  if (!form.value.category_id) e.category_id = "Category is required";
  if (!form.value.unit_id) e.unit_id = "Unit is required";
  if (!form.value.branch_ids?.length) e.branch_ids = "Select at least one branch";
  if (!form.value.buying_price || form.value.buying_price <= 0)
    e.buying_price = "Enter a valid buying price";
  if (!form.value.selling_price || form.value.selling_price <= 0)
    e.selling_price = "Enter a valid selling price";
  errors.value = e;
  return Object.keys(e).length === 0;
};

/* ─── SUBMIT ────────────────────────────── */
const submitProduct = async () => {
  if (!validate()) {
    await Swal.fire({
      icon: "error",
      title: "Validation Error",
      text: "Please fix the highlighted fields before saving.",
      confirmButtonColor: "#229DE7",
    });
    return;
  }

  loading.value = true;
  try {
    const payload = {
      ...form.value,
      branch_ids: (form.value.branch_ids || [])
        .map((b) => (typeof b === "object" ? b.id : b))
        .map(Number)
        .filter((id) => !isNaN(id)),
      buying_price: Number(form.value.buying_price),
      selling_price: Number(form.value.selling_price),
      wholesale_price: Number(form.value.wholesale_price),
      opening_stock: Number(form.value.opening_stock),
      variants: (form.value.variants || []).map((v) => ({
        ...(v.id ? { id: v.id } : {}),
        name: v.name,
        code: v.code,
        buying_price: Number(v.buying_price),
        selling_price: Number(v.selling_price),
        stock: Number(v.stock),
      })),
    };

    if (isEdit.value) {
      await ProductService.update(productId.value, payload);
      await Swal.fire({
        icon: "success",
        title: "Product Updated",
        text: "Changes have been saved successfully.",
        confirmButtonColor: "#229DE7",
      });
    } else {
      await ProductService.create(payload);
      await Swal.fire({
        icon: "success",
        title: "Product Created",
        text: "New product has been added to inventory.",
        confirmButtonColor: "#229DE7",
      });
      form.value = getDefaultForm();
      profitPercent.value = 0;
      errors.value = {};
    }

    router.push("/products/list");
  } catch (err) {
    console.error(err);
    await Swal.fire({
      icon: "error",
      title: "Save Failed",
      text: err?.response?.data?.message || "Something went wrong. Please try again.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.field-label {
  display: block;
  font-size: 11.5px;
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

/* Align vue-multiselect to theme */
:deep(.multiselect__tags) {
  background: #f5faff;
  border: 1.5px solid #d9ebf7;
  border-radius: 12px;
  padding: 6px 14px;
  min-height: 42px;
  font-size: 13.5px;
}
:deep(.multiselect--error .multiselect__tags) {
  border-color: #f87171;
}
:deep(.multiselect__tag) {
  background: #229de7;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
}
:deep(.multiselect__tag-icon::after) {
  color: #fff;
}
:deep(.multiselect__tag-icon:hover) {
  background: #1682c4;
}
:deep(.multiselect__option--highlight) {
  background: #229de7;
}
:deep(.multiselect__option--selected) {
  background: #eaf5fd;
  color: #1682c4;
  font-weight: 700;
}
:deep(.multiselect__input) {
  background: transparent;
  font-size: 13.5px;
}
</style>
