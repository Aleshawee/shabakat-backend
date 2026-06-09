<template>
  <div class="min-h-screen bg-slate-50 pb-20">
    <div class="bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 text-white px-5 pt-5 pb-8">
      <div class="flex items-center gap-3 mb-4">
        <button @click="$router.back()" class="text-white/80 hover:text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <h1 class="text-xl font-bold">المزيد</h1>
      </div>
    </div>

    <div class="px-4 -mt-4 space-y-2">
      <router-link v-for="item in items" :key="item.path" :to="item.path"
        class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.04)] border p-4 flex items-center gap-4 hover:bg-slate-50 transition">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center" :class="item.bgClass">
          <svg class="w-6 h-6" :class="item.iconClass" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.icon"/>
          </svg>
        </div>
        <div>
          <p class="font-bold text-slate-800">{{ item.label }}</p>
          <p class="text-xs text-slate-400">{{ item.desc }}</p>
        </div>
        <svg class="w-5 h-5 text-slate-300 mr-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </router-link>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      items: [
        { path: '/absher', label: 'أبشر (سلفة)', desc: 'احصل على مكافأة فورية', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', bgClass: 'bg-rose-50', iconClass: 'text-rose-500' },
        { path: '/transfer', label: 'تحويل نقاط', desc: 'أرسل نقاط لصديق', icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', bgClass: 'bg-teal-50', iconClass: 'text-teal-500' },
        { path: '/redeem-card', label: 'استبدال كرت', desc: 'أدخل رقم الكرت للحصول على نقاط', icon: 'M3 10h18M3 14h18M3 6h18M3 18h18', bgClass: 'bg-amber-50', iconClass: 'text-amber-500' },
        { path: '/card-info', label: 'معلومات الكروت', desc: 'قيمة كل كارت بالنقاط', icon: 'M3 10h18M3 6h18M3 14h18M3 18h18', bgClass: 'bg-sky-50', iconClass: 'text-sky-500' },
        { path: '/history', label: 'سجل العمليات', desc: 'تتبع حركة النقاط', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', bgClass: 'bg-indigo-50', iconClass: 'text-indigo-500' },
        { path: '/profile', label: 'الملف الشخصي', desc: 'تعديل الاسم وكلمة المرور', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', bgClass: 'bg-emerald-50', iconClass: 'text-emerald-500' },
      ],
    }
  },
}
</script>
