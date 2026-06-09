<template>
  <div class="max-w-6xl mx-auto px-4 py-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-slate-800">إعدادات الميزة: أبشر (سلفة)</h1>
    </div>

    <div v-if="loading" class="text-center py-12 text-slate-500">جاري التحميل...</div>

    <template v-else>
      <!-- Hero -->
      <div class="rounded-2xl p-6 text-white relative overflow-hidden shadow-xl"
        style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%);">
        <div class="absolute inset-0 opacity-10 pointer-events-none"
          style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.3) 1px, transparent 1px), radial-gradient(circle at 80% 70%, rgba(255,255,255,0.15) 1px, transparent 1px); background-size: 30px 30px, 20px 20px;">
        </div>
        <div class="relative text-center">
          <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-white/20 flex items-center justify-center text-3xl backdrop-blur-sm shadow-inner">💰</div>
          <h2 class="text-xl font-bold">إعدادات سلفة أبشر</h2>
          <p class="text-sm text-white/80 mt-2">تحديد شروط الأهلية لكل فئة + إعدادات عامة</p>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 text-center">
          <p class="text-2xl font-bold text-indigo-700">{{ stats.total_borrowers }}</p>
          <p class="text-[11px] text-slate-500 mt-1">مستفيد</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 text-center">
          <p class="text-2xl font-bold text-emerald-700">{{ stats.total_loans }}</p>
          <p class="text-[11px] text-slate-500 mt-1">إجمالي السلف</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 text-center">
          <p class="text-2xl font-bold text-amber-700">{{ stats.total_amount.toLocaleString() }}</p>
          <p class="text-[11px] text-slate-500 mt-1">نقاط مستلفة</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 text-center">
          <p class="text-2xl font-bold text-blue-700">{{ stats.active_loans }}</p>
          <p class="text-[11px] text-slate-500 mt-1">نشطة</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 text-center">
          <p class="text-2xl font-bold text-emerald-700">{{ stats.repaid_loans }}</p>
          <p class="text-[11px] text-slate-500 mt-1">مسددة</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 text-center">
          <p class="text-2xl font-bold text-red-600">{{ stats.defaulted_loans }}</p>
          <p class="text-[11px] text-slate-500 mt-1">متعثرة</p>
        </div>
      </div>

      <!-- الإعدادات العامة -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
          <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-lg">⚙️</div>
          <div>
            <h3 class="font-semibold text-slate-800">الإعدادات العامة</h3>
            <p class="text-xs text-slate-500">تفعيل الخدمة وتحديد النطاق الزمني</p>
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-5 mb-5">
          <div class="flex items-center justify-between bg-slate-50 rounded-xl p-4">
            <div>
              <h4 class="font-semibold text-slate-800 text-sm">تفعيل الميزة</h4>
              <p class="text-xs text-slate-500">السماح للمستخدمين بطلب سلفة</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="form.is_enabled" class="sr-only peer">
              <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-0.5 after:start-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
            </label>
          </div>
          <div class="flex items-center justify-between bg-slate-50 rounded-xl p-4">
            <div>
              <h4 class="font-semibold text-slate-800 text-sm">اشتراط النشاط الأخير</h4>
              <p class="text-xs text-slate-500">السماح فقط للمستخدمين النشطين خلال Y يوم</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="form.require_recent_activity" class="sr-only peer">
              <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:bg-indigo-600 after:content-[''] after:absolute after:top-0.5 after:start-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
            </label>
          </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4 mb-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">تاريخ البداية</label>
            <input type="datetime-local" v-model="form.starts_at" class="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 transition" />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">تاريخ النهاية</label>
            <input type="datetime-local" v-model="form.ends_at" class="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 transition" />
          </div>
          <div v-if="form.require_recent_activity">
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">عدد الأيام (Y)</label>
            <input type="number" v-model.number="form.activity_days" min="1" class="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-indigo-400 transition" placeholder="مثال: 7" />
          </div>
        </div>
      </div>

      <!-- إعدادات الفئات -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
          <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-lg">📂</div>
          <div>
            <h3 class="font-semibold text-slate-800">إعدادات الفئات (السلفة)</h3>
            <p class="text-xs text-slate-500">تحديد شروط الأهلية لكل فئة بشكل منفصل</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-right border-b border-slate-200">
                <th class="pb-3 font-semibold text-slate-600 px-2">الفئة</th>
                <th class="pb-3 font-semibold text-slate-600 px-2">السعر</th>
                <th class="pb-3 font-semibold text-slate-600 px-2">النقاط</th>
                <th class="pb-3 font-semibold text-slate-600 px-2">المكافأة</th>
                <th class="pb-3 font-semibold text-slate-600 px-2">قابلية السلفة</th>
                <th class="pb-3 font-semibold text-slate-600 px-2">أقصى سلفة</th>
                <th class="pb-3 font-semibold text-slate-600 px-2">الحد الأدنى</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="cat in categories" :key="cat.id" class="border-b border-slate-100 hover:bg-slate-50 transition">
                <td class="py-3 px-2 font-medium text-slate-800">{{ cat.name }}</td>
                <td class="py-3 px-2 text-slate-600">{{ cat.price }} ريال</td>
                <td class="py-3 px-2 text-slate-600">{{ cat.points }} نقطة</td>
                <td class="py-3 px-2 text-slate-600">{{ cat.reward?.name || '—' }} ({{ cat.reward?.points_cost || '—' }})</td>
                <td class="py-3 px-2">
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="cat.borrow_setting.is_borrowable" class="sr-only peer">
                    <div class="w-10 h-5 bg-slate-300 rounded-full peer peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-0.5 after:start-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full"></div>
                  </label>
                </td>
                <td class="py-3 px-2">
                  <input type="number" v-model.number="cat.borrow_setting.max_borrow_amount" min="0" class="w-20 border border-slate-200 rounded-lg px-2 py-1.5 text-xs outline-none focus:border-emerald-400 transition" />
                </td>
                <td class="py-3 px-2">
                  <input type="number" v-model.number="cat.borrow_setting.min_points_threshold" min="0" class="w-20 border border-slate-200 rounded-lg px-2 py-1.5 text-xs outline-none focus:border-emerald-400 transition" />
                </td>
              </tr>
              <tr v-if="categories.length === 0">
                <td colspan="7" class="py-6 text-center text-slate-400">لا توجد فئات متاحة</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Save -->
      <div class="flex items-center justify-between bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <p v-if="saved" class="text-sm text-emerald-600 font-medium">✅ تم الحفظ بنجاح</p>
        <p v-else class="text-sm text-slate-400">قم بتعديل الإعدادات ثم احفظ</p>
        <button @click="save" :disabled="saving" class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition shadow-md shadow-indigo-200">
          {{ saving ? 'جاري الحفظ...' : '💾 حفظ الإعدادات' }}
        </button>
      </div>
    </template>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  data() {
    return {
      loading: true, saving: false, saved: false,
      categories: [],
      stats: {
        total_borrowers: 0, total_loans: 0, total_amount: 0, total_debt: 0,
        active_loans: 0, repaid_loans: 0, defaulted_loans: 0,
      },
      form: {
        is_enabled: false, starts_at: '', ends_at: '',
        require_recent_activity: false, activity_days: 0,
      },
    }
  },
  async created() { await this.load() },
  methods: {
    async load() {
      try {
        const { data } = await axios.get('/api/admin/v1/absher/settings')
        const s = data.setting
        this.form.is_enabled = s.is_enabled
        this.form.starts_at = s.starts_at ? s.starts_at.slice(0, 16) : ''
        this.form.ends_at = s.ends_at ? s.ends_at.slice(0, 16) : ''
        this.form.require_recent_activity = s.require_recent_activity ?? false
        this.form.activity_days = s.activity_days ?? 0
        this.categories = data.categories || []
        if (data.stats) this.stats = data.stats
      } catch (e) { console.error(e) }
      finally { this.loading = false }
    },
    async save() {
      this.saving = true; this.saved = false
      try {
        const payload = {
          is_enabled: this.form.is_enabled,
          starts_at: this.form.starts_at || null,
          ends_at: this.form.ends_at || null,
          require_recent_activity: this.form.require_recent_activity,
          activity_days: this.form.activity_days || 0,
          category_settings: this.categories.map(c => ({
            category_id: c.id,
            is_borrowable: c.borrow_setting.is_borrowable,
            max_borrow_amount: c.borrow_setting.max_borrow_amount || 0,
            min_points_threshold: c.borrow_setting.min_points_threshold || 0,
          })),
        }
        await axios.put('/api/admin/v1/absher/settings', payload)
        this.saved = true
        setTimeout(() => { this.saved = false }, 3000)
      } catch (e) {
        console.error(e)
        alert('فشل الحفظ')
      } finally { this.saving = false }
    },
  },
}
</script>
