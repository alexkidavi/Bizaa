<template>
  <div class="p-6 bg-[#F0F6FB] min-h-screen font-sans text-slate-900">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-7">
      <div>
        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">Payroll Configuration</h1>
        <p class="text-slate-500 mt-1 text-sm">Earnings/deductions, PAYE bands, statutory rates and pay settings.</p>
      </div>
      <router-link to="/hrm/payroll" class="px-4 py-2.5 bg-white border border-[#D9EBF7] hover:border-[#229DE7] hover:text-[#229DE7] text-slate-600 text-xs font-bold rounded-xl transition-all">
        &larr; Back to Payroll
      </router-link>
    </div>

    <!-- TABS -->
    <div class="flex items-center gap-1.5 mb-6 bg-white border border-[#D9EBF7] rounded-2xl p-1.5 shadow-sm w-fit overflow-x-auto">
      <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
        class="px-4 py-2 text-sm font-bold rounded-xl transition whitespace-nowrap"
        :class="activeTab === tab.key ? 'bg-[#229DE7] text-white shadow-md shadow-[#229DE7]/30' : 'text-slate-500 hover:bg-[#EAF5FD] hover:text-[#229DE7]'">
        {{ tab.label }}
      </button>
    </div>

    <!-- ================= GENERAL SETTINGS ================= -->
    <div v-if="activeTab === 'settings'" class="max-w-2xl">
      <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
        <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
        <div class="px-6 pt-5 pb-4 border-b border-[#EEF5FB]">
          <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">Pay & Working Time Settings</h2>
        </div>
        <div v-if="settingsLoading" class="p-6 space-y-3"><div v-for="n in 4" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>
        <form v-else @submit.prevent="saveSettings" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="field-label">Pay Frequency</label>
              <select v-model="settingsForm.pay_frequency" class="field-input">
                <option value="monthly">Monthly</option>
                <option value="biweekly">Biweekly</option>
                <option value="weekly">Weekly</option>
              </select>
            </div>
            <div>
              <label class="field-label">Currency</label>
              <input v-model="settingsForm.currency" type="text" maxlength="5" class="field-input" />
            </div>
            <div>
              <label class="field-label">Working Days / Month</label>
              <input v-model="settingsForm.working_days_per_month" type="number" min="1" max="31" class="field-input" />
            </div>
            <div>
              <label class="field-label">Standard Hours / Day</label>
              <input v-model="settingsForm.standard_hours_per_day" type="number" step="0.5" min="1" max="24" class="field-input" />
            </div>
            <div>
              <label class="field-label">Overtime Multiplier</label>
              <input v-model="settingsForm.overtime_multiplier" type="number" step="0.1" min="1" max="5" class="field-input" />
              <p class="text-[11px] text-slate-400 mt-1">e.g. 1.5 = time-and-a-half</p>
            </div>
            <div>
              <label class="field-label">PAYE Personal Relief</label>
              <input v-model="settingsForm.personal_relief" type="number" step="0.01" min="0" class="field-input" />
            </div>
            <div>
              <label class="field-label">Rounding Mode</label>
              <select v-model="settingsForm.rounding_mode" class="field-input">
                <option value="none">No rounding</option>
                <option value="nearest">Nearest whole unit</option>
                <option value="up">Round up</option>
                <option value="down">Round down</option>
              </select>
            </div>
            <div>
              <label class="field-label">Proration Method</label>
              <select v-model="settingsForm.proration_method" class="field-input">
                <option value="calendar_days">Calendar days</option>
                <option value="working_days">Working days</option>
              </select>
              <p class="text-[11px] text-slate-400 mt-1">For partial-month pay (new hires/exits)</p>
            </div>
          </div>
          <button type="submit" :disabled="savingSettings" class="px-6 py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
            {{ savingSettings ? 'Saving...' : 'Save Settings' }}
          </button>
        </form>
      </div>
    </div>

    <!-- ================= SALARY COMPONENTS ================= -->
    <div v-else-if="activeTab === 'components'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">{{ compEditingId ? 'Edit Component' : 'Add Component' }}</h2>
            <button v-if="compEditingId" @click="resetComponentForm" class="text-xs font-bold text-slate-400 hover:text-red-500 transition">Cancel</button>
          </div>
          <form @submit.prevent="saveComponent" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Name</label>
              <input v-model="compForm.name" type="text" class="field-input" placeholder="e.g. House Allowance" />
            </div>
            <div>
              <label class="field-label required">Code</label>
              <input v-model="compForm.code" type="text" class="field-input" placeholder="e.g. HOUSE_ALLOWANCE" />
            </div>
            <div>
              <label class="field-label required">Type</label>
              <select v-model="compForm.type" class="field-input">
                <option value="earning">Earning</option>
                <option value="deduction">Deduction</option>
              </select>
            </div>
            <div>
              <label class="field-label required">Calculation</label>
              <select v-model="compForm.calculation_type" class="field-input">
                <option value="fixed">Fixed amount</option>
                <option value="percentage_of_basic">% of basic salary</option>
              </select>
            </div>
            <div v-if="compForm.calculation_type === 'fixed'">
              <label class="field-label">Amount</label>
              <input v-model="compForm.amount" type="number" step="0.01" min="0" class="field-input" />
            </div>
            <div v-else>
              <label class="field-label">Percentage</label>
              <input v-model="compForm.percentage" type="number" step="0.01" min="0" max="100" class="field-input" />
            </div>
            <div>
              <label class="field-label">Description</label>
              <textarea v-model="compForm.description" rows="2" class="field-input"></textarea>
            </div>
            <div class="space-y-2">
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input v-model="compForm.is_taxable" type="checkbox" class="w-4 h-4 rounded border-[#D9EBF7] text-[#229DE7]" />
                <span class="text-xs font-bold text-slate-600">Taxable (counts toward PAYE income)</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input v-model="compForm.is_active" type="checkbox" class="w-4 h-4 rounded border-[#D9EBF7] text-[#229DE7]" />
                <span class="text-xs font-bold text-slate-600">Active</span>
              </label>
            </div>
            <button type="submit" :disabled="compSaving" class="w-full py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              {{ compSaving ? 'Saving...' : compEditingId ? 'Update' : 'Add Component' }}
            </button>
          </form>
        </div>
      </aside>
      <main class="lg:col-span-8">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB]"><span class="font-bold text-sm text-slate-700">{{ components.length }} components</span></div>
          <div v-if="compLoading" class="p-6 space-y-3"><div v-for="n in 3" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>
          <div v-else-if="components.length === 0" class="py-20 text-center"><p class="font-bold text-slate-700">No salary components defined</p></div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                  <th class="px-5 py-3.5">Name</th>
                  <th class="px-5 py-3.5">Type</th>
                  <th class="px-5 py-3.5">Value</th>
                  <th class="px-5 py-3.5">Flags</th>
                  <th class="px-5 py-3.5 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-[#EEF5FB]">
                <tr v-for="c in components" :key="c.id" class="hover:bg-[#F5FAFF] transition-colors">
                  <td class="px-5 py-4">
                    <p class="font-bold text-sm text-slate-900">{{ c.name }}</p>
                    <p class="text-xs text-slate-400">{{ c.code }}</p>
                  </td>
                  <td class="px-5 py-4">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold capitalize" :class="c.type === 'earning' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">{{ c.type }}</span>
                  </td>
                  <td class="px-5 py-4 text-sm font-bold text-slate-700">
                    {{ c.calculation_type === 'fixed' ? fmtMoney(c.amount) : `${c.percentage}% of basic` }}
                  </td>
                  <td class="px-5 py-4">
                    <div class="flex flex-wrap gap-1">
                      <span v-if="!c.is_taxable" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Non-taxable</span>
                      <span v-if="!c.is_active" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Inactive</span>
                    </div>
                  </td>
                  <td class="px-5 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button @click="editComponent(c)" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                      <button @click="removeComponent(c.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>

    <!-- ================= PAYE TAX BANDS ================= -->
    <div v-else-if="activeTab === 'tax-bands'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">{{ bandEditingId ? 'Edit Tax Band' : 'Add Tax Band' }}</h2>
            <button v-if="bandEditingId" @click="resetBandForm" class="text-xs font-bold text-slate-400 hover:text-red-500 transition">Cancel</button>
          </div>
          <form @submit.prevent="saveBand" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Effective Date</label>
              <input v-model="bandForm.effective_date" type="date" class="field-input" />
              <p class="text-[11px] text-slate-400 mt-1">Bands sharing a date form one PAYE version.</p>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label required">Lower Limit</label>
                <input v-model="bandForm.lower_limit" type="number" step="0.01" min="0" class="field-input" />
              </div>
              <div>
                <label class="field-label">Upper Limit</label>
                <input v-model="bandForm.upper_limit" type="number" step="0.01" min="0" class="field-input" placeholder="blank = unbounded" />
              </div>
            </div>
            <div>
              <label class="field-label required">Rate (%)</label>
              <input v-model="bandForm.rate" type="number" step="0.01" min="0" max="100" class="field-input" />
            </div>
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input v-model="bandForm.is_active" type="checkbox" class="w-4 h-4 rounded border-[#D9EBF7] text-[#229DE7]" />
              <span class="text-xs font-bold text-slate-600">Active</span>
            </label>
            <button type="submit" :disabled="bandSaving" class="w-full py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              {{ bandSaving ? 'Saving...' : bandEditingId ? 'Update' : 'Add Band' }}
            </button>
          </form>
        </div>
      </aside>
      <main class="lg:col-span-8 space-y-4">
        <div v-if="bandLoading" class="bg-white border border-[#D9EBF7] rounded-2xl p-6 space-y-3"><div v-for="n in 3" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>
        <div v-else-if="bandVersions.length === 0" class="bg-white border border-[#D9EBF7] rounded-2xl py-20 text-center"><p class="font-bold text-slate-700">No PAYE bands defined</p></div>
        <div v-for="version in bandVersions" :key="version.date" class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB] flex items-center justify-between">
            <span class="font-bold text-sm text-slate-700">Effective {{ fmtDate(version.date) }}</span>
            <span class="text-xs text-slate-400">{{ version.bands.length }} band(s)</span>
          </div>
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                <th class="px-5 py-3">Range (KES)</th>
                <th class="px-5 py-3">Rate</th>
                <th class="px-5 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF5FB]">
              <tr v-for="b in version.bands" :key="b.id" class="hover:bg-[#F5FAFF] transition-colors">
                <td class="px-5 py-3 text-sm text-slate-700">{{ fmtMoney(b.lower_limit) }} – {{ b.upper_limit ? fmtMoney(b.upper_limit) : '∞' }}</td>
                <td class="px-5 py-3 text-sm font-bold text-slate-900">{{ b.rate }}%<span v-if="!b.is_active" class="ml-2 text-[10px] font-bold text-slate-400">(inactive)</span></td>
                <td class="px-5 py-3 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="editBand(b)" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                    <button @click="removeBand(b.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </main>
    </div>

    <!-- ================= STATUTORY RATES ================= -->
    <div v-else-if="activeTab === 'statutory'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
      <aside class="lg:col-span-4">
        <div class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden sticky top-6">
          <div class="h-[3px] bg-gradient-to-r from-[#229DE7] via-[#5BB8F5] to-[#229DE7]"></div>
          <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-[#EEF5FB]">
            <h2 class="font-extrabold text-sm uppercase tracking-widest text-[#1682C4]">{{ rateEditingId ? 'Edit Rate' : 'Add Statutory Rate' }}</h2>
            <button v-if="rateEditingId" @click="resetRateForm" class="text-xs font-bold text-slate-400 hover:text-red-500 transition">Cancel</button>
          </div>
          <form @submit.prevent="saveRate" class="p-6 space-y-4">
            <div>
              <label class="field-label required">Scheme</label>
              <select v-model="rateForm.scheme" class="field-input">
                <option value="nssf">NSSF</option>
                <option value="shif">SHIF</option>
                <option value="housing_levy">Housing Levy</option>
              </select>
            </div>
            <div>
              <label class="field-label required">Effective Date</label>
              <input v-model="rateForm.effective_date" type="date" class="field-input" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label">Lower Limit</label>
                <input v-model="rateForm.lower_limit" type="number" step="0.01" min="0" class="field-input" />
              </div>
              <div>
                <label class="field-label">Upper Limit</label>
                <input v-model="rateForm.upper_limit" type="number" step="0.01" min="0" class="field-input" placeholder="blank = unbounded" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="field-label">Employee Rate (%)</label>
                <input v-model="rateForm.employee_rate" type="number" step="0.01" min="0" max="100" class="field-input" />
              </div>
              <div>
                <label class="field-label">Employer Rate (%)</label>
                <input v-model="rateForm.employer_rate" type="number" step="0.01" min="0" max="100" class="field-input" />
              </div>
            </div>
            <div>
              <label class="field-label">Minimum Amount</label>
              <input v-model="rateForm.minimum_amount" type="number" step="0.01" min="0" class="field-input" placeholder="e.g. SHIF's KES 300 floor" />
            </div>
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input v-model="rateForm.is_active" type="checkbox" class="w-4 h-4 rounded border-[#D9EBF7] text-[#229DE7]" />
              <span class="text-xs font-bold text-slate-600">Active</span>
            </label>
            <button type="submit" :disabled="rateSaving" class="w-full py-3 bg-[#229DE7] hover:bg-[#1682C4] text-white text-sm font-bold rounded-xl shadow-md shadow-[#229DE7]/30 transition-all disabled:opacity-60">
              {{ rateSaving ? 'Saving...' : rateEditingId ? 'Update' : 'Add Rate' }}
            </button>
          </form>
        </div>
      </aside>
      <main class="lg:col-span-8 space-y-4">
        <div v-if="rateLoading" class="bg-white border border-[#D9EBF7] rounded-2xl p-6 space-y-3"><div v-for="n in 3" :key="n" class="h-10 bg-slate-100 animate-pulse rounded-xl"></div></div>
        <div v-else-if="rateGroups.length === 0" class="bg-white border border-[#D9EBF7] rounded-2xl py-20 text-center"><p class="font-bold text-slate-700">No statutory rates defined</p></div>
        <div v-for="group in rateGroups" :key="group.scheme" class="bg-white border border-[#D9EBF7] rounded-2xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-[#EEF5FB]"><span class="font-bold text-sm text-slate-700 uppercase">{{ schemeLabel(group.scheme) }}</span></div>
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#EAF5FD] border-b border-[#B3DEFA] text-[#1682C4] text-[10.5px] font-bold uppercase tracking-widest">
                <th class="px-5 py-3">Range (KES)</th>
                <th class="px-5 py-3">Employee</th>
                <th class="px-5 py-3">Employer</th>
                <th class="px-5 py-3">Min.</th>
                <th class="px-5 py-3">Effective</th>
                <th class="px-5 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#EEF5FB]">
              <tr v-for="r in group.rates" :key="r.id" class="hover:bg-[#F5FAFF] transition-colors">
                <td class="px-5 py-3 text-sm text-slate-700">{{ fmtMoney(r.lower_limit) }} – {{ r.upper_limit ? fmtMoney(r.upper_limit) : '∞' }}</td>
                <td class="px-5 py-3 text-sm font-bold text-slate-900">{{ r.employee_rate ?? '—' }}%</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ r.employer_rate ?? '—' }}%</td>
                <td class="px-5 py-3 text-sm text-slate-600">{{ r.minimum_amount ? fmtMoney(r.minimum_amount) : '—' }}</td>
                <td class="px-5 py-3 text-xs text-slate-500">{{ fmtDate(r.effective_date) }}<span v-if="!r.is_active" class="ml-2 text-[10px] font-bold text-slate-400">(inactive)</span></td>
                <td class="px-5 py-3 text-right">
                  <div class="flex justify-end gap-2">
                    <button @click="editRate(r)" class="w-8 h-8 rounded-lg flex items-center justify-center text-[#229DE7] hover:bg-[#EAF5FD] transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                    <button @click="removeRate(r.id)" class="w-8 h-8 rounded-lg flex items-center justify-center text-red-500 hover:bg-red-50 transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import Swal from 'sweetalert2'
import PayrollComponentService from '@/services/PayrollComponentService'
import PayrollTaxBandService from '@/services/PayrollTaxBandService'
import PayrollStatutoryRateService from '@/services/PayrollStatutoryRateService'
import PayrollSettingService from '@/services/PayrollSettingService'

const tabs = [
  { key: 'settings', label: 'General Settings' },
  { key: 'components', label: 'Salary Components' },
  { key: 'tax-bands', label: 'PAYE Tax Bands' },
  { key: 'statutory', label: 'Statutory Rates' },
]
const activeTab = ref('settings')

const toast = (icon, title) => Swal.fire({ icon, title, toast: true, position: 'top-end', showConfirmButton: false, timer: 2500, timerProgressBar: true })
const errorAlert = (err, fallback) => Swal.fire({ icon: 'error', title: 'Error', text: err?.response?.data?.message || Object.values(err?.response?.data?.errors || {})[0]?.[0] || fallback, confirmButtonColor: '#229DE7' })
const fmtMoney = (v) => Number(v ?? 0).toLocaleString('en-KE', { minimumFractionDigits: 0, maximumFractionDigits: 2 })
// The backend (app timezone Africa/Nairobi) serializes calendar dates as
// the UTC instant for Nairobi midnight. Pinning the display timezone here
// — rather than letting the viewer's own local timezone reinterpret that
// instant — keeps the date correct regardless of where it's viewed from.
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-KE', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'Africa/Nairobi' }) : '—'

/* ---------------- General Settings ---------------- */
const settingsLoading = ref(true)
const savingSettings = ref(false)
const settingsForm = reactive({
  pay_frequency: 'monthly', working_days_per_month: 22, standard_hours_per_day: 8,
  overtime_multiplier: 1.5, rounding_mode: 'nearest', proration_method: 'calendar_days',
  personal_relief: 2400, currency: 'KES',
})
const loadSettings = async () => {
  settingsLoading.value = true
  try {
    const res = await PayrollSettingService.get()
    Object.assign(settingsForm, res.data)
  } finally { settingsLoading.value = false }
}
const saveSettings = async () => {
  savingSettings.value = true
  try {
    const res = await PayrollSettingService.update({ ...settingsForm })
    Object.assign(settingsForm, res.data)
    toast('success', 'Settings saved')
  } catch (err) { errorAlert(err, 'Failed to save settings') }
  finally { savingSettings.value = false }
}

/* ---------------- Salary Components ---------------- */
const components = ref([])
const compLoading = ref(true)
const compSaving = ref(false)
const compEditingId = ref(null)
const defaultCompForm = () => ({ name: '', code: '', type: 'earning', calculation_type: 'fixed', amount: '', percentage: '', is_taxable: true, is_active: true, description: '' })
const compForm = reactive(defaultCompForm())

const loadComponents = async () => {
  compLoading.value = true
  try { const res = await PayrollComponentService.getAll(); components.value = res?.data ?? [] }
  finally { compLoading.value = false }
}
const saveComponent = async () => {
  if (!compForm.name.trim() || !compForm.code.trim()) { Swal.fire({ icon: 'warning', title: 'Name and code are required', confirmButtonColor: '#229DE7' }); return }
  compSaving.value = true
  try {
    const payload = { ...compForm }
    if (compEditingId.value) {
      const res = await PayrollComponentService.update(compEditingId.value, payload)
      const idx = components.value.findIndex(c => c.id === compEditingId.value)
      if (idx !== -1) components.value[idx] = res.data
      toast('success', 'Component updated')
    } else {
      const res = await PayrollComponentService.create(payload)
      components.value.push(res.data)
      toast('success', 'Component added')
    }
    resetComponentForm()
  } catch (err) { errorAlert(err, 'Failed to save component') }
  finally { compSaving.value = false }
}
const editComponent = (c) => {
  compEditingId.value = c.id
  Object.assign(compForm, {
    name: c.name, code: c.code, type: c.type, calculation_type: c.calculation_type,
    amount: c.amount ?? '', percentage: c.percentage ?? '', is_taxable: !!c.is_taxable, is_active: !!c.is_active,
    description: c.description || '',
  })
}
const removeComponent = async (id) => {
  const r = await Swal.fire({ title: 'Delete this component?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await PayrollComponentService.remove(id); components.value = components.value.filter(c => c.id !== id); toast('success', 'Deleted') }
  catch { Swal.fire({ icon: 'error', title: 'Delete failed', confirmButtonColor: '#229DE7' }) }
}
const resetComponentForm = () => { compEditingId.value = null; Object.assign(compForm, defaultCompForm()) }

/* ---------------- PAYE Tax Bands ---------------- */
const taxBands = ref([])
const bandLoading = ref(true)
const bandSaving = ref(false)
const bandEditingId = ref(null)
const defaultBandForm = () => ({ effective_date: '', lower_limit: '', upper_limit: '', rate: '', is_active: true })
const bandForm = reactive(defaultBandForm())

// Groups bands into PAYE "versions" by their Nairobi calendar date. Slicing
// the raw UTC-serialized string would strip its time component and can
// land on the wrong day; converting through Africa/Nairobi first keeps the
// grouping (and the date shown for it) consistent with fmtDate below.
const nairobiDateKey = (iso) => iso ? new Date(iso).toLocaleDateString('en-CA', { timeZone: 'Africa/Nairobi' }) : ''
const bandVersions = computed(() => {
  const groups = {}
  for (const b of taxBands.value) {
    const key = nairobiDateKey(b.effective_date)
    if (!groups[key]) groups[key] = []
    groups[key].push(b)
  }
  return Object.entries(groups)
    .sort((a, b) => b[0].localeCompare(a[0]))
    .map(([date, bands]) => ({ date, bands: bands.sort((a, b) => Number(a.lower_limit) - Number(b.lower_limit)) }))
})

const loadBands = async () => {
  bandLoading.value = true
  try { const res = await PayrollTaxBandService.getAll(); taxBands.value = res?.data ?? [] }
  finally { bandLoading.value = false }
}
const saveBand = async () => {
  if (!bandForm.effective_date || bandForm.lower_limit === '' || bandForm.rate === '') { Swal.fire({ icon: 'warning', title: 'Effective date, lower limit and rate are required', confirmButtonColor: '#229DE7' }); return }
  bandSaving.value = true
  try {
    const payload = { ...bandForm, upper_limit: bandForm.upper_limit === '' ? null : bandForm.upper_limit }
    if (bandEditingId.value) {
      const res = await PayrollTaxBandService.update(bandEditingId.value, payload)
      const idx = taxBands.value.findIndex(b => b.id === bandEditingId.value)
      if (idx !== -1) taxBands.value[idx] = res.data
      toast('success', 'Tax band updated')
    } else {
      const res = await PayrollTaxBandService.create(payload)
      taxBands.value.push(res.data)
      toast('success', 'Tax band added')
    }
    resetBandForm()
  } catch (err) { errorAlert(err, 'Failed to save tax band') }
  finally { bandSaving.value = false }
}
const editBand = (b) => {
  bandEditingId.value = b.id
  Object.assign(bandForm, {
    effective_date: (b.effective_date || '').slice(0, 10), lower_limit: b.lower_limit,
    upper_limit: b.upper_limit ?? '', rate: b.rate, is_active: !!b.is_active,
  })
}
const removeBand = async (id) => {
  const r = await Swal.fire({ title: 'Delete this tax band?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await PayrollTaxBandService.remove(id); taxBands.value = taxBands.value.filter(b => b.id !== id); toast('success', 'Deleted') }
  catch { Swal.fire({ icon: 'error', title: 'Delete failed', confirmButtonColor: '#229DE7' }) }
}
const resetBandForm = () => { bandEditingId.value = null; Object.assign(bandForm, defaultBandForm()) }

/* ---------------- Statutory Rates ---------------- */
const statutoryRates = ref([])
const rateLoading = ref(true)
const rateSaving = ref(false)
const rateEditingId = ref(null)
const defaultRateForm = () => ({ scheme: 'nssf', effective_date: '', lower_limit: '', upper_limit: '', employee_rate: '', employer_rate: '', minimum_amount: '', is_active: true })
const rateForm = reactive(defaultRateForm())

const schemeLabel = (s) => ({ nssf: 'NSSF', shif: 'SHIF', housing_levy: 'Housing Levy' }[s] || s)
const rateGroups = computed(() => {
  const groups = {}
  for (const r of statutoryRates.value) {
    if (!groups[r.scheme]) groups[r.scheme] = []
    groups[r.scheme].push(r)
  }
  const order = ['nssf', 'shif', 'housing_levy']
  return order.filter(s => groups[s]).map(scheme => ({
    scheme,
    rates: groups[scheme].sort((a, b) => Number(a.lower_limit) - Number(b.lower_limit)),
  }))
})

const loadRates = async () => {
  rateLoading.value = true
  try { const res = await PayrollStatutoryRateService.getAll(); statutoryRates.value = res?.data ?? [] }
  finally { rateLoading.value = false }
}
const saveRate = async () => {
  if (!rateForm.scheme || !rateForm.effective_date) { Swal.fire({ icon: 'warning', title: 'Scheme and effective date are required', confirmButtonColor: '#229DE7' }); return }
  rateSaving.value = true
  try {
    const payload = {
      ...rateForm,
      lower_limit: rateForm.lower_limit === '' ? 0 : rateForm.lower_limit,
      upper_limit: rateForm.upper_limit === '' ? null : rateForm.upper_limit,
      employee_rate: rateForm.employee_rate === '' ? null : rateForm.employee_rate,
      employer_rate: rateForm.employer_rate === '' ? null : rateForm.employer_rate,
      minimum_amount: rateForm.minimum_amount === '' ? null : rateForm.minimum_amount,
    }
    if (rateEditingId.value) {
      const res = await PayrollStatutoryRateService.update(rateEditingId.value, payload)
      const idx = statutoryRates.value.findIndex(r => r.id === rateEditingId.value)
      if (idx !== -1) statutoryRates.value[idx] = res.data
      toast('success', 'Rate updated')
    } else {
      const res = await PayrollStatutoryRateService.create(payload)
      statutoryRates.value.push(res.data)
      toast('success', 'Rate added')
    }
    resetRateForm()
  } catch (err) { errorAlert(err, 'Failed to save rate') }
  finally { rateSaving.value = false }
}
const editRate = (r) => {
  rateEditingId.value = r.id
  Object.assign(rateForm, {
    scheme: r.scheme, effective_date: (r.effective_date || '').slice(0, 10), lower_limit: r.lower_limit,
    upper_limit: r.upper_limit ?? '', employee_rate: r.employee_rate ?? '', employer_rate: r.employer_rate ?? '',
    minimum_amount: r.minimum_amount ?? '', is_active: !!r.is_active,
  })
}
const removeRate = async (id) => {
  const r = await Swal.fire({ title: 'Delete this rate?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#229DE7', cancelButtonColor: '#f43f5e', confirmButtonText: 'Yes, delete' })
  if (!r.isConfirmed) return
  try { await PayrollStatutoryRateService.remove(id); statutoryRates.value = statutoryRates.value.filter(x => x.id !== id); toast('success', 'Deleted') }
  catch { Swal.fire({ icon: 'error', title: 'Delete failed', confirmButtonColor: '#229DE7' }) }
}
const resetRateForm = () => { rateEditingId.value = null; Object.assign(rateForm, defaultRateForm()) }

onMounted(() => {
  loadSettings()
  loadComponents()
  loadBands()
  loadRates()
})
</script>

<style scoped>
.field-label { display: block; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.field-label.required::after { content: ' *'; color: #ef4444; }
.field-input { width: 100%; padding: 10px 14px; background: #f5faff; border: 1.5px solid #d9ebf7; border-radius: 12px; font-size: 13.5px; color: #1e293b; outline: none; transition: border-color .15s; font-family: inherit; }
.field-input:focus { border-color: #229de7; box-shadow: 0 0 0 3px rgba(34,157,231,.12); background: #fff; }
</style>
