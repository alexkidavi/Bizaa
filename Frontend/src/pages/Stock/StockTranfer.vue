<template>
  <div class="p-4 md:p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">
          Stock Transfer
        </h1>
        <p class="text-slate-500 mt-1 text-sm">
          Move stock between branches with a full transfer record.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <div
          class="px-4 py-2.5 bg-white border border-[#D9EBF7] rounded-xl shadow-sm text-xs font-bold text-slate-500"
        >
          Ref: <span class="text-[#229DE7] font-mono">{{ referenceNumber }}</span>
        </div>
        <div
          class="px-3 py-2 rounded-xl text-xs font-bold border"
          :class="
            transferLines.length > 0
              ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
              : 'bg-slate-50 text-slate-400 border-slate-200'
          "
        >
          {{ transferLines.length }} item{{ transferLines.length !== 1 ? "s" : "" }} added
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <!-- ── LEFT: FORM ── -->
      <div class="lg:col-span-5 space-y-5">
        <!-- Branch Selection Card -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
            <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#1682C4]">
              Transfer Route
            </h2>
            <p class="text-xs text-slate-400 font-semibold mt-0.5">
              Select source and destination branches
            </p>
          </div>

          <!-- Skeleton while branches load -->
          <div v-if="branchesLoading" class="p-6 space-y-3">
            <div
              v-for="n in 4"
              :key="n"
              class="h-11 bg-slate-50 animate-pulse rounded-xl"
            ></div>
          </div>

          <div v-else class="p-6 space-y-4">
            <!-- FROM Branch -->
            <div>
              <label class="field-label required">From Branch</label>
              <select
                v-model="form.from_branch_id"
                class="field-input"
                :class="{ 'border-red-400': vErrors.from_branch_id }"
                @change="onFromBranchChange"
              >
                <option value="">Select source branch</option>
                <option
                  v-for="b in branches"
                  :key="b.id"
                  :value="b.id"
                  :disabled="b.id === form.to_branch_id"
                >
                  {{ b.name }}
                </option>
              </select>
              <p v-if="vErrors.from_branch_id" class="field-error">
                Source branch is required
              </p>
            </div>

            <!-- SWAP -->
            <div class="flex items-center justify-center">
              <button
                type="button"
                @click="swapBranches"
                :disabled="!form.from_branch_id || !form.to_branch_id"
                class="w-9 h-9 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] hover:bg-[#D9EBF7] transition disabled:opacity-40 disabled:cursor-not-allowed"
                title="Swap branches"
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
                    d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"
                  />
                </svg>
              </button>
            </div>

            <!-- TO Branch -->
            <div>
              <label class="field-label required">To Branch</label>
              <select
                v-model="form.to_branch_id"
                class="field-input"
                :class="{ 'border-red-400': vErrors.to_branch_id }"
              >
                <option value="">Select destination branch</option>
                <option
                  v-for="b in branches"
                  :key="b.id"
                  :value="b.id"
                  :disabled="b.id === form.from_branch_id"
                >
                  {{ b.name }}
                </option>
              </select>
              <p v-if="vErrors.to_branch_id" class="field-error">
                Destination branch is required
              </p>
              <p v-if="vErrors.same_branch" class="field-error">
                Source and destination cannot be the same
              </p>
            </div>

            <!-- Transfer Date -->
            <div>
              <label class="field-label required">Transfer Date</label>
              <input
                type="date"
                v-model="form.transfer_date"
                class="field-input"
                :class="{ 'border-red-400': vErrors.transfer_date }"
              />
              <p v-if="vErrors.transfer_date" class="field-error">Date is required</p>
            </div>

            <!-- Notes -->
            <div>
              <label class="field-label"
                >Notes
                <span class="text-slate-400 font-normal text-[11px]"
                  >(Optional)</span
                ></label
              >
              <textarea
                v-model="form.notes"
                rows="3"
                class="field-input resize-none"
                placeholder="Reason for transfer, special instructions..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Add Product Card -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-visible"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
            <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#1682C4]">
              Add Products
            </h2>
            <p class="text-xs text-slate-400 font-semibold mt-0.5">
              {{
                form.from_branch_id
                  ? "Search products available in source branch"
                  : "Select a source branch first"
              }}
            </p>
          </div>
          <div class="p-6 space-y-4">
            <!-- Product search -->
            <div>
              <label class="field-label">Search Product</label>
              <div class="relative">
                <svg
                  class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <circle cx="11" cy="11" r="8" />
                  <path d="M21 21l-4.35-4.35" />
                </svg>
                <!-- Search spinner -->
                <svg
                  v-if="searchLoading"
                  class="absolute right-3.5 top-1/2 -translate-y-1/2 animate-spin w-4 h-4 text-[#229DE7]"
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
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"
                  />
                </svg>
                <input
                  v-model="productSearch"
                  type="text"
                  placeholder="Type product name or code..."
                  :disabled="!form.from_branch_id"
                  class="field-input pl-10 disabled:opacity-50 disabled:cursor-not-allowed"
                  @input="searchProducts"
                  @focus="showDropdown = true"
                  @blur="hideDropdownSoon"
                />

                <!-- Results dropdown -->
                <div
                  v-if="showDropdown && productResults.length > 0"
                  class="absolute z-50 top-full mt-1 left-0 right-0 bg-white border border-[#D9EBF7] rounded-xl shadow-xl overflow-hidden max-h-56 overflow-y-auto"
                >
                  <button
                    v-for="p in productResults"
                    :key="p.id"
                    type="button"
                    @mousedown.prevent="selectProduct(p)"
                    class="w-full flex items-center gap-3 px-4 py-3 hover:bg-[#F5FAFF] transition text-left border-b border-[#EEF5FB] last:border-0"
                  >
                    <div
                      class="w-8 h-8 rounded-lg bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-xs flex-shrink-0"
                    >
                      {{ p.name?.charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="font-bold text-sm text-slate-900 truncate">
                        {{ p.name }}
                      </div>
                      <div class="font-mono text-[10px] text-slate-400">
                        {{ p.product_code }}
                      </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                      <div
                        class="text-xs font-bold"
                        :class="
                          p.available_stock > 0 ? 'text-emerald-600' : 'text-red-500'
                        "
                      >
                        {{ p.available_stock }} in stock
                      </div>
                    </div>
                  </button>
                </div>

                <!-- No results -->
                <div
                  v-if="
                    showDropdown &&
                    productSearch.length >= 2 &&
                    productResults.length === 0 &&
                    !searchLoading
                  "
                  class="absolute z-50 top-full mt-1 left-0 right-0 bg-white border border-[#D9EBF7] rounded-xl shadow-xl px-4 py-3 text-xs text-slate-400 font-semibold"
                >
                  No products found for "{{ productSearch }}" in this branch
                </div>
              </div>
            </div>

            <!-- Selected product quick-add -->
            <div
              v-if="selectedProduct"
              class="p-4 bg-[#F5FAFF] border border-[#B3DEFA] rounded-xl"
            >
              <div class="flex items-center gap-3 mb-3">
                <div
                  class="w-9 h-9 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-sm flex-shrink-0"
                >
                  {{ selectedProduct.name?.charAt(0).toUpperCase() }}
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-bold text-sm text-slate-900 truncate">
                    {{ selectedProduct.name }}
                  </div>
                  <div class="flex items-center gap-2 mt-0.5">
                    <span class="font-mono text-[10px] text-slate-400">{{
                      selectedProduct.product_code
                    }}</span>
                    <span
                      class="text-[10px] font-bold"
                      :class="
                        selectedProduct.available_stock > 0
                          ? 'text-emerald-600'
                          : 'text-red-500'
                      "
                    >
                      {{ selectedProduct.available_stock }} available
                    </span>
                  </div>
                </div>
                <button
                  type="button"
                  @click="selectedProduct = null"
                  class="w-7 h-7 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition"
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
              </div>
              <div class="flex gap-2">
                <div class="flex-1">
                  <label class="field-label">Quantity to Transfer</label>
                  <input
                    type="number"
                    v-model.number="addQty"
                    min="1"
                    :max="selectedProduct.available_stock"
                    class="field-input"
                    :class="{
                      'border-red-400': addQty > selectedProduct.available_stock,
                    }"
                    placeholder="0"
                  />
                  <p v-if="addQty > selectedProduct.available_stock" class="field-error">
                    Exceeds available stock ({{ selectedProduct.available_stock }})
                  </p>
                </div>
                <div class="flex items-end">
                  <button
                    type="button"
                    @click="addToTransfer"
                    :disabled="
                      !addQty || addQty <= 0 || addQty > selectedProduct.available_stock
                    "
                    class="h-10 px-5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Add
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ── RIGHT: TRANSFER LINES + SUBMIT ── -->
      <div class="lg:col-span-7 space-y-5">
        <!-- Transfer Lines Table -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div
            class="px-6 pt-5 pb-4 border-b border-[#EEF5FB] flex items-center justify-between"
          >
            <div>
              <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#1682C4]">
                Transfer Items
              </h2>
              <p class="text-xs text-slate-400 font-semibold mt-0.5">
                Products queued for transfer
              </p>
            </div>
            <button
              v-if="transferLines.length > 0"
              type="button"
              @click="clearAll"
              class="text-xs font-bold text-red-400 hover:text-red-600 transition"
            >
              Clear all
            </button>
          </div>

          <!-- Empty -->
          <div v-if="transferLines.length === 0" class="py-16 text-center">
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
                  d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                />
              </svg>
            </div>
            <h3 class="font-bold text-sm text-slate-900">No products added yet</h3>
            <p class="text-slate-400 text-xs mt-1 font-semibold">
              Search and add products from the left panel.
            </p>
          </div>

          <!-- Table -->
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest"
                >
                  <th class="px-5 py-3.5 w-8">#</th>
                  <th class="px-5 py-3.5">Product</th>
                  <th class="px-5 py-3.5 text-center">Available</th>
                  <th class="px-5 py-3.5 text-center">Transfer Qty</th>
                  <th class="px-5 py-3.5 text-right">Remove</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr
                  v-for="(line, index) in transferLines"
                  :key="line.product_id"
                  class="group hover:bg-[#F5FAFF] transition-colors"
                  :class="{ 'bg-red-50/30': line.qty > line.available_stock }"
                >
                  <td class="px-5 py-3 text-xs font-bold text-slate-400">
                    {{ index + 1 }}
                  </td>

                  <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-8 h-8 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] font-extrabold text-xs flex-shrink-0 group-hover:scale-105 transition-transform"
                      >
                        {{ line.name?.charAt(0).toUpperCase() }}
                      </div>
                      <div>
                        <div class="font-bold text-sm text-slate-900">
                          {{ line.name }}
                        </div>
                        <div class="font-mono text-[10px] text-slate-400">
                          {{ line.product_code }}
                        </div>
                      </div>
                    </div>
                  </td>

                  <td class="px-5 py-3 text-center">
                    <span
                      class="text-xs font-bold"
                      :class="
                        line.available_stock > 0 ? 'text-emerald-600' : 'text-red-500'
                      "
                    >
                      {{ line.available_stock }}
                    </span>
                  </td>

                  <td class="px-5 py-3 text-center">
                    <div class="flex items-center justify-center gap-1">
                      <button
                        type="button"
                        @click="adjustQty(line, -1)"
                        class="w-6 h-6 rounded-md bg-slate-100 hover:bg-[#EAF5FD] hover:text-[#229DE7] text-slate-500 flex items-center justify-center text-xs font-bold transition"
                      >
                        −
                      </button>
                      <input
                        type="number"
                        v-model.number="line.qty"
                        min="1"
                        :max="line.available_stock"
                        class="w-16 text-center px-2 py-1 bg-white border border-[#D9EBF7] rounded-lg text-sm font-bold text-slate-900 outline-none focus:border-[#229DE7] focus:ring-2 focus:ring-[#229DE7]/15 transition"
                        :class="{ 'border-red-400': line.qty > line.available_stock }"
                      />
                      <button
                        type="button"
                        @click="adjustQty(line, 1)"
                        :disabled="line.qty >= line.available_stock"
                        class="w-6 h-6 rounded-md bg-slate-100 hover:bg-[#EAF5FD] hover:text-[#229DE7] text-slate-500 flex items-center justify-center text-xs font-bold transition disabled:opacity-40"
                      >
                        +
                      </button>
                    </div>
                    <p
                      v-if="line.qty > line.available_stock"
                      class="text-[10px] text-red-500 font-bold mt-1"
                    >
                      Exceeds stock
                    </p>
                  </td>

                  <td class="px-5 py-3 text-right">
                    <button
                      type="button"
                      @click="removeLine(index)"
                      class="w-7 h-7 rounded-lg flex items-center justify-center text-red-400 border border-transparent hover:bg-red-50 hover:border-red-200 transition"
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
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Summary footer -->
          <div
            v-if="transferLines.length > 0"
            class="px-6 py-4 border-t border-[#EEF5FB] bg-[#F5FAFF] flex flex-col sm:flex-row sm:items-center justify-between gap-3"
          >
            <div class="flex items-center gap-4 text-xs font-bold text-slate-500">
              <span
                >{{ transferLines.length }} product{{
                  transferLines.length !== 1 ? "s" : ""
                }}</span
              >
              <span class="text-slate-300">·</span>
              <span>{{ totalQty }} total units</span>
              <span v-if="hasStockErrors" class="text-red-500 flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    fill-rule="evenodd"
                    d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                    clip-rule="evenodd"
                  />
                </svg>
                Some quantities exceed available stock
              </span>
            </div>
            <div
              v-if="fromBranchName && toBranchName"
              class="flex items-center gap-2 text-xs font-bold"
            >
              <span
                class="px-2.5 py-1 bg-[#EAF5FD] text-[#1682C4] border border-[#B3DEFA] rounded-lg"
                >{{ fromBranchName }}</span
              >
              <svg
                class="w-4 h-4 text-[#229DE7]"
                fill="none"
                stroke="currentColor"
                stroke-width="2.2"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M17 8l4 4m0 0l-4 4m4-4H3"
                />
              </svg>
              <span
                class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg"
                >{{ toBranchName }}</span
              >
            </div>
          </div>
        </div>

        <!-- SUBMIT CARD -->
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm">
          <div class="p-6">
            <!-- Validation errors -->
            <div
              v-if="submitErrors.length > 0"
              class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl"
            >
              <p class="text-xs font-extrabold text-red-600 mb-2 uppercase tracking-wide">
                Please fix the following:
              </p>
              <ul class="space-y-1">
                <li
                  v-for="err in submitErrors"
                  :key="err"
                  class="flex items-center gap-2 text-xs text-red-600 font-semibold"
                >
                  <span class="w-1.5 h-1.5 rounded-full bg-red-500 flex-shrink-0"></span>
                  {{ err }}
                </li>
              </ul>
            </div>

            <!-- Transfer summary preview -->
            <div
              v-if="form.from_branch_id && form.to_branch_id && transferLines.length > 0"
              class="mb-5 p-4 bg-[#F5FAFF] border border-[#D9EBF7] rounded-xl"
            >
              <p
                class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mb-3"
              >
                Transfer Summary
              </p>
              <div class="grid grid-cols-3 gap-3 text-center">
                <div>
                  <div
                    class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5"
                  >
                    From
                  </div>
                  <div class="font-extrabold text-sm text-slate-900">
                    {{ fromBranchName || "—" }}
                  </div>
                </div>
                <div class="flex items-center justify-center">
                  <div
                    class="w-8 h-8 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7]"
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
                        d="M17 8l4 4m0 0l-4 4m4-4H3"
                      />
                    </svg>
                  </div>
                </div>
                <div>
                  <div
                    class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5"
                  >
                    To
                  </div>
                  <div class="font-extrabold text-sm text-slate-900">
                    {{ toBranchName || "—" }}
                  </div>
                </div>
              </div>
              <div
                class="mt-3 pt-3 border-t border-[#EEF5FB] flex justify-between text-xs font-bold text-slate-500"
              >
                <span
                  >{{ transferLines.length }} product{{
                    transferLines.length !== 1 ? "s" : ""
                  }}</span
                >
                <span>{{ totalQty }} total units</span>
                <span>{{ form.transfer_date || "No date set" }}</span>
              </div>
            </div>

            <div class="flex gap-3">
              <button
                type="button"
                @click="resetAll"
                class="px-6 py-3 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-600 hover:border-[#B3DEFA] hover:text-[#229DE7] hover:bg-[#EAF5FD] transition shadow-sm"
              >
                Reset
              </button>
              <button
                type="button"
                @click="submitTransfer"
                :disabled="submitting || transferLines.length === 0"
                class="flex-1 flex items-center justify-center gap-2 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed"
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
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"
                  />
                </svg>
                <svg
                  v-else
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2.2"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                  />
                </svg>
                {{ submitting ? "Processing Transfer..." : "Submit Transfer" }}
              </button>
            </div>
          </div>
        </div>

        <!-- RECENT TRANSFERS -->
        <div
          class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden"
        >
          <div
            class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"
          ></div>
          <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
            <h2 class="text-sm font-extrabold uppercase tracking-widest text-[#1682C4]">
              Recent Transfers
            </h2>
          </div>

          <!-- Skeleton -->
          <div v-if="recentLoading" class="p-6 space-y-3">
            <div
              v-for="n in 3"
              :key="n"
              class="h-16 bg-slate-50 animate-pulse rounded-xl"
            ></div>
          </div>

          <div
            v-else-if="recentTransfers.length === 0"
            class="py-8 text-center text-slate-400 text-xs font-semibold"
          >
            No recent transfers.
          </div>

          <div v-else class="divide-y divide-[#EEF5FB]">
            <div
              v-for="t in recentTransfers"
              :key="t.reference"
              class="flex items-start gap-4 px-5 py-3.5 hover:bg-[#F5FAFF] transition group"
            >
              <div
                class="w-9 h-9 rounded-xl bg-[#EAF5FD] border border-[#B3DEFA] flex items-center justify-center text-[#229DE7] flex-shrink-0 group-hover:scale-105 transition-transform"
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
                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                  />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-bold text-sm text-slate-900 truncate"
                    >{{ t.from_branch }} → {{ t.to_branch }}</span
                  >
                  <span class="font-mono text-[10px] text-[#229DE7] font-bold">{{
                    t.reference
                  }}</span>
                </div>
                <div class="mt-1 text-[11px] text-slate-600">
                  <div v-for="(item, index) in t.items" :key="index" class="leading-5">
                    {{ item.product_name }} × {{ item.qty }}
                  </div>
                </div>
                <div class="text-[10.5px] text-slate-400 font-semibold mt-1">
                  {{ t.items_count }} item{{ t.items_count !== 1 ? "s" : "" }} ·
                  {{ formatDate(t.transfer_date) }}
                </div>
              </div>
              <span
                v-if="t.status"
                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-bold border flex-shrink-0"
                :class="transferStatusBadge(t.status)"
              >
                {{ t.status }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import BranchesService from "@/services/Branchesservice";
import StockTransferService from "@/services/stockService";

/* ─── STATE ───────────────────────────── */
const branchesLoading = ref(true);
const recentLoading = ref(true);
const submitting = ref(false);
const searchLoading = ref(false);
const showDropdown = ref(false);

const branches = ref([]);
const productResults = ref([]);
const selectedProduct = ref(null);
const addQty = ref(1);
const transferLines = ref([]);
const recentTransfers = ref([]);
const submitErrors = ref([]);
const productSearch = ref("");

let searchTimer = null;

/* ─── FORM ────────────────────────────── */
const form = ref({
  from_branch_id: "",
  to_branch_id: "",
  transfer_date: new Date().toISOString().split("T")[0],
  notes: "",
});

const vErrors = ref({
  from_branch_id: false,
  to_branch_id: false,
  same_branch: false,
  transfer_date: false,
});

/* ─── REFERENCE ───────────────────────── */
const referenceNumber = ref(generateRef());
function generateRef() {
  return `TRF-${Date.now().toString().slice(-6)}`;
}

/* ─── COMPUTED ────────────────────────── */
const fromBranchName = computed(
  () => branches.value.find((b) => b.id == form.value.from_branch_id)?.name || ""
);
const toBranchName = computed(
  () => branches.value.find((b) => b.id == form.value.to_branch_id)?.name || ""
);
const totalQty = computed(() =>
  transferLines.value.reduce((s, l) => s + (l.qty || 0), 0)
);
const hasStockErrors = computed(() =>
  transferLines.value.some((l) => l.qty > l.available_stock)
);

/* ─── INIT ────────────────────────────── */
onMounted(async () => {
  // Load branches and recent transfers independently so each shows its own skeleton
  BranchesService.getAllBranches()
    .then((br) => {
      branches.value = br.data?.data || br.data || br;
    })
    .catch(() => {
      Swal.fire({
        icon: "error",
        title: "Load Failed",
        text: "Could not load branches.",
        confirmButtonColor: "#229DE7",
      });
    })
    .finally(() => {
      branchesLoading.value = false;
    });

  StockTransferService.getRecent()
    .then((res) => {
      recentTransfers.value = res.data?.data || res.data || [];
    })
    .catch(() => {
      recentTransfers.value = [];
    })
    .finally(() => {
      recentLoading.value = false;
    });
});

/* ─── BRANCH HANDLERS ─────────────────── */
const onFromBranchChange = () => {
  if (transferLines.value.length === 0) return;
  Swal.fire({
    icon: "warning",
    title: "Change source branch?",
    text: "Changing the source branch will clear your current transfer items.",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: "Yes, change it",
  }).then((result) => {
    if (result.isConfirmed) {
      transferLines.value = [];
      productResults.value = [];
      selectedProduct.value = null;
    }
  });
};

const swapBranches = () => {
  const tmp = form.value.from_branch_id;
  form.value.from_branch_id = form.value.to_branch_id;
  form.value.to_branch_id = tmp;
  transferLines.value = [];
  productResults.value = [];
  selectedProduct.value = null;
};

/* ─── PRODUCT SEARCH (branch-scoped) ──── */
const searchProducts = () => {
  if (!form.value.from_branch_id) return;
  if (productSearch.value.length < 2) {
    productResults.value = [];
    return;
  }
  clearTimeout(searchTimer);
  searchLoading.value = true;
  searchTimer = setTimeout(async () => {
    try {
      // Fetch products scoped to the selected branch with stock > 0
      const res = await StockTransferService.searchBranchProducts({
        branch_id: form.value.from_branch_id,
        search: productSearch.value,
        per_page: 10,
      });
      productResults.value = (res.data?.data || res.data || []).map((p) => ({
        ...p,
        available_stock: p.available_stock ?? p.current_stock ?? 0,
      }));
    } catch {
      productResults.value = [];
    } finally {
      searchLoading.value = false;
    }
  }, 300);
};

const selectProduct = (p) => {
  const existing = transferLines.value.find((l) => l.product_id === p.id);
  if (existing) {
    Swal.fire({
      icon: "info",
      title: "Already added",
      text: `${p.name} is already in the transfer list. Adjust its quantity there.`,
      confirmButtonColor: "#229DE7",
    });
    showDropdown.value = false;
    productSearch.value = "";
    return;
  }
  selectedProduct.value = p;
  addQty.value = 1;
  productSearch.value = "";
  productResults.value = [];
  showDropdown.value = false;
};

const hideDropdownSoon = () => {
  setTimeout(() => {
    showDropdown.value = false;
  }, 150);
};

/* ─── ADD TO TRANSFER ─────────────────── */
const addToTransfer = () => {
  if (!selectedProduct.value || !addQty.value || addQty.value <= 0) return;
  if (addQty.value > selectedProduct.value.available_stock) return;
  transferLines.value.push({
    product_id: selectedProduct.value.id,
    name: selectedProduct.value.name,
    product_code: selectedProduct.value.product_code,
    available_stock: selectedProduct.value.available_stock,
    qty: addQty.value,
  });
  selectedProduct.value = null;
  addQty.value = 1;
  productSearch.value = "";
};

/* ─── LINE ACTIONS ────────────────────── */
const adjustQty = (line, delta) => {
  const next = (line.qty || 0) + delta;
  if (next < 1 || next > line.available_stock) return;
  line.qty = next;
};
const removeLine = (index) => transferLines.value.splice(index, 1);
const clearAll = () => {
  Swal.fire({
    title: "Clear all items?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: "Yes, clear",
  }).then((r) => {
    if (r.isConfirmed) transferLines.value = [];
  });
};

/* ─── VALIDATE ────────────────────────── */
const validate = () => {
  const errors = [];
  vErrors.value = {
    from_branch_id: false,
    to_branch_id: false,
    same_branch: false,
    transfer_date: false,
  };
  if (!form.value.from_branch_id) {
    vErrors.value.from_branch_id = true;
    errors.push("Source branch is required");
  }
  if (!form.value.to_branch_id) {
    vErrors.value.to_branch_id = true;
    errors.push("Destination branch is required");
  }
  if (
    form.value.from_branch_id &&
    form.value.to_branch_id &&
    form.value.from_branch_id === form.value.to_branch_id
  ) {
    vErrors.value.same_branch = true;
    errors.push("Source and destination cannot be the same branch");
  }
  if (!form.value.transfer_date) {
    vErrors.value.transfer_date = true;
    errors.push("Transfer date is required");
  }
  if (transferLines.value.length === 0)
    errors.push("Add at least one product to transfer");
  if (hasStockErrors.value) errors.push("One or more items exceed available stock");
  submitErrors.value = errors;
  return errors.length === 0;
};

/* ─── SUBMIT ──────────────────────────── */
const submitTransfer = async () => {
  if (!validate()) return;
  const confirm = await Swal.fire({
    icon: "question",
    title: "Confirm Transfer",
    html: `
      <div style="text-align:left;font-size:14px">
        <p style="margin-bottom:8px">Transfer <b>${totalQty.value} units</b> across <b>${transferLines.value.length} product(s)</b></p>
        <p style="color:#64748b">From: <b>${fromBranchName.value}</b> → To: <b>${toBranchName.value}</b></p>
      </div>
    `,
    showCancelButton: true,
    confirmButtonColor: "#229DE7",
    cancelButtonColor: "#f43f5e",
    confirmButtonText: "Yes, Transfer",
  });
  if (!confirm.isConfirmed) return;

  submitting.value = true;
  try {
    const payload = {
      reference: referenceNumber.value,
      from_branch_id: form.value.from_branch_id,
      to_branch_id: form.value.to_branch_id,
      transfer_date: form.value.transfer_date,
      notes: form.value.notes,
      items: transferLines.value.map((l) => ({
        product_id: l.product_id,
        quantity: l.qty,
      })),
    };
    await StockTransferService.transferStock(payload);
    await Swal.fire({
      icon: "success",
      title: "Transfer Submitted!",
      text: `${totalQty.value} units transferred from ${fromBranchName.value} to ${toBranchName.value}.`,
      confirmButtonColor: "#229DE7",
    });
    resetAll();
    // Refresh recent transfers
    recentLoading.value = true;
    StockTransferService.getRecent()
      .then((res) => {
        recentTransfers.value = res.data?.data || res.data || [];
      })
      .finally(() => {
        recentLoading.value = false;
      });
  } catch (err) {
    Swal.fire({
      icon: "error",
      title: "Transfer Failed",
      text: err?.response?.data?.message || "Something went wrong. Please try again.",
      confirmButtonColor: "#229DE7",
    });
  } finally {
    submitting.value = false;
  }
};

/* ─── RESET ───────────────────────────── */
const resetAll = () => {
  form.value = {
    from_branch_id: "",
    to_branch_id: "",
    transfer_date: new Date().toISOString().split("T")[0],
    notes: "",
  };
  transferLines.value = [];
  selectedProduct.value = null;
  productSearch.value = "";
  productResults.value = [];
  addQty.value = 1;
  submitErrors.value = [];
  vErrors.value = {
    from_branch_id: false,
    to_branch_id: false,
    same_branch: false,
    transfer_date: false,
  };
  referenceNumber.value = generateRef();
};

/* ─── HELPERS ─────────────────────────── */
const formatDate = (d) =>
  d
    ? new Date(d).toLocaleDateString("en-KE", {
        year: "numeric",
        month: "short",
        day: "numeric",
      })
    : "—";

const transferStatusBadge = (status) => {
  const s = (status || "").toLowerCase();
  if (s === "completed" || s === "approved")
    return "bg-emerald-50 text-emerald-700 border-emerald-200";
  if (s === "pending") return "bg-amber-50 text-amber-600 border-amber-200";
  if (s === "cancelled") return "bg-red-50 text-red-600 border-red-200";
  return "bg-[#EAF5FD] text-[#1682C4] border-[#B3DEFA]";
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
.field-input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.field-error {
  font-size: 11px;
  font-weight: 600;
  color: #ef4444;
  margin-top: 4px;
}
</style>
