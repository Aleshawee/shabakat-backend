<template>
  <div class="min-h-screen bg-slate-50 pb-20">
    <div class="bg-gradient-to-br from-teal-600 via-teal-700 to-slate-800 text-white px-5 pt-5 pb-8 relative overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(255,255,255,0.06),transparent_60%)]"></div>
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-4">
          <button @click="$router.back()" class="text-white/80 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <h1 class="text-xl font-bold">تحويل نقاط</h1>
        </div>
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/10">
          <div class="flex items-center justify-between">
            <p class="text-teal-200 text-sm">الرصيد الحالي</p>
            <svg class="w-5 h-5 text-teal-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <p class="text-2xl font-bold mt-1">{{ balance || 0 }} <span class="text-sm font-normal text-teal-200">نقطة</span></p>
        </div>
      </div>
    </div>

    <div class="px-4 mt-4">
      <div v-if="!enabled" class="bg-white rounded-2xl shadow-sm p-12 text-center">
        <div class="text-6xl mb-4"></div>
        <p class="text-slate-500 font-medium">الميزة غير مفعلة</p>
        <p class="text-xs text-slate-400 mt-1">تحويل النقاط غير متاح حالياً</p>
      </div>

      <div v-else class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.04)] border p-5">
        <form @submit.prevent="sendTransfer" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">رقم هاتف المستلم</label>
            <div class="relative" dir="ltr">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm border-l border-slate-200 pl-3 ml-1">967+</span>
              <input v-model="recipientPhone" type="tel" required dir="ltr" maxlength="9"
                class="text-right w-full px-4 py-3.5 pr-16 border-2 border-slate-200 rounded-2xl focus:ring-0 focus:border-teal-500 outline-none text-lg transition"
                placeholder="774009094" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">المبلغ (نقطة)</label>
            <input v-model.number="amount" type="number" required :min="settings.min_amount || 1" :max="settings.max_amount || 1000"
              class="w-full px-4 py-3.5 border-2 border-slate-200 rounded-2xl focus:ring-0 focus:border-teal-500 outline-none text-lg transition"
              placeholder="أدخل المبلغ" />
          </div>

          <div class="bg-teal-50 rounded-2xl p-4 space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-slate-500">المبلغ</span>
              <span class="font-bold text-slate-700">{{ amount || 0 }} نقطة</span>
            </div>
            <div class="flex justify-between">
              <span class="text-slate-500">رسوم التحويل ({{ settings.fee_percent || 0 }}%)</span>
              <span class="font-bold text-red-500">{{ fee }} نقطة</span>
            </div>
            <div class="border-t border-teal-200 pt-2 flex justify-between">
              <span class="text-slate-700 font-bold">المستلم يحصل على</span>
              <span class="font-bold text-teal-600">{{ netAmount }} نقطة</span>
            </div>
          </div>

          <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 text-sm text-center px-4 py-2.5 rounded-xl">
            {{ error }}
          </div>

          <div v-if="success" class="bg-teal-50 border border-teal-200 text-teal-700 text-sm text-center px-4 py-2.5 rounded-xl">
            {{ success }}
          </div>

          <button type="submit" :disabled="loading"
            class="w-full bg-gradient-to-l from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold py-3.5 rounded-2xl transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-teal-200">
            <span v-if="loading" class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              جاري...
            </span>
            <span v-else>تحويل النقاط</span>
          </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100">
          <h4 class="text-xs font-bold text-slate-600 mb-2">شروط التحويل:</h4>
          <ul class="text-xs text-slate-400 space-y-1">
            <li>• الحد الأدنى للتحويل: {{ settings.min_amount || 1 }} نقطة</li>
            <li>• الحد الأقصى للتحويل: {{ settings.max_amount || 1000 }} نقطة</li>
            <li>• رسوم التحويل: {{ settings.fee_percent || 0 }}%</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  data() {
    return {
      balance: 0,
      enabled: true,
      settings: {},
      recipientPhone: '',
      amount: 0,
      loading: false,
      error: '',
      success: '',
    }
  },
  computed: {
    fee() {
      return Math.round((this.amount || 0) * (this.settings.fee_percent || 0) / 100)
    },
    netAmount() {
      return (this.amount || 0) - this.fee
    },
  },
  mounted() {
    this.loadSettings()
  },
  methods: {
    async loadSettings() {
      try {
        const profile = await axios.get('/api/v1/user/profile')
        this.balance = profile.data?.points_balance ?? profile.data?.user?.points_balance ?? 0

        const r = await axios.get('/api/v1/transfer/settings')
        if (!r.data.enabled) {
          this.enabled = false
          return
        }
        this.settings = r.data
      } catch (e) {
        this.enabled = false
      }
    },
    async sendTransfer() {
      this.loading = true; this.error = ''; this.success = ''
      try {
        const r = await axios.post('/api/v1/transfer/send', {
          receiver_phone: this.recipientPhone,
          amount: this.amount,
        })
        this.success = `✅ تم تحويل ${r.data.amount} نقطة إلى ${r.data.recipient}`
        this.balance = r.data.points_balance
        this.amount = 0
        this.recipientPhone = ''
      } catch (e) {
        this.error = e.response?.data?.message || 'فشل التحويل'
      } finally { this.loading = false }
    },
  },
}
</script>
