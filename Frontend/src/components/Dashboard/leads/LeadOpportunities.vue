<template>
  <div class="bg-white p-6 rounded-xl shadow-md flex flex-col gap-4">
    <div class="flex justify-between items-center">
      <h2 class="text-xl font-semibold text-gray-800">Opportunities</h2>
      <button
        @click="openOpportunityModal()"
        :disabled="loading"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition disabled:opacity-50"
      >
        Add Opportunity
      </button>
    </div>

    <div v-if="loading" class="space-y-2">
      <div v-for="i in 3" :key="i" class="skeleton h-6 w-full"></div>
    </div>
    <div v-else>
      <div
        v-if="opportunities.length === 0"
        class="text-gray-500 text-center py-6"
      >
        No opportunities found.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 rounded-lg">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider"
              >
                #
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider"
              >
                Title
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider"
              >
                Amount
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider"
              >
                Stage
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider"
              >
                Close Date
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr
              v-for="(opp, index) in opportunities"
              :key="opp.id"
              class="hover:bg-blue-50 cursor-pointer transition"
              @click="openOpportunityModal(opp)"
            >
              <td class="px-6 py-4">{{ index + 1 }}</td>
              <td class="px-6 py-4">{{ opp.name }}</td>
              <td class="px-6 py-4">
                {{ formatCurrency(opp.amount) }}
              </td>
              <td class="px-6 py-4 capitalize">{{ opp.stage }}</td>
              <td class="px-6 py-4">
                {{ formatDate(opp.expected_close) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <transition name="fade">
      <div
        v-if="showOpportunityModal"
        class="fixed inset-0 bg-black/50 flex justify-center items-start pt-20 z-50"
        @click.self="closeOpportunityModal"
      >
        <div class="bg-white w-full max-w-2xl p-6 rounded-xl shadow-lg">
          <h2 class="text-xl font-semibold mb-4">
            {{ opportunityForm.id ? "Edit Opportunity" : "Add Opportunity" }}
          </h2>
          <form @submit.prevent="submitOpportunity" class="grid gap-4 md:grid-cols-2">
            <input type="hidden" v-model="opportunityForm.lead_id" />

            <div>
              <label class="font-medium">Opportunity Name</label>
              <input
                v-model="opportunityForm.name"
                required
                class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
              />
            </div>

            <div>
              <label class="font-medium">Stage</label>
              <select
                v-model="opportunityForm.stage"
                class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
              >
                <option value="Prospecting">Prospecting</option>
                <option value="Qualification">Qualification</option>
                <option value="Proposal">Proposal</option>
                <option value="Negotiation">Negotiation</option>
                <option value="Closed Won">Closed Won</option>
                <option value="Closed Lost">Closed Lost</option>
              </select>
            </div>

            <div>
              <label class="font-medium">Amount</label>
              <input
                v-model="opportunityForm.amount"
                type="number"
                class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
              />
            </div>

            <div>
              <label class="font-medium">Expected Close Date</label>
              <input
                v-model="opportunityForm.expected_close"
                type="date"
                class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
              />
            </div>

            <div class="col-span-2">
              <label class="font-medium">Notes</label>
              <textarea
                v-model="opportunityForm.notes"
                rows="3"
                class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
              ></textarea>
            </div>

            <div class="col-span-2 flex justify-end gap-2">
              <button
                type="button"
                @click="closeOpportunityModal"
                class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
              >
                {{ opportunityForm.id ? "Update" : "Save" }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from "vue";
import opportunityService from "@/services/opportunityService";

const props = defineProps({
  leadId: {
    type: [Number, String],
    required: true,
  },
});
const emit = defineEmits(["opportunityUpdated"]); 
/* reactive state */
const opportunities = ref([]);
const loading = ref(true);

/* Opportunity state */
const showOpportunityModal = ref(false);
const opportunityForm = reactive({
  id: null,
  name: "",
  lead_id: props.leadId,
  stage: "Prospecting",
  amount: "",
  expected_close: "",
  notes: "",
});

/* helper formatters */
const formatDate = (dateStr) =>
  !dateStr
    ? "—"
    : new Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
      }).format(new Date(dateStr));

const formatCurrency = (val) => {
  const num = Number(val) || 0;
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "KES",
  }).format(num);
};

/* fetchers */
const fetchOpportunities = async () => {
  loading.value = true;
  try {
    if (!props.leadId) {
      opportunities.value = [];
      return;
    }
    const res = await opportunityService.fetchByLead(props.leadId);
    opportunities.value = res.data || [];
  } catch (err) {
    console.error("fetchOpportunities:", err);
    opportunities.value = [];
  } finally {
    loading.value = false;
  }
};

/* modals & handlers */
const openOpportunityModal = (opp = null) => {
  opportunityForm.id = opp?.id || null;
  opportunityForm.name = opp?.name || "";
  opportunityForm.lead_id = props.leadId; // Always use prop's leadId
  opportunityForm.stage = opp?.stage || "Prospecting";
  opportunityForm.amount = opp?.amount || "";
  opportunityForm.expected_close = opp?.expected_close || "";
  opportunityForm.notes = opp?.notes || "";
  showOpportunityModal.value = true;
};

const closeOpportunityModal = () => {
  showOpportunityModal.value = false;
};

const submitOpportunity = async () => {
  try {
    Swal.fire({
      title: opportunityForm.id ? "Updating..." : "Saving...",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    const payload = new FormData();
    Object.keys(opportunityForm).forEach((k) =>
      payload.append(k, opportunityForm[k])
    );
    if (opportunityForm.id) {
      await opportunityService.update(opportunityForm.id, payload);
    } else {
      await opportunityService.create(payload);
    }

    await fetchOpportunities();
    closeOpportunityModal();
    // Emit event up to parent in case it needs to react (e.g., refresh tasks)
    emit("opportunityUpdated"); 
    
    Swal.fire({
      icon: "success",
      title: "Success!",
      text: opportunityForm.id
        ? "Opportunity updated."
        : "Opportunity added.",
      timer: 1500,
      showConfirmButton: false,
    });
  } catch (err) {
    console.error("submitOpportunity:", err);
    Swal.fire({ icon: "error", title: "Error!", text: "Something went wrong." });
  }
};

watch(() => props.leadId, (newId) => {
    opportunityForm.lead_id = newId;
    fetchOpportunities();
}, { immediate: true });

</script>

<style scoped>
/* Keep skeleton and fade transition styles if they are not in a global stylesheet */
.skeleton {
  background-color: #e2e8f0;
  border-radius: 0.25rem;
  position: relative;
  overflow: hidden;
}
.skeleton::after {
  content: "";
  position: absolute;
  top: 0;
  left: -100%;
  height: 100%;
  width: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.5),
    transparent
  );
  animation: shimmer 1.5s infinite;
}
@keyframes shimmer {
  100% {
    transform: translateX(100%);
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>