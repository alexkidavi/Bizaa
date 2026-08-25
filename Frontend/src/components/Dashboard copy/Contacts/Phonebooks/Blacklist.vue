<template>
  <div class="min-h-screen bg-gray-50 py-10 px-6 font-sans">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4"
    >
      <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">
        <span class="text-gray-600 font-semibold"> Manage Blacklisted Contacts</span>
      </h2>

      <!-- <div class="flex gap-2">
        <button
          @click="exportContacts"
          class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded-xl shadow transition flex items-center gap-2"
        >
          <DownloadCloud class="w-5 h-5" /> Export Contacts
        </button>
      </div> -->
    </div>

    <!-- Search + Filters -->
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
        {{ filteredContacts.length }} contact(s)
      </span>
    </div>

    <!-- Loader shimmer -->
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

      <!-- Data Table -->
      <div
        v-else-if="filteredContacts.length > 0"
        key="table"
        class="bg-white rounded-2xl shadow border border-gray-200 overflow-hidden transition-all duration-300"
      >
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-gray-700">
            <!-- Table Header -->
            <thead class="bg-gray-50 sticky top-0 z-10">
              <tr class="text-xs uppercase text-gray-500 tracking-wider">
                <th
                  @click="sort('name')"
                  class="py-3.5 px-6 text-left cursor-pointer"
                >
                  Name
                  <span v-if="sortField === 'name'">{{
                    sortOrder === "asc" ? "⬆️" : "⬇️"
                  }}</span>
                </th>
                <th
                  @click="sort('phone')"
                  class="py-3.5 px-6 text-left cursor-pointer"
                >
                  Phone
                  <span v-if="sortField === 'phone'">{{
                    sortOrder === "asc" ? "⬆️" : "⬇️"
                  }}</span>
                </th>
                <th class="py-3.5 px-6 text-left">Email</th>
                <th class="py-3.5 px-6 text-center">Status</th>
                <!-- NEW COLUMN -->
                <th class="py-3.5 px-6 text-right hidden">Actions</th>
              </tr>
            </thead>

            <!-- Table Body -->
            <tbody class="divide-y divide-gray-100">
              <tr
                v-for="(c, index) in paginatedContacts"
                :key="c.id"
                class="hover:bg-emerald-50 transition-colors duration-200"
              >
                <td class="py-3.5 px-6 font-medium text-gray-800">
                  {{ c.name }}
                </td>
                <td class="py-3.5 px-6 font-mono text-gray-700">
                  {{ c.phone }}
                </td>
                <td class="py-3.5 px-6 text-gray-500">{{ c.email || "—" }}</td>

                <!-- Status Label -->
                <td class="py-3.5 px-6 text-center">
                  <span
                    class="inline-block bg-red-100 text-black-800 text-xs font-semibold px-2.5 py-1 rounded-full"
                  >
                    Blacklisted
                  </span>
                </td>
 
                <td class="py-3.5 px-6 text-right hidden">
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

        <!-- Pagination -->
        <div
          class="flex justify-between items-center px-6 py-4 text-sm text-gray-500"
        >
          <span>Page {{ currentPage }} of {{ totalPages }}</span>
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
              :disabled="currentPage === totalPages"
              class="px-3 py-1 border rounded-lg hover:bg-gray-100 disabled:opacity-50"
            >
              Next
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-else
        key="empty"
        class="bg-white p-10 rounded-2xl shadow-sm text-center text-gray-500"
      >
        <p class="text-lg font-medium mb-2">No contacts found</p>
        <p class="text-sm text-gray-400">
          Try adding some or adjust your search.
        </p>
      </div>
    </transition>

    <!-- Add/Edit Modal -->
    <Modal v-if="showModal" @close="closeModal">
      <template #title>
        <div class="flex items-center gap-2">
          <component
            :is="editingContactId ? Edit3 : PlusCircle"
            class="w-5 h-5"
          />
          <span>{{ editingContactId ? "Edit Contact" : "Add Contact" }}</span>
        </div>
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
          class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition min-w-[90px]"
        >
          <span v-if="!saving">Save</span>
          <span v-else>Saving...</span>
        </button>
      </template>
    </Modal>
    <!-- Import Contacts Modal -->
    <Modal v-if="showImportModal" @close="closeImportModal">
      <template #title>
        <div class="flex items-center gap-2">
          <UploadCloud class="w-5 h-5" />
          <span>Import Contacts</span>
        </div>
      </template>

      <div class="space-y-4">
        <div class="text-sm text-gray-600">
          <p>
            Upload a <span class="font-semibold">.csv</span> file containing
            your contacts. The file should include columns:
            <span class="italic text-emerald-600"
              >name, phone, email(optional)</span
            >.
          </p>
        </div>

        <div
          class="flex flex-col items-center border-2 border-dashed border-gray-300 rounded-lg p-6 text-center"
        >
          <UploadCloud class="w-10 h-10 text-gray-400 mb-3" />
          <input
            type="file"
            accept=".xlsx,.xls"
            @change="handleFileChange"
            class="hidden"
            ref="fileInput"
          />

          <p class="text-sm text-gray-500 mb-3">
            {{ selectedFile ? selectedFile.name : "No file selected" }}
          </p>
          <button
            @click="$refs.fileInput.click()"
            class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold"
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
          class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition min-w-[100px]"
        >
          <span v-if="!uploading">Upload</span>
          <span v-else>Uploading...</span>
        </button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
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
  Edit3,
  PlusCircle,
} from "lucide-vue-next";

const route = useRoute();
const contacts = ref([]);
const phonebookName = ref("Loading...");
const phonebookId = ref(route.params.id);
const loading = ref(true);
const saving = ref(false);
const showModal = ref(false);
const editingContactId = ref(null);

const form = ref({ name: "", phone: "", email: "" });
const searchQuery = ref("");
const sortField = ref("name");
const sortOrder = ref("asc");
const currentPage = ref(1);
const perPage = 100;

// Load contacts
const loadContacts = async () => {
  loading.value = true;
  try {
    const res = await contactService.getBlacklistedContacts();
    if (res.success) {
      contacts.value = res.contacts || [];
    }
  } catch (e) {
    Swal.fire("Error", "Failed to load contacts.", "error");
  } finally {
    loading.value = false;
  }
};

// Filtering + Sorting
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
  return data.sort((a, b) => {
    const valA = a[sortField.value]?.toString().toLowerCase() || "";
    const valB = b[sortField.value]?.toString().toLowerCase() || "";
    return sortOrder.value === "asc"
      ? valA.localeCompare(valB)
      : valB.localeCompare(valA);
  });
});

const totalPages = computed(() =>
  Math.ceil(filteredContacts.value.length / perPage)
);
const paginatedContacts = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredContacts.value.slice(start, start + perPage);
});

// Sorting & pagination
const sort = (field) => {
  if (sortField.value === field) {
    sortOrder.value = sortOrder.value === "asc" ? "desc" : "asc";
  } else {
    sortField.value = field;
    sortOrder.value = "asc";
  }
};
const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};
const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

// Modal handlers
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

// Save Contact
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
      editingContactId.value = null;
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

// Delete contact
const deleteContact = async (id) => {
  const confirm = await Swal.fire({
    title: "Delete this contact?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, delete it!",
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
    }
  } catch {
    Swal.fire("Error", "Failed to delete contact.", "error");
  }
};

// Blacklist contact
const blacklistContact = async (id) => {
  const confirm = await Swal.fire({
    title: "Blacklist this contact?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, blacklist!",
  });
  if (!confirm.isConfirmed) return;

  Swal.fire({
    title: "Blacklisting...",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });

  try {
    const res = await contactService.blacklistContact(id);
    if (res.success) {
      Swal.fire("Blacklisted!", res.message, "success");
      await loadContacts();
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
    const response = await contactService.exportContacts(100000000000000);
    const blob = new Blob([response], {
      type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    });

    const link = document.createElement("a");
    link.href = window.URL.createObjectURL(blob);
    link.download = `${phonebookName.value.replace(/\s+/g, "_")}_contacts.xlsx`;
    link.click();

    Swal.fire({
      icon: "success",
      title: "Export Complete!",
      text: "Your contacts have been downloaded successfully.",
      timer: 2500,
      showConfirmButton: false,
    });
  } catch (error) {
    console.error(error);
    Swal.fire("Error", "Failed to export contacts.", "error");
  }
};
const showImportModal = ref(false);
const selectedFile = ref(null);
const uploading = ref(false);

const openImportModal = () => {
  selectedFile.value = null;
  showImportModal.value = true;
};

const closeImportModal = () => {
  showImportModal.value = false;
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
</style>
