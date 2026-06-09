<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">إدارة المكافآت</h1>
      <button @click="openModal()" class="bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
        + إضافة مكافأة جديدة
      </button>
    </div>

    <!-- شريط الإحصائيات -->
    <div class="grid grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow-sm border p-4">
        <p class="text-sm text-slate-500">إجمالي المكافآت</p>
        <p class="text-2xl font-bold text-slate-800">{{ rewards.length }}</p>
      </div>
      <div class="bg-emerald-50 rounded-xl shadow-sm border border-emerald-200 p-4">
        <p class="text-sm text-emerald-600">نشطة</p>
        <p class="text-2xl font-bold text-emerald-700">{{ activeCount }}</p>
      </div>
      <div class="bg-slate-50 rounded-xl shadow-sm border border-slate-200 p-4">
        <p class="text-sm text-slate-500">مخفية</p>
        <p class="text-2xl font-bold text-slate-500">{{ inactiveCount }}</p>
      </div>
    </div>

    <!-- شبكة البطاقات -->
    <div v-if="rewards.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
      <div v-for="reward in rewards" :key="reward.id"
        class="bg-white rounded-xl shadow-sm border hover:shadow-md transition-shadow p-5 flex flex-col">
        <div class="flex items-start justify-between mb-3">
          <div class="flex-1 min-w-0">
            <h3 class="font-bold text-slate-800 truncate">{{ reward.name }}</h3>
            <p class="text-xs text-slate-400 mt-0.5">
              {{ reward.category?.name || 'بدون فئة' }}
            </p>
          </div>
          <span :class="reward.is_active
            ? 'bg-emerald-100 text-emerald-700'
            : 'bg-slate-100 text-slate-500'"
            class="px-2 py-0.5 rounded-full text-xs font-medium shrink-0 mr-2">
            {{ reward.is_active ? 'ظاهر' : 'مخفي' }}
          </span>
        </div>

        <p v-if="reward.description" class="text-sm text-slate-500 line-clamp-2 mb-3">{{ reward.description }}</p>

        <div class="flex gap-3 text-sm mt-auto pt-3 border-t">
          <div>
            <span class="text-slate-400 text-xs">التكلفة</span>
            <p class="font-semibold text-violet-700">{{ reward.points_cost }} نقطة</p>
          </div>
          <div v-if="reward.card_value">
            <span class="text-slate-400 text-xs">قيمة الكرت</span>
            <p class="font-semibold text-emerald-700">{{ reward.card_value }} ريال</p>
          </div>
        </div>

        <div class="flex gap-2 mt-3 pt-3 border-t">
          <button @click="openModal(reward)"
            class="flex-1 text-violet-600 border border-violet-200 hover:bg-violet-50 rounded-lg py-1.5 text-sm font-medium transition-colors">
            تعديل
          </button>
          <button @click="toggleActive(reward)"
            :class="reward.is_active
              ? 'text-amber-600 border border-amber-200 hover:bg-amber-50'
              : 'text-emerald-600 border border-emerald-200 hover:bg-emerald-50'"
            class="px-3 rounded-lg py-1.5 text-sm font-medium transition-colors">
            {{ reward.is_active ? 'إخفاء' : 'إظهار' }}
          </button>
          <button @click="destroy(reward)"
            class="text-red-500 border border-red-200 hover:bg-red-50 rounded-lg px-3 py-1.5 text-sm font-medium transition-colors">
            حذف
          </button>
        </div>
      </div>
    </div>

    <p v-else-if="!error" class="bg-white rounded-xl shadow-sm border p-8 text-center text-slate-400">
      لا توجد مكافآت بعد
    </p>

    <p v-if="error && !showModal" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm mb-4">{{ error }}</p>

    <!-- Modal إضافة/تعديل -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" @click.self="showModal=false">
      <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl">
        <h2 class="text-lg font-bold mb-4">{{ editing ? 'تعديل المكافأة' : 'إضافة مكافأة جديدة' }}</h2>
        <form @submit.prevent="save">
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium mb-1">الفئة</label>
              <select v-model="form.category_id" required
                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none bg-white">
                <option value="" disabled>اختر الفئة</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">اسم المكافأة</label>
              <input v-model="form.name" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">الوصف</label>
              <textarea v-model="form.description" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium mb-1">نقاط التكلفة</label>
                <input v-model.number="form.points_cost" type="number" required min="1" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">قيمة الكرت (ريال)</label>
                <input v-model.number="form.card_value" type="number" min="0" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">رابط الصورة</label>
              <input v-model="form.image" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 outline-none" />
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
      rewards: [],
      categories: [],
      showModal: false,
      editing: null,
      error: '',
      form: { category_id: '', name: '', description: '', points_cost: 100, card_value: null, image: '', is_active: true },
    }
  },
  computed: {
    activeCount() {
      return this.rewards.filter(r => r.is_active).length
    },
    inactiveCount() {
      return this.rewards.filter(r => !r.is_active).length
    },
  },
  mounted() { this.fetch(); this.fetchCategories() },
  methods: {
    async fetchCategories() {
      try {
        const res = await axios.get('/api/admin/v1/categories', {
          headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` },
        })
        this.categories = res.data
      } catch (e) {
        // غير حرجي
      }
    },
    async fetch() {
      try {
        const res = await axios.get('/api/admin/v1/rewards', {
          headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` },
        })
        this.rewards = res.data
      } catch (e) {
        this.error = 'فشل تحميل المكافآت'
      }
    },
    openModal(reward) {
      this.editing = reward || null
      this.error = ''
      this.form = reward
        ? {
            category_id: reward.category_id || '',
            name: reward.name,
            description: reward.description,
            points_cost: reward.points_cost,
            card_value: reward.card_value,
            image: reward.image,
            is_active: reward.is_active,
          }
        : { category_id: '', name: '', description: '', points_cost: 100, card_value: null, image: '', is_active: true }
      this.showModal = true
    },
    async save() {
      this.error = ''
      const config = { headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` } }
      try {
        if (this.editing) {
          console.debug('Saving reward:', this.editing.id, this.form)
          const res = await axios.put(`/api/admin/v1/rewards/${this.editing.id}`, this.form, config)
          console.debug('Save response:', res)
        } else {
          await axios.post('/api/admin/v1/rewards', this.form, config)
        }
        this.showModal = false
        this.fetch()
      } catch (e) {
        this.error = e.response?.data?.message || e.response?.data?.errors?.[Object.keys(e.response?.data?.errors || {})[0]]?.[0] || 'فشل العملية'
      }
    },
    async toggleActive(reward) {
      this.error = ''
      const config = { headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` } }
      try {
        await axios.put(`/api/admin/v1/rewards/${reward.id}`, { is_active: !reward.is_active }, config)
        this.fetch()
      } catch (e) {
        this.error = e.response?.data?.message || 'فشل التحديث'
      }
    },
    async destroy(reward) {
      if (!confirm('تأكيد حذف المكافأة؟')) return
      this.error = ''
      try {
        await axios.delete(`/api/admin/v1/rewards/${reward.id}`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` },
        })
        this.fetch()
      } catch (e) {
        this.error = e.response?.data?.message || 'فشل الحذف'
      }
    },
  },
}
</script>
