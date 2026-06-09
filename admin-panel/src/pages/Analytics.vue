<template>
  <div class="p-6" dir="rtl">
    <!-- Header -->
    <div class="bg-gradient-to-l from-indigo-600 to-purple-600 rounded-2xl p-6 mb-6 shadow-lg shadow-indigo-200">
      <div class="flex justify-between items-center flex-wrap gap-3">
        <div>
          <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            التحليلات والإحصائيات
          </h1>
          <p class="text-indigo-200 text-sm mt-1">
            <svg class="w-3.5 h-3.5 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            من {{ period.start }} إلى {{ period.end }}
          </p>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-16">
      <div class="inline-block w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
      <p class="text-slate-400 text-sm mt-3">جاري تحميل البيانات...</p>
    </div>

    <template v-else>
      <!-- Filter Section -->
      <div class="bg-white rounded-xl shadow-sm border p-5 mb-6">
        <div class="flex flex-wrap gap-3 items-end">
          <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">الفترة الزمنية</label>
            <select v-model="form.period" @change="onPeriodChange"
                    class="border rounded-xl px-3 py-2 text-sm bg-white min-w-[140px] focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none">
              <option value="7days">آخر 7 أيام</option>
              <option value="10days">آخر 10 أيام</option>
              <option value="30days">آخر 30 يوم</option>
              <option value="custom">فترة مخصصة</option>
            </select>
          </div>
          <div v-if="form.period === 'custom'">
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">من تاريخ</label>
            <input type="date" v-model="form.start_date"
                   class="border rounded-xl px-3 py-2 text-sm bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none">
          </div>
          <div v-if="form.period === 'custom'">
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">إلى تاريخ</label>
            <input type="date" v-model="form.end_date"
                   class="border rounded-xl px-3 py-2 text-sm bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none">
          </div>
          <button @click="loadData"
                  class="bg-gradient-to-l from-indigo-600 to-purple-600 text-white px-5 py-2 rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-indigo-200 transition-all">
            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            تطبيق الفلتر
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3 mb-6">
        <div v-for="card in statCards" :key="card.key"
             class="bg-white rounded-xl shadow-sm border p-4 hover:shadow-md hover:-translate-y-0.5 transition-all cursor-default">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3" :style="{ background: card.bg }">
            <svg v-if="card.key === 'cards'" class="w-5 h-5" :style="{ color: card.color }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <svg v-else-if="card.key === 'points'" class="w-5 h-5" :style="{ color: card.color }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <svg v-else-if="card.key === 'active' || card.key === 'total'" class="w-5 h-5" :style="{ color: card.color }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <svg v-else-if="card.key === 'boxes'" class="w-5 h-5" :style="{ color: card.color }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <svg v-else-if="card.key === 'wheel'" class="w-5 h-5" :style="{ color: card.color }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <svg v-else-if="card.key === 'pred'" class="w-5 h-5" :style="{ color: card.color }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          </div>
          <p class="text-2xl font-bold text-slate-800">{{ card.value.toLocaleString() }}</p>
          <p class="text-xs text-slate-400 mt-0.5">{{ card.label }}</p>
        </div>
      </div>

      <!-- Charts Row 1 -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Daily Activity Chart -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border p-5">
          <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-indigo-50 flex items-center justify-center">
              <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
            </span>
            نشاط استبدال الكروت اليومي
          </h3>
          <div style="height: 260px;">
            <canvas ref="activityChart"></canvas>
          </div>
        </div>

        <!-- Category Distribution Pie -->
        <div class="bg-white rounded-xl shadow-sm border p-5">
          <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-amber-50 flex items-center justify-center">
              <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
              </svg>
            </span>
            توزيع الكروت حسب الفئة
          </h3>
          <div style="height: 260px;">
            <canvas ref="categoryChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Charts Row 2 -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Points Sources Doughnut -->
        <div class="bg-white rounded-xl shadow-sm border p-5">
          <h3 class="font-bold text-slate-700 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-emerald-50 flex items-center justify-center">
              <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </span>
            مصادر النقاط
          </h3>
          <div style="height: 260px;">
            <canvas ref="sourcesChart"></canvas>
          </div>
        </div>

        <!-- Top Users Leaderboard -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border overflow-hidden">
          <div class="p-5 pb-0">
            <h3 class="font-bold text-slate-700 mb-1 flex items-center gap-2">
              <span class="w-6 h-6 rounded-lg bg-rose-50 flex items-center justify-center">
                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
              </span>
              أكثر المستخدمين جمعاً للنقاط
            </h3>
            <p class="text-xs text-slate-400 mb-4">أفضل {{ topUsers.length }} مستخدم خلال هذه الفترة</p>
          </div>
          <div v-if="topUsers.length === 0" class="text-center py-10 text-slate-400 text-sm">
            لا توجد بيانات
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-gradient-to-l from-slate-800 to-slate-700 text-white">
                  <th class="text-right px-4 py-3 font-semibold text-xs">#</th>
                  <th class="text-right px-4 py-3 font-semibold text-xs">المستخدم</th>
                  <th class="text-right px-4 py-3 font-semibold text-xs">رقم الهاتف</th>
                  <th class="text-center px-4 py-3 font-semibold text-xs">الكروت</th>
                  <th class="text-center px-4 py-3 font-semibold text-xs">النقاط المكتسبة</th>
                  <th class="text-center px-4 py-3 font-semibold text-xs">الرصيد الحالي</th>
                  <th class="text-center px-4 py-3 font-semibold text-xs">النشاطات</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(u, idx) in topUsers" :key="u.id"
                    class="border-b border-slate-50 hover:bg-indigo-50/30 transition-colors">
                  <td class="px-4 py-3">
                    <div v-if="idx === 0" class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                      <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </div>
                    <div v-else-if="idx === 1" class="w-7 h-7 rounded-full bg-gradient-to-br from-slate-300 to-slate-400 flex items-center justify-center text-white text-xs font-bold">2</div>
                    <div v-else-if="idx === 2" class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-600 to-amber-700 flex items-center justify-center text-white text-xs font-bold">3</div>
                    <div v-else class="w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-xs font-bold">{{ idx + 1 }}</div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2.5">
                      <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                        {{ (u.name || '?')[0] }}
                      </div>
                      <span class="font-semibold text-slate-700">{{ u.name }}</span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-slate-500 dir-ltr text-left">{{ u.phone }}</td>
                  <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                      </svg>
                      {{ u.cards_count }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span class="inline-flex bg-gradient-to-l from-emerald-500 to-emerald-400 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm">
                      +{{ u.points_earned?.toLocaleString() || 0 }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center font-bold text-slate-700">{{ u.points_balance?.toLocaleString() || 0 }}</td>
                  <td class="px-4 py-3 text-center text-slate-500">{{ u.activities }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
Chart.register(...registerables)

const STAT_COLORS = [
  { bg: 'linear-gradient(135deg, #eef2ff, #e0e7ff)', color: '#6366f1' },
  { bg: 'linear-gradient(135deg, #ecfdf5, #d1fae5)', color: '#10b981' },
  { bg: 'linear-gradient(135deg, #fef3c7, #fde68a)', color: '#f59e0b' },
  { bg: 'linear-gradient(135deg, #fef3c7, #fde68a)', color: '#f59e0b' },
  { bg: 'linear-gradient(135deg, #fce7f3, #fbcfe8)', color: '#ec4899' },
  { bg: 'linear-gradient(135deg, #ede9fe, #ddd6fe)', color: '#8b5cf6' },
  { bg: 'linear-gradient(135deg, #cffafe, #a5f3fc)', color: '#06b6d4' },
]

export default {
  data() {
    return {
      loading: true,
      form: { period: '7days', start_date: '', end_date: '' },
      period: { start: '', end: '' },
      stats: {},
      dailyActivity: [],
      categoryDist: [],
      pointsSources: [],
      topUsers: [],
      chartInstances: {},
    }
  },
  computed: {
    statCards() {
      const s = this.stats
      return [
        { key: 'cards', label: 'كروت مستبدلة', value: s.cards_redeemed || 0, ...STAT_COLORS[0] },
        { key: 'points', label: 'نقاط ممنوحة', value: s.points_given || 0, ...STAT_COLORS[1] },
        { key: 'active', label: 'مستخدمين نشطين', value: s.active_users || 0, ...STAT_COLORS[2] },
        { key: 'total', label: 'إجمالي المستخدمين', value: s.total_users || 0, ...STAT_COLORS[2] },
        { key: 'boxes', label: 'فتح صناديق', value: s.box_opens || 0, ...STAT_COLORS[3] },
        { key: 'wheel', label: 'تدوير العجلة', value: s.wheel_spins || 0, ...STAT_COLORS[4] },
        { key: 'pred', label: 'التوقعات', value: s.predictions || 0, ...STAT_COLORS[5] },
      ]
    },
  },
  mounted() {
    this.loadData()
  },
  methods: {
    onPeriodChange() {
      if (this.form.period !== 'custom') this.loadData()
    },
    async loadData() {
      this.loading = true
      this.destroyCharts()
      try {
        const params = { period: this.form.period }
        if (this.form.period === 'custom') {
          params.start_date = this.form.start_date
          params.end_date = this.form.end_date
        }
        const res = await axios.get('/api/admin/v1/analytics', { params })
        const d = res.data
        this.period = d.period
        this.stats = d.stats
        this.dailyActivity = d.daily_activity || []
        this.categoryDist = d.category_distribution || []
        this.pointsSources = d.points_sources || []
        this.topUsers = d.top_users || []
        this.$nextTick(() => this.renderCharts())
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    destroyCharts() {
      Object.values(this.chartInstances).forEach(c => c?.destroy())
      this.chartInstances = {}
    },
    renderCharts() {
      this.renderActivityChart()
      this.renderCategoryChart()
      this.renderSourcesChart()
    },
    renderActivityChart() {
      const el = this.$refs.activityChart
      if (!el || !this.dailyActivity.length) return
      this.chartInstances.activity = new Chart(el, {
        type: 'bar',
        data: {
          labels: this.dailyActivity.map(i => {
            const d = new Date(i.date)
            return `${d.getDate()}/${d.getMonth() + 1}`
          }),
          datasets: [{
            label: 'كروت مستبدلة',
            data: this.dailyActivity.map(i => i.count),
            backgroundColor: this.dailyActivity.map(i =>
              i.count > 0 ? 'rgba(99, 102, 241, 0.75)' : 'rgba(99, 102, 241, 0.12)'
            ),
            borderColor: 'rgba(99, 102, 241, 0.9)',
            borderWidth: 1,
            borderRadius: 6,
            borderSkipped: false,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { stepSize: 1, color: '#94a3b8', font: { size: 11 } },
              grid: { color: '#f1f5f9' },
            },
            x: {
              ticks: { color: '#64748b', font: { size: 10 } },
              grid: { display: false },
            },
          },
        },
      })
    },
    renderCategoryChart() {
      const el = this.$refs.categoryChart
      if (!el || !this.categoryDist.length) return
      const colors = ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4', '#ef4444']
      this.chartInstances.category = new Chart(el, {
        type: 'doughnut',
        data: {
          labels: this.categoryDist.map(i => i.category),
          datasets: [{
            data: this.categoryDist.map(i => i.count),
            backgroundColor: colors.slice(0, this.categoryDist.length),
            borderWidth: 2,
            borderColor: '#fff',
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '55%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: { padding: 12, usePointStyle: true, font: { size: 10 }, color: '#64748b' },
            },
          },
        },
      })
    },
    renderSourcesChart() {
      const el = this.$refs.sourcesChart
      if (!el || !this.pointsSources.length) return
      const colors = ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4', '#ef4444', '#14b8a6', '#f97316']
      this.chartInstances.sources = new Chart(el, {
        type: 'doughnut',
        data: {
          labels: this.pointsSources.map(i => i.source),
          datasets: [{
            data: this.pointsSources.map(i => Math.abs(i.points)),
            backgroundColor: colors.slice(0, this.pointsSources.length),
            borderWidth: 2,
            borderColor: '#fff',
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '60%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: { padding: 10, usePointStyle: true, font: { size: 9 }, color: '#64748b' },
            },
          },
        },
      })
    },
  },
  beforeUnmount() {
    this.destroyCharts()
  },
}
</script>
