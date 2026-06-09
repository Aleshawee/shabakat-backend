<template>
  <div class="min-h-screen bg-gradient-to-b from-emerald-900 via-slate-900 to-slate-950 pb-20 relative overflow-hidden">
    <!-- Floating Sparkles -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <div v-for="i in 15" :key="i" class="sparkle absolute w-1 h-1 bg-amber-300 rounded-full"
        :style="{
          left: Math.random() * 100 + '%',
          top: Math.random() * 100 + '%',
          animationDelay: Math.random() * 5 + 's',
          animationDuration: (3 + Math.random() * 4) + 's',
        }">
      </div>
    </div>

    <!-- Header -->
    <div class="px-5 pt-5 pb-6 relative z-10">
      <div class="flex items-center gap-3 mb-4">
        <button @click="$router.back()" class="text-white/80 hover:text-white transition-transform hover:scale-110 active:scale-95">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <h1 class="text-xl font-bold text-white">المكافآت المتاحة</h1>
      </div>
      <p class="text-emerald-200/80 text-sm text-center mb-5">استبدل نقاطك بمكافآت قيمة</p>

      <!-- Balance Card -->
      <div class="balance-card relative rounded-3xl p-6 text-center overflow-hidden group">
        <div class="absolute inset-0 balance-glow"></div>
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-amber-400/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-emerald-400/10 rounded-full blur-3xl"></div>
        <div class="relative z-10">
          <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-amber-400/20 border border-amber-400/30 mb-3">
            <svg class="w-7 h-7 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          </div>
          <p class="text-emerald-200/70 text-sm">رصيدك الحالي</p>
          <p class="text-5xl font-black text-white mt-1 balance-number">{{ balance ?? 0 }}</p>
          <p class="text-sm text-emerald-200/70">نقطة</p>
        </div>
      </div>
    </div>

    <!-- Rewards Grid -->
    <div class="px-4 relative z-10">
      <div v-if="loading" class="text-center py-16">
        <div class="w-14 h-14 border-4 border-emerald-400/30 border-t-emerald-400 rounded-full animate-spin mx-auto shadow-lg shadow-emerald-400/10"></div>
        <p class="text-emerald-200/60 text-sm mt-4">جاري تحميل المكافآت...</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="(reward, idx) in rewards" :key="reward.id"
          class="reward-card rounded-3xl p-5 text-center relative overflow-hidden transition-all duration-500 cursor-default"
          :class="canAfford(reward) ? 'card-active' : 'card-locked'"
          :style="{ animationDelay: idx * 0.07 + 's' }">

          <!-- Locked overlay -->
          <div v-if="!canAfford(reward)" class="absolute inset-0 bg-slate-900/40 backdrop-blur-[1px] z-10 rounded-3xl flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-300">
            <div class="text-center">
              <div class="w-12 h-12 rounded-full bg-rose-500/20 border border-rose-500/30 flex items-center justify-center mx-auto mb-2">
                <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </div>
              <p class="text-rose-300 text-xs font-medium">تحتاج {{ reward.points_cost - (balance ?? 0) }} نقطة إضافية</p>
            </div>
          </div>

          <!-- Shine line -->
          <div class="shine-line"></div>

          <!-- Top glow bar -->
          <div v-if="canAfford(reward)" class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-l from-emerald-400 via-amber-400 to-teal-400 rounded-t-3xl glow-bar"></div>

          <!-- Icon -->
          <div class="relative w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-3 mt-2"
            :class="canAfford(reward) ? 'icon-glow' : 'icon-dim'">
            <div class="absolute inset-0 rounded-2xl"
              :class="canAfford(reward) ? 'bg-gradient-to-br from-emerald-400/30 to-teal-500/30' : 'bg-white/5'">
            </div>
            <svg class="w-8 h-8 relative z-10" :class="canAfford(reward) ? 'text-emerald-300' : 'text-white/30'"
              fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
            </svg>
          </div>

          <!-- Name -->
          <h3 class="font-bold text-white text-base mb-2" :class="{ 'text-white/50': !canAfford(reward) }">{{ reward.name }}</h3>

          <!-- Points Badge -->
          <div class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full mb-4"
            :class="canAfford(reward) ? 'bg-emerald-400/10 border border-emerald-400/20' : 'bg-white/5 border border-white/10'">
            <svg class="w-4 h-4" :class="canAfford(reward) ? 'text-amber-400' : 'text-white/30'" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            <span class="font-bold text-sm" :class="canAfford(reward) ? 'text-white' : 'text-white/40'">{{ reward.points_cost }} نقطة</span>
          </div>

          <!-- Action Button -->
          <button v-if="canAfford(reward)" @click="redeem(reward)"
            :disabled="redeeming === reward.id"
            class="w-full py-3 rounded-xl font-bold text-white relative overflow-hidden group/btn transition-all duration-300 active:scale-[0.97]"
            :class="redeeming === reward.id ? 'opacity-70' : 'shadow-lg shadow-emerald-500/20 hover:shadow-emerald-400/40'">
            <div class="absolute inset-0 bg-gradient-to-l from-emerald-500 via-emerald-400 to-teal-500 opacity-90 group-hover/btn:opacity-100 transition-opacity"></div>
            <div class="absolute inset-0 bg-[length:200%_100%] bg-gradient-to-r from-transparent via-white/20 to-transparent shine-btn"></div>
            <span class="relative z-10" v-if="redeeming === reward.id">
              <svg class="animate-spin h-4 w-4 inline ml-1" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              جاري...
            </span>
            <span class="relative z-10" v-else>
              <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              استبدال
            </span>
          </button>

          <button v-else disabled
            class="w-full py-3 rounded-xl font-bold text-white/30 bg-white/5 border border-white/10 cursor-not-allowed text-sm">
            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            غير متاح
          </button>
        </div>
      </div>

      <div v-if="!loading && rewards.length === 0" class="bg-white/5 backdrop-blur-xl rounded-3xl p-12 text-center border border-white/10">
        <div class="text-6xl mb-4">🎁</div>
        <p class="text-white/60 font-medium">لا توجد مكافآت متاحة حاليًا</p>
        <p class="text-white/30 text-sm mt-2">تحقق لاحقًا</p>
      </div>
    </div>

    <!-- Redeem Result Modal -->
    <div v-if="redeemResult" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="redeemResult = null">
      <div class="bg-gradient-to-b from-emerald-800/90 via-slate-900 to-slate-950 border border-emerald-500/20 rounded-3xl p-6 w-full max-w-sm text-center animate-modal relative overflow-hidden shadow-2xl shadow-emerald-500/10">
        <div class="absolute -top-20 -right-20 w-60 h-60 bg-emerald-400/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 w-60 h-60 bg-amber-400/5 rounded-full blur-3xl"></div>
        <div class="relative z-10">
          <div class="w-20 h-20 rounded-full bg-gradient-to-br from-emerald-400/30 to-teal-500/30 border border-emerald-400/30 flex items-center justify-center mx-auto mb-4 animate-bounce-in">
            <svg class="w-10 h-10 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h3 class="text-xl font-black text-white mb-2">تم الاستبدال بنجاح!</h3>
          <div class="bg-white/5 rounded-2xl p-4 mb-4 border border-emerald-500/10">
            <p class="text-xs text-emerald-200/60 mb-1">رمز المكافأة</p>
            <p class="text-lg font-bold text-emerald-300 font-mono tracking-wider" dir="ltr">{{ redeemResult.code }}</p>
            <button @click="copyCode(redeemResult.code)" class="mt-2 text-xs text-emerald-400/80 hover:text-emerald-300 transition-colors">
              <svg class="w-3.5 h-3.5 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
              </svg>
              نسخ الرمز
            </button>
          </div>
          <div class="bg-white/5 rounded-2xl p-4 mb-4 border border-amber-500/10">
            <p class="text-xs text-emerald-200/60">الرصيد المتبقي</p>
            <p class="text-2xl font-black text-amber-400">{{ newBalance }} نقطة</p>
          </div>
          <button @click="redeemResult = null; loadRewards()"
            class="w-full py-3 rounded-2xl font-bold text-white bg-gradient-to-l from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 transition-all active:scale-[0.97] shadow-lg shadow-emerald-500/20">
            حسناً
          </button>
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
      rewards: [], balance: 0, loading: true,
      redeemResult: null, newBalance: 0, redeeming: null,
    }
  },
  mounted() { this.loadRewards() },
  methods: {
    canAfford(reward) {
      return (this.balance ?? 0) >= reward.points_cost
    },
    async loadRewards() {
      this.loading = true
      try {
        const profile = await axios.get('/api/v1/user/profile')
        this.balance = profile.data?.points_balance ?? profile.data?.user?.points_balance ?? 0
        const r = await axios.get('/api/v1/rewards')
        this.rewards = r.data.rewards
      } catch (e) { console.error(e) }
      finally { this.loading = false }
    },
    async redeem(reward) {
      if (!this.canAfford(reward)) return
      this.redeeming = reward.id
      try {
        const r = await axios.post(`/api/v1/rewards/${reward.id}/redeem`)
        this.redeemResult = r.data.card
        this.newBalance = r.data.points_balance
        this.balance = r.data.points_balance
      } catch (e) {
        alert(e.response?.data?.message || 'فشل الاستبدال')
      }
      finally { this.redeeming = null }
    },
    async copyCode(code) {
      try {
        await navigator.clipboard.writeText(code)
        alert('✅ تم نسخ الرمز')
      } catch {
        alert('❌ فشل النسخ')
      }
    },
  },
}
</script>

<style scoped>
/* Sparkle animation */
.sparkle {
  animation: sparkle-float 4s ease-in-out infinite;
  box-shadow: 0 0 6px 2px rgba(251, 191, 36, 0.3);
}
@keyframes sparkle-float {
  0%, 100% { opacity: 0; transform: translateY(0) scale(0); }
  50% { opacity: 1; transform: translateY(-40px) scale(1.2); }
}

/* Balance card */
.balance-card {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.05));
  border: 1px solid rgba(16, 185, 129, 0.2);
  box-shadow: 0 8px 32px rgba(16, 185, 129, 0.08);
}
.balance-glow {
  background: radial-gradient(ellipse at 50% 0%, rgba(251, 191, 36, 0.08) 0%, transparent 70%);
}
.balance-number {
  text-shadow: 0 0 30px rgba(251, 191, 36, 0.2);
}

/* Rewards cards */
.reward-card {
  opacity: 0;
  animation: card-appear 0.5s ease-out forwards;
}
@keyframes card-appear {
  from { opacity: 0; transform: translateY(20px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
.card-active {
  background: linear-gradient(145deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));
  border: 1px solid rgba(16, 185, 129, 0.25);
  box-shadow: 0 8px 32px rgba(16, 185, 129, 0.06), inset 0 1px 0 rgba(255,255,255,0.1);
}
.card-active:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 48px rgba(16, 185, 129, 0.12), inset 0 1px 0 rgba(255,255,255,0.15);
  border-color: rgba(16, 185, 129, 0.4);
}
.card-locked {
  background: linear-gradient(145deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01));
  border: 1px solid rgba(255,255,255,0.06);
}
.card-locked:hover {
  border-color: rgba(255,255,255,0.12);
}

/* Shine line */
.shine-line {
  position: absolute;
  top: 0;
  left: -100%;
  width: 60%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.04), transparent);
  transform: skewX(-20deg);
  animation: shine-sweep 6s ease-in-out infinite;
  pointer-events: none;
}
@keyframes shine-sweep {
  0% { left: -100%; }
  20% { left: 150%; }
  100% { left: 150%; }
}

/* Glow bar */
.glow-bar {
  animation: glow-pulse 2s ease-in-out infinite;
}
@keyframes glow-pulse {
  0%, 100% { opacity: 0.6; box-shadow: 0 0 8px rgba(16, 185, 129, 0.3); }
  50% { opacity: 1; box-shadow: 0 0 16px rgba(251, 191, 36, 0.4); }
}

/* Icon glow */
.icon-glow {
  box-shadow: 0 0 20px rgba(16, 185, 129, 0.15), 0 0 40px rgba(16, 185, 129, 0.05);
}
.icon-dim {
  opacity: 0.5;
}

/* Button shine */
.shine-btn {
  animation: btn-shine 3s ease-in-out infinite;
}
@keyframes btn-shine {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Modal */
.animate-modal {
  animation: modal-in 0.35s ease-out;
}
@keyframes modal-in {
  from { opacity: 0; transform: scale(0.85) translateY(10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
.animate-bounce-in {
  animation: bounce-in 0.6s ease-out 0.1s both;
}
@keyframes bounce-in {
  0% { opacity: 0; transform: scale(0.3); }
  50% { transform: scale(1.1); }
  70% { transform: scale(0.95); }
  100% { opacity: 1; transform: scale(1); }
}
</style>
