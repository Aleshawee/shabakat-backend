<template>
  <div class="min-h-screen bg-slate-50 pb-20">
    <div class="bg-gradient-to-br from-emerald-700 via-emerald-800 to-slate-900 text-white px-5 pt-5 pb-16 relative overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(255,255,255,0.06),transparent_60%)]"></div>
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-4">
          <button @click="$router.back()" class="text-white/80 hover:text-white p-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <h1 class="text-xl font-bold">معلومات الكروت</h1>
        </div>
        <p class="text-emerald-200 text-sm">تعرف على قيمة كل كارت بالنقاط</p>
      </div>
    </div>

    <div class="px-4 -mt-10 relative z-20">
      <div v-if="loading" class="text-center py-16 bg-white rounded-2xl shadow-sm border">
        <div class="w-12 h-12 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin mx-auto"></div>
        <p class="text-slate-400 text-sm mt-3">جاري التحميل...</p>
      </div>

      <div v-else-if="categories.length === 0" class="bg-white rounded-2xl shadow-sm border p-12 text-center">
        <div class="text-6xl mb-4">💳</div>
        <p class="text-slate-500 font-medium">لا توجد فئات متاحة</p>
      </div>

      <div v-else class="grid grid-cols-2 gap-4">
        <div v-for="cat in categories" :key="cat.id"
          class="bg-white rounded-2xl shadow-sm border overflow-hidden hover:shadow-md transition-shadow">
          <div class="bg-gradient-to-br from-emerald-50 to-teal-50 px-4 pt-6 pb-4 text-center border-b border-emerald-100">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center mx-auto mb-3 shadow-lg shadow-emerald-200">
              <span class="text-white text-2xl font-black">{{ cat.points || cat.name }}</span>
            </div>
            <h3 class="font-bold text-slate-800">كرت أبو {{ cat.name }}</h3>
          </div>
          <div class="px-4 py-4 text-center">
            <p class="text-xs text-slate-400 mb-1">عند استبدال الكرت تحصل على</p>
            <p class="text-xl font-black text-emerald-600">{{ cat.points }} <span class="text-sm font-medium">نقطة</span></p>
          </div>
          <div class="px-4 pb-4">
            <div class="bg-emerald-50 rounded-xl px-3 py-2 flex items-center justify-between text-xs">
              <span class="text-slate-500">رمز الفئة</span>
              <span class="font-bold text-emerald-700" dir="ltr">#{{ cat.id }}</span>
            </div>
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
      categories: [],
      loading: true,
    }
  },
  mounted() {
    this.loadCategories()
  },
  methods: {
    async loadCategories() {
      this.loading = true
      try {
        const r = await axios.get('/api/v1/categories')
        this.categories = r.data.categories || []
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
  },
}
</script>
