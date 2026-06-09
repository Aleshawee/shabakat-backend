<template>
  <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-slate-800">🔒 قيود المستخدمين</h1>
    </div>

    <div v-if="loading" class="text-center py-12 text-slate-500">جاري التحميل...</div>

    <template v-else>
      <!-- تصفير النقاط -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
          <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center text-white text-lg">🔄</div>
          <div>
            <h3 class="font-semibold text-slate-800">تصفير النقاط</h3>
            <p class="text-xs text-slate-500">إعادة أرصدة المستخدمين إلى صفر</p>
          </div>
        </div>

        <!-- Auto reset toggle -->
        <div class="flex items-center justify-between bg-slate-50 rounded-xl p-4 mb-4">
          <div>
            <h4 class="font-semibold text-slate-800 text-sm">التصفير التلقائي أول كل شهر</h4>
            <p class="text-xs text-slate-500">عند التفعيل، سيتم تصفير أرصدة جميع المستخدمين تلقائياً في اليوم الأول من كل شهر</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="form.auto_reset_enabled" class="sr-only peer">
            <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:bg-rose-500 after:content-[''] after:absolute after:top-0.5 after:start-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
          </label>
        </div>

        <!-- Reset type -->
        <div v-if="form.auto_reset_enabled" class="bg-slate-50 rounded-xl p-4 mb-4">
          <label class="block font-semibold text-slate-800 text-sm mb-3">نوع التصفير</label>
          <div class="space-y-3">
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="radio" v-model="form.reset_type" value="zero" class="text-rose-500">
              <div>
                <span class="text-sm font-medium text-slate-700">تصفير كلي</span>
                <p class="text-xs text-slate-400">إعادة رصيد جميع المستخدمين إلى صفر</p>
              </div>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="radio" v-model="form.reset_type" value="percentage" class="text-rose-500">
              <div>
                <span class="text-sm font-medium text-slate-700">خصم نسبة مئوية</span>
                <p class="text-xs text-slate-400">خصم نسبة مئوية محددة من رصيد كل مستخدم</p>
              </div>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
              <input type="radio" v-model="form.reset_type" value="fixed" class="text-rose-500">
              <div>
                <span class="text-sm font-medium text-slate-700">خصم رقم ثابت</span>
                <p class="text-xs text-slate-400">خصم عدد ثابت من النقاط من رصيد كل مستخدم</p>
              </div>
            </label>
          </div>
          <div v-if="form.reset_type === 'percentage'" class="mt-3">
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">نسبة الخصم (%)</label>
            <input type="number" v-model.number="form.reset_value" min="1" max="99" class="w-32 border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-rose-400 transition" placeholder="مثال: 50" />
            <p class="text-xs text-slate-400 mt-1">مثال: 50 = خصم 50% من رصيد كل مستخدم</p>
          </div>
          <div v-if="form.reset_type === 'fixed'" class="mt-3">
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">عدد النقاط المراد خصمها</label>
            <input type="number" v-model.number="form.reset_value" min="1" class="w-32 border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-rose-400 transition" placeholder="مثال: 1000" />
            <p class="text-xs text-slate-400 mt-1">مثال: 1000 = خصم 1000 نقطة من رصيد كل مستخدم (لا يقل عن صفر)</p>
          </div>
        </div>

        <!-- Keep debtors -->
        <div class="flex items-center justify-between bg-slate-50 rounded-xl p-4 mb-4">
          <div>
            <h4 class="font-semibold text-slate-800 text-sm">إبقاء المديونين</h4>
            <p class="text-xs text-slate-500">عند التصفير (يدوي أو تلقائي)، لا تمسح رصيد المستخدمين الذين رصيدهم سالب</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="form.reset_keep_debtors" class="sr-only peer">
            <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-0.5 after:start-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
          </label>
        </div>

        <!-- Notification toggle -->
        <div class="flex items-center justify-between bg-slate-50 rounded-xl p-4 mb-4">
          <div>
            <h4 class="font-semibold text-slate-800 text-sm">تشغيل/إيقاف الإشعارات</h4>
            <p class="text-xs text-slate-500">عند التفعيل، تظهر رسالة تحذيرية للمستخدمين بعدد الأيام المتبقية لتصفير النقاط</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="form.reset_notification_enabled" class="sr-only peer">
            <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:bg-amber-500 after:content-[''] after:absolute after:top-0.5 after:start-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
          </label>
        </div>

        <!-- Notification message -->
        <div v-if="form.reset_notification_enabled" class="mb-4">
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">نص الرسالة</label>
          <textarea v-model="form.reset_notification_message" rows="2" class="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-400 transition" placeholder="باقي {days} أيام لتصفير النقاط"></textarea>
          <p class="text-xs text-slate-400 mt-1.5">استخدم <code class="bg-slate-100 px-1 rounded text-rose-500">{days}</code> لعدد الأيام المتبقية. إن تركت الحقل فارغاً، سيتم استخدام الرسالة الافتراضية: "باقي {days} أيام لتصفير النقاط"</p>
        </div>

        <!-- Last reset date -->
        <div v-if="form.last_reset_date" class="text-xs text-slate-400 mb-4 bg-slate-50 rounded-xl px-4 py-2 inline-block">
          🕐 آخر تصفير شامل: {{ form.last_reset_date }}
        </div>

        <!-- Manual reset buttons -->
        <div class="flex flex-wrap gap-3">
          <button @click="confirmResetUser" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            تصفير مستخدم
          </button>
          <button @click="confirmResetAll" class="bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            تصفير الكل فوراً
          </button>
        </div>

        <!-- Reset messages -->
        <p v-if="resetMsg" class="mt-3 text-sm" :class="resetError ? 'text-red-600' : 'text-emerald-600'">{{ resetMsg }}</p>
      </div>

      <!-- السقف اليومي لاستبدال الكروت -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
          <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white text-lg">💳</div>
          <div>
            <h3 class="font-semibold text-slate-800">السقف اليومي لاستبدال الكروت</h3>
            <p class="text-xs text-slate-500">تحديد عدد الكروت التي يمكن للمستخدم استبدالها يومياً</p>
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-5">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">الحد الأقصى اليومي</label>
            <input type="number" v-model.number="form.daily_card_redeem_limit" min="0" class="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-400 transition" placeholder="0 = غير محدود" />
            <p class="text-xs text-slate-400 mt-1.5">0 يعني غير محدود</p>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">نوع الإجراء عند تجاوز الحد</label>
            <select v-model="form.daily_card_exceed_action" class="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-amber-400 transition">
              <option value="block">منع الاستبدال مع رسالة (تجاوز الحد اليومي)</option>
              <option value="suspend">حظر الحساب فوراً</option>
            </select>
            <p class="text-xs text-slate-400 mt-1.5">اختيار ما يحدث عندما يتجاوز المستخدم الحد المسموح</p>
          </div>
        </div>
      </div>

      <!-- Save -->
      <div class="flex items-center justify-between bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <p v-if="saved" class="text-sm text-emerald-600 font-medium">✅ تم الحفظ بنجاح</p>
        <p v-else class="text-sm text-slate-400">قم بتعديل الإعدادات ثم احفظ</p>
        <button @click="save" :disabled="saving" class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition shadow-md shadow-indigo-200">
          {{ saving ? 'جاري الحفظ...' : '💾 حفظ القيود' }}
        </button>
      </div>
    </template>

    <!-- Reset User Dialog -->
    <div v-if="showResetUser" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" @click.self="showResetUser = false">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl">
        <h3 class="font-bold text-slate-800 text-lg mb-4">تصفير رصيد مستخدم</h3>
        <input v-model="resetUserPhone" type="text" class="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-rose-400 transition mb-4" placeholder="رقم هاتف المستخدم" />
        <button @click="executeResetUser" :disabled="resetting" class="w-full bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white py-2.5 rounded-xl font-semibold text-sm transition">
          {{ resetting ? 'جاري...' : '🔁 تصفير الرصيد' }}
        </button>
      </div>
    </div>

    <!-- Reset All Dialog -->
    <div v-if="showResetAll" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" @click.self="showResetAll = false">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl">
        <div class="text-center">
          <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
          </div>
          <h3 class="font-bold text-slate-800 text-lg mb-2">⚠️ تأكيد التصفير الشامل</h3>
          <p class="text-sm text-slate-500 mb-4">سيتم تصفير أرصدة جميع المستخدمين إلى صفر. هذا الإجراء لا يمكن التراجع عنه!</p>

          <label class="flex items-center gap-2 bg-slate-50 rounded-xl px-4 py-3 mb-4 cursor-pointer">
            <input type="checkbox" v-model="resetAllKeepDebtors" class="rounded text-emerald-500">
            <span class="text-sm text-slate-700">إبقاء المديونين (رصيد سالب) كما هم</span>
          </label>

          <div class="flex gap-3">
            <button @click="showResetAll = false" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-xl font-semibold text-sm transition">إلغاء</button>
            <button @click="executeResetAll" :disabled="resetting" class="flex-1 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white py-2.5 rounded-xl font-semibold text-sm transition">
              {{ resetting ? 'جاري...' : '🔁 تأكيد التصفير' }}
            </button>
          </div>
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
      loading: true, saving: false, saved: false,
      resetting: false, resetMsg: '', resetError: false,
      showResetUser: false, showResetAll: false,
      resetUserPhone: '', resetAllKeepDebtors: true,
      form: {
        daily_card_redeem_limit: 0,
        daily_card_exceed_action: 'block',
        auto_reset_enabled: false,
        reset_type: 'zero',
        reset_value: 0,
        reset_keep_debtors: true,
        last_reset_date: null,
        reset_notification_enabled: false,
        reset_notification_message: '',
      },
    }
  },
  async created() { await this.load() },
  methods: {
    async load() {
      try {
        const { data } = await axios.get('/api/admin/v1/restrictions')
        this.form.daily_card_redeem_limit = data.daily_card_redeem_limit ?? 0
        this.form.daily_card_exceed_action = data.daily_card_exceed_action || 'block'
        this.form.auto_reset_enabled = data.auto_reset_enabled ?? false
        this.form.reset_type = data.reset_type || 'zero'
        this.form.reset_value = data.reset_value ?? 0
        this.form.reset_keep_debtors = data.reset_keep_debtors ?? true
        this.form.last_reset_date = data.last_reset_date || null
        this.form.reset_notification_enabled = data.reset_notification_enabled ?? false
        this.form.reset_notification_message = data.reset_notification_message || ''
      } catch (e) { console.error(e) }
      finally { this.loading = false }
    },
    async save() {
      this.saving = true; this.saved = false
      try {
        await axios.put('/api/admin/v1/restrictions', {
          daily_card_redeem_limit: this.form.daily_card_redeem_limit,
          daily_card_exceed_action: this.form.daily_card_exceed_action,
          auto_reset_enabled: this.form.auto_reset_enabled,
          reset_type: this.form.reset_type,
          reset_value: this.form.reset_value,
          reset_keep_debtors: this.form.reset_keep_debtors,
          reset_notification_enabled: this.form.reset_notification_enabled,
          reset_notification_message: this.form.reset_notification_message,
        })
        this.saved = true
        setTimeout(() => { this.saved = false }, 3000)
      } catch (e) { alert('فشل الحفظ') }
      finally { this.saving = false }
    },
    confirmResetUser() {
      this.resetUserPhone = ''
      this.showResetUser = true
      this.resetMsg = ''
    },
    async executeResetUser() {
      if (!this.resetUserPhone.trim()) return alert('أدخل رقم الهاتف')
      this.resetting = true
      try {
        const { data } = await axios.post(`/api/admin/v1/reset-points/by-phone`, { phone: this.resetUserPhone.trim() })
        this.resetMsg = data.message
        this.resetError = false
        this.showResetUser = false
      } catch (e) {
        this.resetMsg = e.response?.data?.message || 'فشل التصفير'
        this.resetError = true
      }
      finally { this.resetting = false }
    },
    confirmResetAll() {
      this.resetAllKeepDebtors = this.form.reset_keep_debtors
      this.showResetAll = true
      this.resetMsg = ''
    },
    async executeResetAll() {
      this.resetting = true
      try {
        const { data } = await axios.post('/api/admin/v1/reset-points/all', { keep_debtors: this.resetAllKeepDebtors })
        this.resetMsg = data.message
        this.resetError = false
        this.showResetAll = false
        this.form.last_reset_date = new Date().toISOString().slice(0, 10)
      } catch (e) {
        this.resetMsg = e.response?.data?.message || 'فشل التصفير'
        this.resetError = true
      }
      finally { this.resetting = false }
    },
  },
}
</script>
