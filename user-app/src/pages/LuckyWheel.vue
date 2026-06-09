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
          <h1 class="text-xl font-bold">عجلة الحظ</h1>
        </div>
        <p class="text-emerald-200 text-sm">دَوّر العجلة واربح جوائز رائعة</p>
      </div>
    </div>

    <div class="px-4 -mt-10 relative z-20 space-y-4">
      <!-- Stats row -->
      <div class="grid grid-cols-2 gap-3">
        <div class="bg-white rounded-2xl shadow border border-slate-100 p-4 text-center">
          <p class="text-slate-500 text-xs mb-1">تكلفة اللفة</p>
          <p class="text-slate-800 font-bold text-lg">{{ wheel?.point_cost || 0 }} <span class="text-xs font-normal text-emerald-600">نقطة</span></p>
        </div>
        <div class="bg-white rounded-2xl shadow border border-slate-100 p-4 text-center">
          <p class="text-slate-500 text-xs mb-1">عدد لفاتك</p>
          <p class="text-slate-800 font-bold text-lg">{{ wheel?.today_spins || 0 }}</p>
        </div>
      </div>

      <!-- Countdown -->
      <div v-if="showCountdown" class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center">
        <p class="text-amber-700 text-xs mb-2">⏳ اللفة المجانية ستكون متاحة بعد</p>
        <p class="text-3xl font-black text-amber-600 tracking-wider" dir="ltr">{{ countdown }}</p>
        <p class="text-amber-500/60 text-xs mt-1">تحتاج 24 ساعة من آخر لفة</p>
      </div>

      <!-- Wheel -->
      <div class="bg-white rounded-2xl shadow border border-slate-100 p-6">
        <div v-if="!wheel && !loading" class="text-center py-10 text-slate-400 text-sm">
          لا توجد عجلة متاحة
        </div>
        <template v-if="wheel">
          <div class="flex justify-center">
            <div class="relative" style="pointer-events: auto;">
              <div class="absolute -top-3 left-1/2 -translate-x-1/2 z-20">
                <svg class="w-8 h-8 text-emerald-500 drop-shadow" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2L2 22h20L12 2z"/>
                </svg>
              </div>
              <canvas ref="wheelCanvas" width="300" height="300"
                class="block cursor-pointer"
                :class="{ 'cursor-not-allowed': !canSpin }"
                @click="handleWheelClick"
                @touchstart.prevent="handleWheelClick">
              </canvas>
              <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 border-4 border-white/50 flex items-center justify-center shadow-lg">
                  <span class="text-white font-black text-sm">{{ spinning ? '...' : 'GO' }}</span>
                </div>
              </div>
            </div>
          </div>

          <button @click="handleWheelClick"
            :disabled="!canSpin"
            class="w-full mt-4 py-3.5 rounded-xl font-bold text-white transition-all"
            :class="canSpin ? 'bg-gradient-to-l from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow active:scale-[0.98]' : 'bg-slate-300 cursor-not-allowed'">
          <span v-if="spinning" class="flex items-center justify-center gap-2">
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            جاري التدوير...
          </span>
          <span v-else-if="wheel?.can_spin_free">🎰 لفة مجانية!</span>
          <span v-else-if="!canSpin && !hasBalance">💰 رصيد غير كافٍ ({{ wheel?.point_cost || 0 }} نقطة)</span>
          <span v-else>🎰 تدوير بـ {{ wheel?.point_cost || 0 }} نقطة</span>
          </button>
        </template>
      </div>
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
export default {
  data() {
    return {
      wheel: null,
      balance: 0,
      loading: true,
      spinning: false,
      result: null,
      newBalance: 0,
      cardCode: null,
      copied: false,
      rotation: 0,
      countdown: '00:00:00',
      countdownInterval: null,
    }
  },
  computed: {
    canSpin() {
      if (!this.wheel) return false
      if (this.spinning) return false
      if (this.wheel.remaining_spins === 0) return false
      if (this.wheel.spin_mode === 'daily_free_only' && !this.wheel.can_spin_free) return false
      if (!this.wheel.can_spin_free && this.balance < this.wheel.point_cost) return false
      return true
    },
    hasBalance() {
      return this.balance >= (this.wheel?.point_cost || 0)
    },
    showCountdown() {
      return this.wheel?.last_spin_at && !this.wheel?.can_spin_free
    },
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
  mounted() {
    this.loadWheel()
  },
  beforeUnmount() {
    if (this.countdownInterval) clearInterval(this.countdownInterval)
  },
  methods: {
    async loadWheel() {
      this.loading = true
      try {
        const [wheelRes, profileRes] = await Promise.all([
          axios.get('/api/v1/lucky-wheels'),
          axios.get('/api/v1/user/profile'),
        ])
        if (wheelRes.data.wheels.length > 0) {
          this.wheel = wheelRes.data.wheels[0]
          this.balance = profileRes.data?.points_balance ?? profileRes.data?.user?.points_balance ?? 0
          this.$nextTick(() => this.drawWheel())
        }
      } catch (e) { console.error(e) }
      finally { this.loading = false }
    },
    drawWheel(rotation = 0) {
      const canvas = this.$refs.wheelCanvas
      if (!canvas || !this.wheel) return

      const ctx = canvas.getContext('2d')
      const w = canvas.width
      const h = canvas.height
      const cx = w / 2
      const cy = h / 2
      const r = Math.min(cx, cy) - 12

      ctx.clearRect(0, 0, w, h)

      const prizes = this.wheel.prizes
      if (!prizes || prizes.length === 0) {
        ctx.beginPath()
        ctx.arc(cx, cy, r, 0, Math.PI * 2)
        ctx.fillStyle = '#e2e8f0'
        ctx.fill()
        ctx.fillStyle = '#64748b'
        ctx.font = 'bold 14px Tajawal'
        ctx.textAlign = 'center'
        ctx.fillText('لا توجد جوائز', cx, cy + 5)
        return
      }

      // Outer ring
      ctx.beginPath()
      ctx.arc(cx, cy, r + 6, 0, Math.PI * 2)
      ctx.fillStyle = '#065f46'
      ctx.fill()
      ctx.strokeStyle = '#10b981'
      ctx.lineWidth = 3
      ctx.stroke()

      const totalWeight = prizes.reduce((s, p) => s + p.weight, 0) || 1
      let currentAngle = rotation - Math.PI / 2

      prizes.forEach((prize) => {
        const sliceAngle = (prize.weight / totalWeight) * Math.PI * 2

        ctx.beginPath()
        ctx.moveTo(cx, cy)
        ctx.arc(cx, cy, r, currentAngle, currentAngle + sliceAngle)
        ctx.closePath()

        const grad2 = ctx.createRadialGradient(cx, cy, 0, cx, cy, r)
        const c1 = this.lightenColor(prize.color || '#10b981', 40)
        const c2 = this.darkenColor(prize.color || '#10b981', 20)
        grad2.addColorStop(0, c1)
        grad2.addColorStop(1, c2)
        ctx.fillStyle = grad2
        ctx.fill()

        ctx.strokeStyle = 'rgba(255,255,255,0.35)'
        ctx.lineWidth = 2
        ctx.stroke()

        ctx.save()
        ctx.translate(cx, cy)
        ctx.rotate(currentAngle + sliceAngle / 2)
        ctx.textAlign = 'center'
        ctx.fillStyle = '#fff'
        ctx.shadowColor = 'rgba(0,0,0,0.6)'
        ctx.shadowBlur = 4

        let icon = '⭐'
        if (prize.type === 'card') icon = '💳'
        else if (prize.type === 'nothng') icon = '😅'
        ctx.font = 'bold 18px Tajawal'
        ctx.fillText(icon, r * 0.62, -8)

        ctx.font = 'bold 12px Tajawal'
        ctx.fillText(prize.name, r * 0.62, 14)

        if (prize.value && prize.type === 'point') {
          ctx.font = '11px Tajawal'
          ctx.fillText(`${prize.value} نقطة`, r * 0.62, 28)
        }

        ctx.restore()
        currentAngle += sliceAngle
      })

      // Center hub
      const hubGrad = ctx.createRadialGradient(cx, cy, 0, cx, cy, 30)
      hubGrad.addColorStop(0, '#10b981')
      hubGrad.addColorStop(1, '#065f46')
      ctx.beginPath()
      ctx.arc(cx, cy, 30, 0, Math.PI * 2)
      ctx.fillStyle = hubGrad
      ctx.fill()
      ctx.strokeStyle = '#f59e0b'
      ctx.lineWidth = 3
      ctx.stroke()

      ctx.beginPath()
      ctx.arc(cx, cy, 22, 0, Math.PI * 2)
      ctx.fillStyle = '#0d3b2e'
      ctx.fill()
    },
    darkenColor(hex, percent) {
      const num = parseInt(hex.replace('#', ''), 16)
      const amt = Math.round(2.55 * percent)
      const R = Math.max(0, (num >> 16) - amt)
      const G = Math.max(0, ((num >> 8) & 0x00FF) - amt)
      const B = Math.max(0, (num & 0x0000FF) - amt)
      return `rgb(${R},${G},${B})`
    },
    lightenColor(hex, percent) {
      const num = parseInt(hex.replace('#', ''), 16)
      const amt = Math.round(2.55 * percent)
      const R = Math.min(255, (num >> 16) + amt)
      const G = Math.min(255, ((num >> 8) & 0x00FF) + amt)
      const B = Math.min(255, (num & 0x0000FF) + amt)
      return `rgb(${R},${G},${B})`
    },
    handleWheelClick() {
      if (!this.canSpin) return
      this.spin()
    },
    async spin() {
      this.spinning = true
      this.cardCode = null; this.copied = false
      const canvas = this.$refs.wheelCanvas
      if (canvas) {
        await this.animateSpin(canvas)
      }
      try {
        const r = await axios.post(`/api/v1/lucky-wheels/${this.wheel.id}/spin`)
        this.result = r.data.prize
        this.cardCode = r.data.card_code || null
        this.newBalance = r.data.points_balance
        this.balance = r.data.points_balance
        this.wheel = { ...this.wheel, ...r.data.wheel }
      } catch (e) {
        alert(e.response?.data?.message || 'حدث خطأ')
      }
      finally {
        this.spinning = false
        this.$nextTick(() => this.drawWheel(this.rotation))
      }
    },
    closeResult() {
      this.result = null
      this.cardCode = null
      this.loadWheel()
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
    animateSpin(canvas) {
      return new Promise((resolve) => {
        const ctx = canvas.getContext('2d')
        const w = canvas.width
        const h = canvas.height
        const cx = w / 2
        const cy = h / 2
        const r = Math.min(cx, cy) - 12
        const prizes = this.wheel.prizes
        const totalWeight = prizes.reduce((s, p) => s + p.weight, 0) || 1

        const spinDuration = 4000
        const totalRotation = Math.PI * 2 * (6 + Math.random() * 4)
        const startTime = performance.now()
        const startAngle = this.rotation

        const animate = (currentTime) => {
          const elapsed = currentTime - startTime
          const progress = Math.min(elapsed / spinDuration, 1)
          const ease = 1 - Math.pow(1 - progress, 3)
          const currentAngle = startAngle + totalRotation * ease

          ctx.clearRect(0, 0, w, h)

          ctx.beginPath()
          ctx.arc(cx, cy, r + 6, 0, Math.PI * 2)
          ctx.fillStyle = '#065f46'
          ctx.fill()
          ctx.strokeStyle = '#10b981'
          ctx.lineWidth = 3
          ctx.stroke()

          let angle = currentAngle
          prizes.forEach((prize) => {
            const sliceAngle = (prize.weight / totalWeight) * Math.PI * 2

            ctx.beginPath()
            ctx.moveTo(cx, cy)
            ctx.arc(cx, cy, r, angle, angle + sliceAngle)
            ctx.closePath()

            const grad2 = ctx.createRadialGradient(cx, cy, 0, cx, cy, r)
            const c1 = this.lightenColor(prize.color || '#10b981', 40)
            const c2 = this.darkenColor(prize.color || '#10b981', 20)
            grad2.addColorStop(0, c1)
            grad2.addColorStop(1, c2)
            ctx.fillStyle = grad2
            ctx.fill()

            ctx.strokeStyle = 'rgba(255,255,255,0.35)'
            ctx.lineWidth = 2
            ctx.stroke()

            ctx.save()
            ctx.translate(cx, cy)
            ctx.rotate(angle + sliceAngle / 2)
            ctx.textAlign = 'center'
            ctx.fillStyle = '#fff'
            ctx.shadowColor = 'rgba(0,0,0,0.6)'
            ctx.shadowBlur = 4

            let icon = '⭐'
            if (prize.type === 'card') icon = '💳'
            else if (prize.type === 'nothng') icon = '😅'
            ctx.font = 'bold 18px Tajawal'
            ctx.fillText(icon, r * 0.62, -8)

            ctx.font = 'bold 12px Tajawal'
            ctx.fillText(prize.name, r * 0.62, 14)

            if (prize.value && prize.type === 'point') {
              ctx.font = '11px Tajawal'
              ctx.fillText(`${prize.value} نقطة`, r * 0.62, 28)
            }

            ctx.restore()
            angle += sliceAngle
          })

          const hubGrad = ctx.createRadialGradient(cx, cy, 0, cx, cy, 30)
          hubGrad.addColorStop(0, '#10b981')
          hubGrad.addColorStop(1, '#065f46')
          ctx.beginPath()
          ctx.arc(cx, cy, 30, 0, Math.PI * 2)
          ctx.fillStyle = hubGrad
          ctx.fill()
          ctx.strokeStyle = '#f59e0b'
          ctx.lineWidth = 3
          ctx.stroke()
          ctx.beginPath()
          ctx.arc(cx, cy, 22, 0, Math.PI * 2)
          ctx.fillStyle = '#0d3b2e'
          ctx.fill()

          if (progress < 1) {
            requestAnimationFrame(animate)
          } else {
            this.rotation = currentAngle % (Math.PI * 2)
            resolve()
          }
        }

        requestAnimationFrame(animate)
      })
    },
    startCountdown() {
      if (this.countdownInterval) clearInterval(this.countdownInterval)
      if (!this.wheel?.last_spin_at) return

      const lastSpin = new Date(this.wheel.last_spin_at).getTime()
      const nextFree = lastSpin + 24 * 60 * 60 * 1000

      const update = () => {
        const now = Date.now()
        const diff = nextFree - now

        if (diff <= 0) {
          this.countdown = '00:00:00'
          clearInterval(this.countdownInterval)
          this.loadWheel()
          return
        }

        const hours = Math.floor(diff / (1000 * 60 * 60))
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
        const seconds = Math.floor((diff % (1000 * 60)) / 1000)

        this.countdown = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
      }

      update()
      this.countdownInterval = setInterval(update, 1000)
    },
  },
  watch: {
    wheel: {
      handler() {
        this.startCountdown()
      },
      deep: true,
    },
  },
}
</script>

<style scoped>
@keyframes modal {
  from { opacity: 0; transform: scale(0.9); }
  to { opacity: 1; transform: scale(1); }
}
.animate-modal { animation: modal 0.3s ease-out; }
</style>
