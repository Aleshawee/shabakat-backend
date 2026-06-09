<template>
  <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-slate-800">إعدادات تحويل النقاط</h1>
    </div>

    <div v-if="loading" class="text-center py-12 text-slate-500">جاري التحميل...</div>

    <template v-else>
      <!-- Hero + Stats -->
      <div class="rounded-2xl p-6 text-white relative overflow-hidden shadow-xl mb-5"
        style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 40%, #14b8a6 100%);">
        <div class="absolute inset-0 opacity-10 pointer-events-none"
          style="background-image: radial-gradient(circle at 80% 20%, rgba(255,255,255,0.3) 1px, transparent 1px); background-size: 25px 25px;">
        </div>
        <div class="relative">
          <div class="flex items-center gap-4 mb-4">
            <div class="w-14 h-14 rounded-xl bg-white/20 flex items-center justify-center text-2xl backdrop-blur shrink-0">🔄</div>
            <div>
              <h2 class="text-lg font-bold">تحويل النقاط</h2>
              <p class="text-sm text-white/70 mt-0.5">إعدادات وتحليلات ميزة تحويل النقاط بين المستخدمين</p>
            </div>
          </div>
          <div class="grid grid-cols-5 gap-3">
            <div class="bg-white/15 rounded-xl p-3 text-center backdrop-blur">
              <p class="text-xl font-bold">{{ stats.total_transfers }}</p>
              <p class="text-[10px] text-white/70 mt-0.5">تحويل</p>
            </div>
            <div class="bg-white/15 rounded-xl p-3 text-center backdrop-blur">
              <p class="text-xl font-bold">{{ stats.total_amount.toLocaleString() }}</p>
              <p class="text-[10px] text-white/70 mt-0.5">نقاط</p>
            </div>
            <div class="bg-white/15 rounded-xl p-3 text-center backdrop-blur">
              <p class="text-xl font-bold">{{ stats.total_fees.toLocaleString() }}</p>
              <p class="text-[10px] text-white/70 mt-0.5">رسوم</p>
            </div>
            <div class="bg-white/15 rounded-xl p-3 text-center backdrop-blur">
              <p class="text-xl font-bold">{{ stats.total_senders }}</p>
              <p class="text-[10px] text-white/70 mt-0.5">محوِّل</p>
            </div>
            <div class="bg-white/15 rounded-xl p-3 text-center backdrop-blur">
              <p class="text-xl font-bold">{{ stats.total_receivers }}</p>
              <p class="text-[10px] text-white/70 mt-0.5">مستقبِل</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main settings -->
      <div class="space-y-5">
        <!-- Toggle feature -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center justify-between">
          <div>
            <h3 class="font-semibold text-slate-800">تفعيل تحويل النقاط</h3>
            <p class="text-xs text-slate-500 mt-0.5">السماح للمستخدمين بتحويل النقاط لبعضهم</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="form.is_enabled" class="sr-only peer">
            <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:bg-teal-600 after:content-[''] after:absolute after:top-0.5 after:start-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
          </label>
        </div>

        <!-- Transfer limits -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
          <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-teal-400 to-cyan-500 flex items-center justify-center text-white">🔢</div>
            <div>
              <h3 class="font-semibold text-slate-800">حدود التحويل</h3>
              <p class="text-xs text-slate-500">الحد الأدنى والأقصى للتحويل الواحد</p>
            </div>
          </div>
          <div class="grid md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">الحد الأدنى (نقطة)</label>
              <input type="number" v-model.number="form.min_transfer_amount" class="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-teal-400 focus:ring-4 focus:ring-teal-50 transition" />
              <p class="text-xs text-slate-400 mt-1.5">أقل مبلغ يمكن تحويله</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">الحد الأقصى (نقطة)</label>
              <input type="number" v-model.number="form.max_transfer_amount" class="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-teal-400 focus:ring-4 focus:ring-teal-50 transition" />
              <p class="text-xs text-slate-400 mt-1.5">أقصى مبلغ يمكن تحويله</p>
            </div>
          </div>
        </div>

        <!-- Fees & balance -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
          <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white">⚖️</div>
            <div>
              <h3 class="font-semibold text-slate-800">الرسوم والرصيد</h3>
              <p class="text-xs text-slate-500">نسبة الرسوم والحد الأدنى لرصيد المحوِّل</p>
            </div>
          </div>
          <div class="grid md:grid-cols-2 gap-5">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">نسبة الرسوم (%)</label>
              <input type="number" v-model.number="form.transfer_fee_percent" step="0.1" min="0" max="100" class="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-teal-400 focus:ring-4 focus:ring-teal-50 transition" />
              <p class="text-xs text-slate-400 mt-1.5">0 = بدون رسوم</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">الحد الأدنى لرصيد المحوِّل</label>
              <input type="number" v-model.number="form.min_balance_required" class="w-full border-2 border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-teal-400 focus:ring-4 focus:ring-teal-50 transition" />
              <p class="text-xs text-slate-400 mt-1.5">0 = بدون شرط</p>
            </div>
          </div>
        </div>

        <!-- Phone verification toggle -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center justify-between">
          <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center text-white shrink-0 mt-0.5">🔍</div>
            <div>
              <h3 class="font-semibold text-slate-800">التحقق من المستلم</h3>
              <p class="text-xs text-slate-500 mt-0.5">عند التفعيل، يجب على المستخدم إدخال رقم جوال المستلم والتحقق من وجوده في النظام قبل إتمام التحويل</p>
              <p v-if="!form.require_phone_verification" class="text-xs text-amber-600 mt-1">⚠️ معطل — ستتم الحوالة بدون تحقق، ويُخصم المبلغ من المحوِّل</p>
              <p v-else class="text-xs text-emerald-600 mt-1">✅ مفعل — سيتم التحقق من وجود رقم الجوال قبل إتمام التحويل</p>
            </div>
          </div>
          <label class="relative inline-flex items-center cursor-pointer shrink-0">
            <input type="checkbox" v-model="form.require_phone_verification" class="sr-only peer">
            <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:bg-violet-600 after:content-[''] after:absolute after:top-0.5 after:start-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
          </label>
        </div>

        <!-- Save -->
        <div class="flex items-center justify-between bg-white rounded-xl shadow-sm border border-slate-200 p-5">
          <p v-if="saved" class="text-sm text-emerald-600 font-medium">✅ تم الحفظ</p>
          <p v-else class="text-sm text-slate-400">قم بتعديل الإعدادات ثم احفظ</p>
          <button @click="save" :disabled="saving" class="bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition shadow-sm">
            {{ saving ? 'جاري الحفظ...' : '💾 حفظ الإعدادات' }}
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  data() {
    return {
      loading: true,
      saving: false,
      saved: false,
      stats: {
        total_transfers: 0,
        total_amount: 0,
        total_fees: 0,
        total_senders: 0,
        total_receivers: 0,
      },
      form: {
        is_enabled: false,
        min_transfer_amount: 10,
        max_transfer_amount: 1000,
        transfer_fee_percent: 0,
        min_balance_required: 0,
        require_phone_verification: false,
      },
    }
  },
  async created() {
    await this.load()
  },
  methods: {
    async load() {
      try {
        const { data } = await axios.get('/api/admin/v1/transfer/settings')
        const s = data.setting
        Object.assign(this.form, {
          is_enabled: s.is_enabled,
          min_transfer_amount: s.min_transfer_amount,
          max_transfer_amount: s.max_transfer_amount,
          transfer_fee_percent: s.transfer_fee_percent,
          min_balance_required: s.min_balance_required,
          require_phone_verification: s.require_phone_verification,
        })
        if (data.stats) this.stats = data.stats
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    async save() {
      this.saving = true
      this.saved = false
      try {
        await axios.put('/api/admin/v1/transfer/settings', this.form)
        this.saved = true
        setTimeout(() => { this.saved = false }, 3000)
      } catch (e) {
        console.error(e)
        alert('فشل الحفظ')
      } finally {
        this.saving = false
      }
    },
  },
}
</script>
