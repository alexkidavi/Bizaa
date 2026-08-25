<template>
  <div class="min-h-screen bg-gray-50 py-10 px-6 font-sans">
    <div
      class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4"
    >
      <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">
        Active Contacts in
        <span class="text-blue-500">{{ phonebookName }}</span>
      </h2>

      <div class="flex gap-2">
        <button
          @click="exportContacts"
          class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-xl shadow transition flex items-center gap-2"
        >
          <DownloadCloud class="w-5 h-5" /> Export
        </button>

        <button
          @click="openImportModal"
          class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-4 py-2 rounded-xl shadow transition flex items-center gap-2"
        >
          <UploadCloud class="w-5 h-5" /> Import
        </button>

        <button
          @click="openAddModal"
          class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold px-5 py-2 rounded-xl shadow transition flex items-center gap-2"
        >
          <PlusIcon class="w-5 h-5" /> Add
        </button>
      </div>
    </div>

    <div
      class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4 flex flex-col sm:flex-row justify-between items-center gap-4"
    >
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search by name, phone, or email..."
        class="w-full sm:w-1/2 border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-emerald-400"
      />

      <span class="text-sm text-gray-500">
        {{ filteredContacts.length }} active contact(s)
      </span>
    </div>

    <transition name="fade" mode="out-in">
      <div v-if="loading" key="loading" class="space-y-3">
        <div
          v-for="n in 6"
          :key="n"
          class="bg-white rounded-xl p-5 shadow-sm animate-pulse"
        >
          <div class="h-4 bg-gray-200 rounded w-1/3 mb-2"></div>
          <div class="h-3 bg-gray-200 rounded w-1/2"></div>
        </div>
      </div>

      <div
        v-else-if="filteredContacts.length > 0"
        key="table"
        class="bg-white rounded-2xl shadow border border-gray-200 overflow-hidden transition-all duration-300"
      >
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-gray-700">
            <thead class="bg-gray-50 sticky top-0 z-10">
              <tr class="text-xs uppercase text-gray-500 tracking-wider">
                <th class="py-3.5 px-6 text-left">#</th>

                <th
                  @click="sort('name')"
                  class="py-3.5 px-6 text-left cursor-pointer select-none"
                >
                  Name
                  <span v-if="sortField === 'name'">
                    {{ sortOrder === "asc" ? "⬆️" : "⬇️" }}
                  </span>
                </th>
                <th
                  @click="sort('phone')"
                  class="py-3.5 px-6 text-left cursor-pointer select-none"
                >
                  Phone
                  <span v-if="sortField === 'phone'">
                    {{ sortOrder === "asc" ? "⬆️" : "⬇️" }}
                  </span>
                </th>
                <th class="py-3.5 px-6 text-left">Email</th>

                <th class="py-3.5 px-6 text-left">Status</th>

                <th class="py-3.5 px-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="(c, index) in paginatedContacts"
                :key="c.id"
                class="hover:bg-emerald-50 transition-colors duration-200"
              >
                <td class="py-3.5 px-6 text-gray-500">
                  {{ (currentPage - 1) * perPage + index + 1 }}
                </td>
                <td class="py-3.5 px-6 font-medium text-gray-800">
                  {{ c.name }}
                </td>
                <td class="py-3.5 px-6 font-mono text-gray-700">
                  {{ c.phone }}
                </td>
                <td class="py-3.5 px-6 text-gray-500">{{ c.email || "—" }}</td>

                <td class="py-3.5 px-6">
                  <span
                    class="px-2 py-0.5 rounded-full text-xs font-medium uppercase bg-emerald-100 text-emerald-700"
                  >
                    Active
                  </span>
                </td>

                <td class="py-3.5 px-6 text-right">
                  <div class="flex justify-end gap-3">
                    <button
                      @click="openEditModal(c)"
                      class="text-blue-500 hover:text-blue-600 transition"
                      title="Edit Contact"
                    >
                      <EditIcon class="w-5 h-5" />
                    </button>
                    <button
                      @click="deleteContact(c.id)"
                      class="text-red-500 hover:text-red-600 transition"
                      title="Delete Contact"
                    >
                      <Trash2Icon class="w-5 h-5" />
                    </button>
                    <button
                      @click="blacklistContact(c.id)"
                      class="text-gray-500 hover:text-gray-700 transition"
                      title="Blacklist Contact"
                    >
                      <BanIcon class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          class="flex justify-between items-center px-6 py-4 text-sm text-gray-500"
        >
          <span
            >Page <b>{{ currentPage }}</b
            >of <b>{{ totalPages }}</b></span
          >
          <div class="flex gap-2">
            <button
              @click="prevPage"
              :disabled="currentPage === 1"
              class="px-3 py-1 border rounded-lg hover:bg-gray-100 disabled:opacity-50 transition-colors"
            >
              Prev
            </button>
            <button
              @click="nextPage"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 border rounded-lg hover:bg-gray-100 disabled:opacity-50 transition-colors"
            >
              Next
            </button>
          </div>
        </div>
      </div>

      <div
        v-else
        key="empty"
        class="bg-white p-10 rounded-2xl shadow-sm text-center text-gray-500"
      >
        <p class="text-lg font-medium mb-2">No active contacts found</p>
        <p class="text-sm text-gray-400">
          Try adding some or adjust your search.
        </p>
      </div>
    </transition>

    <SimpleModal v-if="showModal" @close="closeModal">
      <template #title>
        {{ editingContactId ? "✏️ Edit Contact" : "➕ Add Contact" }}
      </template>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Name</label
          >
          <input
            v-model="form.name"
            type="text"
            class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-emerald-400"
            placeholder="John Doe"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Phone</label
          >
          <input
            v-model="form.phone"
            type="text"
            class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-emerald-400"
            placeholder="+254700000000"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1"
            >Email</label
          >
          <input
            v-model="form.email"
            type="email"
            class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-emerald-400"
            placeholder="optional@email.com"
          />
        </div>
      </div>

      <template #footer>
        <button
          @click="closeModal"
          class="px-4 py-2 rounded-lg border text-gray-600 hover:bg-gray-100 transition"
        >
          Cancel
        </button>
        <button
          @click="saveContact"
          :disabled="saving"
          class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition min-w-[90px] disabled:opacity-75"
        >
          <span v-if="!saving">Save</span>
          <span v-else>Saving...</span>
        </button>
      </template>
    </SimpleModal>

    <SimpleModal v-if="showImportModal" @close="closeImportModal">
      <template #title>📤 Import Contacts</template>

      <div class="space-y-4">
        <div class="text-sm text-gray-600">
          <p>
            Upload an
            <span class="font-semibold">Excel (.xlsx or .xls)</span> file
            containing your contacts. The file should include columns:
            <span class="italic text-emerald-600">name, phone, email</span>.
          </p>
        </div>

        <div
          class="flex flex-col items-center border-2 border-dashed border-gray-300 rounded-lg p-6 text-center"
        >
          <UploadCloud class="w-10 h-10 text-gray-400 mb-3" />
          <input
            type="file"
            accept=".xlsx, .xls"
            @change="handleFileChange"
            class="hidden"
            ref="fileInput"
          />
          <p class="text-sm text-gray-500 mb-3">
            {{ selectedFile ? selectedFile.name : "No file selected" }}
          </p>
          <button
            @click="fileInput.click()"
            class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
          >
            Select File
          </button>
        </div>
      </div>

      <template #footer>
        <button
          @click="closeImportModal"
          class="px-4 py-2 rounded-lg border text-gray-600 hover:bg-gray-100 transition"
        >
          Cancel
        </button>
        <button
          @click="uploadContacts"
          :disabled="!selectedFile || uploading"
          class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition min-w-[100px] disabled:opacity-75"
        >
          <span v-if="!uploading">Upload</span>
          <span v-else>Uploading...</span>
        </button>
      </template>
    </SimpleModal>
  </div>
</template>
<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRoute } from "vue-router";
import Swal from "sweetalert2";
import contactService from "@/services/phonebookService";
import {
  EditIcon,
  Trash2Icon,
  PlusIcon,
  BanIcon,
  UploadCloud,
  DownloadCloud,
} from "lucide-vue-next";

// --- SimpleModal Component Implementation (for completeness) ---
const SimpleModal = {
  props: {
    large: { type: Boolean, default: false },
  },
  template: `
    <transition name="modal-fade">
      <div class="fixed inset-0 z-50 flex items-center justify-center bg-transparent p-4 pointer-events-none">
        <div 
          class="bg-white rounded-xl shadow-2xl transition-all max-h-[90vh] overflow-y-auto pointer-events-auto"
          :class="large ? 'w-full max-w-2xl' : 'w-full max-w-md'"
          @click.stop
        >
          <div class="flex justify-between items-center p-5 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-800">
              <slot name="title">Modal Title</slot>
            </h3>
            <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
          </div>

          <div class="p-5">
            <slot></slot>
          </div>

          <div v-if="$slots.footer" class="p-5 border-t border-gray-100 flex justify-end gap-3">
            <slot name="footer"></slot>
          </div>
        </div>
      </div>
    </transition>
  `,
  emits: ["close"],
};
// --- End of SimpleModal Component Implementation ---

const route = useRoute();
const contacts = ref([]);
const phonebookName = ref("Loading...");
const phonebookId = ref(route.params.id);
const loading = ref(true);
const saving = ref(false);

// Add/Edit Modal State
const showModal = ref(false);
const editingContactId = ref(null);
const form = ref({ name: "", phone: "", email: "" });
// Search, Sort, and Pagination State
const searchQuery = ref("");
const sortField = ref("name");
const sortOrder = ref("asc");
const currentPage = ref(1);
const perPage = 50;

// Import Modal State and Refs
const showImportModal = ref(false);
const selectedFile = ref(null);
const uploading = ref(false);
const fileInput = ref(null);

// --- Data Loading ---
const loadContacts = async () => {
  loading.value = true;
  try {
    const res = await contactService.getContacts(phonebookId.value);
    if (res.success) {
      contacts.value = (res.contacts || [])
        .map((c) => ({ ...c, status: c.status || "active" }))
        .filter((c) => c.status !== "blacklisted");
      if (currentPage.value > totalPages.value) {
        currentPage.value = 1;
      }
      phonebookName.value = res.phonebook_name || "Phonebook";
    }
  } catch (e) {
    Swal.fire("Error", "Failed to load contacts.", "error");
  } finally {
    loading.value = false;
  }
};

watch(searchQuery, () => {
  currentPage.value = 1;
});
const filteredContacts = computed(() => {
  let data = contacts.value;
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    data = data.filter(
      (c) =>
        c.name.toLowerCase().includes(q) ||
        c.phone.toLowerCase().includes(q) ||
        (c.email && c.email.toLowerCase().includes(q))
    );
  }

  // 2. Apply Sorting
  return data.sort((a, b) => {
    const valA = a[sortField.value]?.toString().toLowerCase() || "";
    const valB = b[sortField.value]?.toString().toLowerCase() || "";
    const comparison = valA.localeCompare(valB);

    return sortOrder.value === "asc" ? comparison : -comparison;
  });
});

// Computed property for total pages
const totalPages = computed(() =>
  Math.ceil(filteredContacts.value.length / perPage)
);

// Computed property for contacts on the current page
const paginatedContacts = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredContacts.value.slice(start, start + perPage);
});

// Sorting handler
const sort = (field) => {
  if (sortField.value === field) {
    sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
  } else {
    sortField.value = field;
    sortOrder.value = "asc";
  }
  // Reset to first page after sorting
  currentPage.value = 1;
};

// Pagination handlers
const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};
const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

// --- Modal Handlers & Actions (Same logic as before) ---
const openAddModal = () => {
  form.value = { name: "", phone: "", email: "" };
  editingContactId.value = null;
  showModal.value = true;
};
const openEditModal = (contact) => {
  form.value = {
    name: contact.name,
    phone: contact.phone,
    email: contact.email,
  };
  editingContactId.value = contact.id;
  showModal.value = true;
};
const closeModal = () => (showModal.value = false);

const saveContact = async () => {
  if (!form.value.name.trim() || !form.value.phone.trim()) {
    return Swal.fire(
      "Missing Fields",
      "Name and phone are required.",
      "warning"
    );
  }
  saving.value = true;
  try {
    let res;
    // NOTE: When adding a new contact, the server should default the status to 'active'.
    if (editingContactId.value) {
      res = await contactService.editContact(
        editingContactId.value,
        form.value
      );
    } else {
      res = await contactService.addContact(phonebookId.value, form.value);
    }
    if (res.success) {
      Swal.fire("Success", res.message, "success");
      closeModal();
      await loadContacts();
    } else {
      Swal.fire("Error", res.message || "Something went wrong.", "error");
    }
  } catch {
    Swal.fire("Error", "Failed to save contact.", "error");
  } finally {
    saving.value = false;
  }
};

const deleteContact = async (id) => {
  const confirm = await Swal.fire({
    title: "Delete this contact?",
    text: "This action cannot be undone!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, delete it!",
    confirmButtonColor: "#ef4444",
  });
  if (!confirm.isConfirmed) return;

  Swal.fire({
    title: "Deleting...",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });

  try {
    const res = await contactService.deleteContact(id);
    if (res.success) {
      Swal.fire("Deleted!", res.message, "success");
      await loadContacts();
    } else {
      Swal.fire("Error", res.message || "Failed to delete contact.", "error");
    }
  } catch {
    Swal.fire("Error", "Failed to delete contact.", "error");
  }
};

const blacklistContact = async (id) => {
  const confirm = await Swal.fire({
    title: "Blacklist this contact?",
    text: "This contact will be blocked from receiving messages. It will be removed from this 'Active' list.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, blacklist!",
    confirmButtonColor: "#6b7280",
  });
  if (!confirm.isConfirmed) return;

  Swal.fire({
    title: "Blacklisting...",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });

  try {
    // This action on the server updates the contact's status to 'blacklisted'
    const res = await contactService.blacklistContact(id);
    if (res.success) {
      Swal.fire("Blacklisted!", res.message, "success");
      await loadContacts(); // Reload will automatically filter it out
    } else {
      Swal.fire(
        "Error",
        res.message || "Failed to blacklist contact.",
        "error"
      );
    }
  } catch {
    Swal.fire("Error", "Failed to blacklist contact.", "error");
  }
};

const exportContacts = async () => {
  const loadingAlert = Swal.fire({
    title: "Exporting Contacts...",
    text: "Please wait while we prepare your file.",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });

  try {
    // You might need a specific endpoint to export *only* active contacts if the server doesn't handle it by default.
    const response = await contactService.exportContacts(phonebookId.value, {
      status: "active",
    });

    const blob = new Blob([response], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });

    const link = document.createElement("a");
    link.href = window.URL.createObjectURL(blob);
    link.download = `${phonebookName.value.replace(
      /\s+/g,
      "_"
    )}_active_contacts.xlsx`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    Swal.fire({
      icon: "success",
      title: "Export Complete!",
      text: "Your active contacts have been downloaded successfully.",
      timer: 2500,
      showConfirmButton: false,
    });
  } catch (error) {
    console.error(error);
    Swal.fire("Error", "Failed to export contacts.", "error");
  } finally {
    loadingAlert.close();
  }
};

const fileExtensions = [".xlsx", ".xls"];

const openImportModal = () => {
  selectedFile.value = null;
  if (fileInput.value) fileInput.value.value = "";
  showImportModal.value = true;
};

const closeImportModal = () => {
  showImportModal.value = false;
};

const handleFileChange = (e) => {
  const file = e.target.files[0];
  const isValid =
    file && fileExtensions.some((ext) => file.name.toLowerCase().endsWith(ext));

  if (isValid) {
    selectedFile.value = file;
  } else {
    Swal.fire(
      "Invalid File",
      "Please upload a valid Excel file (.xlsx or .xls).",
      "warning"
    );
    selectedFile.value = null;
    e.target.value = null;
  }
};

const uploadContacts = async () => {
  if (!selectedFile.value) {
    return Swal.fire("No File", "Please select a file first.", "info");
  }

  uploading.value = true;
  const formData = new FormData();
  formData.append("file", selectedFile.value);

  try {
    const res = await contactService.importContacts(phonebookId.value, formData);

    if (res.success) {
      Swal.fire("Success", res.message, "success");
      await loadContacts();
      closeImportModal();
    } else {
      // Show API error message (like missing columns)
      Swal.fire("Error", res.message || "Failed to import contacts.", "error");
    }
  } catch (error) {
    console.error(error);
    // Handle error response from backend (like validation errors)
    const errorMsg =
      error.response?.data?.message ||
      "Failed to upload contacts. Please try again.";
    Swal.fire("Error", errorMsg, "error");
  } finally {
    uploading.value = false;
  }
};


onMounted(() => loadContacts());
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
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
</style>
