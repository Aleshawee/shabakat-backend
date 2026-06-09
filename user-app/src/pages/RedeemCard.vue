<template>
  <div class="min-h-screen bg-gradient-to-b from-amber-700 via-amber-800 to-slate-900 pb-20">
    <div class="px-5 pt-5 pb-6">
      <div class="flex items-center gap-3 mb-4">
        <button @click="$router.back()" class="text-white/80 hover:text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <h1 class="text-xl font-bold text-white">استبدال كرت</h1>
      </div>
      <p class="text-amber-200 text-sm text-center mb-5">أدخل رقم الكرت للحصول على نقاط</p>

      <!-- Balance -->
      <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-5 border border-white/10 text-center mb-6">
        <p class="text-amber-200 text-sm">رصيدك الحالي</p>
        <p class="text-3xl font-black text-white mt-1">{{ balance || 0 }} <span class="text-base font-normal text-amber-200">نقطة</span></p>
      </div>

      <!-- Card Input -->
      <div class="bg-white/10 backdrop-blur-xl rounded-3xl p-6 border border-white/10">
        <div class="text-center mb-5">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center mx-auto mb-3 shadow-lg">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18M3 6h18M3 18h18"/>
            </svg>
          </div>
          <h3 class="font-bold text-white text-lg">أدخل رقم الكرت</h3>
          <p class="text-amber-200 text-xs mt-1">سيتم التحقق من الكرت وإضافة النقاط لرصيدك</p>
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-amber-200 mb-2">رقم الكرت</label>
            <input v-model="cardCode" type="text" dir="ltr"
              class="w-full px-4 py-4 bg-white/10 border-2 border-white/20 rounded-2xl text-white text-center text-lg font-mono tracking-widest focus:ring-0 focus:border-amber-400 outline-none transition placeholder-white/30"
              placeholder="XXXX-XXXX-XXXX" />
          </div>

          <div v-if="error" class="bg-rose-500/20 border border-rose-400/30 text-rose-200 text-sm text-center px-4 py-3 rounded-xl">
            {{ error }}
          </div>

          <div v-if="success" class="bg-emerald-500/20 border border-emerald-400/30 text-emerald-200 text-sm text-center px-4 py-3 rounded-xl">
            {{ success }}
          </div>

          <button @click="redeemCard" :disabled="loading || !cardCode.trim()"
            class="w-full py-4 rounded-2xl font-bold text-white bg-gradient-to-l from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 transition-all shadow-lg shadow-amber-500/30 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98]">
            <span v-if="loading" class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              جاري التحقق...
            </span>
            <span v-else>استبدال الكرت</span>
          </button>
        </div>
      </div>

      <!-- Success Result -->
      <div v-if="redeemSuccess" class="mt-6 bg-white/10 backdrop-blur-xl rounded-3xl p-6 border border-emerald-400/30 text-center">
        <div class="text-5xl mb-3"></div>
        <h3 class="font-black text-white text-lg mb-2">تم الاستبدال بنجاح!</h3>
        <div class="bg-white/5 rounded-2xl p-4 mb-3 border border-white/10">
          <p class="text-xs text-amber-200">النقاط المضافة</p>
          <p class="text-3xl font-black text-emerald-400">+{{ redeemSuccess.points_earned }}</p>
        </div>
        <div class="bg-white/5 rounded-2xl p-4 mb-3 border border-white/10">
          <p class="text-xs text-amber-200">نوع الكرت</p>
          <p class="text-lg font-bold text-white">{{ redeemSuccess.card_name }}</p>
        </div>
        <div class="bg-white/5 rounded-2xl p-4 border border-white/10">
          <p class="text-xs text-amber-200">الرصيد الجديد</p>
          <p class="text-2xl font-black text-amber-400">{{ redeemSuccess.points_balance }} نقطة</p>
        </div>
        <button @click="redeemSuccess = null; cardCode = ''"
          class="mt-4 w-full py-3 rounded-xl font-bold text-white bg-white/10 hover:bg-white/20 transition">
          استبدال كرت آخر
        </button>
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
      cardCode: '',
      loading: false,
      error: '',
      success: '',
      redeemSuccess: null,
    }
  },
  mounted() {
    this.loadBalance()
  },
  methods: {
    async loadBalance() {
      try {
        const r = await axios.get('/api/v1/user/profile')
        this.balance = r.data.points_balance ?? r.data.user?.points_balance ?? 0
      } catch (e) { console.error(e) }
    },
    async redeemCard() {
      this.loading = true; this.error = ''; this.success = ''; this.redeemSuccess = null
      try {
        const r = await axios.post('/api/v1/cards/redeem', { code: this.cardCode.trim() })
        this.redeemSuccess = r.data
        this.balance = r.data.points_balance
        this.success = r.data.message
      } catch (e) {
        this.error = e.response?.data?.message || 'فشل استبدال الكرت'
      }
      finally { this.loading = false }
    },
  },
}
</script>
