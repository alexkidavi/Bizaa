<template>
  <div class="space-y-6">
    <!-- Page header -->
    <div>
      <h1 class="text-2xl font-extrabold text-slate-900">Support</h1>
      <p class="text-slate-500 mt-1 text-sm">We're here to help. Reach us through any channel below.</p>
    </div>

    <!-- Contact cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- WhatsApp -->
      <a
        href="https://wa.me/254705030613"
        target="_blank"
        rel="noopener"
        class="flex items-start gap-4 p-5 bg-white rounded-2xl border border-[#D9EBF7] hover:border-[#229de7] hover:shadow-md transition cursor-pointer group"
      >
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 bg-green-100 text-green-600 group-hover:bg-green-200 transition">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
          </svg>
        </div>
        <div>
          <p class="font-bold text-slate-900 text-sm">WhatsApp Chat</p>
          <p class="text-slate-500 text-xs mt-0.5">+254 705 030 613</p>
          <p class="text-green-600 text-xs font-semibold mt-1">Chat now →</p>
        </div>
      </a>

      <!-- Phone -->
      <a
        href="tel:+254705030613"
        class="flex items-start gap-4 p-5 bg-white rounded-2xl border border-[#D9EBF7] hover:border-[#229de7] hover:shadow-md transition cursor-pointer group"
      >
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 bg-[#eaf5fd] text-[#229de7] group-hover:bg-[#d0ebf8] transition">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.17h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.82a16 16 0 0 0 6 6l.93-.94a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
          </svg>
        </div>
        <div>
          <p class="font-bold text-slate-900 text-sm">Call Us</p>
          <p class="text-slate-500 text-xs mt-0.5">+254 705 030 613</p>
          <p class="text-[#229de7] text-xs font-semibold mt-1">Call now →</p>
        </div>
      </a>

      <!-- Email -->
      <a
        href="mailto:brimsoftsolutions@gmail.com"
        class="flex items-start gap-4 p-5 bg-white rounded-2xl border border-[#D9EBF7] hover:border-[#229de7] hover:shadow-md transition cursor-pointer group"
      >
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 bg-purple-100 text-purple-600 group-hover:bg-purple-200 transition">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
        </div>
        <div>
          <p class="font-bold text-slate-900 text-sm">Email Support</p>
          <p class="text-slate-500 text-xs mt-0.5">brimsoftsolutions@gmail.com</p>
          <p class="text-purple-600 text-xs font-semibold mt-1">Send email →</p>
        </div>
      </a>
    </div>

    <!-- Quick message to WhatsApp -->
    <div class="bg-white rounded-2xl border border-[#D9EBF7] p-6">
      <h2 class="font-extrabold text-slate-900 text-base mb-1">Send us a quick message</h2>
      <p class="text-slate-500 text-sm mb-4">Describe your issue and we'll open WhatsApp with your message pre-filled.</p>
      <div class="space-y-3">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Your name</label>
          <input
            v-model="form.name"
            type="text"
            placeholder="John Mwangi"
            class="w-full px-3 py-2.5 text-sm border border-[#D9EBF7] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#229de7] focus:border-transparent text-slate-900 bg-[#F8FBFE]"
          />
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Subject</label>
          <select v-model="form.subject" class="w-full px-3 py-2.5 text-sm border border-[#D9EBF7] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#229de7] focus:border-transparent text-slate-900 bg-[#F8FBFE]">
            <option value="">Select a topic…</option>
            <option>Subscription / billing issue</option>
            <option>Can't log in</option>
            <option>POS / sales problem</option>
            <option>Inventory question</option>
            <option>M-Pesa payment issue</option>
            <option>Feature request</option>
            <option>Other</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Message</label>
          <textarea
            v-model="form.message"
            rows="4"
            placeholder="Describe what's happening…"
            class="input-field resize-none"
          ></textarea>
        </div>
        <button
          @click="sendWhatsApp"
          :disabled="!canSend"
          class="flex items-center gap-2 bg-green-500 hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-sm px-5 py-2.5 rounded-xl transition"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
          </svg>
          Send via WhatsApp
        </button>
      </div>
    </div>

    <!-- FAQ -->
    <div class="bg-white rounded-2xl border border-[#D9EBF7] p-6">
      <h2 class="font-extrabold text-slate-900 text-base mb-4">Frequently asked questions</h2>
      <div class="divide-y divide-[#EEF5FB]">
        <div v-for="(faq, i) in faqs" :key="i">
          <button
            @click="open === i ? open = null : open = i"
            class="w-full flex items-center justify-between gap-4 py-3.5 text-left"
          >
            <span class="font-semibold text-slate-800 text-sm">{{ faq.q }}</span>
            <svg
              :class="['w-4 h-4 text-slate-400 flex-shrink-0 transition-transform', open === i ? 'rotate-180' : '']"
              viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            >
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </button>
          <div v-if="open === i" class="pb-4 text-sm text-slate-500 leading-relaxed">
            {{ faq.a }}
          </div>
        </div>
      </div>
    </div>

    <!-- Social links -->
    <div class="bg-white rounded-2xl border border-[#D9EBF7] p-6">
      <h2 class="font-extrabold text-slate-900 text-base mb-3">Find us on social media</h2>
      <div class="flex flex-wrap gap-3">
        <a href="https://facebook.com/bizaakenya" target="_blank" rel="noopener"
          class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-slate-700 bg-[#F0F6FB] border border-[#D9EBF7] rounded-full hover:bg-[#eaf5fd] hover:border-[#229de7] hover:text-[#229de7] transition">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
          Facebook
        </a>
        <a href="https://instagram.com/bizaakenya" target="_blank" rel="noopener"
          class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-slate-700 bg-[#F0F6FB] border border-[#D9EBF7] rounded-full hover:bg-[#eaf5fd] hover:border-[#229de7] hover:text-[#229de7] transition">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
          Instagram
        </a>
        <a href="https://twitter.com/bizaakenya" target="_blank" rel="noopener"
          class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-slate-700 bg-[#F0F6FB] border border-[#D9EBF7] rounded-full hover:bg-[#eaf5fd] hover:border-[#229de7] hover:text-[#229de7] transition">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.259 5.632L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
          X / Twitter
        </a>
        <a href="https://tiktok.com/@bizaakenya" target="_blank" rel="noopener"
          class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-slate-700 bg-[#F0F6FB] border border-[#D9EBF7] rounded-full hover:bg-[#eaf5fd] hover:border-[#229de7] hover:text-[#229de7] transition">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.75a8.24 8.24 0 0 0 4.83 1.55V6.85a4.85 4.85 0 0 1-1.06-.16z"/></svg>
          TikTok
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const open = ref(null)

const form = ref({
  name: '',
  subject: '',
  message: '',
})

const canSend = computed(() => form.value.name.trim() && form.value.message.trim())

function sendWhatsApp() {
  const text = [
    `Hi Bizaa Support!`,
    form.value.name ? `Name: ${form.value.name}` : '',
    form.value.subject ? `Topic: ${form.value.subject}` : '',
    '',
    form.value.message,
  ].filter(Boolean).join('\n')

  window.open(`https://wa.me/254705030613?text=${encodeURIComponent(text)}`, '_blank', 'noopener')
}

const faqs = [
  {
    q: 'How do I renew my subscription?',
    a: 'Go to Settings → Subscription in the sidebar, choose a plan, and pay via M-Pesa STK push. Your subscription activates automatically once the payment clears.',
  },
  {
    q: 'I can\'t log in — what should I do?',
    a: 'First, check that you\'re using the correct email. If you\'ve forgotten your password, use the "Forgot password" link on the login page. If you still can\'t access your account, WhatsApp us and we\'ll verify your identity and help restore access.',
  },
  {
    q: 'The M-Pesa prompt didn\'t come through — what now?',
    a: 'Make sure the number you entered is a registered Safaricom line and has sufficient balance. Sometimes there\'s a short delay. If it doesn\'t arrive within 2 minutes, try again or contact us.',
  },
  {
    q: 'Can I use Bizaa on multiple devices?',
    a: 'Yes — the web POS works on any browser, and the mobile app works on Android. Multiple staff can be logged in at the same time on different devices.',
  },
  {
    q: 'How do I add more staff / cashiers?',
    a: 'Go to HRM → Staff, click "Add Staff", fill in their details and assign a role. They\'ll receive their login credentials by email.',
  },
  {
    q: 'Is my data backed up?',
    a: 'Yes. All data is stored securely on our servers and backed up daily. You can also export reports and sales history at any time.',
  },
]
</script>

