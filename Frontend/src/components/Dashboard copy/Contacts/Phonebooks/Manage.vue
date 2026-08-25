<template>
  <div class="min-h-screen bg-gray-50 text-gray-900">
    <main class="p-4 sm:p-6 md:p-8 max-w-7xl mx-auto">
      <header
        class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4"
      >
        <h2
          class="text-3xl font-extrabold text-gray-800 tracking-tight flex items-center gap-3"
        >
          <!-- <BookOpen class="w-8 h-8 text-emerald-600" /> -->
          <span class="pb-1">Phonebooks Management</span>
        </h2>

        <button
          @click="openAddModal"
          class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-3 rounded-xl shadow-lg shadow-emerald-400/50 transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]"
        >
          <PlusCircle class="w-5 h-5" />
          Create New Phonebook
        </button>
      </header>

      <div v-if="loading" class="animate-pulse space-y-4">
        <div
          v-for="n in 6"
          :key="n"
          class="bg-white rounded-xl p-5 shadow-lg flex justify-between items-center"
        >
          <div class="flex-1 space-y-3">
            <div class="h-4 bg-gray-200 rounded w-1/4"></div>
            <div class="h-3 bg-gray-100 rounded w-1/2"></div>
          </div>
          <div class="h-10 w-32 bg-gray-200 rounded-lg"></div>
        </div>
      </div>

      <div
        v-else-if="phonebooks.length === 0"
        class="bg-white p-12 rounded-2xl shadow-2xl border border-gray-100/50 text-center text-gray-500"
      >
        <BookDashed class="w-12 h-12 mx-auto text-emerald-500 mb-4" />
        <p class="text-xl font-semibold mb-2 text-gray-700">
          No Phonebooks Found
        </p>
        <p class="text-sm text-gray-500">
          You haven't created any phonebooks yet. Click the button above to
          manage your contact groups.
        </p>
      </div>

      <div
        v-else
        class="bg-white p-6 rounded-xl shadow-lg space-y-4 h-fit sticky top-4"
      >
        <div
          class="flex flex-col md:flex-row items-center justify-between gap-4 p-4"
        >
          <div
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 space-y-3 sm:space-y-0"
          >
            <input
              v-model="search"
              type="text"
              placeholder="Search by ID, Name, or Description..."
              class="border border-gray-300 rounded-lg px-4 py-2 w-full sm:w-auto transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div class="text-sm text-gray-600 flex items-center gap-3">
            <label class="font-medium text-gray-700 hidden sm:block"
              >Rows per page:</label
            >
            <select
              v-model="rowsPerPage"
              class="border border-gray-300 rounded-xl px-3 py-2.5 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
            >
              <option v-for="n in [5, 10, 20, 50]" :key="n" :value="n">
                {{ n }}
              </option>
            </select>
          </div>
        </div>

        <div
          class="overflow-x-auto bg-white shadow-md rounded-lg border border-gray-200"
        >
          <table class="min-w-full divide-y divide-gray-200">
            <thead
              class="bg-gray-100 text-gray-700 uppercase text-xs font-semibold border-b"
            >
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider select-none"
                  @click="sortBy('id')"
                >
                  <span
                    class="flex items-center gap-1 cursor-pointer hover:text-emerald-600 transition"
                  >
                    #
                    <ArrowUpDown
                      v-if="sort.key !== 'id'"
                      class="inline w-3 h-3"
                    />
                    <ArrowUp
                      v-if="sort.key === 'id' && sort.order === 'asc'"
                      class="inline w-3 h-3 text-emerald-600"
                    />
                    <ArrowDown
                      v-if="sort.key === 'id' && sort.order === 'desc'"
                      class="inline w-3 h-3 text-emerald-600"
                    />
                  </span>
                </th>
                <th
                  class="py-3 px-6 text-left select-none"
                  @click="sortBy('name')"
                >
                  <span
                    class="flex items-center gap-1 cursor-pointer hover:text-emerald-600 transition"
                  >
                    Name
                    <ArrowUpDown
                      v-if="sort.key !== 'name'"
                      class="inline w-3 h-3"
                    />
                    <ArrowUp
                      v-if="sort.key === 'name' && sort.order === 'asc'"
                      class="inline w-3 h-3 text-emerald-600"
                    />
                    <ArrowDown
                      v-if="sort.key === 'name' && sort.order === 'desc'"
                      class="inline w-3 h-3 text-emerald-600"
                    />
                  </span>
                </th>
                <th class="py-3 px-6 text-left w-[35%]">Description</th>
                <th
                  class="py-3 px-6 text-center select-none"
                  @click="sortBy('contacts_count')"
                >
                  <span
                    class="flex items-center justify-center gap-1 cursor-pointer hover:text-emerald-600 transition"
                  >
                    Contacts
                    <ArrowUpDown
                      v-if="sort.key !== 'contacts_count'"
                      class="inline w-3 h-3"
                    />
                    <ArrowUp
                      v-if="
                        sort.key === 'contacts_count' && sort.order === 'asc'
                      "
                      class="inline w-3 h-3 text-emerald-600"
                    />
                    <ArrowDown
                      v-if="
                        sort.key === 'contacts_count' && sort.order === 'desc'
                      "
                      class="inline w-3 h-3 text-emerald-600"
                    />
                  </span>
                </th>
                <th class="py-3 px-6 text-right w-40">Created At</th>
                <th class="py-3 px-6 text-right w-40">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(book, index) in paginatedData"
                :key="book.id"
                class="hover:bg-blue-50 transition duration-100 border-b border-gray-100"
              >
                <td class="py-4 px-6 text-gray-600">
                  {{ index + 1 + (currentPage - 1) * rowsPerPage }}
                </td>
                <td
                  class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700"
                >
                  {{ book.name }}
                </td>
                <td class="py-4 px-6 text-gray-500 truncate max-w-xs">
                  {{ book.description || "— No description provided —" }}
                </td>
                <td class="py-4 px-6 text-center">
                  <span
                    class="inline-flex items-center justify-center px-3 py-1 text-xs font-bold leading-none text-emerald-700 bg-emerald-100 rounded-full min-w-[32px]"
                  >
                    {{ book.contacts_count ?? 0 }}
                  </span>
                </td>
                <td class="py-4 px-6 text-gray-500 truncate max-w-xs">
                  {{ formatDate(book.created_at) }}
                </td>

                <td class="py-4 px-6 text-right flex justify-end gap-2">
                  <button
                    @click="goToContacts(book.id)"
                    title="View Contacts"
                    class="p-2 rounded-full text-blue-600 bg-blue-50 hover:bg-blue-100 transition"
                  >
                    <Eye class="w-4 h-4" />
                  </button>
                  <button
                    @click="openEditModal(book)"
                    title="Edit Phonebook"
                    class="p-2 rounded-full text-orange-600 bg-orange-50 hover:bg-orange-100 transition"
                  >
                    <Edit class="w-4 h-4" />
                  </button>
                  <button
                    @click="deletePhonebook(book.id)"
                    title="Delete Phonebook"
                    class="p-2 rounded-full text-red-600 bg-red-50 hover:bg-red-100 transition"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          class="flex flex-col sm:flex-row justify-between items-center border-t p-4 text-sm text-gray-600 bg-gray-50"
        >
          <p class="mb-2 sm:mb-0">
            Showing
            <strong class="font-semibold">{{ startItem }}</strong
            >–<strong class="font-semibold">{{ endItem }}</strong> of
            <strong class="font-semibold">{{ filteredData.length }}</strong>
            phonebooks
          </p>
          <div class="flex gap-2">
            <button
              @click="prevPage"
              :disabled="currentPage === 1"
              class="flex items-center gap-1 px-4 py-2 border border-gray-300 rounded-xl hover:bg-gray-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ChevronLeft class="w-4 h-4" /> Previous
            </button>
            <button
              @click="nextPage"
              :disabled="endItem >= filteredData.length"
              class="flex items-center gap-1 px-4 py-2 border border-gray-300 rounded-xl hover:bg-gray-100 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next <ChevronRight class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      <transition name="fade">
        <div
          v-if="showModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
          @click.self="closeModal"
          @keydown.esc.window="closeModal"
        >
          <div
            class="bg-white rounded-3xl w-full max-w-lg p-6 relative shadow-2xl transition-all duration-300 transform scale-100"
          >
            <button
              @click="closeModal"
              class="absolute top-4 right-4 p-2 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition"
            >
              <X class="w-5 h-5" />
            </button>

            <div class="flex items-center gap-3 mb-6 border-b pb-3">
              <component
                :is="isEditing ? Edit : PlusCircle"
                class="w-6 h-6 text-emerald-600"
              />
              <h3 class="text-xl font-bold text-gray-800">
                {{ isEditing ? "Edit Phonebook" : "Add New Phonebook" }}
              </h3>
            </div>

            <div class="space-y-5">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Name <span class="text-red-500">*</span></label
                >
                <input
                  v-model="form.name"
                  type="text"
                  class="w-full border border-gray-300 rounded-xl p-3 text-sm placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                  placeholder="e.g. November Sales Leads"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Description</label
                >
                <textarea
                  v-model="form.description"
                  rows="4"
                  class="w-full border border-gray-300 rounded-xl p-3 text-sm placeholder-gray-400 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                  placeholder="Optional description of the phonebook's purpose or source."
                ></textarea>
              </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
              <button
                @click="closeModal"
                class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-100 transition font-medium"
              >
                Cancel
              </button>
              <button
                @click="savePhonebook"
                :disabled="saving || !form.name.trim()"
                class="px-6 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition font-semibold disabled:bg-emerald-300 disabled:cursor-not-allowed min-w-[120px]"
              >
                <span v-if="!saving">{{
                  isEditing ? "Update Phonebook" : "Save Phonebook"
                }}</span>
                <span v-else class="flex items-center justify-center gap-2">
                  <svg
                    class="animate-spin h-5 w-5 text-white"
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
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                  </svg>
                  Saving...
                </span>
              </button>
            </div>
          </div>
        </div>
      </transition>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import { useRouter } from "vue-router";
// Ensure these paths are correct in your project environment
import phonebookService from "@/services/phonebookService";

import {
  Edit,
  Trash2,
  Eye,
  BookOpen,
  PlusCircle,
  Search,
  ArrowUp,
  ArrowDown,
  ArrowUpDown,
  ChevronLeft,
  ChevronRight,
  BookDashed,
  X,
} from "lucide-vue-next";

const router = useRouter();

// --- State ---
const phonebooks = ref([]);
const loading = ref(false);
const saving = ref(false);
const isEditing = ref(false);
const showModal = ref(false);
const form = ref({ id: null, name: "", description: "" });

// --- Data Fetching ---
const loadPhonebooks = async () => {
  loading.value = true;
  try {
    // Assuming phonebookService.getPhonebooks() is correctly implemented
    const res = await phonebookService.getPhonebooks();
    if (res.success) phonebooks.value = res.phonebooks;
  } catch (error) {
    console.error("Failed to load phonebooks:", error);
    Swal.fire("Error", "Failed to load phonebooks. Please try again.", "error");
  } finally {
    loading.value = false;
  }
};

// --- CRUD Modals ---
const openAddModal = () => {
  form.value = { id: null, name: "", description: "" };
  isEditing.value = false;
  showModal.value = true;
};

const openEditModal = (book) => {
  form.value = { ...book };
  isEditing.value = true;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  // Delay clearing form to allow transition to finish if needed
  setTimeout(() => {
    isEditing.value = false;
    form.value = { id: null, name: "", description: "" };
  }, 150);
};

// --- CRUD Actions ---
const savePhonebook = async () => {
  if (!form.value.name.trim()) {
    return Swal.fire("Missing Name", "Phonebook name is required.", "warning");
  }

  try {
    saving.value = true;
    let res;
    if (isEditing.value) {
      res = await phonebookService.updatePhoneBook(form.value.id, form.value);
    } else {
      res = await phonebookService.createPhoneBook(form.value);
    }

    if (res.success) {
      Swal.fire("Success", res.message, "success");
      closeModal();
      await loadPhonebooks(); // Refresh data table
    } else {
      Swal.fire(
        "Error",
        res.message || "Something went wrong while saving.",
        "error"
      );
    }
  } catch (err) {
    console.error("Save phonebook failed:", err);
    Swal.fire(
      "Error",
      err.response?.data?.message ||
        "Failed to save phonebook due to a server error.",
      "error"
    );
  } finally {
    saving.value = false;
  }
};

const deletePhonebook = async (id) => {
  const confirm = await Swal.fire({
    title: "Confirm Deletion",
    text: "Are you sure you want to delete this phonebook? All associated contacts will also be removed.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Yes, Delete It!",
  });

  if (!confirm.isConfirmed) return;

  try {
    const res = await phonebookService.delete(id);
    if (res.success) {
      Swal.fire("Deleted!", res.message, "success");
      // Optimistically update the list
      phonebooks.value = phonebooks.value.filter((b) => b.id !== id);
      // Reset pagination if current page is now empty
      if (paginatedData.value.length === 0 && currentPage.value > 1) {
        currentPage.value--;
      }
    } else {
      Swal.fire(
        "Error",
        res.message || "Something went wrong during deletion.",
        "error"
      );
    }
  } catch (error) {
    console.error("Delete phonebook failed:", error);
    Swal.fire(
      "Error",
      "Failed to delete phonebook due to a server error.",
      "error"
    );
  }
};

// --- Navigation ---
const goToContacts = (id) =>
  router.push(`/contacts/phonebooks/all-contacts/${id}`);

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

const formatDate = (datetime) => {
  if (!datetime) return "— No date —";
  const date = new Date(datetime);
  return date.toISOString().split("T")[0]; // returns "YYYY-MM-DD"
};

const sortedData = computed(() => {
  return [...filteredData.value].sort((a, b) => {
    const key = sort.value.key;
    const order = sort.value.order === "asc" ? 1 : -1;

    const aValue = a[key] ?? "";
    const bValue = b[key] ?? "";

    if (typeof aValue === "number" && typeof bValue === "number") {
      return (aValue - bValue) * order;
    }

    // String comparison
    if (String(aValue).toLowerCase() > String(bValue).toLowerCase())
      return order;
    if (String(aValue).toLowerCase() < String(bValue).toLowerCase())
      return -order;
    return 0;
  });
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage.value;
  return sortedData.value.slice(start, start + rowsPerPage.value);
});

const startItem = computed(() =>
  filteredData.value.length === 0
    ? 0
    : (currentPage.value - 1) * rowsPerPage.value + 1
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
  currentPage.value = 1; // Always reset pagination on new sort
};

const nextPage = () => {
  if (endItem.value < filteredData.value.length) currentPage.value++;
};
const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

// --- Lifecycle ---
onMounted(loadPhonebooks);
</script>

<style scoped>
/* Modal Fade Transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
/* Optional: Add slight scale effect to modal content */
.fade-enter-active .scale-100,
.fade-leave-active .scale-100 {
  transition: transform 0.3s ease;
}
.fade-enter-from .scale-100,
.fade-leave-to .scale-100 {
  transform: scale(0.95);
}

/* Custom class for extra shadow if needed */
.hover\:shadow-3xl:hover {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04); /* md shadow */
  box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.15),
    0 15px 25px -5px rgba(0, 0, 0, 0.05); /* slightly deeper/wider shadow for elevation effect */
}
</style>
