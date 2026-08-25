<template>
  <div class="min-h-screen bg-gray-50 py-10 px-6 font-sans">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
      <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">📇 Phonebooks</h2>
      <button
        @click="openAddModal"
        class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-5 py-2 rounded-xl shadow transition"
      >
        + Add Phonebook
      </button>
    </div>

    <!-- Skeleton shimmer -->
    <div v-if="loading" class="animate-pulse space-y-3">
      <div v-for="n in 6" :key="n" class="bg-white rounded-xl p-4 shadow-sm flex justify-between">
        <div class="flex-1 space-y-2">
          <div class="h-4 bg-gray-200 rounded w-1/3"></div>
          <div class="h-3 bg-gray-200 rounded w-2/3"></div>
        </div>
        <div class="h-8 w-20 bg-gray-200 rounded"></div>
      </div>
    </div>

    <!-- Empty state -->
    <div
      v-else-if="phonebooks.length === 0"
      class="bg-white p-10 rounded-2xl shadow-sm text-center text-gray-500"
    >
      <p class="text-lg font-medium mb-2">No phonebooks yet</p>
      <p class="text-sm text-gray-400">Create one to get started.</p>
    </div>

    <!-- Table view -->
    <div v-else class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
      <!-- Search + Rows per page -->
      <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b">
        <div class="relative w-full sm:w-1/3">
          <input
            v-model="search"
            type="text"
            placeholder="🔍 Search phonebooks..."
            class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400"
          />
        </div>
        <div class="text-sm text-gray-600 flex items-center gap-2">
          <label>Rows per page:</label>
          <select
            v-model="rowsPerPage"
            class="border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400"
          >
            <option v-for="n in [5, 10, 20, 50]" :key="n" :value="n">{{ n }}</option>
          </select>
        </div>
      </div>

      <!-- Table -->
      <table class="min-w-full border-collapse text-sm">
        <thead class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold">
          <tr>
            <th class="py-3 px-6 text-left cursor-pointer" @click="sortBy('id')">
              #
              <span v-if="sort.key === 'id'">{{ sort.order === 'asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="py-3 px-6 text-left cursor-pointer" @click="sortBy('name')">
              Name
              <span v-if="sort.key === 'name'">{{ sort.order === 'asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="py-3 px-6 text-left w-1/3">Description</th>
            <th class="py-3 px-6 text-center cursor-pointer" @click="sortBy('contacts_count')">
              Contacts
              <span v-if="sort.key === 'contacts_count'">{{ sort.order === 'asc' ? '▲' : '▼' }}</span>
            </th>
            <th class="py-3 px-6 text-right w-40">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(book, index) in paginatedData"
            :key="book.id"
            class="border-t hover:bg-gray-50 transition duration-200 even:bg-gray-50/50"
          >
            <td class="py-3 px-6 text-gray-600">{{ index + 1 + (currentPage - 1) * rowsPerPage }}</td>
            <td class="py-3 px-6 font-medium text-gray-800">{{ book.name }}</td>
            <td class="py-3 px-6 text-gray-500 truncate max-w-[300px]">
              {{ book.description || "—" }}
            </td>
            <td class="py-3 px-6 text-center text-gray-700 font-semibold">
              {{ book.contacts_count ?? 0 }}
            </td>
            <td class="py-3 px-6 text-right space-x-3 flex justify-end">
              <!-- Edit -->
              <button
                @click="openEditModal(book)"
                class="text-blue-500 hover:text-blue-600 transition"
                title="Edit"
              >
                <Edit class="w-5 h-5" />
              </button>

              <!-- Download -->
              <button
                @click="downloadContacts(book.id)"
                class="text-emerald-600 hover:text-emerald-700 transition"
                title="Download Contacts"
              >
                <Download class="w-5 h-5" />
              </button>

              <!-- Delete -->
              <button
                @click="deletePhonebook(book.id)"
                class="text-red-500 hover:text-red-600 transition"
                title="Delete"
              >
                <Trash2 class="w-5 h-5" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="flex justify-between items-center border-t p-4 text-sm text-gray-600">
        <p>
          Showing
          <strong>{{ startItem }}</strong>–<strong>{{ endItem }}</strong>
          of <strong>{{ filteredData.length }}</strong> phonebooks
        </p>

        <div class="flex gap-2">
          <button
            @click="prevPage"
            :disabled="currentPage === 1"
            class="px-3 py-1 border rounded-lg hover:bg-gray-100 disabled:opacity-50"
          >
            Prev
          </button>
          <button
            @click="nextPage"
            :disabled="endItem >= filteredData.length"
            class="px-3 py-1 border rounded-lg hover:bg-gray-100 disabled:opacity-50"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <Modal v-if="showAddModal" @close="showAddModal = false">
      <template #title>
        {{ isEditing ? "✏️ Edit Phonebook" : "📘 Add New Phonebook" }}
      </template>

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
          <input
            v-model="newPhonebook.name"
            type="text"
            class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400"
            placeholder="e.g. Marketing List"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
          <textarea
            v-model="newPhonebook.description"
            rows="3"
            class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400"
            placeholder="Optional description..."
          ></textarea>
        </div>
      </div>

      <template #footer>
        <button
          @click="showAddModal = false"
          class="px-4 py-2 rounded-lg border text-gray-600 hover:bg-gray-100 transition"
        >
          Cancel
        </button>
        <button
          @click="savePhonebook"
          :disabled="saving"
          class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition flex items-center justify-center min-w-[90px]"
        >
          <span v-if="!saving">{{ isEditing ? "Update" : "Save" }}</span>
          <span v-else class="flex items-center gap-2">
            <svg
              class="w-4 h-4 animate-spin text-white"
              xmlns="http://www.w3.org/2000/svg"
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
              ></circle>
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8v8H4z"
              ></path>
            </svg>
            Saving...
          </span>
        </button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import Swal from "sweetalert2";
import phonebookService from "@/services/phonebookService";
import Modal from "@/pages/Dashboard/Components/PhonebookModal.vue";
import { Edit, Download, Trash2 } from "lucide-vue-next";

// ================================
// 🗂️ State Variables
// ================================
const phonebooks = ref([]);
const loading = ref(false);
const saving = ref(false);
const isEditing = ref(false);

// Modal state
const showAddModal = ref(false);

// Form data
const newPhonebook = ref({ id: null, name: "", description: "" });

// ================================
// 📦 Fetch Phonebooks
// ================================
const loadPhonebooks = async () => {
  loading.value = true;
  try {
    const res = await phonebookService.getPhonebooks();
    if (res.success) phonebooks.value = res.phonebooks;
  } catch {
    Swal.fire("Error", "Failed to load phonebooks.", "error");
  } finally {
    loading.value = false;
  }
};

// ================================
// ➕ Add/Edit Phonebook
// ================================
const openAddModal = () => {
  newPhonebook.value = { id: null, name: "", description: "" };
  isEditing.value = false;
  showAddModal.value = true;
};

const openEditModal = (book) => {
  newPhonebook.value = { ...book };
  isEditing.value = true;
  showAddModal.value = true;
};

const savePhonebook = async () => {
  if (!newPhonebook.value.name.trim()) {
    return Swal.fire("Missing name", "Phonebook name is required.", "warning");
  }

  try {
    saving.value = true;
    const payload = { ...newPhonebook.value };
    const res = isEditing.value
      ? await phonebookService.updatePhoneBook(payload.id, payload)
      : await phonebookService.createPhoneBook(payload);

    if (res.success) {
      Swal.fire("Success", res.message, "success");
      showAddModal.value = false;
      await loadPhonebooks();
    }
  } catch (err) {
    Swal.fire(
      "Error",
      err.response?.data?.message || "Failed to save phonebook.",
      "error"
    );
  } finally {
    saving.value = false;
  }
};

// ================================
// 🗑️ Delete Phonebook
// ================================
const deletePhonebook = async (id) => {
  const confirm = await Swal.fire({
    title: "Delete phonebook?",
    text: "This will remove all its contacts.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, delete it!",
  });
  if (!confirm.isConfirmed) return;

  try {
    const { data } = await phonebookService.delete(id);
    if (data.success) {
      Swal.fire("Deleted!", data.message, "success");
      phonebooks.value = phonebooks.value.filter((b) => b.id !== id);
    }
  } catch (err) {
    Swal.fire(
      "Error",
      err.response?.data?.message || "Failed to delete phonebook.",
      "error"
    );
  }
};

// ================================
// 📥 Download Contacts (Excel)
// ================================
const downloadContacts = async (id) => {
  try {
    const res = await phonebookService.downloadContacts(id);
    if (!res) throw new Error("No response");

    const url = window.URL.createObjectURL(new Blob([res]));
    const a = document.createElement("a");
    a.href = url;
    a.download = `phonebook-${id}-contacts.xlsx`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
  } catch (err) {
    Swal.fire("Error", "Failed to download contacts.", "error");
  }
};

// ================================
// 📊 Table Logic (Search, Sort, Paginate)
// ================================
const search = ref("");
const currentPage = ref(1);
const rowsPerPage = ref(10);
const sort = ref({ key: "id", order: "asc" });

const filteredData = computed(() => {
  const q = search.value.toLowerCase();
  return phonebooks.value.filter(
    (b) =>
      b.name.toLowerCase().includes(q) ||
      (b.description && b.description.toLowerCase().includes(q))
  );
});

const sortedData = computed(() => {
  return [...filteredData.value].sort((a, b) => {
    const key = sort.value.key;
    const order = sort.value.order === "asc" ? 1 : -1;
    if (a[key] < b[key]) return -1 * order;
    if (a[key] > b[key]) return 1 * order;
    return 0;
  });
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage.value;
  return sortedData.value.slice(start, start + rowsPerPage.value);
});

const startItem = computed(() =>
  filteredData.value.length === 0 ? 0 : (currentPage.value - 1) * rowsPerPage.value + 1
);
const endItem = computed(() =>
  Math.min(currentPage.value * rowsPerPage.value, filteredData.value.length)
);

const sortBy = (key) => {
  if (sort.value.key === key) {
    sort.value.order = sort.value.order === "asc" ? "desc" : "asc";
  } else {
    sort.value.key = key;
    sort.value.order = "asc";
  }
};

const nextPage = () => {
  if (endItem.value < filteredData.value.length) currentPage.value++;
};
const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

// ================================
// 🚀 Lifecycle
// ================================
onMounted(loadPhonebooks);
</script>
