<template>
  <div class="min-h-screen bg-slate-50 pb-24">
    <div class="bg-gradient-to-br from-emerald-700 via-emerald-800 to-slate-900 text-white px-5 pt-5 pb-14 relative overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(255,255,255,0.06),transparent_60%)]"></div>
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-4">
          <button @click="$router.back()" class="text-white/80 hover:text-white p-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <h1 class="text-xl font-bold">صندوق الحظ</h1>
        </div>
        <!-- Balance card in header -->
        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-4 border border-white/10 mt-3">
          <div class="flex items-center justify-between">
            <p class="text-emerald-200 text-sm">رصيد النقاط</p>
            <svg class="w-5 h-5 text-amber-300" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          </div>
          <p class="text-2xl font-bold mt-1">{{ balance || 0 }} <span class="text-sm font-normal text-emerald-200">نقطة</span></p>
        </div>
      </div>
    </div>

    <div class="px-4 -mt-10 relative z-20 space-y-4">
      <template v-if="loading">
        <div class="bg-white rounded-2xl shadow border border-slate-100 py-20 text-center">
          <div class="w-10 h-10 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin mx-auto"></div>
          <p class="text-slate-400 text-sm mt-3">جاري التحميل...</p>
        </div>
      </template>

      <template v-else-if="boxes.length === 0">
        <div class="bg-white rounded-2xl shadow border border-slate-100 p-12 text-center">
          <p class="text-slate-500 font-medium">لا توجد صناديق متاحة</p>
          <p class="text-xs text-slate-400 mt-1">الميزة غير مفعلة أو لا توجد صناديق</p>
        </div>
      </template>

      <template v-else>
        <div v-for="box in boxes" :key="box.id"
          class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">

          <!-- Probability toggle -->
          <button @click="toggleProb(box.id)"
            class="w-full text-xs text-slate-400 hover:text-slate-600 flex items-center justify-center gap-1 pt-3 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            عرض الاحتمالات
            <svg class="w-3 h-3 transition" :class="{ 'rotate-180': showProb === box.id }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>

          <div v-if="showProb === box.id" class="bg-slate-50 px-4 pb-3 space-y-2">
            <p class="text-xs font-bold text-slate-600 pt-2">نسبة كل جائزة:</p>
            <div v-for="p in box.prizes" :key="p.id" class="flex items-center gap-3">
              <span class="text-xs text-slate-600 w-16 truncate">{{ p.name }}</span>
              <div class="flex-1 h-2.5 bg-slate-200 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500" :style="{ width: calcWeight(p, box) + '%', backgroundColor: box.color || '#10b981' }"></div>
              </div>
              <span class="text-xs font-bold text-slate-500 w-10 text-left">{{ calcWeight(p, box) }}%</span>
            </div>
          </div>

          <!-- Box visual -->
          <div class="p-6 pt-4">
            <div class="flex flex-col items-center text-center">
              <div @click="play(box)" :disabled="box.remaining_plays === 0 || playing === box.id"
                class="relative cursor-pointer group"
                :class="{ 'opacity-40 cursor-not-allowed': box.remaining_plays === 0, 'animate-wiggle': playing !== box.id }">
                <svg viewBox="0 0 200 180" class="w-36 h-36 drop-shadow-xl transition-transform duration-200 group-active:scale-95">
                  <ellipse cx="100" cy="170" rx="80" ry="8" fill="rgba(0,0,0,0.08)"/>
                  <rect x="20" y="75" width="160" height="90" rx="8"
                    :fill="box.color || '#10b981'"
                    :stroke="darken(box.color || '#10b981', 30)"
                    stroke-width="2"/>
                  <path d="M18 75 Q18 35 60 25 Q100 15 140 25 Q182 35 182 75 Z"
                    :fill="lighten(box.color || '#10b981', 20)"
                    :stroke="darken(box.color || '#10b981', 30)"
                    stroke-width="2"/>
                  <line x1="18" y1="72" x2="182" y2="72"
                    :stroke="darken(box.color || '#10b981', 40)"
                    stroke-width="3"/>
                  <rect x="85" y="68" width="30" height="22" rx="5"
                    :fill="box.color || '#10b981'"
                    :stroke="darken(box.color || '#10b981', 40)"
                    stroke-width="2"/>
                  <rect x="93" y="75" width="14" height="8" rx="2"
                    :fill="darken(box.color || '#10b981', 50)"/>
                  <circle cx="100" cy="80" r="2" :fill="getContrastText(box.color || '#10b981')"/>
                  <rect x="20" y="115" width="160" height="6" rx="2" fill="rgba(255,215,0,0.4)"/>
                  <rect x="20" y="140" width="160" height="6" rx="2" fill="rgba(255,215,0,0.4)"/>
                  <circle cx="50" cy="95" r="3" fill="rgba(255,215,0,0.6)">
                    <animate attributeName="opacity" values="0.6;0.2;0.6" dur="2s" repeatCount="indefinite"/>
                  </circle>
                  <circle cx="150" cy="100" r="2.5" fill="rgba(255,215,0,0.5)">
                    <animate attributeName="opacity" values="0.5;0.1;0.5" dur="2.5s" repeatCount="indefinite"/>
                  </circle>
                  <circle cx="100" cy="50" r="2" fill="rgba(255,215,0,0.7)">
                    <animate attributeName="opacity" values="0.7;0.2;0.7" dur="1.8s" repeatCount="indefinite"/>
                  </circle>
                  <text x="100" y="68" text-anchor="middle" font-size="14" font-weight="bold"
                    :fill="getContrastText(box.color || '#10b981')" class="group-hover:opacity-100 opacity-0 transition-opacity">?</text>
                </svg>
                <div class="absolute -top-2 -left-2 bg-white rounded-xl shadow border px-3 py-1.5 text-sm font-bold"
                  :style="{ borderColor: box.color || '#10b981', color: box.color || '#10b981' }">
                  {{ box.cost }} نقطة
                </div>
                <div v-if="box.daily_limit > 0" class="absolute -top-2 -right-2 bg-white rounded-xl shadow border px-3 py-1.5 text-xs font-bold"
                  :class="box.remaining_plays > 0 ? 'text-emerald-600 border-emerald-300' : 'text-red-500 border-red-300'">
                  {{ box.remaining_plays }}/{{ box.daily_limit }}
                </div>
                <div v-else class="absolute -top-2 -right-2 bg-white rounded-xl shadow border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-500">
                  ♾ غير محدود
                </div>
              </div>

              <h3 class="text-lg font-bold text-slate-800 mt-4" :style="{ color: box.color || '#1e293b' }">{{ box.name }}</h3>

              <div class="flex gap-2 flex-wrap justify-center mt-2 mb-4">
                <span v-for="p in box.prizes.slice(0, 3)" :key="p.id"
                  class="px-2.5 py-1 rounded-full text-[11px] font-medium"
                  :class="prizeBadge(p.type)">
                  {{ p.name }}
                </span>
                <span v-if="box.prizes.length > 3" class="text-[11px] text-slate-400 flex items-center">+{{ box.prizes.length - 3 }}</span>
              </div>

              <button @click="play(box)"
                :disabled="box.remaining_plays === 0 || playing === box.id"
                class="w-full py-3.5 rounded-xl font-bold text-white transition-all disabled:opacity-40 disabled:cursor-not-allowed active:scale-[0.98] bg-gradient-to-l from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow">
                <span v-if="playing === box.id" class="flex items-center justify-center gap-2">
                  <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  جاري...
                </span>
                <span v-else>
                  <template v-if="box.remaining_plays === 0">اكتفيت اليومي</template>
                  <template v-else>افتح الصندوق</template>
                </span>
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Result Modal -->
    <div v-if="result" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" @click.self="closeResult">
      <div class="bg-white rounded-3xl p-6 w-full max-w-sm text-center animate-modal shadow-2xl">
        <div class="text-6xl mb-4">{{ resultIcon }}</div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">{{ result.name }}</h3>
        <p class="text-sm text-slate-500 mb-4">{{ resultValue }}</p>

        <div v-if="cardCode" class="bg-gradient-to-l from-amber-50 to-yellow-50 border border-amber-200 rounded-2xl p-4 mb-4">
          <p class="text-xs text-amber-600 mb-2">🎴 رقم الكرت</p>
          <p class="text-xl font-bold text-slate-800 font-mono tracking-wider ltr text-center" dir="ltr">{{ cardCode }}</p>
          <button @click="copyCode(cardCode)"
            class="mt-2 inline-flex items-center gap-1.5 text-sm text-amber-700 hover:text-amber-900 bg-amber-100 px-4 py-1.5 rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            {{ copied ? 'تم النسخ ✓' : 'نسخ الكود' }}
          </button>
        </div>

        <div class="bg-slate-50 rounded-2xl p-4 mb-4">
          <p class="text-xs text-slate-400">الرصيد المتبقي</p>
          <p class="text-2xl font-bold text-slate-800">{{ newBalance }} نقطة</p>
        </div>
        <button @click="closeResult"
          class="w-full py-3 rounded-xl font-bold text-white bg-gradient-to-l from-emerald-600 to-teal-600 transition active:scale-[0.98]">
          حسناً
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { getContrastText } from '../utils/color.js'
export default {
  data() {
    return {
      boxes: [], balance: 0, loading: true, playing: null,
      result: null, newBalance: 0, boxColor: null,
      cardCode: null, copied: false,
      showProb: null,
    }
  },
  computed: {
    resultIcon() {
      const m = { point: '⭐', card: '💳', nothng: '😅' }
      return m[this.result?.type] || '🎁'
    },
    resultValue() {
      if (!this.result) return ''
      if (this.result.type === 'point') return `${this.result.value} نقطة`
      if (this.result.type === 'card') return `كارت من فئة ${this.result.value}`
      return 'حظ أوفر في المرة القادمة!'
    },
  },
  mounted() { this.loadBoxes() },
  methods: {
    getContrastText,
    lighten(hex, percent) {
      if (!hex) return '#34d399'
      const num = parseInt(hex.replace('#', ''), 16)
      const amt = Math.round(2.55 * percent)
      const R = Math.min(255, (num >> 16) + amt)
      const G = Math.min(255, ((num >> 8) & 0x00FF) + amt)
      const B = Math.min(255, (num & 0x0000FF) + amt)
      return `rgb(${R},${G},${B})`
    },
    darken(hex, percent) {
      if (!hex) return '#059669'
      const num = parseInt(hex.replace('#', ''), 16)
      const amt = Math.round(2.55 * percent)
      const R = Math.max(0, (num >> 16) - amt)
      const G = Math.max(0, ((num >> 8) & 0x00FF) - amt)
      const B = Math.max(0, (num & 0x0000FF) - amt)
      return `rgb(${R},${G},${B})`
    },
    calcWeight(p, box) {
      const total = box.prizes.reduce((s, x) => s + x.weight, 0)
      return total > 0 ? Math.round((p.weight / total) * 100) : 0
    },
    toggleProb(id) {
      this.showProb = this.showProb === id ? null : id
    },
    async loadBoxes() {
      this.loading = true
      try {
        const r = await axios.get('/api/v1/lucky-boxes')
        this.boxes = r.data.boxes
        const profile = await axios.get('/api/v1/user/profile')
        this.balance = profile.data.points_balance
      } catch (e) { console.error(e) }
      finally { this.loading = false }
    },
    async play(box) {
      if (box.remaining_plays === 0) return
      this.playing = box.id; this.result = null; this.boxColor = box.color; this.cardCode = null; this.copied = false
      try {
        const r = await axios.post(`/api/v1/lucky-boxes/${box.id}/play`)
        this.result = r.data.prize
        this.cardCode = r.data.card_code || null
        this.newBalance = r.data.points_balance
        this.balance = r.data.points_balance
      } catch (e) {
        alert(e.response?.data?.message || 'حدث خطأ')
      }
      finally { this.playing = null }
    },
    closeResult() {
      this.result = null
      this.loadBoxes()
    },
    async copyCode(code) {
      try {
        await navigator.clipboard.writeText(code)
        this.copied = true
        setTimeout(() => this.copied = false, 2000)
      } catch {
        const ta = document.createElement('textarea')
        ta.value = code; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta)
        this.copied = true
        setTimeout(() => this.copied = false, 2000)
      }
    },
    prizeBadge(type) {
      const m = { point: 'bg-amber-50 text-amber-700', card: 'bg-teal-50 text-teal-700', nothng: 'bg-slate-100 text-slate-500' }
      return m[type] || 'bg-slate-100 text-slate-600'
    },
  },
}
</script>

<style scoped>
@keyframes modal {
  from { opacity: 0; transform: scale(0.9); }
  to { opacity: 1; transform: scale(1); }
}
@keyframes wiggle {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(-2deg); }
  75% { transform: rotate(2deg); }
}
.animate-modal { animation: modal 0.3s ease-out; }
.animate-wiggle:hover { animation: wiggle 0.5s ease-in-out infinite; }
</style>
