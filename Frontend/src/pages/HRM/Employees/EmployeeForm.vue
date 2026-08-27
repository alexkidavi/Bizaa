<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex items-center gap-3 mb-7">
      <router-link to="/hrm/employees" class="w-9 h-9 rounded-xl flex items-center justify-center bg-white border border-[#D9EBF7] text-slate-500 hover:text-[#229DE7] hover:border-[#229DE7] transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
      </router-link>
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ isEdit ? "Edit Staff Profile" : "Add Staff Profile" }}</h1>
        <p class="text-slate-500 mt-1 text-sm">{{ isEdit ? "Update this staff member's HR profile." : "Create an HR profile for a staff login." }}</p>
      </div>
    </div>

    <div v-if="loading" class="max-w-4xl space-y-4">
      <div v-for="n in 4" :key="n" class="h-40 bg-slate-100 animate-pulse rounded-2xl"></div>
    </div>

    <form v-else @submit.prevent="save" class="max-w-4xl space-y-6">
      <!-- LOGIN & ACCESS -->
      <section class="card">
        <h2 class="card-title">Login &amp; Access</h2>
        <div class="card-body space-y-4">
          <!-- CREATE: new login, always -->
          <template v-if="!isEdit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="field-label required">Email</label>
                <input v-model="form.email" type="email" class="field-input" :class="{ 'border-red-400': errors.email }" placeholder="e.g. jane@business.com" />
                <p v-if="errors.email" class="field-error">A valid, unused email is required</p>
              </div>
              <div>
                <label class="field-label required">Phone</label>
                <input v-model="form.phone" type="tel" class="field-input" :class="{ 'border-red-400': errors.phone }" placeholder="e.g. 0712 345 678" />
                <p v-if="errors.phone" class="field-error">Phone is required</p>
              </div>
              <div>
                <label class="field-label required">Role</label>
                <select v-model="form.role_id" class="field-input" :class="{ 'border-red-400': errors.role_id }">
                  <option value="" disabled>Select role...</option>
                  <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                </select>
                <p v-if="errors.role_id" class="field-error">Select a role</p>
              </div>
              <div>
                <label class="field-label">Password</label>
                <input v-model="form.password" type="text" class="field-input" placeholder="Leave blank to auto-generate" />
              </div>
            </div>
          </template>

          <!-- EDIT: login fields stay editable here, not on a separate Staff screen -->
          <template v-else>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div>
                <label class="field-label required">Email</label>
                <input v-model="form.email" type="email" class="field-input" :class="{ 'border-red-400': errors.email }" />
              </div>
              <div>
                <label class="field-label required">Phone</label>
                <input v-model="form.phone" type="tel" class="field-input" :class="{ 'border-red-400': errors.phone }" />
              </div>
              <div>
                <label class="field-label required">Role</label>
                <select v-model="form.role_id" class="field-input" :class="{ 'border-red-400': errors.role_id }">
                  <option value="" disabled>Select role...</option>
                  <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
                </select>
              </div>
              <div>
                <label class="field-label">Reset Password</label>
                <input v-model="form.password" type="text" class="field-input" placeholder="Leave blank to keep current" />
              </div>
            </div>
            <p class="text-[10.5px] text-slate-400">
              Login access follows employment status — only "Active" employees can sign in.
            </p>
          </template>
        </div>
      </section>

      <!-- PERSONAL -->
      <section class="card">
        <h2 class="card-title">Personal Information</h2>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="flex items-center gap-4 md:col-span-2">
            <div class="w-20 h-20 rounded-2xl border-2 border-dashed border-[#B3DEFA] bg-[#EAF5FD] flex items-center justify-center overflow-hidden relative cursor-pointer flex-shrink-0" @click="photoInput?.click()">
              <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" />
              <svg v-else class="w-8 h-8 text-[#229DE7] opacity-60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 12V6.75A2.25 2.25 0 015.25 4.5h13.5A2.25 2.25 0 0121 6.75v10.5A2.25 2.25 0 0118.75 19.5H5.25A2.25 2.25 0 013 17.25V12z" />
              </svg>
            </div>
            <div>
              <button type="button" @click="photoInput?.click()" class="text-xs font-bold text-[#229DE7] hover:text-[#1682C4] underline underline-offset-2">
                {{ photoPreview ? "Change Photo" : "Upload Photo" }}
              </button>
              <p class="text-[10.5px] text-slate-400 mt-1">PNG or JPG, max 2MB.</p>
              <input ref="photoInput" type="file" accept="image/*" class="hidden" @change="onPhotoChange" />
            </div>
          </div>

          <div>
            <label class="field-label required">First Name</label>
            <input v-model="form.first_name" type="text" class="field-input" :class="{ 'border-red-400': errors.first_name }" />
          </div>
          <div>
            <label class="field-label">Middle Name</label>
            <input v-model="form.middle_name" type="text" class="field-input" />
          </div>
          <div>
            <label class="field-label required">Last Name</label>
            <input v-model="form.last_name" type="text" class="field-input" :class="{ 'border-red-400': errors.last_name }" />
          </div>
          <div>
            <label class="field-label">Gender</label>
            <select v-model="form.gender" class="field-input">
              <option value="">Select...</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div>
            <label class="field-label">Date of Birth</label>
            <input v-model="form.date_of_birth" type="date" class="field-input" />
          </div>
          <div>
            <label class="field-label">National ID / Passport</label>
            <input v-model="form.national_id" type="text" class="field-input" />
          </div>
          <div class="md:col-span-2">
            <label class="field-label">Address</label>
            <textarea v-model="form.address" rows="2" class="field-input"></textarea>
          </div>
        </div>
      </section>

      <!-- EMPLOYMENT -->
      <section class="card">
        <h2 class="card-title">Employment</h2>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="field-label">Employee Number</label>
            <input v-model="form.employee_number" type="text" class="field-input" placeholder="Auto-generated if left blank" />
          </div>
          <div>
            <label class="field-label required">Employment Date</label>
            <input v-model="form.employment_date" type="date" class="field-input" :class="{ 'border-red-400': errors.employment_date }" />
          </div>
          <div>
            <label class="field-label">Department</label>
            <select v-model="form.department_id" class="field-input">
              <option value="">Unassigned</option>
              <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
          </div>
          <div>
            <label class="field-label">Branch</label>
            <select v-model="form.branch_id" class="field-input">
              <option value="">Unassigned</option>
              <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
          <div>
            <label class="field-label">Designation</label>
            <input v-model="form.designation" type="text" class="field-input" placeholder="e.g. Store Manager" />
          </div>
          <div>
            <label class="field-label">Employment Type</label>
            <select v-model="form.employment_type" class="field-input">
              <option value="full_time">Full Time</option>
              <option value="part_time">Part Time</option>
              <option value="contract">Contract</option>
              <option value="intern">Intern</option>
              <option value="casual">Casual</option>
            </select>
          </div>
          <div class="md:col-span-2">
            <label class="field-label">Manager / Supervisor</label>
            <select v-model="form.manager_id" class="field-input">
              <option value="">None</option>
              <option v-for="m in managerOptions" :key="m.id" :value="m.id">{{ m.full_name }} ({{ m.employee_number }})</option>
            </select>
          </div>
        </div>
      </section>

      <!-- COMPENSATION & BANK -->
      <section class="card">
        <h2 class="card-title">Compensation &amp; Bank</h2>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="field-label">Basic Salary (KES)</label>
            <input v-model="form.basic_salary" type="number" min="0" step="0.01" class="field-input" />
          </div>
          <div></div>
          <div>
            <label class="field-label">Bank Name</label>
            <select v-model="form.bank_name" class="field-input">
              <option value="">Select bank...</option>
              <option v-for="b in banks" :key="b.id" :value="b.name">{{ b.name }}</option>
            </select>
          </div>
          <div>
            <label class="field-label">Bank Branch</label>
            <input v-model="form.bank_branch" type="text" class="field-input" />
          </div>
          <div>
            <label class="field-label">Account Number</label>
            <input v-model="form.bank_account_number" type="text" class="field-input" />
          </div>
        </div>
      </section>

      <!-- STATUTORY -->
      <section class="card">
        <h2 class="card-title">Statutory Information</h2>
        <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-5">
          <div>
            <label class="field-label">KRA PIN</label>
            <input v-model="form.kra_pin" type="text" class="field-input" />
          </div>
          <div>
            <label class="field-label">NSSF Number</label>
            <input v-model="form.nssf_number" type="text" class="field-input" />
          </div>
          <div>
            <label class="field-label">SHIF Number</label>
            <input v-model="form.shif_number" type="text" class="field-input" />
          </div>
          <div>
            <label class="field-label">Housing / Benefit Info</label>
            <input v-model="form.housing_benefit" type="text" class="field-input" />
          </div>
        </div>
      </section>

      <!-- EMERGENCY CONTACT -->
      <section class="card">
        <h2 class="card-title">Emergency Contact</h2>
        <div class="card-body grid grid-cols-1 md:grid-cols-3 gap-5">
          <div>
            <label class="field-label">Name</label>
            <input v-model="form.emergency_contact_name" type="text" class="field-input" />
          </div>
          <div>
            <label class="field-label">Phone</label>
            <input v-model="form.emergency_contact_phone" type="tel" class="field-input" />
          </div>
          <div>
            <label class="field-label">Relationship</label>
            <input v-model="form.emergency_contact_relationship" type="text" class="field-input" />
          </div>
        </div>
      </section>

      <!-- DOCUMENTS + NOTES -->
      <section class="card">
        <h2 class="card-title">Documents &amp; Notes</h2>
        <div class="card-body space-y-5">
          <div>
            <label class="field-label">Attach Documents</label>
            <input ref="docsInput" type="file" multiple class="field-input" @change="onDocsChange" />
            <ul v-if="newDocuments.length" class="mt-2 space-y-1">
              <li v-for="(f, i) in newDocuments" :key="i" class="text-xs text-slate-500 flex items-center justify-between bg-[#F5FAFF] rounded-lg px-3 py-1.5">
                {{ f.name }}
                <button type="button" @click="newDocuments.splice(i, 1)" class="text-red-500 font-bold">×</button>
              </li>
            </ul>
            <ul v-if="existingDocuments.length" class="mt-2 space-y-1">
              <li v-for="doc in existingDocuments" :key="doc.path" class="text-xs text-slate-600 flex items-center justify-between bg-white border border-[#D9EBF7] rounded-lg px-3 py-1.5">
                <button type="button" class="text-left hover:text-[#229DE7]" @click="download(doc)">{{ doc.name }}</button>
                <button type="button" @click="removeExistingDocument(doc)" class="text-red-500 font-bold">×</button>
              </li>
            </ul>
          </div>
          <div>
            <label class="field-label">Notes</label>
            <textarea v-model="form.notes" rows="3" class="field-input"></textarea>
          </div>
        </div>
      </section>

      <div class="flex items-center justify-end gap-3">
        <router-link to="/hrm/employees" class="px-5 py-2.5 border border-[#D9EBF7] rounded-xl bg-white text-sm font-bold text-slate-500 hover:border-red-300 hover:text-red-500 hover:bg-red-50 transition">Cancel</router-link>
        <button type="submit" :disabled="saving" class="flex items-center justify-center gap-2 px-8 py-2.5 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all active:scale-[0.98] disabled:opacity-60">
          {{ saving ? "Saving..." : isEdit ? "Update Staff Profile" : "Create Staff Profile" }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import Swal from "sweetalert2";
import EmployeeProfileService from "@/services/EmployeeProfileService";
import DepartmentService from "@/services/DepartmentService";
import BranchesService from "@/services/Branchesservice";
import RoleService from "@/services/RoleService";
import BankService from "@/services/BankService";

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const employeeId = computed(() => route.params.id);

const loading = ref(true);
const saving = ref(false);
const departments = ref([]);
const branches = ref([]);
const roles = ref([]);
const banks = ref([]);
const employees = ref([]);
const existingDocuments = ref([]);
const newDocuments = ref([]);
const photoFile = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);
const docsInput = ref(null);

const BASE_URL = import.meta.env.VITE_APP_URL ?? "";

const form = reactive({
  email: "",
  phone: "",
  role_id: "",
  password: "",
  employee_number: "",
  first_name: "",
  middle_name: "",
  last_name: "",
  gender: "",
  date_of_birth: "",
  national_id: "",
  address: "",
  employment_date: new Date().toISOString().slice(0, 10),
  department_id: "",
  branch_id: "",
  designation: "",
  employment_type: "full_time",
  manager_id: "",
  basic_salary: "",
  bank_name: "",
  bank_branch: "",
  bank_account_number: "",
  kra_pin: "",
  nssf_number: "",
  shif_number: "",
  housing_benefit: "",
  emergency_contact_name: "",
  emergency_contact_phone: "",
  emergency_contact_relationship: "",
  notes: "",
});

const errors = reactive({ email: false, phone: false, role_id: false, first_name: false, last_name: false, employment_date: false });

const managerOptions = computed(() => employees.value.filter((e) => e.id !== Number(employeeId.value)));

const onPhotoChange = (e) => {
  const file = e.target.files[0];
  if (!file) return;
  photoFile.value = file;
  const reader = new FileReader();
  reader.onload = (ev) => (photoPreview.value = ev.target.result);
  reader.readAsDataURL(file);
};

const onDocsChange = (e) => {
  newDocuments.value.push(...Array.from(e.target.files));
  docsInput.value.value = "";
};

const removeExistingDocument = async (doc) => {
  if (!isEdit.value) return;
  const result = await Swal.fire({ title: "Remove this document?", icon: "warning", showCancelButton: true, confirmButtonColor: "#229DE7" });
  if (!result.isConfirmed) return;
  await EmployeeProfileService.removeDocument(employeeId.value, doc.path);
  existingDocuments.value = existingDocuments.value.filter((d) => d.path !== doc.path);
};

const download = async (doc) => {
  const blob = await EmployeeProfileService.downloadDocument(employeeId.value, doc.path);
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = doc.name;
  a.click();
  URL.revokeObjectURL(url);
};

const validate = () => {
  errors.first_name = !form.first_name.trim();
  errors.last_name = !form.last_name.trim();
  errors.employment_date = !form.employment_date;
  errors.email = !form.email.trim();
  errors.phone = !form.phone.trim();
  errors.role_id = !form.role_id;

  return !errors.email && !errors.phone && !errors.role_id && !errors.first_name && !errors.last_name && !errors.employment_date;
};

const save = async () => {
  if (!validate()) return;
  saving.value = true;
  try {
    const payload = { ...form };
    if (photoFile.value) payload.photo = photoFile.value;
    if (newDocuments.value.length) payload.documents = newDocuments.value;

    let res;
    if (isEdit.value) {
      res = await EmployeeProfileService.update(employeeId.value, payload);
    } else {
      res = await EmployeeProfileService.create(payload);
    }

    const tempPassword = res?.temp_password;
    if (tempPassword) {
      await Swal.fire({
        icon: "success",
        title: "Staff profile created",
        html: `Temporary password for this login:<br><strong style="font-size:1.1em;letter-spacing:0.05em;">${tempPassword}</strong><br><span style="font-size:12px;color:#64748b;">Share this now — it won't be shown again.</span>`,
        confirmButtonColor: "#229DE7",
      });
    } else {
      Swal.fire({ icon: "success", title: isEdit.value ? "Staff profile updated" : "Staff profile created", confirmButtonColor: "#229DE7" });
    }
    router.push("/hrm/employees");
  } catch (err) {
    const res = err?.response;
    if (res?.status === 422 && res.data?.errors) {
      Swal.fire({ icon: "error", title: "Validation Error", text: Object.values(res.data.errors)[0]?.[0] || "Please check the form.", confirmButtonColor: "#229DE7" });
      return;
    }
    Swal.fire({ icon: "error", title: "Error", text: res?.data?.message || "Something went wrong.", confirmButtonColor: "#229DE7" });
  } finally {
    saving.value = false;
  }
};

const populateForm = (e) => {
  Object.keys(form).forEach((key) => {
    if (key in e && e[key] !== null) form[key] = e[key];
  });
  form.employment_date = e.employment_date ? e.employment_date.slice(0, 10) : "";
  form.date_of_birth = e.date_of_birth ? e.date_of_birth.slice(0, 10) : "";
  form.department_id = e.department_id ?? "";
  form.branch_id = e.branch_id ?? "";
  form.manager_id = e.manager_id ?? "";
  form.email = e.user?.email ?? "";
  form.phone = e.user?.phone ?? "";
  form.role_id = e.user?.role_id ?? "";
  form.password = "";
  if (e.photo) photoPreview.value = `${BASE_URL}/storage/${e.photo}`;
  existingDocuments.value = e.documents ?? [];
};

onMounted(async () => {
  try {
    const [deptRes, branchRes, roleRes, bankRes, empRes] = await Promise.all([
      DepartmentService.getAll(),
      BranchesService.getAllBranches(),
      RoleService.getAll(),
      BankService.getAll({ active_only: 1 }),
      EmployeeProfileService.getAll({ per_page: 500 }),
    ]);
    departments.value = deptRes?.data ?? [];
    branches.value = branchRes?.data?.data ?? branchRes?.data ?? [];
    roles.value = roleRes?.data?.data ?? roleRes?.data ?? [];
    banks.value = bankRes?.data ?? [];
    employees.value = empRes?.data ?? [];

    if (isEdit.value) {
      const res = await EmployeeProfileService.getById(employeeId.value);
      populateForm(res?.data ?? {});
    }
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.card {
  background: #fff;
  border: 1px solid #d9ebf7;
  border-radius: 16px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
  overflow: hidden;
}
.card-title {
  padding: 16px 24px;
  font-weight: 800;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #1682c4;
  border-bottom: 1px solid #eef5fb;
}
.card-body {
  padding: 20px 24px;
}
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
.field-error {
  font-size: 11px;
  font-weight: 600;
  color: #ef4444;
  margin-top: 4px;
}
</style>
