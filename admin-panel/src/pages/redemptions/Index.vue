<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <h1 class="text-2xl font-bold text-slate-800">📋 سجل عمليات الاستبدال</h1>
      <div class="flex items-center gap-4">
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-5 py-2 text-center">
          <span class="text-xs text-emerald-500">إجمالي النقاط المستخدمة</span>
          <p class="text-xl font-bold text-emerald-700">{{ totalPointsSpent.toLocaleString() }}</p>
        </div>
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-2 text-center">
          <span class="text-xs text-amber-600">القيمة الإجمالية للكروت</span>
          <p class="text-xl font-bold text-amber-700">{{ totalCardValue.toLocaleString() }} <span class="text-xs font-normal">ريال</span></p>
        </div>
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl px-5 py-2 text-center">
          <span class="text-xs text-indigo-500">إجمالي العمليات</span>
          <p class="text-xl font-bold text-indigo-700">{{ total }}</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-3 mb-4 flex flex-wrap gap-2 items-center">
      <input v-model="search" @input="debouncedFetch" placeholder="🔍 بحث برقم الهاتف"
        class="border border-slate-200 rounded-lg px-3 py-1.5 text-sm w-48 outline-none focus:border-emerald-400 transition" />
      <select v-model="rewardId" @change="fetchRedemptions" class="border border-slate-200 rounded-lg px-3 py-1.5 text-sm outline-none focus:border-emerald-400 transition">
        <option value="">كل المكافآت</option>
        <option v-for="r in rewards" :key="r.id" :value="r.id">{{ r.name }}</option>
      </select>
      <span class="text-xs text-slate-400 mr-auto">{{ total }} عملية</span>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-16">
      <div class="w-10 h-10 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin mx-auto"></div>
      <p class="text-slate-400 text-sm mt-3">جاري التحميل...</p>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
              <th class="text-right px-4 py-3 font-semibold text-slate-600">العميل</th>
              <th class="text-right px-4 py-3 font-semibold text-slate-600">المكافأة المستبدلة</th>
              <th class="text-center px-4 py-3 font-semibold text-slate-600">النقاط المستخدمة</th>
              <th class="text-center px-4 py-3 font-semibold text-slate-600">قيمة الكرت</th>
              <th class="text-center px-4 py-3 font-semibold text-slate-600">الكرت الناتج</th>
              <th class="text-center px-4 py-3 font-semibold text-slate-600">تاريخ العملية</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in redemptions" :key="r.id" class="border-b border-slate-100 hover:bg-slate-50 transition">
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 font-bold text-xs">
                    {{ (r.user_name || r.user_phone || '?')[0] }}
                  </div>
                  <div>
                    <p class="font-medium text-slate-800 text-sm">{{ r.user_name || '—' }}</p>
                    <p class="text-xs text-slate-400" dir="ltr">{{ r.user_phone || '—' }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 font-medium text-slate-800">{{ r.reward_name }}</td>
              <td class="px-4 py-3 text-center">
                <span class="bg-rose-50 text-rose-700 px-2.5 py-1 rounded-lg text-xs font-bold">{{ r.points_spent }}</span>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="text-slate-700 font-medium">{{ r.card_value ? r.card_value + ' ريال' : '—' }}</span>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="font-mono text-xs bg-slate-100 px-2.5 py-1.5 rounded-lg text-slate-700" dir="ltr">{{ r.card_code }}</span>
              </td>
              <td class="px-4 py-3 text-center text-slate-500 text-xs">{{ formatDate(r.created_at) }}</td>
            </tr>
            <tr v-if="redemptions.length === 0">
              <td colspan="6" class="text-center py-12 text-slate-400">لا توجد عمليات استبدال</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="lastPage > 1" class="flex items-center justify-between px-4 py-3 border-t border-slate-100">
        <button @click="changePage(currentPage - 1)" :disabled="currentPage <= 1"
          class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 transition">
          → السابق
        </button>
        <span class="text-sm text-slate-500">صفحة {{ currentPage }} من {{ lastPage }}</span>
        <button @click="changePage(currentPage + 1)" :disabled="currentPage >= lastPage"
          class="px-3 py-1.5 text-sm rounded-lg border border-slate-200 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 transition">
          التالي ←
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
      redemptions: [],
      rewards: [],
      total: 0,
      totalPointsSpent: 0,
      totalCardValue: 0,
      currentPage: 1,
      lastPage: 1,
      search: '',
      rewardId: '',
      loading: true,
      debounceTimer: null,
    }
  },
  mounted() {
    this.fetchRedemptions()
  },
  methods: {
    async fetchRedemptions() {
      this.loading = true
      try {
        const { data } = await axios.get('/api/admin/v1/redemptions', {
          params: {
            search: this.search,
            reward_id: this.rewardId,
            page: this.currentPage,
            per_page: 20,
          },
        })
        this.redemptions = data.data
        this.total = data.total
        this.totalPointsSpent = data.total_points_spent
        this.totalCardValue = data.total_card_value || 0
        this.currentPage = data.current_page
        this.lastPage = data.last_page
        this.rewards = data.rewards || this.rewards
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    changePage(page) {
      this.currentPage = page
      this.fetchRedemptions()
    },
    debouncedFetch() {
      clearTimeout(this.debounceTimer)
      this.debounceTimer = setTimeout(() => {
        this.currentPage = 1
        this.fetchRedemptions()
      }, 400)
    },
    formatDate(dateStr) {
      if (!dateStr) return '—'
      const d = new Date(dateStr)
      return d.toLocaleDateString('ar-YE', {
        year: 'numeric', month: 'numeric', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
      })
    },
  },
}
</script>
