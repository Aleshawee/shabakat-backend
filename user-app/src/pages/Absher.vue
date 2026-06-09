<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 pb-20">
    <!-- Header -->
    <div class="bg-gradient-to-br from-rose-700 via-rose-800 to-pink-900 text-white px-5 pt-5 pb-8 relative overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(255,255,255,0.06),transparent_60%)]"></div>
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-4">
          <button @click="$router.back()" class="text-white/80 hover:text-white transition-transform hover:scale-110">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <h1 class="text-xl font-bold">أبشر (سلفة)</h1>
        </div>
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/10">
          <div class="flex items-center justify-between">
            <p class="text-rose-200 text-sm">الرصيد الحالي</p>
            <svg class="w-5 h-5 text-rose-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <p class="text-2xl font-bold mt-1">{{ balance ?? 0 }} <span class="text-sm font-normal text-rose-200">نقطة</span></p>
        </div>
      </div>
    </div>

    <div class="px-4 mt-4 space-y-4">
      <!-- Disabled state -->
      <div v-if="!enabled && !loading" class="bg-white/5 backdrop-blur rounded-2xl p-8 text-center border border-white/10">
        <div class="text-5xl mb-4">🔒</div>
        <p class="text-white font-medium">الميزة غير مفعلة</p>
        <p class="text-xs text-white/40 mt-1">خدمة أبشر غير متاحة حالياً</p>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-12">
        <div class="w-10 h-10 border-4 border-rose-300/30 border-t-rose-400 rounded-full animate-spin mx-auto"></div>
        <p class="text-rose-200/60 text-sm mt-3">جاري تحميل الإعدادات...</p>
      </div>

      <!-- Info -->
      <div v-if="enabled && starts_at && ends_at" class="bg-amber-400/10 border border-amber-400/20 rounded-2xl p-3 flex items-center gap-2 text-xs text-amber-300/80">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>الخدمة متاحة من {{ formatDate(starts_at) }} إلى {{ formatDate(ends_at) }}</span>
      </div>

      <div v-if="enabled && require_recent_activity" class="bg-blue-400/10 border border-blue-400/20 rounded-2xl p-3 flex items-center gap-2 text-xs text-blue-300/80">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <span>مطلوب آخر نشاط خلال {{ activity_days }} أيام ماضية</span>
      </div>

      <!-- Eligible Categories -->
      <div v-if="enabled">
        <h3 class="text-white font-bold text-base mb-3">الفئات المتاحة للسلفة</h3>

        <div v-if="categories.length === 0" class="bg-white/5 backdrop-blur rounded-2xl p-8 text-center border border-white/10">
          <p class="text-white/50">لا توجد فئات متاحة للسلفة حاليًا</p>
        </div>

        <div class="grid grid-cols-1 gap-4">
          <div v-for="cat in categories" :key="cat.category_id"
            class="rounded-2xl p-5 relative overflow-hidden transition-all duration-300"
            :class="cat.is_eligible
              ? 'bg-gradient-to-br from-emerald-900/60 to-teal-900/40 border border-emerald-500/20 shadow-lg shadow-emerald-500/5 hover:shadow-emerald-500/10 hover:-translate-y-0.5'
              : 'bg-white/5 border border-white/10 opacity-60'">

            <!-- Top indicator -->
            <div v-if="cat.is_eligible" class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-l from-emerald-400 to-teal-400"></div>

            <div class="flex items-start gap-4">
              <!-- Icon -->
              <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-xl shrink-0"
                :class="cat.is_eligible ? 'bg-emerald-500/20' : 'bg-white/5'">
                <svg class="w-7 h-7" :class="cat.is_eligible ? 'text-emerald-300' : 'text-white/30'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="font-bold" :class="cat.is_eligible ? 'text-white' : 'text-white/50'">فئة {{ cat.category_name }}</h4>
                <p class="text-xs mt-1" :class="cat.is_eligible ? 'text-emerald-200/70' : 'text-white/30'">
                  المكافأة: {{ cat.reward.name }} ({{ cat.reward.points_cost }} نقطة)
                </p>
                <div class="flex flex-wrap gap-3 mt-2">
                  <span class="text-[11px] px-2 py-0.5 rounded-full" :class="cat.is_eligible ? 'bg-emerald-500/10 text-emerald-300/80' : 'bg-white/5 text-white/30'">
                    رصيدك: {{ cat.user_balance }} نقطة
                  </span>
                  <span class="text-[11px] px-2 py-0.5 rounded-full" :class="cat.is_eligible ? 'bg-amber-500/10 text-amber-300/80' : 'bg-white/5 text-white/30'">
                    تحتاج: {{ cat.needed_amount }} نقطة
                  </span>
                  <span class="text-[11px] px-2 py-0.5 rounded-full" :class="cat.is_eligible ? 'bg-rose-500/10 text-rose-300/80' : 'bg-white/5 text-white/30'">
                    أقصى سلفة: {{ cat.borrow_setting.max_borrow_amount }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Ineligible reason -->
            <div v-if="!cat.is_eligible && cat.ineligible_reason" class="mt-3 bg-rose-500/10 border border-rose-500/20 rounded-xl px-3 py-2 text-xs text-rose-300/80">
              {{ cat.ineligible_reason }}
            </div>

            <!-- Action -->
            <button v-if="cat.is_eligible" @click="requestBorrow(cat.category_id)"
              :disabled="requesting"
              class="w-full mt-4 py-3 rounded-xl font-bold text-white bg-gradient-to-l from-rose-600 to-pink-600 hover:from-rose-500 hover:to-pink-500 transition-all active:scale-[0.97] shadow-lg shadow-rose-500/20 disabled:opacity-50">
              <span v-if="requesting">
                <svg class="animate-spin h-4 w-4 inline ml-1" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                جاري...
              </span>
              <span v-else>طلب سلفة — احصل على {{ cat.reward.name }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Result Modal -->
    <div v-if="result" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="result = null">
      <div class="bg-gradient-to-b from-emerald-800/90 via-slate-900 to-slate-950 border border-emerald-500/20 rounded-3xl p-6 w-full max-w-sm text-center animate-modal relative overflow-hidden shadow-2xl shadow-emerald-500/10">
        <div class="absolute -top-20 -right-20 w-60 h-60 bg-emerald-400/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-amber-400/5 rounded-full blur-3xl"></div>
        <div class="relative z-10">
          <div class="w-20 h-20 rounded-full bg-gradient-to-br from-emerald-400/30 to-teal-500/30 border border-emerald-400/30 flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h3 class="text-xl font-black text-white mb-1">تمت السلفة بنجاح!</h3>
          <p class="text-sm text-emerald-200/70 mb-4">حصلت على {{ result.reward_name }}</p>

          <div class="bg-white/5 rounded-2xl p-4 mb-3 border border-emerald-500/10">
            <p class="text-xs text-emerald-200/60 mb-1">رمز الكرت</p>
            <p class="text-lg font-bold text-emerald-300 font-mono tracking-wider" dir="ltr">{{ result.card_code }}</p>
            <button @click="copyCode(result.card_code)" class="mt-2 text-xs text-emerald-400/80 hover:text-emerald-300 transition-colors">
              <svg class="w-3.5 h-3.5 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
              </svg>
              نسخ الرمز
            </button>
          </div>

          <div class="bg-white/5 rounded-2xl p-3 mb-4 border border-amber-500/10 flex justify-between items-center">
            <span class="text-xs text-emerald-200/60">المبلغ المقترض</span>
            <span class="font-bold text-amber-400">{{ result.borrow_amount }} نقطة</span>
          </div>
          <div class="bg-rose-500/10 rounded-2xl p-3 mb-4 border border-rose-500/20 flex justify-between items-center">
            <span class="text-xs text-rose-200/60">الرصيد بعد السلفة</span>
            <span class="font-bold text-rose-300">{{ result.points_balance }} نقطة</span>
          </div>

          <button @click="result = null; loadData()"
            class="w-full py-3 rounded-2xl font-bold text-white bg-gradient-to-l from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 transition-all active:scale-[0.97] shadow-lg shadow-emerald-500/20">
            حسناً
          </button>
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
      balance: 0, enabled: false, loading: true,
      starts_at: null, ends_at: null,
      require_recent_activity: false, activity_days: 0,
      categories: [], requesting: false, result: null,
    }
  },
  mounted() { this.loadData() },
  methods: {
    async loadData() {
      this.loading = true
      try {
        const profile = await axios.get('/api/v1/user/profile')
        this.balance = profile.data?.points_balance ?? profile.data?.user?.points_balance ?? 0

        const r = await axios.get('/api/v1/absher/settings')
        if (!r.data.enabled) { this.enabled = false; return }
        this.enabled = true
        this.starts_at = r.data.starts_at
        this.ends_at = r.data.ends_at
        this.require_recent_activity = r.data.require_recent_activity
        this.activity_days = r.data.activity_days
        this.categories = r.data.categories || []
      } catch (e) {
        if (e.response?.data?.message !== 'الميزة غير مفعلة') console.error(e)
        this.enabled = false
      }
      finally { this.loading = false }
    },
    async requestBorrow(categoryId) {
      this.requesting = true
      try {
        const r = await axios.post('/api/v1/absher/request', { category_id: categoryId })
        this.result = r.data
        this.balance = r.data.points_balance
      } catch (e) {
        alert(e.response?.data?.message || 'فشل طلب السلفة')
      }
      finally { this.requesting = false }
    },
    formatDate(dt) {
      if (!dt) return ''
      const d = new Date(dt)
      return d.toLocaleDateString('ar-YE', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
    },
    async copyCode(code) {
      try { await navigator.clipboard.writeText(code); alert('✅ تم نسخ الرمز') }
      catch { alert('❌ فشل النسخ') }
    },
  },
}
</script>

<style scoped>
@keyframes modal {
  from { opacity: 0; transform: scale(0.85) translateY(10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
.animate-modal { animation: modal 0.35s ease-out; }
</style>
