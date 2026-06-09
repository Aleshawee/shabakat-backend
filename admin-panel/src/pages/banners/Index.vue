<template>
  <div class="p-6" dir="rtl">
    <h1 class="text-2xl font-bold mb-6 text-slate-800">البانرات</h1>

    <!-- Stats -->
    <div class="grid grid-cols-4 gap-4 mb-6">
      <div @click="filterActive = ''; load()" class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 cursor-pointer hover:shadow-md transition">
        <p class="text-sm text-slate-500">الكل</p>
        <p class="text-2xl font-bold text-slate-800">{{ stats.total }}</p>
      </div>
      <div @click="filterActive = '1'; load()" class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 cursor-pointer hover:shadow-md transition">
        <p class="text-sm text-slate-500">نشط</p>
        <p class="text-2xl font-bold text-emerald-600">{{ stats.active }}</p>
      </div>
      <div @click="filterActive = '0'; load()" class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 cursor-pointer hover:shadow-md transition">
        <p class="text-sm text-slate-500">غير نشط</p>
        <p class="text-2xl font-bold text-slate-600">{{ stats.inactive }}</p>
      </div>
      <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200">
        <p class="text-sm text-slate-500">منتهية</p>
        <p class="text-2xl font-bold text-red-600">{{ stats.expired }}</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 mb-6">
      <div class="flex gap-3 flex-wrap items-end">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-xs text-slate-500 mb-1">بحث</label>
          <input v-model="search" @input="debouncedLoad" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="عنوان البانر">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">الحالة</label>
          <select v-model="filterActive" @change="load" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">الكل</option>
            <option value="1">نشط</option>
            <option value="0">غير نشط</option>
          </select>
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
        <button @click="openModal()" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-violet-700">+ بانر جديد</button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
      <div v-if="loading" class="text-center py-8 text-slate-400">جاري التحميل...</div>
      <table v-else class="w-full">
        <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
          <tr>
            <th class="text-right px-4 py-3">العنوان</th>
            <th class="text-right px-4 py-3">الحالة</th>
            <th class="text-right px-4 py-3">الترتيب</th>
            <th class="text-right px-4 py-3">ينتهي</th>
            <th class="text-center px-4 py-3">إجراءات</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          <tr v-for="b in banners" :key="b.id" class="border-t border-slate-100 hover:bg-slate-50">
            <td class="px-4 py-3 font-medium">{{ b.title }}</td>
            <td class="px-4 py-3">
              <span :class="b.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded-full text-xs">{{ b.is_active ? 'نشط' : 'غير نشط' }}</span>
            </td>
            <td class="px-4 py-3 text-slate-500">{{ b.sort_order }}</td>
            <td class="px-4 py-3 text-slate-500">{{ b.expires_at || '—' }}</td>
            <td class="px-4 py-3 text-center">
              <button @click="openModal(b)" class="text-violet-600 hover:text-violet-800 ml-2">تعديل</button>
              <button @click="confirmDelete(b)" class="text-red-500 hover:text-red-700">حذف</button>
            </td>
          </tr>
          <tr v-if="banners.length === 0">
            <td colspan="5" class="text-center py-8 text-slate-400">لا توجد بانرات</td>
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
        <h2 class="text-lg font-bold mb-4">{{ editing ? 'تعديل البانر' : 'بانر جديد' }}</h2>
        <form @submit.prevent="save">
          <div class="mb-3">
            <label class="block text-sm text-slate-600 mb-1">العنوان</label>
            <input v-model="form.title" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
          <div class="mb-3">
            <label class="block text-sm text-slate-600 mb-1">صورة (رابط)</label>
            <input v-model="form.image" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="https://...">
          </div>
          <div class="mb-3">
            <label class="block text-sm text-slate-600 mb-1">رابط</label>
            <input v-model="form.link" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="اختياري">
          </div>
          <div class="grid grid-cols-3 gap-3 mb-3">
            <div>
              <label class="block text-sm text-slate-600 mb-1">نشط</label>
              <select v-model="form.is_active" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                <option :value="true">نعم</option>
                <option :value="false">لا</option>
              </select>
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">الترتيب</label>
              <input type="number" v-model.number="form.sort_order" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">ينتهي</label>
              <input type="date" v-model="form.expires_at" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
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
      banners: [],
      stats: { total: 0, active: 0, inactive: 0, expired: 0 },
      loading: false,
      search: '',
      filterActive: '',
      perPage: 50,
      pagination: null,
      showModal: false,
      showDelete: false,
      editing: false,
      deleteItem: null,
      form: { title: '', image: '', link: '', is_active: true, sort_order: 0, expires_at: '' },
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
        if (this.filterActive !== '') params.is_active = this.filterActive
        if (this.pagination?.current_page) params.page = this.pagination.current_page

        const [res, statsRes] = await Promise.all([
          axios.get('/api/admin/v1/banners', { params }),
          axios.get('/api/admin/v1/banners/stats'),
        ])
        this.banners = res.data.data
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
      this.form = item ? { ...item, is_active: Boolean(Number(item.is_active)) } : { title: '', image: '', link: '', is_active: true, sort_order: 0, expires_at: '' }
      this.showModal = true
    },
    async save() {
      this.error = ''
      try {
        const payload = { ...this.form }
        if (this.editing) {
          const res = await axios.put(`/api/admin/v1/banners/${this.form.id}`, payload)
          const idx = this.banners.findIndex(b => b.id === this.form.id)
          if (idx !== -1) this.banners[idx] = res.data
        } else {
          const res = await axios.post('/api/admin/v1/banners', payload)
          this.banners.unshift(res.data)
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
        await axios.delete(`/api/admin/v1/banners/${this.deleteItem.id}`)
        this.showDelete = false
        this.load()
      } catch (e) {
        console.error(e)
      }
    },
  },
}
</script>
