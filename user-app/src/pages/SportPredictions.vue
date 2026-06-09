<template>
  <div class="min-h-screen bg-slate-50 pb-24">
    <div class="bg-gradient-to-br from-emerald-700 via-emerald-800 to-slate-900 text-white px-5 pt-5 pb-16 relative overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(255,255,255,0.06),transparent_60%)]"></div>
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-5">
          <button @click="$router.back()" class="text-white/80 hover:text-white p-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <h1 class="text-xl font-bold">التوقعات الرياضية</h1>
        </div>
        <p class="text-emerald-200 text-sm">توقع نتائج المباريات واربح النقاط</p>
      </div>
    </div>

    <div class="px-4 -mt-10 relative z-20">
      <div class="bg-white rounded-2xl shadow border border-slate-100 p-2 flex gap-1">
        <button @click="tab = 'upcoming'"
          class="flex-1 py-2.5 text-sm font-bold rounded-xl transition"
          :class="tab === 'upcoming' ? 'bg-emerald-600 text-white shadow' : 'text-slate-500 hover:bg-slate-50'">
          القادمة
        </button>
        <button @click="tab = 'my'"
          class="flex-1 py-2.5 text-sm font-bold rounded-xl transition"
          :class="tab === 'my' ? 'bg-emerald-600 text-white shadow' : 'text-slate-500 hover:bg-slate-50'">
          توقعاتي
        </button>
        <button @click="tab = 'history'"
          class="flex-1 py-2.5 text-sm font-bold rounded-xl transition"
          :class="tab === 'history' ? 'bg-emerald-600 text-white shadow' : 'text-slate-500 hover:bg-slate-50'">
          السابق
        </button>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin"></div>
    </div>

    <template v-else>
      <!-- Disabled banner -->
      <div v-if="!enabled" class="mx-4 mt-4">
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-700 text-center">
          الميزة غير مفعلة حالياً — يمكنك الاطلاع على توقعاتك السابقة
        </div>
      </div>

      <!-- Upcoming Events Tab -->
      <div v-if="tab === 'upcoming'" class="px-4 mt-6 space-y-4">
        <div v-if="events.length === 0" class="text-center py-12 text-slate-400">
          <p>لا توجد أحداث متاحة حالياً</p>
        </div>
        <div v-for="event in events" :key="event.id"
          class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
          <div class="p-5">
            <div class="flex items-center justify-between">
              <div class="flex-1 text-center">
                <div class="w-14 h-14 mx-auto mb-2 rounded-2xl bg-emerald-50 flex items-center justify-center text-lg font-bold text-emerald-700" v-if="!event.option_a_image">{{ event.home_team?.charAt(0) }}</div>
                <img v-else :src="event.option_a_image" class="w-14 h-14 mx-auto mb-2 object-contain">
                <p class="text-sm font-bold text-slate-800">{{ event.home_team }}</p>
              </div>
              <div class="px-4 text-center">
                <span class="text-xs font-bold text-slate-400">ضد</span>
              </div>
              <div class="flex-1 text-center">
                <div class="w-14 h-14 mx-auto mb-2 rounded-2xl bg-emerald-50 flex items-center justify-center text-lg font-bold text-emerald-700" v-if="!event.option_b_image">{{ event.away_team?.charAt(0) }}</div>
                <img v-else :src="event.option_b_image" class="w-14 h-14 mx-auto mb-2 object-contain">
                <p class="text-sm font-bold text-slate-800">{{ event.away_team }}</p>
              </div>
            </div>
            <div class="flex items-center justify-center gap-4 mt-4 text-xs text-slate-500">
              <span>⏱ {{ formatDate(event.match_date) }}</span>
              <span>{{ event.prediction_type === 'exact_score' ? '🎯 نتيجة دقيقة' : '🏆 فائز' }}</span>
              <span v-if="event.entry_fee > 0">💰 {{ event.entry_fee }} نقطة (غير مستردة)</span>
              <span v-if="event.reward_per_winner > 0">🏆 {{ event.reward_per_winner }} نقطة</span>
            </div>
          </div>
          <div v-if="event.my_prediction" class="border-t border-slate-100 px-5 py-3 bg-emerald-50/50">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-emerald-600 font-medium">توقعي: <span class="font-bold">{{ formatPrediction(event.my_prediction.prediction, event) }}</span></p>
                <p v-if="event.my_prediction.points_bet > 0" class="text-xs text-emerald-500">رسوم: {{ event.my_prediction.points_bet }} نقطة</p>
              </div>
              <button v-if="allowEdit" @click="editPrediction(event)" class="text-xs text-emerald-600 font-bold hover:text-emerald-700">تعديل</button>
            </div>
          </div>
          <div v-else class="border-t border-slate-100 px-5 py-3">
            <button v-if="enabled" @click="openPredict(event)"
              class="w-full py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 active:scale-[0.98] transition">
              توقع الآن
            </button>
          </div>
        </div>
      </div>

      <!-- My Predictions Tab -->
      <div v-if="tab === 'my'" class="px-4 mt-6 space-y-4">
        <div v-if="myPredictions.length === 0" class="text-center py-12 text-slate-400">
          <p>لم تقدم أي توقعات بعد</p>
        </div>
        <div v-for="p in myPredictions" :key="p.id"
          class="bg-white rounded-2xl shadow border border-slate-100 p-5">
          <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-bold text-slate-800">{{ p.event?.title }}</p>
            <span class="text-[11px] px-2 py-1 rounded-lg font-medium"
              :class="p.is_winner === true ? 'bg-emerald-50 text-emerald-600' : p.is_winner === false ? 'bg-red-50 text-red-500' : 'bg-amber-50 text-amber-600'">
              {{ p.is_winner === true ? 'فائز' : p.is_winner === false ? 'خاسر' : 'معلق' }}
            </span>
          </div>
          <p class="text-sm text-slate-600">توقعي: <span class="font-bold">{{ formatPrediction(p.prediction, p.event) }}</span></p>
          <p v-if="p.points_bet > 0" class="text-xs text-slate-400 mt-1">رسوم: {{ p.points_bet }} نقطة</p>
          <p class="text-xs text-slate-400 mt-1">{{ formatDate(p.created_at) }}</p>
        </div>
        <button v-if="myPredictionsPage < myPredictionsTotalPages" @click="loadMyPredictions(myPredictionsPage + 1)"
          class="w-full py-3 text-sm text-emerald-600 font-bold hover:text-emerald-700">
          عرض المزيد
        </button>
      </div>

      <!-- History Tab -->
      <div v-if="tab === 'history'" class="px-4 mt-6 space-y-4">
        <div v-if="historyPredictions.length === 0" class="text-center py-12 text-slate-400">
          <p>لا توجد أحداث سابقة</p>
        </div>
        <div v-for="p in historyPredictions" :key="p.id"
          class="bg-white rounded-2xl shadow border border-slate-100 p-5">
          <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-bold text-slate-800">{{ p.event?.title }}</p>
            <span class="text-[11px] px-2 py-1 rounded-lg font-medium"
              :class="p.is_winner === true ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-500'">
              {{ p.is_winner === true ? 'فائز' : 'خاسر' }}
            </span>
          </div>
          <p class="text-sm text-slate-600">توقعي: <span class="font-bold">{{ formatPrediction(p.prediction, p.event) }}</span></p>
          <p v-if="p.points_bet > 0" class="text-xs text-slate-400 mt-1">رسوم: {{ p.points_bet }} نقطة</p>
        </div>
        <button v-if="historyPage < historyTotalPages" @click="loadHistory(historyPage + 1)"
          class="w-full py-3 text-sm text-emerald-600 font-bold hover:text-emerald-700">
          عرض المزيد
        </button>
      </div>
    </template>

    <!-- Predict Modal -->
    <Teleport to="body">
      <div v-if="showPredictModal" class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center" @click.self="showPredictModal = false">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl w-full max-w-md p-5 animate-slide-up">
          <h3 class="text-lg font-bold text-slate-800 mb-1">{{ predictEvent?.home_team }} ضد {{ predictEvent?.away_team }}</h3>
          <p class="text-sm text-slate-400 mb-5">{{ formatDate(predictEvent?.match_date) }}</p>

          <!-- Winner Type -->
          <template v-if="predictEvent?.prediction_type === 'winner'">
            <p class="text-sm font-bold text-slate-700 mb-3">اختر المتوقع فوزه</p>
            <div class="grid gap-3 mb-5" :class="predictEvent?.allow_draw ? 'grid-cols-3' : 'grid-cols-2'">
              <button @click="predictionChoice = predictEvent?.home_team"
                class="py-4 rounded-xl border-2 text-center font-bold text-sm transition"
                :class="predictionChoice === predictEvent?.home_team ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-600 hover:border-slate-300'">
                {{ predictEvent?.home_team }}
              </button>
              <button @click="predictionChoice = predictEvent?.away_team"
                class="py-4 rounded-xl border-2 text-center font-bold text-sm transition"
                :class="predictionChoice === predictEvent?.away_team ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-600 hover:border-slate-300'">
                {{ predictEvent?.away_team }}
              </button>
              <button v-if="predictEvent?.allow_draw" @click="predictionChoice = 'draw'"
                class="py-4 rounded-xl border-2 text-center font-bold text-sm transition"
                :class="predictionChoice === 'draw' ? 'border-amber-500 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-600 hover:border-slate-300'">
                تعادل
              </button>
            </div>
          </template>

          <!-- Exact Score Type -->
          <template v-if="predictEvent?.prediction_type === 'exact_score'">
            <p class="text-sm font-bold text-slate-700 mb-3">أدخل النتيجة المتوقعة</p>
            <div class="flex items-center gap-3 mb-5">
              <div class="flex-1 text-center">
                <p class="text-xs text-slate-500 mb-2">{{ predictEvent?.home_team }}</p>
                <input type="number" v-model.number="homeScore" min="0" max="20"
                  class="w-full border border-slate-200 rounded-xl px-4 py-3 text-center text-lg font-bold text-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none"
                  placeholder="0">
              </div>
              <span class="text-lg font-bold text-slate-400 mt-6">-</span>
              <div class="flex-1 text-center">
                <p class="text-xs text-slate-500 mb-2">{{ predictEvent?.away_team }}</p>
                <input type="number" v-model.number="awayScore" min="0" max="20"
                  class="w-full border border-slate-200 rounded-xl px-4 py-3 text-center text-lg font-bold text-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none"
                  placeholder="0">
              </div>
            </div>
          </template>

          <!-- Points Bet -->
          <div v-if="predictEvent?.entry_fee === 0">
            <p class="text-sm font-bold text-slate-700 mb-2">رسوم المشاركة (اختياري)</p>
            <div class="flex items-center gap-3 mb-2">
              <input type="number" v-model.number="pointsBet" min="0" max="userBalance"
                class="flex-1 border border-slate-200 rounded-xl px-4 py-3 text-center text-lg font-bold text-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none" 
                placeholder="0">
              <span class="text-sm text-slate-400">نقطة</span>
            </div>
            <p v-if="pointsBet > userBalance" class="text-xs text-red-500 mb-2">رصيدك غير كافٍ</p>
          </div>
          <div v-else class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-5">
            <p class="text-sm text-amber-700 text-center">
              رسوم المشاركة: <strong>{{ predictEvent?.entry_fee }}</strong> نقطة (غير مستردة)
            </p>
          </div>

          <!-- Reward info -->
          <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 mb-3">
            <p class="text-sm text-emerald-700 text-center">
              🏆 <strong>المكافأة عند الفوز:</strong> {{ predictEvent?.reward_per_winner || 0 }} نقطة
            </p>
          </div>

          <!-- Fee warning -->
          <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-5">
            <p class="text-xs text-red-600 text-center">
              ⚠️ تنبيه: رسوم المشاركة غير مستردة سواء فزت أو خسرت
            </p>
          </div>

          <!-- Edit fee warning -->
          <div v-if="isEditing && editFee > 0" class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-5">
            <p class="text-sm text-amber-700 text-center">
              ⚠️ رسوم تعديل: <strong>{{ editFee }}</strong> نقطة (رسوم المشاركة الأصلية تبقى كما هي)
            </p>
          </div>

          <p v-if="predictionError" class="text-sm text-red-500 mb-3">{{ predictionError }}</p>

          <button @click="submitPrediction" :disabled="!canSubmit || saving"
            class="w-full py-3.5 rounded-xl text-sm font-bold transition"
            :class="canSubmit && !saving ? 'bg-emerald-600 text-white hover:bg-emerald-700 active:scale-[0.98]' : 'bg-slate-100 text-slate-400 cursor-not-allowed'">
            {{ saving ? 'جاري الإرسال...' : 'تأكيد التوقع' }}
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      enabled: false,
      loading: true,
      allowEdit: false,
      tab: 'upcoming',
      events: [],
      myPredictions: [],
      myPredictionsPage: 1,
      myPredictionsTotalPages: 1,
      historyPredictions: [],
      historyPage: 1,
      historyTotalPages: 1,
      showPredictModal: false,
      predictEvent: null,
      predictionChoice: '',
      homeScore: 0,
      awayScore: 0,
      pointsBet: 0,
      predictionError: '',
      saving: false,
      editFee: 0,
    }
  },
  computed: {
    userBalance() {
      const user = JSON.parse(localStorage.getItem('user') || '{}')
      return user.points_balance || 0
    },
    canSubmit() {
      if (this.predictEvent?.prediction_type === 'exact_score') return true
      return !!this.predictionChoice
    },
    isEditing() {
      if (!this.predictEvent) return false
      return this.predictEvent.my_prediction !== null && this.predictEvent.my_prediction !== undefined
    },
  },
  mounted() {
    this.loadEvents()
    this.loadMyPredictions()
    this.loadHistory()
  },
  methods: {
    async loadEvents() {
      this.loading = true
      try {
        const r = await axios.get('/api/v1/sport-events')
        this.enabled = r.data.enabled !== false
        this.allowEdit = r.data.allow_prediction_edit || false
        this.editFee = r.data.edit_fee || 0
        this.events = r.data.events || []
        this.loadMyPredictions()
        this.loadHistory()
      } catch (e) {}
      this.loading = false
    },
    async loadMyPredictions(page = 1) {
      try {
        const r = await axios.get('/api/v1/user/predictions', { params: { page, per_page: 20 } })
        if (r.data.enabled === false) return
        const data = r.data.data || r.data || []
        if (!Array.isArray(data)) return
        if (page === 1) this.myPredictions = data
        else this.myPredictions = [...this.myPredictions, ...data]
        this.myPredictionsPage = page
        this.myPredictionsTotalPages = r.data.last_page || 1
      } catch (e) {}
    },
    async loadHistory(page = 1) {
      try {
        const r = await axios.get('/api/v1/user/predictions', { params: { page, per_page: 20 } })
        if (r.data.enabled === false) return
        const allData = r.data.data || r.data || []
        if (!Array.isArray(allData)) return
        const data = allData.filter(p => p.is_winner !== null)
        if (page === 1) this.historyPredictions = data
        else this.historyPredictions = [...this.historyPredictions, ...data]
        this.historyPage = page
        this.historyTotalPages = r.data.last_page || 1
      } catch (e) {}
    },
    openPredict(event) {
      if (event.my_prediction && !this.allowEdit) return
      this.predictEvent = event
      this.predictionChoice = ''
      this.homeScore = 0
      this.awayScore = 0
      this.pointsBet = event.entry_fee || 0
      this.predictionError = ''
      this.showPredictModal = true
    },
    editPrediction(event) {
      this.openPredict(event)
      this.predictionChoice = event.my_prediction?.prediction || ''
      this.pointsBet = event.my_prediction?.points_bet || 0
      // Parse exact score if applicable
      if (event.prediction_type === 'exact_score' && event.my_prediction?.prediction) {
        const parts = event.my_prediction.prediction.split('-')
        if (parts.length === 2) {
          this.homeScore = parseInt(parts[0]) || 0
          this.awayScore = parseInt(parts[1]) || 0
        }
      }
    },
    async submitPrediction() {
      if (this.saving) return
      const eventId = this.predictEvent.id
      const betAmount = this.predictEvent.entry_fee > 0 ? this.predictEvent.entry_fee : this.pointsBet

      let prediction = this.predictionChoice
      if (this.predictEvent?.prediction_type === 'exact_score') {
        prediction = `${this.homeScore}-${this.awayScore}`
        if (this.homeScore === undefined || this.awayScore === undefined) {
          this.predictionError = 'الرجاء إدخال النتيجة المتوقعة'
          return
        }
      } else {
        if (!prediction) {
          this.predictionError = 'الرجاء اختيار المتوقع فوزه'
          return
        }
      }

      if (betAmount > this.userBalance) {
        this.predictionError = 'رصيد نقاط غير كافٍ'
        return
      }

      this.saving = true
      this.predictionError = ''
      try {
        await axios.post(`/api/v1/sport-events/${eventId}/predict`, { prediction, points_bet: betAmount })
        this.showPredictModal = false
        await this.loadEvents()
        const profileRes = await axios.get('/api/v1/user/profile')
        const user = JSON.parse(localStorage.getItem('user') || '{}')
        user.points_balance = profileRes.data.points_balance
        localStorage.setItem('user', JSON.stringify(user))
      } catch (e) {
        this.predictionError = e.response?.data?.message || 'حدث خطأ'
      }
      this.saving = false
    },
    formatPrediction(prediction, event) {
      if (!prediction) return ''
      if (event?.prediction_type === 'exact_score') return prediction.replace('-', ' - ')
      return prediction === 'draw' ? 'تعادل' : prediction
    },
    formatDate(d) {
      if (!d) return ''
      const dt = new Date(d)
      const dayNames = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت']
      const monthNames = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر']
      const day = dayNames[dt.getUTCDay()]
      const date = dt.getUTCDate()
      const month = monthNames[dt.getUTCMonth()]
      const hour = String(dt.getUTCHours()).padStart(2, '0')
      const min = String(dt.getUTCMinutes()).padStart(2, '0')
      return `${day}، ${date} ${month}، ${hour}:${min}`
    },
  },
}
</script>

<style scoped>
@keyframes slide-up {
  from { transform: translateY(100%); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.animate-slide-up { animation: slide-up 0.25s ease-out; }
</style>
