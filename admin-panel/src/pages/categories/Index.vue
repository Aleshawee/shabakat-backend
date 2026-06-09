<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">إدارة الفئات والنقاط</h1>
      <button @click="openModal()" class="bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
        + إضافة فئة جديدة
      </button>
    </div>

    <!-- إحصائيات الكروت النشطة لكل فئة -->
    <div v-if="stats.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
      <div v-for="s in stats" :key="s.id"
        class="bg-white rounded-xl shadow-sm border p-4 flex items-center justify-between hover:shadow-md transition-shadow">
        <div>
          <p class="text-sm text-slate-500">بطاقات نشطة</p>
          <p class="text-lg font-bold text-slate-800">{{ s.name }}</p>
        </div>
        <div class="bg-violet-100 text-violet-700 w-12 h-12 rounded-full flex items-center justify-center text-xl font-bold">
          {{ s.network_cards_count }}
        </div>
      </div>
    </div>

    <!-- جدول الفئات -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b">
          <tr>
            <th class="text-right p-3 font-medium text-slate-600">الاسم</th>
            <th class="text-right p-3 font-medium text-slate-600">السعر</th>
            <th class="text-right p-3 font-medium text-slate-600">النقاط</th>
            <th class="text-right p-3 font-medium text-slate-600">بطاقات نشطة</th>
            <th class="text-right p-3 font-medium text-slate-600">الحالة</th>
            <th class="text-center p-3 font-medium text-slate-600">إجراءات</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="cat in categories" :key="cat.id" class="hover:bg-slate-50">
            <td class="p-3 font-medium">{{ cat.name }}</td>
            <td class="p-3">{{ cat.price }} ريال</td>
            <td class="p-3">{{ cat.points }} نقطة</td>
            <td class="p-3">
              <span class="bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full text-xs font-medium">
                {{ cat.network_cards_count ?? 0 }}
              </span>
            </td>
            <td class="p-3">
              <span :class="cat.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                class="px-2 py-0.5 rounded-full text-xs font-medium">
                {{ cat.is_active ? 'ظاهر' : 'مخفي' }}
              </span>
            </td>
            <td class="p-3 text-center">
              <button @click="openModal(cat)" class="text-violet-600 hover:text-violet-800 mx-1">تعديل</button>
              <button @click="deleteCat(cat)" class="text-red-500 hover:text-red-700 mx-1">حذف</button>
            </td>
          </tr>
          <tr v-if="!categories.length">
            <td colspan="6" class="p-8 text-center text-slate-400">لا توجد فئات بعد</td>
          </tr>
        </tbody>
      </table>
    </div>

    <p v-if="error && !showModal" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm mb-4">{{ error }}</p>

    <!-- Modal إضافة/تعديل -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="showModal=false">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
        <h2 class="text-lg font-bold mb-4">{{ editing ? 'تعديل الفئة' : 'إضافة فئة جديدة' }}</h2>
        <form @submit.prevent="save">
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium mb-1">اسم الفئة</label>
              <input v-model="form.name" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium mb-1">السعر (ريال)</label>
                <input v-model.number="form.price" type="number" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">النقاط</label>
                <input v-model.number="form.points" type="number" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none" />
              </div>
            </div>
            <div class="flex items-center gap-2">
              <input v-model="form.is_active" type="checkbox" id="active" class="rounded" />
              <label for="active" class="text-sm">ظاهرة للعملاء</label>
            </div>
          </div>
          <p v-if="error" class="text-red-500 text-sm text-center mt-3">{{ error }}</p>
          <div class="flex gap-2 mt-5">
            <button type="submit" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm font-medium flex-1">
              {{ editing ? 'حفظ التعديلات' : 'إضافة' }}
            </button>
            <button type="button" @click="showModal=false" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-lg text-sm flex-1">إلغاء</button>
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
      categories: [],
      stats: [],
      showModal: false,
      editing: null,
      error: '',
      form: { name: '', price: 0, points: 0, is_active: true },
    }
  },
  mounted() { this.fetch(); this.fetchStats() },
  methods: {
    async fetchStats() {
      try {
        const res = await axios.get('/api/admin/v1/categories/stats', {
          headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` },
        })
        this.stats = res.data
      } catch (e) {
        // غير حرجي
      }
    },
    async fetch() {
      try {
        const res = await axios.get('/api/admin/v1/categories', {
          headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` },
        })
        this.categories = res.data
      } catch (e) {
        this.error = 'فشل تحميل الفئات'
      }
    },
    openModal(cat) {
      this.editing = cat || null
      this.error = ''
      this.form = cat
        ? { name: cat.name, price: cat.price, points: cat.points, is_active: cat.is_active }
        : { name: '', price: 0, points: 0, is_active: true }
      this.showModal = true
    },
    async save() {
      this.error = ''
      const config = { headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` } }
      try {
        if (this.editing) {
          console.debug('Saving category:', this.editing.id, this.form)
          const res = await axios.put(`/api/admin/v1/categories/${this.editing.id}`, this.form, config)
          console.debug('Save response:', res)
        } else {
          await axios.post('/api/admin/v1/categories', this.form, config)
        }
        this.showModal = false
        this.fetch()
        this.fetchStats()
      } catch (e) {
        this.error = e.response?.data?.message || e.response?.data?.errors?.[Object.keys(e.response?.data?.errors || {})[0]]?.[0] || 'فشل العملية'
      }
    },
    async deleteCat(cat) {
      if (!confirm('تأكيد حذف الفئة؟')) return
      this.error = ''
      try {
        console.debug('Deleting category:', cat.id)
        const res = await axios.delete(`/api/admin/v1/categories/${cat.id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` },
        })
        console.debug('Delete response:', res.data)
        this.categories = this.categories.filter(c => c.id !== cat.id)
        this.fetch()
        this.fetchStats()
      } catch (e) {
        console.error('Delete error:', e)
        this.error = e.response?.data?.message || 'فشل الحذف'
      }
    },
  },
}
</script>
