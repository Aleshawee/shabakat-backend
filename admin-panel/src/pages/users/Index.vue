<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
      <h1 class="text-2xl font-bold">إدارة العملاء</h1>
      <div class="flex items-center gap-2">
        <button @click="openBulkAdd" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
          + إضافة نقاط للكل
        </button>
        <div class="bg-violet-50 border border-violet-200 rounded-xl px-5 py-2 text-center">
          <span class="text-xs text-violet-500">إجمالي المستخدمين</span>
          <p class="text-2xl font-bold text-violet-700">{{ total }}</p>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
      <button v-for="s in statCards" :key="s.key" @click="applyStatFilter(s)"
        class="bg-white rounded-xl shadow-sm border p-4 text-right hover:shadow-md transition cursor-pointer"
        :class="statActive(s) ? 'ring-2 ring-violet-500 border-violet-300' : ''">
        <span class="text-xs" :class="s.color">{{ s.label }}</span>
        <p class="text-2xl font-bold mt-1" :class="s.color">{{ s.count }}</p>
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border p-3 mb-4 flex flex-wrap gap-2 items-center">
      <input v-model="filters.search" @input="debouncedFetch" placeholder="🔍 اسم أو هاتف"
        class="border rounded-lg px-3 py-1.5 text-sm w-44" />
      <select v-model="filters.status" @change="fetchAll" class="border rounded-lg px-3 py-1.5 text-sm">
        <option value="">كل الحالات</option>
        <option value="active">🟢 نشط</option>
        <option value="suspended">🟡 موقوف</option>
        <option value="banned">🔴 محظور</option>
      </select>
      <input v-model.number="filters.points_min" @input="debouncedFetch" type="number" placeholder="نقاط ≥"
        class="border rounded-lg px-3 py-1.5 text-sm w-20" />
      <input v-model.number="filters.points_max" @input="debouncedFetch" type="number" placeholder="نقاط ≤"
        class="border rounded-lg px-3 py-1.5 text-sm w-20" />
      <input v-model="filters.from_date" @change="fetchAll" type="date" class="border rounded-lg px-3 py-1.5 text-sm w-32" title="من تاريخ" />
      <input v-model="filters.to_date" @change="fetchAll" type="date" class="border rounded-lg px-3 py-1.5 text-sm w-32" title="إلى تاريخ" />
      <select v-model="sortField" @change="fetchAll" class="border rounded-lg px-3 py-1.5 text-sm">
        <option value="created_at">التسجيل</option>
        <option value="points_balance">النقاط</option>
        <option value="last_active_at">آخر نشاط</option>
      </select>
      <button @click="sortDir = sortDir === 'asc' ? 'desc' : 'asc'" class="text-sm px-2 py-1 rounded border">
        {{ sortDir === 'desc' ? '⬇' : '⬆' }}
      </button>
      <button @click="resetFilters" class="text-sm text-slate-500 hover:text-slate-700 px-2">إعادة تعيين</button>
    </div>

    <!-- Desktop table -->
    <div class="hidden md:block bg-white rounded-xl shadow-sm border overflow-x-auto">
      <table class="w-full min-w-[700px] text-sm">
        <thead class="bg-slate-50 border-b">
          <tr>
            <th class="text-right p-3 font-medium text-slate-600">#</th>
            <th class="text-right p-3 font-medium text-slate-600">الاسم</th>
            <th class="text-right p-3 font-medium text-slate-600">رقم الهاتف</th>
            <th class="text-right p-3 font-medium text-slate-600">النقاط</th>
            <th class="text-right p-3 font-medium text-slate-600">الحالة</th>
            <th class="text-right p-3 font-medium text-slate-600">آخر نشاط</th>
            <th class="text-center p-3 font-medium text-slate-600">إجراءات</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="(user, i) in users" :key="user.id" class="hover:bg-slate-50">
            <td class="p-3 text-slate-400 text-xs">{{ (page - 1) * perPage + i + 1 }}</td>
            <td class="p-3 font-medium">{{ user.name || '—' }}</td>
            <td class="p-3 font-mono text-sm ltr" dir="ltr">{{ user.phone }}</td>
            <td class="p-3">
              <span class="font-bold" :class="user.points_balance > 0 ? 'text-emerald-600' : 'text-slate-400'">{{ user.points_balance }}</span>
            </td>
            <td class="p-3">
              <span :class="{
                'bg-emerald-100 text-emerald-700': user.status === 'active',
                'bg-amber-100 text-amber-600': user.status === 'suspended',
                'bg-red-100 text-red-600': user.status === 'banned',
              }" class="px-2 py-0.5 rounded-full text-xs font-medium">
                {{ { active: 'نشط', suspended: 'موقوف', banned: 'محظور' }[user.status] || user.status }}
              </span>
            </td>
            <td class="p-3 text-slate-500 text-xs">{{ user.last_active_at?.slice(0, 10) || '—' }}</td>
            <td class="p-3 text-center whitespace-nowrap">
              <button @click="openDetails(user)" class="text-violet-600 hover:text-violet-800 mx-1 text-xs font-medium">تفاصيل</button>
              <button @click="openEdit(user)" class="text-violet-600 hover:text-violet-800 mx-1 text-xs font-medium">تعديل</button>
              <button @click="openAddPoints(user)" class="text-emerald-600 hover:text-emerald-800 mx-1 text-xs font-medium">+ نقاط</button>
            </td>
          </tr>
          <tr v-if="!users.length">
            <td colspan="7" class="p-10 text-center text-slate-400">لا يوجد مستخدمين</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile cards -->
    <div class="md:hidden space-y-3">
      <div v-for="user in users" :key="user.id" class="bg-white rounded-xl shadow-sm border p-4">
        <div class="flex items-center justify-between mb-2">
          <span class="font-bold text-sm">{{ user.name || '—' }}</span>
          <span :class="{
            'bg-emerald-100 text-emerald-700': user.status === 'active',
            'bg-amber-100 text-amber-600': user.status === 'suspended',
            'bg-red-100 text-red-600': user.status === 'banned',
          }" class="px-2 py-0.5 rounded-full text-xs font-medium">
            {{ { active: 'نشط', suspended: 'موقوف', banned: 'محظور' }[user.status] || user.status }}
          </span>
        </div>
        <div class="grid grid-cols-2 gap-1 text-xs text-slate-500 mb-3">
          <div><span class="text-slate-400">الهاتف:</span> <span class="font-mono" dir="ltr">{{ user.phone }}</span></div>
          <div><span class="text-slate-400">النقاط:</span> <span class="font-bold" :class="user.points_balance > 0 ? 'text-emerald-600' : 'text-slate-400'">{{ user.points_balance }}</span></div>
          <div><span class="text-slate-400">آخر نشاط:</span> {{ user.last_active_at?.slice(0, 10) || '—' }}</div>
        </div>
        <div class="flex gap-2">
          <button @click="openDetails(user)" class="flex-1 text-xs bg-violet-50 text-violet-700 border border-violet-200 rounded-lg py-2 font-medium">تفاصيل</button>
          <button @click="openEdit(user)" class="flex-1 text-xs bg-violet-50 text-violet-700 border border-violet-200 rounded-lg py-2 font-medium">تعديل</button>
          <button @click="openAddPoints(user)" class="flex-1 text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg py-2 font-medium">+ نقاط</button>
        </div>
      </div>
      <div v-if="!users.length" class="bg-white rounded-xl shadow-sm border p-8 text-center text-slate-400">لا يوجد مستخدمين</div>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between mt-4">
      <div class="flex items-center gap-2">
        <span class="text-xs text-slate-500">عرض</span>
        <select v-model.number="perPage" @change="page = 1; fetchAll()" class="border rounded px-2 py-1 text-sm">
          <option v-for="n in perPageOptions" :key="n" :value="n">{{ n }}</option>
        </select>
        <span class="text-xs text-slate-500">مستخدم لكل صفحة</span>
      </div>
      <div v-if="lastPage > 1" class="flex items-center gap-2">
        <button @click="page = Math.max(1, page - 1); fetchAll()" :disabled="page <= 1"
          class="px-3 py-1 rounded border text-sm disabled:opacity-30">السابق</button>
        <span class="text-sm text-slate-500">{{ page }} / {{ lastPage }}</span>
        <button @click="page = Math.min(lastPage, page + 1); fetchAll()" :disabled="page >= lastPage"
          class="px-3 py-1 rounded border text-sm disabled:opacity-30">التالي</button>
      </div>
    </div>

    <!-- User Details Modal -->
    <div v-if="showDetails" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="showDetails=false">
      <div class="bg-white rounded-2xl p-6 w-full max-w-2xl shadow-xl max-h-[80vh] overflow-y-auto">
        <div class="flex justify-between items-start mb-4">
          <h2 class="text-lg font-bold">{{ detailsUser?.name || 'مستخدم' }}</h2>
          <button @click="showDetails=false" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        <!-- User info -->
        <div class="grid grid-cols-2 gap-3 mb-6 text-sm">
          <div><span class="text-slate-400">الهاتف:</span> <span class="font-mono" dir="ltr">{{ detailsUser?.phone }}</span></div>
          <div><span class="text-slate-400">النقاط:</span> <span class="font-bold text-emerald-600">{{ detailsUser?.points_balance }}</span></div>
          <div><span class="text-slate-400">الحالة:</span> {{ { active: '🟢 نشط', suspended: '🟡 موقوف', banned: '🔴 محظور' }[detailsUser?.status] }}</div>
          <div><span class="text-slate-400">آخر نشاط:</span> {{ detailsUser?.last_active_at?.slice(0, 10) || '—' }}</div>
          <div><span class="text-slate-400">تاريخ التسجيل:</span> {{ detailsUser?.created_at?.slice(0, 10) }}</div>
          <div><span class="text-slate-400">معرف الجهاز:</span> <span class="font-mono text-xs">{{ detailsUser?.device_id || '—' }}</span></div>
        </div>

        <!-- Tabs -->
        <div class="flex gap-1 mb-3 bg-slate-100 p-1 rounded-lg w-fit">
          <button @click="detailTab = 'transactions'" :class="detailTab === 'transactions' ? 'bg-white shadow-sm' : ''"
            class="px-4 py-1.5 rounded-md text-sm">حركات النقاط</button>
          <button @click="detailTab = 'devices'; fetchDevices()" :class="detailTab === 'devices' ? 'bg-white shadow-sm' : ''"
            class="px-4 py-1.5 rounded-md text-sm">الأجهزة</button>
        </div>

        <!-- Transactions -->
        <div v-if="detailTab === 'transactions'">
          <div v-if="transactions.length" class="space-y-2 max-h-60 overflow-y-auto">
            <div v-for="t in transactions" :key="t.id"
              class="flex justify-between items-center p-2 rounded-lg text-sm"
              :class="t.amount > 0 ? 'bg-emerald-50' : 'bg-red-50'">
              <div>
                <span class="font-medium">{{ t.type }}</span>
                <p class="text-xs text-slate-400">{{ t.note || '—' }} · {{ t.created_at?.slice(0, 10) }}</p>
              </div>
              <span class="font-bold" :class="t.amount > 0 ? 'text-emerald-600' : 'text-red-500'">
                {{ t.amount > 0 ? '+' : '' }}{{ t.amount }}
              </span>
            </div>
          </div>
          <p v-else class="text-sm text-slate-400 text-center py-4">لا توجد حركات</p>
        </div>

        <!-- Devices -->
        <div v-if="detailTab === 'devices'">
          <div v-if="devices.length" class="space-y-2">
            <div v-for="d in devices" :key="d.id" class="flex justify-between p-2 rounded-lg bg-slate-50 text-sm">
              <div>
                <span class="font-medium">{{ d.device_name || d.device_id }}</span>
                <p class="text-xs text-slate-400">IP: {{ d.ip_address || '—' }}</p>
              </div>
              <span :class="{
                'text-emerald-600': d.risk_level === 'low',
                'text-amber-600': d.risk_level === 'medium',
                'text-red-600': d.risk_level === 'high',
              }" class="font-medium text-xs">{{ d.risk_level || 'low' }}</span>
            </div>
          </div>
          <p v-else class="text-sm text-slate-400 text-center py-4">لا توجد أجهزة مسجلة</p>
        </div>

        <button @click="showDetails=false" class="mt-4 w-full bg-slate-100 py-2 rounded-lg text-sm text-slate-600">إغلاق</button>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div v-if="showEdit" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="showEdit=false">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
        <h2 class="text-lg font-bold mb-4">تعديل المستخدم</h2>
        <form @submit.prevent="saveEdit">
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium mb-1">الاسم</label>
              <input v-model="editForm.name" class="w-full border rounded-lg px-3 py-2 text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">الحالة</label>
              <select v-model="editForm.status" class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="active">🟢 نشط</option>
                <option value="suspended">🟡 موقوف</option>
                <option value="banned">🔴 محظور</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">رصيد النقاط</label>
              <input v-model.number="editForm.points_balance" type="number" min="0" class="w-full border rounded-lg px-3 py-2 text-sm" />
            </div>
          </div>
          <p v-if="editError" class="text-red-500 text-sm mt-3">{{ editError }}</p>
          <div class="flex gap-2 mt-5">
            <button type="submit" :disabled="saving" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm font-medium flex-1 disabled:opacity-50">
              {{ saving ? 'جاري...' : 'حفظ' }}
            </button>
            <button type="button" @click="showEdit=false" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-lg text-sm flex-1">إلغاء</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add Points Modal (Single) -->
    <div v-if="showAddPoints" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="showAddPoints=false">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
        <h2 class="text-lg font-bold mb-1">إضافة نقاط</h2>
        <p class="text-sm text-slate-500 mb-4">{{ addPointsUser?.name }} ({{ addPointsUser?.phone }}) — الرصيد الحالي: <span class="font-bold text-emerald-600">{{ addPointsUser?.points_balance }}</span></p>
        <form @submit.prevent="saveAddPoints">
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium mb-1">عدد النقاط</label>
              <input v-model.number="addPointsForm.amount" type="number" min="1" required
                class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="أدخل عدد النقاط" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">ملاحظة (اختياري)</label>
              <textarea v-model="addPointsForm.note" rows="2" maxlength="500"
                class="w-full border rounded-lg px-3 py-2 text-sm resize-none" placeholder="ملاحظة"></textarea>
            </div>
          </div>
          <p v-if="addPointsMsg" class="text-sm mt-3" :class="addPointsError ? 'text-red-500' : 'text-emerald-600'">{{ addPointsMsg }}</p>
          <div class="flex gap-2 mt-5">
            <button type="submit" :disabled="savingPoints"
              class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex-1 disabled:opacity-50">
              {{ savingPoints ? 'جاري...' : 'إضافة النقاط' }}
            </button>
            <button type="button" @click="showAddPoints=false" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-lg text-sm flex-1">إلغاء</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Bulk Add Points Modal -->
    <div v-if="showBulkAdd" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="showBulkAdd=false">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
        <h2 class="text-lg font-bold mb-4">إضافة نقاط للكل</h2>
        <p class="text-sm text-slate-500 mb-4">سيتم إضافة النقاط لجميع المستخدمين</p>
        <form @submit.prevent="saveBulkAdd">
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium mb-1">عدد النقاط لكل مستخدم</label>
              <input v-model.number="bulkForm.amount" type="number" min="1" required
                class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="أدخل عدد النقاط" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">ملاحظة (اختياري)</label>
              <textarea v-model="bulkForm.note" rows="2" maxlength="500"
                class="w-full border rounded-lg px-3 py-2 text-sm resize-none"></textarea>
            </div>
          </div>
          <p v-if="bulkMsg" class="text-sm mt-3" :class="bulkError ? 'text-red-500' : 'text-emerald-600'">{{ bulkMsg }}</p>
          <div class="flex gap-2 mt-5">
            <button type="submit" :disabled="savingPoints"
              class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex-1 disabled:opacity-50">
              {{ savingPoints ? 'جاري...' : 'إضافة للكل' }}
            </button>
            <button type="button" @click="showBulkAdd=false" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-lg text-sm flex-1">إلغاء</button>
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
      users: [],
      statCards: [],
      page: 1,
      lastPage: 1,
      perPage: 50,
      perPageOptions: [10, 25, 50, 100],
      total: 0,
      sortField: 'created_at',
      sortDir: 'desc',
      filters: { search: '', status: '', points_min: null, points_max: null, from_date: '', to_date: '' },
      // Details modal
      showDetails: false,
      detailsUser: null,
      detailTab: 'transactions',
      transactions: [],
      devices: [],
      // Edit modal
      showEdit: false,
      editUser: null,
      editForm: { name: '', status: 'active', points_balance: 0 },
      saving: false,
      editError: '',
      // Add points
      showAddPoints: false,
      addPointsUser: null,
      addPointsForm: { amount: null, note: '' },
      addPointsMsg: '',
      addPointsError: false,
      showBulkAdd: false,
      bulkForm: { amount: null, note: '' },
      bulkMsg: '',
      bulkError: false,
      savingPoints: false,
      debounceTimer: null,
    }
  },
  mounted() { this.fetchAll() },
  methods: {
    async fetchAll() {
      const headers = { Authorization: `Bearer ${localStorage.getItem('admin_token')}` }
      const params = { page: this.page, per_page: this.perPage, sort: this.sortField, dir: this.sortDir, ...this.filters }
      Object.keys(params).forEach(k => { if (params[k] === '' || params[k] === null) delete params[k] })
      const [res, ss] = await Promise.all([
        axios.get('/api/admin/v1/users', { headers, params }),
        axios.get('/api/admin/v1/users/stats', { headers }),
      ])
      this.users = res.data.data || []
      this.page = res.data.current_page || 1
      this.lastPage = res.data.last_page || 1
      this.total = res.data.total || 0
      this.buildStatCards(ss.data)
    },
    buildStatCards(ss) {
      this.statCards = [
        { key: 'total', label: '👥 الإجمالي', count: ss.total, color: 'text-violet-600' },
        { key: 'active', label: '🟢 نشط', count: ss.active, color: 'text-emerald-600' },
        { key: 'suspended', label: '🟡 موقوف', count: ss.suspended, color: 'text-amber-500' },
        { key: 'banned', label: '🔴 محظور', count: ss.banned, color: 'text-red-500' },
        { key: 'total_points', label: '⭐ كل النقاط', count: ss.total_points, color: 'text-violet-600' },
        { key: 'avg_points', label: '📊 متوسط النقاط', count: ss.avg_points, color: 'text-slate-600' },
      ]
    },
    statActive(s) {
      if (['total', 'total_points', 'avg_points'].includes(s.key)) return false
      return this.filters.status === s.key
    },
    applyStatFilter(s) {
      if (['total', 'total_points', 'avg_points'].includes(s.key)) return
      this.page = 1
      this.filters.status = this.filters.status === s.key ? '' : s.key
      this.fetchAll()
    },
    debouncedFetch() {
      clearTimeout(this.debounceTimer)
      this.debounceTimer = setTimeout(() => { this.page = 1; this.fetchAll() }, 300)
    },
    resetFilters() {
      this.filters = { search: '', status: '', points_min: null, points_max: null, from_date: '', to_date: '' }
      this.page = 1
      this.fetchAll()
    },
    async openDetails(user) {
      this.detailsUser = user
      this.detailTab = 'transactions'
      this.showDetails = true
      this.transactions = []
      this.devices = []
      try {
        const headers = { Authorization: `Bearer ${localStorage.getItem('admin_token')}` }
        const res = await axios.get(`/api/admin/v1/users/${user.id}`, { headers })
        this.detailsUser = res.data
        this.transactions = res.data.point_transactions || []
        this.devices = res.data.device_fingerprints || []
      } catch (e) {
        console.error(e)
      }
    },
    async fetchDevices() {
      if (this.devices.length || !this.detailsUser) return
      try {
        const headers = { Authorization: `Bearer ${localStorage.getItem('admin_token')}` }
        const res = await axios.get(`/api/admin/v1/users/${this.detailsUser.id}`, { headers })
        this.devices = res.data.device_fingerprints || []
      } catch (e) { console.error(e) }
    },
    openEdit(user) {
      this.editUser = user
      this.editForm = { name: user.name || '', status: user.status, points_balance: user.points_balance }
      this.editError = ''
      this.showEdit = true
    },
    async saveEdit() {
      this.saving = true
      this.editError = ''
      try {
        const headers = { Authorization: `Bearer ${localStorage.getItem('admin_token')}` }
        await axios.put(`/api/admin/v1/users/${this.editUser.id}`, this.editForm, { headers })
        this.showEdit = false
        this.fetchAll()
      } catch (e) {
        this.editError = e.response?.data?.message || 'فشل الحفظ'
      } finally { this.saving = false }
    },
    openAddPoints(user) {
      this.addPointsUser = user
      this.addPointsForm = { amount: null, note: '' }
      this.addPointsMsg = ''
      this.addPointsError = false
      this.showAddPoints = true
    },
    async saveAddPoints() {
      this.savingPoints = true
      this.addPointsMsg = ''
      this.addPointsError = false
      try {
        const headers = { Authorization: `Bearer ${localStorage.getItem('admin_token')}` }
        const res = await axios.post(`/api/admin/v1/users/${this.addPointsUser.id}/add-points`, this.addPointsForm, { headers })
        this.addPointsMsg = res.data.message
        this.addPointsUser.points_balance = res.data.user.points_balance
        setTimeout(() => { this.showAddPoints = false; this.fetchAll() }, 1500)
      } catch (e) {
        this.addPointsMsg = e.response?.data?.message || 'فشل إضافة النقاط'
        this.addPointsError = true
      } finally { this.savingPoints = false }
    },
    openBulkAdd() {
      this.bulkForm = { amount: null, note: '' }
      this.bulkMsg = ''
      this.bulkError = false
      this.showBulkAdd = true
    },
    async saveBulkAdd() {
      this.savingPoints = true
      this.bulkMsg = ''
      this.bulkError = false
      try {
        const headers = { Authorization: `Bearer ${localStorage.getItem('admin_token')}` }
        const res = await axios.post('/api/admin/v1/users/add-points-bulk', this.bulkForm, { headers })
        this.bulkMsg = res.data.message
        setTimeout(() => { this.showBulkAdd = false; this.fetchAll() }, 1500)
      } catch (e) {
        this.bulkMsg = e.response?.data?.message || 'فشل إضافة النقاط'
        this.bulkError = true
      } finally { this.savingPoints = false }
    },
  },
}
</script>