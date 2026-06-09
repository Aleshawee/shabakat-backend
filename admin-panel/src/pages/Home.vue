<template>
  <div class="p-6" dir="rtl">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-800">مرحباً، {{ adminName }}</h1>
      <p class="text-slate-400 text-sm mt-1">نظرة عامة على المنصة</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-16">
      <div class="inline-block w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
      <p class="text-slate-400 text-sm mt-3">جاري تحميل البيانات...</p>
    </div>

    <template v-else>
      <!-- Stats cards row -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border p-5">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
              <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
            </div>
            <div>
              <p class="text-xs text-slate-400">إجمالي المستخدمين</p>
              <p class="text-2xl font-bold text-slate-800">{{ stats.total_users }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
              <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
            <div>
              <p class="text-xs text-slate-400">كروت المكافآت المتاحة</p>
              <p class="text-2xl font-bold text-slate-800">{{ stats.reward_cards_available }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
              <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <p class="text-xs text-slate-400">النقاط الممنوحة</p>
              <p class="text-2xl font-bold text-amber-600">{{ stats.total_points_given.toLocaleString() }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center">
              <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
              </svg>
            </div>
            <div>
              <p class="text-xs text-slate-400">إجمالي النقاط المستخدمة</p>
              <p class="text-2xl font-bold text-rose-600">{{ stats.total_points_used.toLocaleString() }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Reward card inventory -->
      <div class="bg-white rounded-xl shadow-sm border p-5 mb-6">
        <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2">
          <span class="text-lg">💳</span> مخزون كروت المكافآت (المتبقية)
        </h3>
        <div class="flex gap-4 flex-wrap">
          <div v-for="item in cardInventory" :key="item.card_value"
               class="flex-1 min-w-[120px] bg-gradient-to-b from-slate-50 to-white rounded-xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-800 mb-1">{{ item.card_value.toLocaleString() }}</p>
            <p class="text-3xl font-extrabold" :class="{
              'text-emerald-600': item.remaining > 50,
              'text-amber-500': item.remaining > 0 && item.remaining <= 50,
              'text-red-400': item.remaining === 0,
            }">{{ item.remaining }}</p>
            <p class="text-xs text-slate-400 mt-1">كرت متبقي</p>
          </div>
        </div>
      </div>

      <!-- Chart + Top Users row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Registration chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border p-5">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-700 flex items-center gap-2">
              <span class="text-lg">📈</span> تسجيل المستخدمين الجدد
            </h3>
            <select v-model.number="chartDays" @change="loadData"
                    class="text-xs border rounded-lg px-2 py-1 text-slate-600 bg-white">
              <option :value="7">آخر 7 أيام</option>
              <option :value="14">آخر 14 يوم</option>
              <option :value="30">آخر 30 يوم</option>
            </select>
          </div>
          <div v-if="registrationChart.length === 0" class="text-center py-8 text-slate-400 text-sm">
            لا توجد بيانات تسجيل جديدة
          </div>
          <div v-else class="relative" style="height: 240px;">
            <!-- Y-axis labels -->
            <div class="absolute inset-0 flex flex-col-reverse justify-between pointer-events-none" style="padding: 0 0 28px 0;">
              <div v-for="tick in yTicks" :key="tick"
                   class="flex items-center w-full border-t border-dashed border-slate-100" style="margin-top: 0;">
                <span class="text-[11px] font-medium text-slate-400 ml-2 -mt-2.5">{{ tick }}</span>
              </div>
            </div>
            <!-- Bars -->
            <div class="absolute inset-0 flex items-end gap-1.5" style="padding: 0 0 28px 0;">
              <div v-for="(item, idx) in registrationChart" :key="idx"
                   class="flex-1 flex flex-col items-center justify-end h-full min-w-0">
                <div class="w-full max-w-[48px] rounded-t-md transition-all duration-500 relative group cursor-pointer"
                     :style="{ height: barHeight(item.count) + '%' }">
                  <div class="w-full h-full rounded-t-md bg-gradient-to-t from-indigo-500 to-indigo-400 hover:to-indigo-300 transition-colors"></div>
                  <div class="absolute -top-7 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[11px] px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap shadow-lg">
                    {{ item.count }} مستخدم
                  </div>
                </div>
                <span class="text-[11px] text-slate-500 font-medium mt-1.5 whitespace-nowrap">{{ formatDate(item.date) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Top users card -->
        <div class="bg-white rounded-xl shadow-sm border p-5">
          <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2">
            <span class="text-lg">🏆</span> أكثر المستخدمين نشاطاً
          </h3>
          <div class="flex gap-2 mb-4">
            <button @click="topFilter = 'points'"
                    class="flex-1 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors"
                    :class="topFilter === 'points' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'">
              الأكثر نقاط
            </button>
            <button @click="topFilter = 'redemptions'"
                    class="flex-1 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors"
                    :class="topFilter === 'redemptions' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'">
              الأكثر استبدالاً
            </button>
          </div>
          <div v-if="displayedTopUsers.length === 0" class="text-center py-8 text-slate-400 text-sm">
            لا توجد بيانات
          </div>
          <div v-else class="space-y-2">
            <div v-for="(user, idx) in displayedTopUsers" :key="user.id"
                 class="flex items-center gap-3 p-2.5 rounded-xl transition-colors hover:bg-slate-50">
              <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white text-shadow-sm"
                   :class="medalColors[idx] || 'bg-slate-400'">
                {{ idx + 1 }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-700 truncate">{{ user.name }}</p>
                <p class="text-[10px] text-slate-400 dir-ltr text-left">{{ user.phone }}</p>
              </div>
              <div class="text-left">
                <p v-if="topFilter === 'points'" class="text-sm font-bold text-amber-600">{{ user.points_balance?.toLocaleString() || 0 }}</p>
                <p v-else class="text-sm font-bold text-indigo-600">{{ user.redemption_count || 0 }}</p>
                <p class="text-[9px] text-slate-400">{{ topFilter === 'points' ? 'نقطة' : 'استبدال' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      loading: true,
      adminName: '',
      stats: { total_users: 0, reward_cards_available: 0, total_points_given: 0, total_points_used: 0 },
      cardInventory: [],
      registrationChart: [],
      topUsers: { by_points: [], by_redemptions: [] },
      topFilter: 'points',
      chartDays: 14,
    }
  },
  computed: {
    maxChartCount() {
      if (this.registrationChart.length === 0) return 1
      return Math.max(...this.registrationChart.map(i => i.count), 1)
    },
    yTicks() {
      const max = this.maxChartCount
      if (max <= 3) return Array.from({ length: max + 1 }, (_, i) => i)
      const step = Math.max(1, Math.ceil(max / 5))
      const ticks = []
      for (let i = 0; i <= max; i += step) ticks.push(i)
      if (ticks[ticks.length - 1] !== max) ticks.push(max)
      return ticks
    },
    displayedTopUsers() {
      return this.topFilter === 'points' ? this.topUsers.by_points : this.topUsers.by_redemptions
    },
    medalColors() {
      return ['bg-amber-500', 'bg-slate-400', 'bg-amber-700', 'bg-indigo-400', 'bg-indigo-300']
    },
  },
  mounted() {
    this.loadData()
  },
  methods: {
    async loadData() {
      this.loading = true
      try {
        const [profile, dashboard] = await Promise.all([
          axios.get('/api/admin/v1/admin/profile'),
          axios.get(`/api/admin/v1/dashboard?days=${this.chartDays}`),
        ])
        this.adminName = profile.data.name || 'المدير'
        this.stats = dashboard.data.stats
        this.cardInventory = dashboard.data.card_inventory
        this.registrationChart = dashboard.data.registration_chart || []
        this.topUsers = dashboard.data.top_users || { by_points: [], by_redemptions: [] }
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    barHeight(count) {
      const max = this.maxChartCount
      return max <= 1 && count > 0 ? 80 : Math.max((count / max) * 90, 8)
    },
    formatDate(dateStr) {
      const d = new Date(dateStr)
      return `${d.getMonth() + 1}/${d.getDate()}`
    },
  },
}
</script>
