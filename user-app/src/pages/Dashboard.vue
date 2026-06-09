<template>
  <div class="min-h-screen bg-slate-50 pb-20">
    <!-- Header -->
    <div class="bg-gradient-to-br from-emerald-700 via-emerald-800 to-slate-900 text-white px-5 pt-5 pb-20 relative overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(255,255,255,0.06),transparent_60%)]"></div>
      <div class="relative z-10">
        <div class="flex justify-between items-center mb-5">
          <button @click="menuOpen = !menuOpen" class="text-white/80 hover:text-white p-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </button>
          <div class="flex items-center gap-2">
            <h1 class="text-xl font-bold">مكافآتي</h1>
            <span v-if="networkName" class="text-[10px] bg-white/15 px-2 py-0.5 rounded-full text-emerald-200 font-medium">
              {{ networkName }}
            </span>
          </div>
          <button @click="logout" class="text-sm text-emerald-200 hover:text-white flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            خروج
          </button>
        </div>

        <!-- Menu Dropdown -->
        <div v-if="menuOpen" class="bg-white/10 backdrop-blur-xl rounded-2xl overflow-hidden mb-4 animate-dropdown border border-white/10">
          <router-link v-for="item in menuItems" :key="item.path" :to="item.path"
            class="flex items-center gap-3 px-4 py-3.5 text-sm text-white hover:bg-white/10 transition border-b border-white/5 last:border-0"
            @click="menuOpen = false">
            <svg class="w-5 h-5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.icon"/>
            </svg>
            {{ item.label }}
          </router-link>
        </div>

        <!-- Balance Card -->
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-5 border border-white/10">
          <div class="flex items-center justify-between mb-1">
            <p class="text-emerald-200 text-sm">رصيد النقاط</p>
            <svg class="w-5 h-5 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          </div>
          <p class="text-3xl font-bold mt-1">{{ user.points_balance || 0 }} <span class="text-lg font-normal text-emerald-200">نقطة</span></p>
          <p class="text-xs text-emerald-300 mt-2">{{ user.name || 'مستخدم' }}</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="px-4 -mt-12 relative z-20">
      <div class="bg-white rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.06)] border border-slate-100 p-5">
        <h2 class="text-sm font-bold text-slate-700 mb-4">الخدمات</h2>
        <div class="grid grid-cols-4 gap-3">
          <button v-for="f in features" :key="f.key" @click="f.action"
            :disabled="!f.enabled"
            class="flex flex-col items-center gap-1.5 py-3 rounded-xl transition disabled:opacity-30 disabled:cursor-not-allowed"
            :class="f.enabled ? 'hover:bg-emerald-50 active:scale-95' : ''">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center" :class="f.bgClass">
              <svg class="w-6 h-6" :class="f.iconClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="f.icon"/>
              </svg>
            </div>
            <span class="text-[11px] font-medium text-slate-600">{{ f.label }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Stats / Activity -->
    <div class="px-4 mt-6 space-y-4">
      <div class="bg-white rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.06)] border border-slate-100 p-5">
        <h2 class="text-sm font-bold text-slate-700 mb-4">آخر العمليات</h2>
        <div v-if="transactions.length === 0" class="text-center py-6 text-slate-400">
          <svg class="w-12 h-12 mx-auto mb-2 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          <p class="text-sm">لا توجد عمليات بعد</p>
        </div>
        <div v-for="t in transactions.slice(0, 5)" :key="t.id"
          class="flex items-center gap-3 py-3 border-b border-slate-50 last:border-0">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center"
            :class="t.amount > 0 ? 'bg-emerald-50 text-emerald-500' : 'bg-red-50 text-red-500'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="t.amount > 0" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-slate-700 truncate">{{ t.note || 'عملية' }}</p>
            <p class="text-xs text-slate-400">{{ new Date(t.created_at).toLocaleDateString('ar-YE') }}</p>
          </div>
          <span class="text-sm font-bold" :class="t.amount > 0 ? 'text-emerald-600' : 'text-red-500'">
            {{ t.amount > 0 ? '+' : '' }}{{ t.amount }}
          </span>
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
      menuOpen: false,
      user: JSON.parse(localStorage.getItem('user') || '{}'),
      networkName: JSON.parse(localStorage.getItem('network') || '{}').name || '',
      transactions: [],
      features: [
        { key: 'lucky-box', label: 'صندوق الحظ', enabled: true, icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', bgClass: 'bg-amber-50', iconClass: 'text-amber-500', action: () => this.$router.push('/lucky-box') },
        { key: 'lucky-wheel', label: 'عجلة الحظ', enabled: true, icon: 'M12 6V2m0 4a8 8 0 100 16 8 8 0 000-16zM2 12h4m12 0h4M4.93 4.93l2.83 2.83m8.48-2.83l-2.83 2.83', bgClass: 'bg-sky-50', iconClass: 'text-sky-500', action: () => this.$router.push('/lucky-wheel') },
        { key: 'transfer', label: 'تحويل نقاط', enabled: true, icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', bgClass: 'bg-teal-50', iconClass: 'text-teal-500', action: () => this.$router.push('/transfer') },
        { key: 'absher', label: 'أبشر', enabled: true, icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', bgClass: 'bg-rose-50', iconClass: 'text-rose-500', action: () => this.$router.push('/absher') },
        { key: 'sport-predictions', label: 'توقعات رياضية', enabled: true, icon: 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', bgClass: 'bg-indigo-50', iconClass: 'text-indigo-500', action: () => this.$router.push('/sport-predictions') },
      ],
    }
  },
  computed: {
    menuItems() {
      return [
        { path: '/dashboard', label: 'الرئيسية', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
        { path: '/rewards', label: 'المكافآت', icon: 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7' },
        { path: '/redeem-card', label: 'استبدال كرت', icon: 'M3 10h18M3 14h18M3 6h18M3 18h18' },
        { path: '/lucky-box', label: 'صندوق الحظ', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
        { path: '/lucky-wheel', label: 'عجلة الحظ', icon: 'M12 6V2m0 4a8 8 0 100 16 8 8 0 000-16zM2 12h4m12 0h4M4.93 4.93l2.83 2.83m8.48-2.83l-2.83 2.83' },
        { path: '/history', label: 'سجل العمليات', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' },
        { path: '/profile', label: 'الملف الشخصي', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
        { path: '/transfer', label: 'تحويل نقاط', icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4' },
        { path: '/absher', label: 'أبشر (سلفة)', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
        { path: '/sport-predictions', label: 'توقعات رياضية', icon: 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
        { path: '/card-info', label: 'معلومات الكروت', icon: 'M3 10h18M3 6h18M3 14h18M3 18h18' },
      ]
    },
  },
  mounted() {
    this.loadData()
  },
  methods: {
    async loadData() {
      try {
        const r = await axios.get('/api/v1/user/history')
        this.transactions = r.data.transactions || []
        this.user.points_balance = r.data.balance
        localStorage.setItem('user', JSON.stringify(this.user))

        const promises = []
        promises.push(axios.get('/api/v1/absher/settings').then(r => {
          const f = this.features.find(x => x.key === 'absher')
          if (f) f.enabled = r.data.enabled
        }).catch(() => {}))
        promises.push(axios.get('/api/v1/transfer/settings').then(r => {
          const f = this.features.find(x => x.key === 'transfer')
          if (f) f.enabled = r.data.enabled
        }).catch(() => {}))
        promises.push(axios.get('/api/v1/sport-events').then(r => {
          const f = this.features.find(x => x.key === 'sport-predictions')
          if (f) f.enabled = r.data.enabled !== false
        }).catch(() => {}))
        await Promise.allSettled(promises)
      } catch (e) { console.error(e) }
    },
    logout() {
      localStorage.removeItem('user_token')
      localStorage.removeItem('user')
      localStorage.removeItem('network')
      this.$router.push('/login')
    },
  },
}
</script>

<style scoped>
@keyframes dropdown {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-dropdown { animation: dropdown 0.2s ease-out; }
</style>
