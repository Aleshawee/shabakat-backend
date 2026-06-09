<template>
  <div class="p-6">
    <!-- Header: title + total -->
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">إدارة الكروت</h1>
      <div class="bg-violet-50 border border-violet-200 rounded-xl px-5 py-2 text-center">
        <span class="text-xs text-violet-500">إجمالي الكروت</span>
        <p class="text-2xl font-bold text-violet-700">{{ total }}</p>
      </div>
    </div>

    <!-- Stats cards (clickable) -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
      <button v-for="s in statCards" :key="s.key" @click="applyStatFilter(s)"
        class="bg-white rounded-xl shadow-sm border p-4 text-right hover:shadow-md transition cursor-pointer"
        :class="statActive(s) ? 'ring-2 ring-violet-500 border-violet-300' : ''">
        <span class="text-xs" :class="s.color">{{ s.label }}</span>
        <p class="text-2xl font-bold mt-1" :class="s.color">{{ s.count }}</p>
      </button>
    </div>

    <!-- Tabs -->
    <div class="flex gap-1 mb-4 bg-slate-100 p-1 rounded-lg w-fit">
      <button @click="switchTab('reward')"
        class="px-5 py-2 rounded-md text-sm font-medium transition"
        :class="tab === 'reward' ? 'bg-white shadow-sm text-violet-700' : 'hover:bg-white/50'">
        🎁 كروت المكافأة
      </button>
      <button @click="switchTab('network')"
        class="px-5 py-2 rounded-md text-sm font-medium transition"
        :class="tab === 'network' ? 'bg-white shadow-sm text-violet-700' : 'hover:bg-white/50'">
        💳 كروت الشبكة
      </button>
    </div>

    <!-- Filters bar -->
    <div class="bg-white rounded-xl shadow-sm border p-3 mb-4 flex flex-wrap gap-2 items-center">
      <input v-model="filters.search" @input="debouncedFetch" placeholder="🔍 بحث بالكود"
        class="border rounded-lg px-3 py-1.5 text-sm w-36" />
      <select v-model="filters.status" @change="fetchAll" class="border rounded-lg px-3 py-1.5 text-sm">
        <option value="">كل الحالات</option>
        <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
      </select>
      <select v-if="tab === 'network'" v-model="filters.category_id" @change="fetchAll" class="border rounded-lg px-3 py-1.5 text-sm">
        <option value="">كل الفئات</option>
        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>
      <select v-if="tab === 'reward'" v-model="filters.reward_id" @change="fetchAll" class="border rounded-lg px-3 py-1.5 text-sm">
        <option value="">كل المكافآت</option>
        <option v-for="r in rewards" :key="r.id" :value="r.id">{{ r.name }}</option>
      </select>
      <input v-model="filters.from_date" @change="fetchAll" type="date" class="border rounded-lg px-3 py-1.5 text-sm w-32" title="من تاريخ" />
      <input v-model="filters.to_date" @change="fetchAll" type="date" class="border rounded-lg px-3 py-1.5 text-sm w-32" title="إلى تاريخ" />
      <button @click="resetFilters" class="text-sm text-slate-500 hover:text-slate-700 px-2">إعادة تعيين</button>
      <button @click="openImportModal()" class="bg-violet-600 hover:bg-violet-700 text-white px-4 py-1.5 rounded-lg text-sm font-medium mr-auto whitespace-nowrap">
        + استيراد
      </button>
    </div>

    <!-- Bulk actions bar -->
    <div v-if="selectedIds.length" class="bg-violet-50 border border-violet-200 rounded-xl px-4 py-2 mb-4 flex items-center gap-3">
      <span class="text-sm text-violet-700 font-medium">تم تحديد {{ selectedIds.length }} كرت</span>
      <button @click="bulkAction('delete')" class="text-sm bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg">حذف</button>
      <button @click="bulkAction('restore')" class="text-sm bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1 rounded-lg">استعادة</button>
      <button v-if="filters.trashed === 'only' || filters.trashed === 'with'" @click="bulkAction('forceDelete')" class="text-sm bg-red-700 hover:bg-red-800 text-white px-3 py-1 rounded-lg">حذف نهائي</button>
      <button @click="selectedIds = []" class="text-sm text-slate-500 hover:text-slate-700 px-2">إلغاء التحديد</button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b">
          <tr>
            <th class="p-3 w-8">
              <input type="checkbox" :checked="allSelected" @change="toggleAll" class="rounded" />
            </th>
            <th class="text-right p-3 font-medium text-slate-600">#</th>
            <th class="text-right p-3 font-medium text-slate-600">الكود</th>
            <th class="text-right p-3 font-medium text-slate-600">{{ tab === 'reward' ? 'المكافأة' : 'الفئة' }}</th>
            <th class="text-right p-3 font-medium text-slate-600">الحالة</th>
            <th class="text-right p-3 font-medium text-slate-600">تاريخ الرفع</th>
            <th class="text-center p-3 font-medium text-slate-600">إجراءات</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="(card, i) in cards" :key="card.id"
            :class="[card.deleted_at ? 'bg-red-50 text-slate-400' : 'hover:bg-slate-50', selectedIds.includes(card.id) ? 'bg-violet-50/50' : '']">
            <td class="p-3">
              <input type="checkbox" :checked="selectedIds.includes(card.id)" @change="toggleSelect(card.id)" class="rounded" />
            </td>
            <td class="p-3 text-slate-400 text-xs">{{ (page - 1) * perPage + i + 1 }}</td>
            <td class="p-3 font-mono text-sm ltr text-center font-medium">{{ card.code }}</td>
            <td class="p-3">{{ card.reward?.name || card.category?.name || '—' }}</td>
            <td class="p-3">
              <span v-if="card.deleted_at" class="bg-red-100 text-red-600 px-2 py-0.5 rounded-full text-xs font-medium">محذوف</span>
              <span v-else :class="statusClass(card.status)" class="px-2 py-0.5 rounded-full text-xs font-medium">{{ statusLabel(card.status) }}</span>
            </td>
            <td class="p-3 text-slate-500 text-xs">{{ card.created_at?.slice(0, 10) || '—' }}</td>
            <td class="p-3 text-center">
              <button v-if="card.deleted_at" @click="restoreCard(card.id)" class="text-emerald-600 hover:text-emerald-800 mx-1 text-xs font-medium">استعادة</button>
              <button v-else @click="deleteCard(card.id)" class="text-red-500 hover:text-red-700 mx-1 text-xs font-medium">حذف</button>
            </td>
          </tr>
          <tr v-if="!cards.length">
            <td colspan="7" class="p-10 text-center text-slate-400">لا توجد كروت</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination + Page size -->
    <div class="flex items-center justify-between mt-4">
      <div class="flex items-center gap-2">
        <span class="text-xs text-slate-500">عرض</span>
        <select v-model.number="perPage" @change="page = 1; fetchAll()" class="border rounded px-2 py-1 text-sm">
          <option v-for="n in perPageOptions" :key="n" :value="n">{{ n }}</option>
        </select>
        <span class="text-xs text-slate-500">صف لكل صفحة</span>
      </div>
      <div v-if="lastPage > 1" class="flex items-center gap-2">
        <button @click="page = Math.max(1, page - 1); fetchAll()" :disabled="page <= 1"
          class="px-3 py-1 rounded border text-sm disabled:opacity-30 hover:bg-slate-50">السابق</button>
        <span class="text-sm text-slate-500">{{ page }} / {{ lastPage }}</span>
        <button @click="page = Math.min(lastPage, page + 1); fetchAll()" :disabled="page >= lastPage"
          class="px-3 py-1 rounded border text-sm disabled:opacity-30 hover:bg-slate-50">التالي</button>
      </div>
    </div>

    <!-- Import Modal -->
    <div v-if="showImport" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="showImport=false">
      <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl">
        <h2 class="text-lg font-bold mb-4">استيراد {{ tab === 'reward' ? 'كروت مكافأة' : 'كروت شبكة' }}</h2>
        <form @submit.prevent="importCards">
          <div class="space-y-3">
            <div v-if="tab === 'reward'">
              <label class="block text-sm font-medium mb-1">المكافأة</label>
              <select v-model="importForm.reward_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="" disabled>اختر مكافأة</option>
                <option v-for="r in rewards" :key="r.id" :value="r.id">{{ r.name }} ({{ r.points_cost }} نقطة)</option>
              </select>
            </div>
            <div v-else>
              <label class="block text-sm font-medium mb-1">الفئة</label>
              <select v-model="importForm.category_id" required class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="" disabled>اختر فئة</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }} ({{ c.price }} ريال)</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">الأكواد (كود واحد في كل سطر)</label>
              <textarea v-model="importForm.codes" rows="6" dir="ltr"
                class="w-full border rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-violet-500 outline-none"
                placeholder="ABC123&#10;DEF456&#10;GHI789"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">أو ارفع ملف CSV</label>
              <input type="file" accept=".csv,.txt" @change="handleFileUpload"
                class="w-full border rounded-lg px-3 py-2 text-sm file:bg-violet-50 file:border-0 file:text-violet-700 file:font-medium file:px-3 file:py-1 file:rounded-md" />
            </div>
          </div>
          <p v-if="importError" class="text-red-500 text-sm mt-3">{{ importError }}</p>
          <div class="flex gap-2 mt-5">
            <button type="submit" :disabled="importing" class="bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex-1 disabled:opacity-50">
              {{ importing ? 'جاري...' : 'استيراد' }}
            </button>
            <button type="button" @click="showImport=false" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-lg text-sm flex-1">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      tab: 'reward',
      cards: [],
      rewards: [],
      categories: [],
      statCards: [],
      page: 1,
      lastPage: 1,
      perPage: 50,
      perPageOptions: [10, 25, 50, 100],
      total: 0,
      selectedIds: [],
      filters: { search: '', status: '', reward_id: '', category_id: '', from_date: '', to_date: '', trashed: '' },
      showImport: false,
      importing: false,
      importError: '',
      importForm: { reward_id: '', category_id: '', codes: '' },
      importFile: null,
      debounceTimer: null,
    }
  },
  computed: {
    statusOptions() {
      if (this.tab === 'reward') {
        return [
          { value: 'available', label: '🟢 متاح' },
          { value: 'redeemed', label: '⚪ مستبدل' },
          { value: 'expired', label: '🔴 منتهي' },
        ]
      }
      return [
        { value: 'active', label: '🟢 نشط' },
        { value: 'used', label: '⚪ مستخدم' },
        { value: 'expired', label: '🔴 منتهي' },
      ]
    },
    allSelected: {
      get() { return this.cards.length > 0 && this.selectedIds.length === this.cards.length },
      set() {},
    },
  },
  mounted() { this.fetchAll() },
  methods: {
    statusClass(s) {
      const map = {
        available: 'bg-emerald-100 text-emerald-700',
        active: 'bg-emerald-100 text-emerald-700',
        redeemed: 'bg-slate-100 text-slate-500',
        used: 'bg-slate-100 text-slate-500',
        expired: 'bg-red-100 text-red-600',
      }
      return map[s] || 'bg-slate-100 text-slate-500'
    },
    statusLabel(s) {
      const map = { available: 'متاح', active: 'نشط', redeemed: 'مستبدل', used: 'مستخدم', expired: 'منتهي' }
      return map[s] || s
    },
    switchTab(t) {
      this.tab = t
      this.selectedIds = []
      this.page = 1
      this.resetFilters()
      this.fetchAll()
    },
    async fetchAll() {
      const headers = { Authorization: `Bearer ${localStorage.getItem('admin_token')}` }
      const params = { page: this.page, per_page: this.perPage, ...this.filters }
      Object.keys(params).forEach(k => { if (!params[k]) delete params[k] })
      const ep = this.tab === 'reward' ? '/api/admin/v1/reward-cards' : '/api/admin/v1/network-cards'
      const [res, ss] = await Promise.all([
        axios.get(ep, { headers, params }),
        axios.get('/api/admin/v1/cards/stats', { headers }),
      ])
      this.cards = res.data.data || []
      this.page = res.data.current_page || 1
      this.lastPage = res.data.last_page || 1
      this.total = res.data.total || 0
      this.buildStatCards(ss.data)
      if (!this.rewards.length || !this.categories.length) {
        const [rw, ct] = await Promise.all([
          axios.get('/api/admin/v1/rewards', { headers }),
          axios.get('/api/admin/v1/categories', { headers }),
        ])
        this.rewards = rw.data
        this.categories = ct.data
      }
    },
    buildStatCards(ss) {
      const base = this.tab === 'reward' ? ss.reward_cards : ss.network_cards
      const suffix = this.tab === 'reward' ? ['available', 'redeemed'] : ['active', 'used']
      this.statCards = [
        { key: 'total', label: '📦 الإجمالي', count: base.total, color: 'text-violet-600' },
        { key: suffix[0], label: '🟢 ' + (this.tab === 'reward' ? 'متاح' : 'نشط'), count: base[suffix[0]], color: 'text-emerald-600' },
        { key: suffix[1], label: '⚪ ' + (this.tab === 'reward' ? 'مستبدل' : 'مستخدم'), count: base[suffix[1]], color: 'text-slate-600' },
        { key: 'expired', label: '🔴 منتهي', count: base.expired, color: 'text-red-500' },
        { key: 'trashed', label: '🗑️ محذوف', count: base.trashed, color: 'text-orange-500' },
      ]
    },
    statActive(s) {
      if (s.key === 'total') return false
      if (s.key === 'trashed') return this.filters.trashed === 'only'
      if (s.key === 'expired') return this.filters.status === 'expired'
      return this.filters.status === (this.tab === 'reward'
        ? (s.key === 'available' ? 'available' : 'redeemed')
        : (s.key === 'active' ? 'active' : 'used'))
    },
    applyStatFilter(s) {
      if (s.key === 'total') return
      this.page = 1
      if (s.key === 'trashed') {
        this.filters.trashed = this.filters.trashed === 'only' ? '' : 'only'
        this.filters.status = ''
      } else {
        this.filters.status = this.filters.status === s.key ? '' : s.key
        this.filters.trashed = ''
      }
      this.fetchAll()
    },
    debouncedFetch() {
      clearTimeout(this.debounceTimer)
      this.debounceTimer = setTimeout(() => { this.page = 1; this.fetchAll() }, 300)
    },
    resetFilters() {
      this.filters = { search: '', status: '', reward_id: '', category_id: '', from_date: '', to_date: '', trashed: '' }
      this.page = 1
      this.fetchAll()
    },
    toggleAll() {
      if (this.allSelected) {
        this.selectedIds = []
      } else {
        this.selectedIds = this.cards.map(c => c.id)
      }
    },
    toggleSelect(id) {
      const idx = this.selectedIds.indexOf(id)
      if (idx > -1) this.selectedIds.splice(idx, 1)
      else this.selectedIds.push(id)
    },
    openImportModal() {
      this.importForm = { reward_id: '', category_id: '', codes: '' }
      this.importFile = null
      this.importError = ''
      this.showImport = true
    },
    handleFileUpload(e) {
      const file = e.target.files[0]
      if (!file) return
      this.importFile = file
      const reader = new FileReader()
      reader.onload = (ev) => {
        const text = ev.target.result
        const lines = text.split(/\r?\n/).map(l => l.trim()).filter(l => l && !l.startsWith(','))
        const codes = lines.map(l => l.split(',')[0].trim()).filter(c => c)
        this.importForm.codes += (this.importForm.codes ? '\n' : '') + codes.join('\n')
      }
      reader.readAsText(file)
    },
    async importCards() {
      this.importing = true
      this.importError = ''
      try {
        const ep = this.tab === 'reward' ? '/api/admin/v1/reward-cards/import' : '/api/admin/v1/network-cards/import'
        const fd = new FormData()
        if (this.importForm.reward_id) fd.append('reward_id', this.importForm.reward_id)
        if (this.importForm.category_id) fd.append('category_id', this.importForm.category_id)
        if (this.importForm.codes) fd.append('codes', this.importForm.codes)
        if (this.importFile) fd.append('file', this.importFile)
        await axios.post(ep, fd, { headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` } })
        this.showImport = false
        this.page = 1
        this.fetchAll()
      } catch (e) {
        this.importError = e.response?.data?.message || 'فشل الاستيراد'
      } finally { this.importing = false }
    },
    async deleteCard(id) {
      if (!confirm('نقل الكرت إلى سلة المحذوفات؟')) return
      const ep = this.tab === 'reward' ? `/api/admin/v1/reward-cards/${id}` : `/api/admin/v1/network-cards/${id}`
      await axios.delete(ep, { headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` } })
      this.fetchAll()
    },
    async restoreCard(id) {
      const ep = this.tab === 'reward' ? `/api/admin/v1/reward-cards/${id}/restore` : `/api/admin/v1/network-cards/${id}/restore`
      await axios.post(ep, {}, { headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` } })
      this.fetchAll()
    },
    async bulkAction(action) {
      if (!this.selectedIds.length) return
      const label = { delete: 'حذف', restore: 'استعادة', forceDelete: 'حذف نهائي' }[action]
      if (!confirm(`تأكيد ${label} ${this.selectedIds.length} كرت؟`)) return
      try {
        const ep = this.tab === 'reward'
          ? `/api/admin/v1/reward-cards/bulk-${action}`
          : `/api/admin/v1/network-cards/bulk-${action}`
        await axios.post(ep, { ids: this.selectedIds }, {
          headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` },
        })
        this.selectedIds = []
        this.fetchAll()
      } catch (e) {
        alert(e.response?.data?.message || 'فشلت العملية')
      }
    },
  },
}
</script>