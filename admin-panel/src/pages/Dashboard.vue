<template>
  <div class="flex h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white p-5 flex flex-col">
      <div class="flex items-center gap-2 mb-8">
        <span class="text-2xl">🎁</span>
        <span class="font-bold text-lg">لوحة التحكم</span>
      </div>

      <nav class="space-y-1 flex-1">
        <router-link to="/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-violet-600/20 text-violet-300 font-medium">
          <span>📊</span> الرئيسية
        </router-link>
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition">
          <span>🏷️</span> الفئات والنقاط
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition">
          <span>💳</span> الكروت
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition">
          <span>👥</span> المستخدمين
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition">
          <span>🎮</span> الترفيه
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition">
          <span>📈</span> التحليلات
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition">
          <span>⚙️</span> الإعدادات
        </a>
      </nav>

      <div class="border-t border-slate-700 pt-4">
        <p class="text-sm text-slate-400">{{ admin?.name }}</p>
        <button @click="logout" class="text-sm text-red-400 hover:text-red-300 mt-1">تسجيل خروج</button>
      </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-6 overflow-y-auto">
      <h1 class="text-2xl font-bold mb-6">الرئيسية</h1>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border">
          <span class="text-slate-400 text-sm">إجمالي المستخدمين</span>
          <p class="text-3xl font-bold mt-1">0</p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border">
          <span class="text-slate-400 text-sm">الكروت النشطة</span>
          <p class="text-3xl font-bold mt-1">0</p>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border">
          <span class="text-slate-400 text-sm">النقاط الممنوحة</span>
          <p class="text-3xl font-bold mt-1">0</p>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl shadow-sm border">
        <p class="text-slate-500 text-center py-8">المحتوى قيد التطوير 🚧</p>
      </div>
    </main>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return { admin: JSON.parse(localStorage.getItem('admin') || '{}') }
  },
  mounted() {
    this.fetchStats()
  },
  methods: {
    async fetchStats() {
      try {
        const res = await axios.get('/api/admin/v1/admin/profile', {
          headers: { Authorization: `Bearer ${localStorage.getItem('admin_token')}` },
        })
        this.admin = res.data
      } catch (e) {
        if (e.response?.status === 401) this.logout()
      }
    },
    logout() {
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin')
      this.$router.push('/login')
    },
  },
}
</script>
