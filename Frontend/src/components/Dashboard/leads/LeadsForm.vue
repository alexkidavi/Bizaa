<template>
  <div class="md:col-span-2 bg-white p-6 rounded-xl shadow-lg">
    <!-- Header with search + Add button -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
      <h2 class="text-2xl font-bold text-gray-800">
        Leads ({{ filteredLeads.length }})
      </h2>
      <div class="flex items-center gap-2">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search..."
          class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <button
          @click="openModal()"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"
        >
          + Add Lead
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-white shadow-md rounded-lg border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="header in tableHeaders"
              :key="header.key"
              class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider"
            >
              {{ header.label }}
            </th>
          </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-100">
          <!-- Loading -->
          <tr v-if="isLoading">
            <td colspan="7" class="px-6 py-8 text-center text-blue-500 text-base">
              Loading leads...
            </td>
          </tr>

          <!-- Empty -->
          <tr v-else-if="filteredLeads.length === 0">
            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
              No leads found.
            </td>
          </tr>

          <!-- Rows -->
          <tr
            v-for="lead in paginatedLeads"
            :key="lead.id"
            class="hover:bg-blue-50 transition"
          >
            <td class="px-6 py-4 text-sm font-medium text-gray-700">{{ lead.id }}</td>
            <td class="px-6 py-4 text-sm text-gray-800">{{ lead.name }}</td>
            <td class="px-6 py-4 text-sm text-gray-800">{{ lead.email || '—' }}</td>
            <td class="px-6 py-4 text-sm text-gray-800">{{ lead.phone || '—' }}</td>
            <td class="px-6 py-4 text-sm text-gray-800">{{ lead.source?.name || '—' }}</td>
            <td class="px-6 py-4 text-sm">
              <span
                :class="{
                  'bg-green-100 text-green-700':
                    ['new', 'contacted', 'converted'].includes(lead.status),
                  'bg-red-100 text-red-700': lead.status === 'lost',
                }"
                class="px-3 py-1 rounded-full text-xs font-semibold capitalize"
              >
                {{ lead.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap flex items-center space-x-3">
              <!-- Edit -->
              <button
                @click="openModal(lead)"
                class="text-blue-600 hover:text-blue-800 p-2 rounded-full hover:bg-blue-50 transition"
                title="Edit"
              >
                <Pencil :size="18" />
              </button>

              <!-- Toggle -->
              <button
                @click="toggleStatus(lead)"
                :class="lead.status !== 'lost'
                    ? 'text-green-600 hover:text-green-800 hover:bg-green-50'
                    : 'text-red-600 hover:text-red-800 hover:bg-red-50'"
                class="p-2 rounded-full transition"
                :title="lead.status !== 'lost' ? 'Mark as Lost' : 'Restore Lead'"
              >
                <component :is="lead.status !== 'lost' ? EyeOff : Eye" :size="18" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="flex justify-end mt-4 space-x-2">
      <button
        class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 disabled:opacity-50"
        @click="prevPage"
        :disabled="currentPage === 1"
      >
        Prev
      </button>
      <button
        class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 disabled:opacity-50"
        @click="nextPage"
        :disabled="currentPage === totalPages"
      >
        Next
      </button>
    </div>

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white w-full max-w-2xl rounded-lg shadow-lg p-6 relative">
        <h2 class="text-xl font-semibold mb-3">
          {{ form.id ? "Edit Lead" : "Add New Lead" }}
        </h2>

        <form @submit.prevent="handleSubmit" class="grid gap-4 md:grid-cols-2">
          <!-- Name -->
          <div>
            <label class="block mb-1">Name</label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Enter name"
              class="w-full border border-gray-400 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>

          <!-- Email -->
          <div>
            <label class="block mb-1">Email</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="Enter email"
              class="w-full border border-gray-400 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Phone -->
          <div>
            <label class="block mb-1">Phone</label>
            <input
              v-model="form.phone"
              type="tel"
              placeholder="Enter phone"
              class="w-full border border-gray-400 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <!-- Source -->
          <div>
            <label class="block mb-1">Source</label>
            <select
              v-model="form.source_id"
              class="w-full border border-gray-400 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Select Source</option>
              <option v-for="s in sources" :key="s.id" :value="s.id">
                {{ s.name }}
              </option>
            </select>
          </div>

          <!-- Status -->
          <div>
            <label class="block mb-1">Status</label>
            <select
              v-model="form.status"
              class="w-full border border-gray-400 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="new">New</option>
              <option value="contacted">Contacted</option>
              <option value="converted">Converted</option>
              <option value="lost">Lost</option>
            </select>
          </div>

          <!-- Notes -->
          <div class="col-span-2">
            <label class="block mb-1">Notes</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full border border-gray-400 rounded p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
            ></textarea>
          </div>

          <!-- Actions -->
          <div class="col-span-2 flex justify-end gap-2">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              {{ form.id ? "Update Lead" : "Save Lead" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Pencil, Eye, EyeOff } from "lucide-vue-next";
import Swal from "sweetalert2";
import leadService from "@/services/leadService";
import leadSourceService from "@/services/leadSourceService";

// Props
const props = defineProps({
  leads: {
    type: Array,
    default: () => [],
  },
});

// Emits
const emit = defineEmits(["refresh"]);

// State
const isLoading = ref(false);
const searchQuery = ref("");
const currentPage = ref(1);
const perPage = 10;

const showModal = ref(false);
const sources = ref([]);
const form = ref({
  id: null,
  name: "",
  email: "",
  phone: "",
  source_id: "",
  status: "new",
  notes: "",
});

// Headers
const tableHeaders = [
  { key: "id", label: "ID" },
  { key: "name", label: "Name" },
  { key: "email", label: "Email" },
  { key: "phone", label: "Phone" },
  { key: "source", label: "Source" },
  { key: "status", label: "Status" },
  { key: "actions", label: "Actions" },
];

// Computed
const filteredLeads = computed(() => {
  const q = searchQuery.value.toLowerCase();
  return props.leads.filter((l) =>
    [l.name, l.email, l.phone, l.source?.name, l.status]
      .filter(Boolean)
      .some((f) => f.toLowerCase().includes(q))
  );
});
const paginatedLeads = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredLeads.value.slice(start, start + perPage);
});
const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredLeads.value.length / perPage))
);

// Pagination
const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};
const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

// Modal controls
const openModal = (lead = null) => {
  form.value = lead
    ? { ...lead, source_id: lead.source?.id || "" }
    : {
        id: null,
        name: "",
        email: "",
        phone: "",
        source_id: "",
        status: "new",
        notes: "",
      };
  showModal.value = true;
};
const closeModal = () => (showModal.value = false);

// Save lead
const handleSubmit = async () => {
  if (!form.value.name) {
    Swal.fire("Validation Error", "Name is required", "error");
    return;
  }
  Swal.fire({
    title: form.value.id ? "Updating Lead..." : "Saving Lead...",
    didOpen: () => Swal.showLoading(),
  });
  try {
    const response = form.value.id
      ? await leadService.update(form.value.id, form.value)
      : await leadService.store(form.value);
    Swal.fire("Success", "Lead saved successfully", "success");
    closeModal();
    emit("refresh");
  } catch (e) {
    Swal.fire("Error", "Failed to save lead", "error");
  }
};

// Toggle status
const toggleStatus = async (lead) => {
  const confirm = await Swal.fire({
    title: `${lead.status !== "lost" ? "Mark as Lost" : "Restore Lead"}?`,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Yes",
  });
  if (!confirm.isConfirmed) return;
  Swal.fire({ title: "Updating...", didOpen: () => Swal.showLoading() });
  try {
    await leadService.toggleStatus(lead.id);
    lead.status = lead.status !== "lost" ? "lost" : "new";
    Swal.fire("Success", "Lead status updated", "success");
  } catch {
    Swal.fire("Error", "Failed to update lead status", "error");
  }
};

// Load sources
onMounted(async () => {
  try {
    const res = await leadSourceService.fetchAll();
    sources.value = res.data.data || [];
  } catch {
    sources.value = [];
  }
});
</script>

<style scoped>
button:disabled {
  cursor: not-allowed;
}
</style>
