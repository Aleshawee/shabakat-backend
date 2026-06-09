<template>
  <div class="h-screen flex flex-col md:flex-row bg-slate-50" dir="rtl">
    <!-- Mobile Header -->
    <div class="md:hidden fixed top-0 inset-x-0 z-40 bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between shadow-sm">
      <button @click="sidebarOpen = true" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-600 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
      <span class="font-bold text-slate-800">👑 لوحة المالك</span>
      <div class="w-6"></div>
    </div>

    <!-- Mobile Backdrop -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="md:hidden fixed inset-0 bg-black/60 z-30 transition-opacity"></div>

    <!-- Sidebar -->
    <aside :class="[
      'fixed md:relative z-40 h-full w-64 flex flex-col transition-transform duration-300 overflow-hidden',
      sidebarOpen ? 'translate-x-0' : 'translate-x-full md:translate-x-0'
    ]" style="background: linear-gradient(180deg, #0f172a 0%, #1e293b 40%, #334155 100%);">
      <div class="absolute inset-0 opacity-5 pointer-events-none"
        style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.3) 1px, transparent 1px), radial-gradient(circle at 80% 70%, rgba(255,255,255,0.2) 1px, transparent 1px); background-size: 30px 30px, 20px 20px;">
      </div>

      <!-- Logo -->
      <div class="relative px-5 pt-6 pb-5 border-b border-white/10">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-xl shadow-lg shadow-amber-500/30">
            👑
          </div>
          <div>
            <p class="font-bold text-white text-sm">المالك</p>
            <p class="text-[10px] text-slate-400/70 font-medium">لوحة التحكم الرئيسية</p>
          </div>
        </div>
      </div>

      <!-- Nav -->
      <nav class="relative flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
        <router-link to="/owner"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 hover:bg-white/10 group text-slate-300 hover:text-white"
          active-class="!text-white bg-white/15 font-medium shadow-sm"
          @click="sidebarOpen = false">
          <span class="text-lg">📊</span>
          <span>الرئيسية</span>
        </router-link>

        <router-link to="/owner/networks"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 hover:bg-white/10 group text-slate-300 hover:text-white"
          active-class="!text-white bg-white/15 font-medium shadow-sm"
          @click="sidebarOpen = false">
          <span class="text-lg">🌐</span>
          <span>إدارة الشبكات</span>
        </router-link>

        <router-link to="/owner/settings/sms"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 hover:bg-white/10 group text-slate-300 hover:text-white"
          active-class="!text-white bg-white/15 font-medium shadow-sm"
          @click="sidebarOpen = false">
          <span class="text-lg">📡</span>
          <span>بوابة الرسائل</span>
        </router-link>
      </nav>

      <!-- User section -->
      <div class="relative border-t border-white/10 px-5 pt-4 pb-5">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-sm shadow-sm">
            {{ (admin?.name || 'M')[0] }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm text-white font-medium truncate">{{ admin?.name || 'مالك' }}</p>
            <p class="text-[10px] text-slate-400/60">مالك النظام</p>
          </div>
          <button @click="logout" class="text-slate-400/50 hover:text-red-400 transition p-1" title="تسجيل خروج">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto pt-14 md:pt-0">
      <router-view />
    </main>
  </div>
</template>

<script>
export default {
  data() {
    return {
      sidebarOpen: false,
      admin: JSON.parse(localStorage.getItem('admin') || '{}'),
    }
  },
  methods: {
    logout() {
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin')
      this.$router.push('/login')
    },
  },
}
</script>
