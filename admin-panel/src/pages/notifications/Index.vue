<template>
  <div class="p-6" dir="rtl">
    <h1 class="text-2xl font-bold mb-6 text-slate-800">الإشعارات</h1>

    <!-- Stats -->
    <div class="grid grid-cols-4 gap-4 mb-6">
      <div @click="filterStatus = ''; load()" class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 cursor-pointer hover:shadow-md transition">
        <p class="text-sm text-slate-500">الكل</p>
        <p class="text-2xl font-bold text-slate-800">{{ stats.total }}</p>
      </div>
      <div @click="filterStatus = 'draft'; load()" class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 cursor-pointer hover:shadow-md transition">
        <p class="text-sm text-slate-500">مسودة</p>
        <p class="text-2xl font-bold text-amber-600">{{ stats.draft }}</p>
      </div>
      <div @click="filterStatus = 'sent'; load()" class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 cursor-pointer hover:shadow-md transition">
        <p class="text-sm text-slate-500">مرسلة</p>
        <p class="text-2xl font-bold text-emerald-600">{{ stats.sent }}</p>
      </div>
      <div @click="filterStatus = 'cancelled'; load()" class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 cursor-pointer hover:shadow-md transition">
        <p class="text-sm text-slate-500">ملغاة</p>
        <p class="text-2xl font-bold text-red-600">{{ stats.cancelled }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 mb-6">
      <div class="flex gap-3 flex-wrap items-end">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-xs text-slate-500 mb-1">بحث</label>
          <input v-model="search" @input="debouncedLoad" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="عنوان أو نص الإشعار">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">الحالة</label>
          <select v-model="filterStatus" @change="load" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">الكل</option>
            <option value="draft">مسودة</option>
            <option value="sent">مرسلة</option>
            <option value="cancelled">ملغاة</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">الجمهور</label>
          <select v-model="filterAudience" @change="load" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">الكل</option>
            <option value="all">الكل</option>
            <option value="specific">محدد</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">من تاريخ</label>
          <input type="date" v-model="fromDate" @change="load" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">إلى تاريخ</label>
          <input type="date" v-model="toDate" @change="load" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">عرض</label>
          <select v-model="perPage" @change="load" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>
        <button @click="openModal()" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-violet-700">+ إشعار جديد</button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
      <div v-if="loading" class="text-center py-8 text-slate-400">جاري التحميل...</div>
      <table v-else class="w-full">
        <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
          <tr>
            <th class="text-right px-4 py-3">العنوان</th>
            <th class="text-right px-4 py-3">الجمهور</th>
            <th class="text-right px-4 py-3">الحالة</th>
            <th class="text-right px-4 py-3">التاريخ</th>
            <th class="text-center px-4 py-3">إجراءات</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          <tr v-for="n in notifications" :key="n.id" class="border-t border-slate-100 hover:bg-slate-50">
            <td class="px-4 py-3 font-medium">{{ n.title }}</td>
            <td class="px-4 py-3">
              <span :class="n.audience === 'all' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'" class="px-2 py-0.5 rounded-full text-xs">{{ n.audience === 'all' ? 'الكل' : 'محدد' }}</span>
            </td>
            <td class="px-4 py-3">
              <span :class="statusClass(n.status)" class="px-2 py-0.5 rounded-full text-xs">{{ statusLabel(n.status) }}</span>
            </td>
            <td class="px-4 py-3 text-slate-500">{{ n.created_at }}</td>
            <td class="px-4 py-3 text-center">
              <button @click="openModal(n)" class="text-violet-600 hover:text-violet-800 ml-2">تعديل</button>
              <button @click="confirmDelete(n)" class="text-red-500 hover:text-red-700">حذف</button>
            </td>
          </tr>
          <tr v-if="notifications.length === 0">
            <td colspan="5" class="text-center py-8 text-slate-400">لا توجد إشعارات</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination && pagination.last_page > 1" class="flex justify-between items-center mt-4 text-sm">
      <span class="text-slate-500">صفحة {{ pagination.current_page }} من {{ pagination.last_page }} ({{ pagination.total }})</span>
      <div class="flex gap-2">
        <button :disabled="!pagination.prev_page_url" @click="changePage(pagination.current_page - 1)" class="px-3 py-1 rounded border disabled:opacity-30">السابق</button>
        <button :disabled="!pagination.next_page_url" @click="changePage(pagination.current_page + 1)" class="px-3 py-1 rounded border disabled:opacity-30">التالي</button>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" @click.self="showModal = false">
      <div class="bg-white rounded-2xl p-6 w-full max-w-lg mx-4">
        <h2 class="text-lg font-bold mb-4">{{ editing ? 'تعديل الإشعار' : 'إشعار جديد' }}</h2>
        <form @submit.prevent="save">
          <div class="mb-3">
            <label class="block text-sm text-slate-600 mb-1">العنوان</label>
            <input v-model="form.title" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
          <div class="mb-3">
            <label class="block text-sm text-slate-600 mb-1">النص</label>
            <textarea v-model="form.body" required rows="4" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"></textarea>
          </div>
          <div class="mb-3">
            <label class="block text-sm text-slate-600 mb-1">صورة (رابط)</label>
            <input v-model="form.image" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="اختياري">
          </div>
          <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
              <label class="block text-sm text-slate-600 mb-1">الجمهور</label>
              <select v-model="form.audience" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <option value="all">الكل</option>
                <option value="specific">محدد</option>
              </select>
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">الحالة</label>
              <select v-model="form.status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <option value="draft">مسودة</option>
                <option value="sent">مرسلة</option>
                <option value="cancelled">ملغاة</option>
              </select>
            </div>
          </div>
          <div v-if="error" class="mb-3 text-sm text-red-600">{{ error }}</div>
          <div class="flex gap-2 justify-end">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-slate-600 border rounded-lg">إلغاء</button>
            <button type="submit" class="px-4 py-2 text-sm text-white bg-violet-600 rounded-lg hover:bg-violet-700">{{ editing ? 'حفظ' : 'إضافة' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirm -->
    <div v-if="showDelete" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm mx-4 text-center">
        <p class="mb-4">حذف "{{ deleteItem?.title }}"؟</p>
        <div class="flex gap-2 justify-center">
          <button @click="showDelete = false" class="px-4 py-2 text-sm border rounded-lg">إلغاء</button>
          <button @click="doDelete" class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg">حذف</button>
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
      notifications: [],
      stats: { total: 0, draft: 0, sent: 0, cancelled: 0 },
      loading: false,
      search: '',
      filterStatus: '',
      filterAudience: '',
      fromDate: '',
      toDate: '',
      perPage: 50,
      pagination: null,
      showModal: false,
      showDelete: false,
      editing: false,
      deleteItem: null,
      form: { title: '', body: '', image: '', audience: 'all', status: 'draft' },
      error: '',
      debounceTimer: null,
    }
  },
  mounted() { this.load() },
  methods: {
    async load() {
      this.loading = true
      try {
        const params = { per_page: this.perPage }
        if (this.search) params.search = this.search
        if (this.filterStatus) params.status = this.filterStatus
        if (this.filterAudience) params.audience = this.filterAudience
        if (this.fromDate) params.from_date = this.fromDate
        if (this.toDate) params.to_date = this.toDate
        if (this.pagination?.current_page) params.page = this.pagination.current_page

        const [res, statsRes] = await Promise.all([
          axios.get('/api/admin/v1/notifications', { params }),
          axios.get('/api/admin/v1/notifications/stats'),
        ])
        this.notifications = res.data.data
        this.pagination = { current_page: res.data.current_page, last_page: res.data.last_page, total: res.data.total, prev_page_url: res.data.prev_page_url, next_page_url: res.data.next_page_url }
        this.stats = statsRes.data
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    debouncedLoad() {
      clearTimeout(this.debounceTimer)
      this.debounceTimer = setTimeout(() => { this.pagination = null; this.load() }, 400)
    },
    changePage(page) { this.pagination.current_page = page; this.load() },
    openModal(item) {
      this.editing = !!item
      this.error = ''
      this.form = item ? { ...item, target_user_ids: item.target_user_ids || [] } : { title: '', body: '', image: '', audience: 'all', status: 'draft' }
      this.showModal = true
    },
    async save() {
      this.error = ''
      try {
        const payload = { ...this.form }
        if (this.editing) {
          const res = await axios.put(`/api/admin/v1/notifications/${this.form.id}`, payload)
          const idx = this.notifications.findIndex(n => n.id === this.form.id)
          if (idx !== -1) this.notifications[idx] = res.data
        } else {
          const res = await axios.post('/api/admin/v1/notifications', payload)
          this.notifications.unshift(res.data)
        }
        this.showModal = false
        this.load()
      } catch (e) {
        this.error = e.response?.data?.message || 'حدث خطأ'
      }
    },
    confirmDelete(item) {
      this.deleteItem = item
      this.showDelete = true
    },
    async doDelete() {
      try {
        await axios.delete(`/api/admin/v1/notifications/${this.deleteItem.id}`)
        this.showDelete = false
        this.load()
      } catch (e) {
        console.error(e)
      }
    },
    statusClass(s) {
      if (s === 'sent') return 'bg-emerald-100 text-emerald-700'
      if (s === 'draft') return 'bg-amber-100 text-amber-700'
      return 'bg-red-100 text-red-700'
    },
    statusLabel(s) {
      if (s === 'sent') return 'مرسلة'
      if (s === 'draft') return 'مسودة'
      return 'ملغاة'
    },
  },
}
</script>
