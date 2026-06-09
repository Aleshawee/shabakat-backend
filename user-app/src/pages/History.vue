<template>
  <div class="min-h-screen bg-slate-50 pb-20">
    <div class="bg-gradient-to-br from-indigo-700 via-indigo-800 to-slate-900 text-white px-5 pt-5 pb-8 relative overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(255,255,255,0.06),transparent_60%)]"></div>
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-4">
          <button @click="$router.back()" class="text-white/80 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <h1 class="text-xl font-bold">سجل العمليات</h1>
        </div>
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/10">
          <div class="flex items-center justify-between">
            <p class="text-indigo-200 text-sm">الرصيد الحالي</p>
            <svg class="w-5 h-5 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          </div>
          <p class="text-2xl font-bold mt-1">{{ balance || 0 }} <span class="text-sm font-normal text-indigo-200">نقطة</span></p>
        </div>
      </div>
    </div>

    <div class="px-4 mt-4">
      <div class="flex gap-2 mb-4">
        <button v-for="tab in tabs" :key="tab.key"
          @click="activeTab = tab.key"
          class="px-4 py-2 rounded-xl text-sm font-medium transition-all"
          :class="activeTab === tab.key ? 'bg-indigo-700 text-white shadow' : 'bg-white text-slate-500 border'">
          {{ tab.label }}
        </button>
      </div>

      <div v-if="loading" class="text-center py-16">
        <div class="w-12 h-12 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mx-auto"></div>
        <p class="text-slate-400 text-sm mt-3">جاري التحميل...</p>
      </div>

      <div v-if="activeTab === 'transactions'" class="space-y-2">
        <div v-for="t in transactions" :key="t.id"
          class="bg-white rounded-xl shadow-sm border p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
            :class="t.amount > 0 ? 'bg-emerald-50' : 'bg-red-50'">
            <svg class="w-5 h-5" :class="t.amount > 0 ? 'text-emerald-500' : 'text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="t.amount > 0" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-slate-700 truncate">{{ t.note || 'عملية' }}</p>
            <p class="text-xs text-slate-400">{{ new Date(t.created_at).toLocaleDateString('ar-YE') }} {{ new Date(t.created_at).toLocaleTimeString('ar-YE', { hour: '2-digit', minute: '2-digit' }) }}</p>
          </div>
          <div class="text-left">
            <p class="text-sm font-bold" :class="t.amount > 0 ? 'text-emerald-600' : 'text-red-500'">
              {{ t.amount > 0 ? '+' : '' }}{{ t.amount }}
            </p>
            <p class="text-[10px] text-slate-400">{{ t.balance_after }} رصيد</p>
          </div>
        </div>

        <div v-if="transactions.length === 0 && !loading" class="bg-white rounded-xl shadow-sm p-12 text-center">
          <div class="text-5xl mb-3"></div>
          <p class="text-slate-500 font-medium">لا توجد عمليات</p>
        </div>
      </div>

      <div v-if="activeTab === 'cards'" class="space-y-2">
        <div v-for="card in cards" :key="card.id"
          class="bg-white rounded-xl shadow-sm border p-4">
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-lg">
                💳
              </div>
              <div>
                <p class="text-sm font-bold text-slate-800">{{ card.reward?.name || 'مكافأة' }}</p>
                <p class="text-xs" :class="card.status === 'active' ? 'text-emerald-600' : 'text-slate-400'">
                  {{ card.status === 'active' ? 'نشط' : 'مستخدم' }}
                </p>
              </div>
            </div>
            <span class="text-xs text-slate-400">{{ new Date(card.created_at).toLocaleDateString('ar-YE') }}</span>
          </div>
          <div class="bg-slate-50 rounded-xl p-3">
            <p class="text-xs text-slate-400">رمز الكارت</p>
            <p class="text-sm font-bold text-slate-700 font-mono tracking-wider" dir="ltr">{{ card.code }}</p>
          </div>
        </div>

        <div v-if="cards.length === 0 && !loading" class="bg-white rounded-xl shadow-sm p-12 text-center">
          <div class="text-5xl mb-3">💳</div>
          <p class="text-slate-500 font-medium">لا توجد بطاقات مكافآت</p>
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
      activeTab: 'transactions',
      tabs: [
        { key: 'transactions', label: 'العمليات' },
        { key: 'cards', label: 'بطاقاتي' },
      ],
      transactions: [],
      cards: [],
      balance: 0,
      loading: true,
    }
  },
  mounted() { this.loadData() },
  methods: {
    async loadData() {
      this.loading = true
      try {
        const res = await axios.get('/api/v1/user/history')
        this.transactions = res.data.transactions || []
        this.balance = res.data.balance

        const r = await axios.get('/api/v1/user/reward-cards')
        this.cards = r.data.cards || []
      } catch (e) { console.error(e) }
      finally { this.loading = false }
    },
  },
}
</script>
