<template>
  <div class="h-screen flex flex-col md:flex-row bg-slate-50" dir="rtl">
    <!-- Mobile Header -->
    <div class="md:hidden fixed top-0 inset-x-0 z-40 bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between shadow-sm">
      <button @click="sidebarOpen = true" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-600 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
      <span class="font-bold text-slate-800 truncate max-w-[200px]">{{ networkName ? '🎁 ' + networkName + (networkSlug ? ' (' + networkSlug + ')' : '') : '🎁 لوحة التحكم' }}</span>
      <div class="w-6"></div>
    </div>

    <!-- Mobile Backdrop -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="md:hidden fixed inset-0 bg-black/60 z-30 transition-opacity"></div>

    <!-- Sidebar -->
    <aside :class="[
      'fixed md:relative z-40 h-full w-64 flex flex-col transition-transform duration-300 overflow-hidden',
      sidebarOpen ? 'translate-x-0' : 'translate-x-full md:translate-x-0'
    ]" style="background: linear-gradient(180deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%);">
      <div class="absolute inset-0 opacity-5 pointer-events-none"
        style="background-image: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.3) 1px, transparent 1px), radial-gradient(circle at 80% 70%, rgba(255,255,255,0.2) 1px, transparent 1px); background-size: 30px 30px, 20px 20px;">
      </div>

      <!-- Logo section -->
      <div class="relative px-5 pt-6 pb-5 border-b border-white/10">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-400 to-indigo-500 flex items-center justify-center text-xl shadow-lg shadow-indigo-500/30">
            🎁
          </div>
          <div class="min-w-0 flex-1">
            <p class="font-bold text-white text-sm truncate">{{ networkName || 'شبكات' }}</p>
            <p class="text-[10px] text-indigo-300/70 font-medium">{{ networkSlug ? networkSlug + ' — لوحة التحكم' : 'لوحة التحكم' }}</p>
          </div>
        </div>
      </div>

      <!-- Nav -->
      <nav class="relative flex-1 overflow-y-auto px-3 py-4 space-y-0.5 scrollbar-thin">
        <template v-for="item in sidebarItems" :key="item.label">
          <router-link v-if="!item.children" :to="item.path"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 hover:bg-white/10 group text-indigo-200 hover:text-white"
            active-class="!text-white bg-white/15 font-medium shadow-sm"
            @click="sidebarOpen = false">
            <span class="text-lg group-hover:scale-110 transition-transform duration-200">{{ item.icon }}</span>
            <span>{{ item.label }}</span>
          </router-link>
          <!-- Dropdown group -->
          <div v-else class="space-y-0.5">
            <button @click="toggleGroup(item.label)"
              class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm transition-all duration-200 hover:bg-white/10 group text-indigo-200 hover:text-white text-right">
              <span class="text-lg group-hover:scale-110 transition-transform duration-200">{{ item.icon }}</span>
              <span class="flex-1">{{ item.label }}</span>
              <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="openGroups[item.label] ? 'rotate-180' : ''"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            <div v-if="openGroups[item.label]" class="mr-3 space-y-0.5 border-r border-white/10 pr-2">
              <router-link v-for="child in item.children" :key="child.path" :to="child.path"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-white/10 group text-indigo-300 hover:text-white"
                active-class="!text-white bg-white/15 font-medium shadow-sm"
                @click="sidebarOpen = false">
                <span class="text-sm">{{ child.icon }}</span>
                <span>{{ child.label }}</span>
              </router-link>
            </div>
          </div>
        </template>
      </nav>

      <!-- User section -->
      <div class="relative border-t border-white/10 px-5 pt-4 pb-5">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 flex items-center justify-center text-white font-bold text-sm shadow-sm">
            {{ (admin?.name || 'A')[0] }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm text-white font-medium truncate">{{ admin?.name || 'مدير' }}</p>
            <p class="text-[10px] text-indigo-300/60">متصل</p>
          </div>
          <button @click="logout" class="text-indigo-300/50 hover:text-red-400 transition p-1" title="تسجيل خروج">
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
import axios from 'axios'

export default {
  data() {
    return {
      sidebarOpen: false,
      admin: JSON.parse(localStorage.getItem('admin') || '{}'),
      networkName: '',
      networkSlug: '',
      openGroups: {},
    }
  },
  async created() {
    try {
      const res = await axios.get('/api/admin/v1/admin/profile')
      this.networkName = res.data.network?.name || ''
      this.networkSlug = res.data.network?.slug || ''
    } catch (e) { /* ignore */ }
  },
  computed: {
    sidebarItems() {
      return [
        { icon: '📊', label: 'الرئيسية', path: '/admin' },
        { icon: '📈', label: 'التحليلات', path: '/admin/analytics' },
        {
          icon: '👥', label: 'إدارة المستخدمين',
          children: [
            { icon: '👤', label: 'إدارة العملاء', path: '/admin/users' },
            { icon: '👔', label: 'إدارة مدراء الشبكة', path: '/admin/network-admins' },
          ],
        },
        {
          icon: '🎁', label: 'المكافآت والكروت',
          children: [
            { icon: '🏷️', label: 'الفئات والنقاط', path: '/admin/categories' },
            { icon: '🎁', label: 'المكافآت', path: '/admin/rewards' },
            { icon: '💳', label: 'الكروت', path: '/admin/cards' },
            { icon: '📋', label: 'سجل الاستبدال', path: '/admin/redemptions' },
          ],
        },
        {
          icon: '📢', label: 'المحتوى',
          children: [
            { icon: '🔔', label: 'الإشعارات', path: '/admin/notifications' },
            { icon: '🖼️', label: 'البانرات', path: '/admin/banners' },
          ],
        },
        {
          icon: '🎮', label: 'الترفيه',
          children: [
            { icon: '⚽', label: 'التوقعات', path: '/admin/predictions' },
            { icon: '🎰', label: 'صندوق الحظ والعجلة', path: '/admin/entertainment' },
          ],
        },
        {
          icon: '💼', label: 'الخدمات',
          children: [
            { icon: '💰', label: 'أبشر', path: '/admin/absher' },
            { icon: '🔄', label: 'تحويل النقاط', path: '/admin/transfer' },
          ],
        },
        {
          icon: '⚙️', label: 'الإعدادات',
          children: [
            { icon: '💬', label: 'الرسائل النصية', path: '/admin/sms' },
            { icon: '🔒', label: 'قيود المستخدمين', path: '/admin/restrictions' },
          ],
        },
      ]
    },
  },
  methods: {
    toggleGroup(label) {
      this.openGroups[label] = !this.openGroups[label]
    },
    logout() {
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin')
      this.$router.push('/login')
    },
  },
}
</script>
