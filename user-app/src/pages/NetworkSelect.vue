<template>
  <div class="min-h-screen bg-gradient-to-br from-emerald-700 via-emerald-800 to-slate-900 flex flex-col items-center justify-center p-6 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.06),transparent_70%)]"></div>

    <div class="text-center mb-8 relative z-10">
      <div class="w-20 h-20 bg-white/10 backdrop-blur rounded-2xl flex items-center justify-center mx-auto mb-4 border border-white/10">
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-white">شبكة المكافآت</h1>
      <p class="text-emerald-200 text-sm mt-1">اختر شبكتك للمتابعة</p>
    </div>

    <div class="w-full max-w-sm relative z-10 space-y-3">
      <div v-if="loading" class="text-center py-8">
        <svg class="animate-spin h-8 w-8 text-white mx-auto" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <p class="text-white/70 text-sm mt-3">جاري تحميل الشبكات...</p>
      </div>

      <div v-else-if="networks.length === 0" class="bg-white/10 backdrop-blur rounded-3xl p-7 text-center">
        <p class="text-white/70">لا توجد شبكات متاحة حالياً</p>
      </div>

      <button v-for="net in networks" :key="net.id" @click="selectNetwork(net)"
        class="w-full bg-white/95 backdrop-blur hover:bg-white rounded-3xl p-5 text-right shadow-lg transition-all hover:shadow-xl active:scale-[0.98] group">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="font-bold text-lg text-slate-800">{{ net.name }}</h3>
            <p class="text-sm text-slate-500 mt-0.5">{{ net.slug }}</p>
          </div>
          <svg class="w-6 h-6 text-emerald-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 18l6-6-6-6"/>
          </svg>
        </div>
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  data() {
    return { networks: [], loading: true }
  },
  async mounted() {
    try {
      const res = await axios.get('/api/v1/auth/networks')
      this.networks = res.data.networks || []
    } catch (e) {
      console.error('Failed to load networks', e)
    } finally {
      this.loading = false
    }
  },
  methods: {
    selectNetwork(net) {
      localStorage.setItem('network', JSON.stringify({ id: net.id, slug: net.slug, name: net.name }))
      this.$router.push('/login')
    },
  },
}
</script>
