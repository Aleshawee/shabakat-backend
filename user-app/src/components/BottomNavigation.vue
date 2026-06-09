<template>
  <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 safe-area-bottom z-50 shadow-[0_-2px_10px_rgba(0,0,0,0.05)]">
    <div class="flex justify-around items-center h-16 max-w-lg mx-auto">
      <router-link v-for="item in items" :key="item.path" :to="item.path"
        class="flex flex-col items-center justify-center w-full h-full relative"
        :class="$route.path === item.path ? 'text-emerald-600' : 'text-slate-400'">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.icon"/>
        </svg>
        <span class="text-[10px] mt-0.5 font-medium">{{ item.label }}</span>
      </router-link>
    </div>
  </nav>
</template>

<script>
export default {
  data() {
    return {
      items: [
        { path: '/dashboard', label: 'الرئيسية', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
        { path: '/rewards', label: 'المكافآت', icon: 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7' },
        { path: '/lucky-box', label: 'صندوق الحظ', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
        { path: '/lucky-wheel', label: 'عجلة الحظ', icon: 'M12 6V2m0 4a8 8 0 100 16 8 8 0 000-16zM2 12h4m12 0h4M4.93 4.93l2.83 2.83m8.48-2.83l-2.83 2.83' },
        { path: '/more', label: 'المزيد', icon: 'M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z M12 11v2m0 2h.01M12 7h.01' },
      ],
    }
  },
}
</script>
