<template>
  <div class="min-h-screen bg-gradient-to-br from-emerald-700 via-emerald-800 to-slate-900 flex flex-col items-center justify-center p-6 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.06),transparent_70%)]"></div>

    <div class="text-center mb-8 relative z-10">
      <div class="w-20 h-20 bg-white/10 backdrop-blur rounded-2xl flex items-center justify-center mx-auto mb-4 border border-white/10">
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <h1 class="text-2xl font-bold text-white">شبكة المكافآت</h1>
      <p v-if="networkName" class="text-emerald-200 text-sm mt-1">{{ networkName }}</p>
      <p v-else class="text-emerald-200 text-sm mt-1">سجّل كروتك واجمع النقاط</p>
    </div>

    <div class="bg-white/95 backdrop-blur rounded-3xl p-7 w-full max-w-sm shadow-2xl relative z-10">
      <!-- Login with password -->
      <form v-if="mode === 'login'" @submit.prevent="loginWithPassword" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">رقم الهاتف</label>
          <div class="relative" dir="ltr">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm border-l border-slate-200 pl-3 ml-1">967</span>
            <input v-model="phone" type="tel" required dir="ltr" maxlength="9"
              class="text-right w-full px-4 py-3.5 pr-16 border-2 border-slate-200 rounded-2xl focus:ring-0 focus:border-emerald-500 outline-none text-lg transition" />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-2">كلمة المرور</label>
          <div class="relative">
            <input v-model="password" :type="showPassword ? 'text' : 'password'" required
              class="w-full px-4 py-3.5 border-2 border-slate-200 rounded-2xl focus:ring-0 focus:border-emerald-500 outline-none text-lg transition pl-12" />
            <button type="button" @click="showPassword = !showPassword"
              class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="!showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
              </svg>
            </button>
          </div>
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 text-sm text-center px-4 py-2.5 rounded-xl">
          {{ error }}
        </div>

        <button type="submit" :disabled="loading"
          class="w-full bg-gradient-to-l from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold py-3.5 rounded-2xl transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-emerald-200">
          <span v-if="loading" class="flex items-center justify-center gap-2">
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            جاري...
          </span>
          <span v-else>دخول</span>
        </button>

        <div class="flex justify-between text-sm">
          <button type="button" @click="mode = 'register'" class="text-emerald-600 hover:underline">
            حساب جديد؟
          </button>
          <button type="button" @click="mode = 'forgot'" class="text-slate-500 hover:text-slate-700 hover:underline">
            نسيت كلمة المرور؟
          </button>
        </div>
      </form>

      <!-- Register -->
      <div v-else-if="mode === 'register'">
        <!-- Step 1: Register form -->
        <form v-if="regStep === 1" @submit.prevent="register" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">الاسم</label>
            <input v-model="name" type="text" required
              class="w-full px-4 py-3.5 border-2 border-slate-200 rounded-2xl focus:ring-0 focus:border-emerald-500 outline-none text-lg transition" />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">رقم الهاتف</label>
            <div class="relative" dir="ltr">
              <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm border-l border-slate-200 pl-3 ml-1">967</span>
              <input v-model="phone" type="tel" required dir="ltr" maxlength="9"
                class="text-right w-full px-4 py-3.5 pr-16 border-2 border-slate-200 rounded-2xl focus:ring-0 focus:border-emerald-500 outline-none text-lg transition" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">كلمة المرور</label>
            <input v-model="password" type="password" required minlength="6"
              class="w-full px-4 py-3.5 border-2 border-slate-200 rounded-2xl focus:ring-0 focus:border-emerald-500 outline-none text-lg transition" />
          </div>

          <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 text-sm text-center px-4 py-2.5 rounded-xl">
            {{ error }}
          </div>

          <button type="submit" :disabled="loading"
            class="w-full bg-gradient-to-l from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold py-3.5 rounded-2xl transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-emerald-200">
            <span v-if="loading" class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              جاري...
            </span>
            <span v-else>تسجيل حساب جديد</span>
          </button>

          <button type="button" @click="mode = 'login'"
            class="w-full text-sm text-slate-500 hover:text-slate-700 py-2 transition">
            ← لدي حساب بالفعل
          </button>
        </form>

        <!-- Step 2: OTP verification -->
        <form v-else @submit.prevent="verifyRegisterOtp" class="space-y-5">
          <h3 class="text-lg font-bold text-slate-800 text-center">تفعيل الحساب</h3>
          <p class="text-sm text-slate-500 text-center">أدخل رمز التفعيل المرسل إلى هاتفك</p>
          <div class="flex justify-center gap-2" dir="ltr">
            <input v-for="i in 6" :key="i" :ref="el => setOtpRef(i, el)"
              v-model="otp[i-1]" type="text" maxlength="1" required
              @input="onOtpInput(i)" @keydown.back.prevent="onOtpBack(i)"
              class="w-12 h-14 text-center border-2 border-slate-200 rounded-2xl focus:border-emerald-500 outline-none text-2xl font-bold transition" />
          </div>
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 text-sm text-center px-4 py-2.5 rounded-xl">
            {{ error }}
          </div>
          <button type="submit" :disabled="loading"
            class="w-full bg-gradient-to-l from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold py-3.5 rounded-2xl transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-emerald-200">
            <span v-if="loading" class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              جاري...
            </span>
            <span v-else>تأكيد الرمز</span>
          </button>
          <button type="button" @click="regStep = 1; error = ''"
            class="w-full text-sm text-slate-500 hover:text-slate-700 py-2 transition">
            ← تعديل البيانات
          </button>
        </form>
      </div>

      <!-- Forgot password / OTP -->
      <form v-else @submit.prevent="step === 1 ? sendOtp() : step === 2 ? verifyOtpReset() : resetPasswordFinal()" class="space-y-5">
        <h3 class="text-lg font-bold text-slate-800 text-center">استعادة كلمة المرور</h3>

        <div v-if="step === 1">
          <label class="block text-sm font-medium text-slate-700 mb-2">رقم الهاتف</label>
          <div class="relative" dir="ltr">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm border-l border-slate-200 pl-3 ml-1">967</span>
            <input v-model="phone" type="tel" required dir="ltr" maxlength="9"
              class="text-right w-full px-4 py-3.5 pr-16 border-2 border-slate-200 rounded-2xl focus:ring-0 focus:border-emerald-500 outline-none text-lg transition" />
          </div>
        </div>

        <div v-if="step === 2">
          <label class="block text-sm font-medium text-slate-700 mb-2">رمز التفعيل</label>
          <div class="flex justify-center gap-2" dir="ltr">
            <input v-for="i in 6" :key="i" :ref="el => setOtpRef(i, el)"
              v-model="otp[i-1]" type="text" maxlength="1" required
              @input="onOtpInput(i)" @keydown.back.prevent="onOtpBack(i)"
              class="w-12 h-14 text-center border-2 border-slate-200 rounded-2xl focus:border-emerald-500 outline-none text-2xl font-bold transition" />
          </div>
          <p class="text-center mt-3 text-xs text-slate-500">تم إرسال رمز التفعيل إلى هاتفك</p>
        </div>

        <div v-if="step === 3">
          <label class="block text-sm font-medium text-slate-700 mb-2">كلمة المرور الجديدة</label>
          <input v-model="password" type="password" required minlength="6"
            class="w-full px-4 py-3.5 border-2 border-slate-200 rounded-2xl focus:ring-0 focus:border-emerald-500 outline-none text-lg transition" />
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-600 text-sm text-center px-4 py-2.5 rounded-xl">
          {{ error }}
        </div>

        <button type="submit" :disabled="loading"
          class="w-full bg-gradient-to-l from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold py-3.5 rounded-2xl transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-emerald-200">
          <span v-if="loading" class="flex items-center justify-center gap-2">
            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            جاري...
          </span>
          <span v-else>{{ step === 1 ? 'إرسال رمز التفعيل' : step === 2 ? 'تأكيد الرمز' : 'حفظ كلمة المرور' }}</span>
        </button>

        <button v-if="step > 1" type="button" @click="step--; error = ''"
          class="w-full text-sm text-slate-500 hover:text-slate-700 py-2 transition">
          ← رجوع
        </button>

        <button type="button" @click="mode = 'login'; step = 1; error = ''"
          class="w-full text-sm text-emerald-600 hover:underline py-1">
          تذكرت كلمة المرور؟ دخول
        </button>
      </form>

      <div v-if="mode === 'login'" class="mt-6 pt-5 border-t border-slate-100 text-center">
        <p class="text-xs text-slate-400">
          بالتسجيل أنت توافق على
          <a href="#" class="text-emerald-600 underline">شروط الاستخدام</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { getNetworkSlug } from '../utils/network.js'
export default {
  data() {
    return {
      mode: 'login',
      step: 1,
      regStep: 1,
      phone: '',
      password: '',
      showPassword: false,
      name: '',
      networkSlug: getNetworkSlug(),
      otp: Array(6).fill(''),
      tempToken: '',
      loading: false,
      error: '',
      otpRefs: {},
    }
  },
  computed: {
    networkName() {
      const net = JSON.parse(localStorage.getItem('network') || '{}')
      return net.name || null
    },
  },
  methods: {
    setOtpRef(i, el) { if (el) this.otpRefs[i] = el },
    onOtpInput(i) {
      if (this.otp[i-1] && i < 6) this.otpRefs[i+1]?.focus()
      if (i === 6 && this.otp.every(o => o)) {
        if (this.mode === 'register') this.verifyRegisterOtp()
        else this.verifyOtpReset()
      }
    },
    onOtpBack(i) {
      if (!this.otp[i-1] && i > 1) { this.otp[i-2] = ''; this.otpRefs[i-1]?.focus() }
    },
    async loginWithPassword() {
      this.loading = true; this.error = ''
      try {
        const res = await axios.post('/api/v1/auth/login', {
          phone: this.phone,
          password: this.password,
        })
        localStorage.setItem('user_token', res.data.token)
        localStorage.setItem('user', JSON.stringify(res.data.user))
        if (res.data.network) localStorage.setItem('network', JSON.stringify(res.data.network))
        this.$router.push('/dashboard')
      } catch (e) {
        this.error = e.response?.data?.message || 'فشل تسجيل الدخول'
      } finally { this.loading = false }
    },
    async register() {
      this.loading = true; this.error = ''
      try {
        await axios.post('/api/v1/auth/register', {
          phone: this.phone,
          name: this.name,
          password: this.password,
          network_slug: this.networkSlug,
        })
        this.regStep = 2
        this.otp = Array(6).fill('')
        this.$nextTick(() => this.otpRefs[1]?.focus())
      } catch (e) {
        this.error = e.response?.data?.message || 'فشل التسجيل'
      } finally { this.loading = false }
    },
    async verifyRegisterOtp() {
      if (this.otp.some(o => !o)) return
      this.loading = true; this.error = ''
      try {
        const res = await axios.post('/api/v1/auth/verify-otp', {
          phone: this.phone,
          otp: this.otp.join(''),
        })
        localStorage.setItem('user_token', res.data.token)
        localStorage.setItem('user', JSON.stringify(res.data.user))
        if (res.data.network) localStorage.setItem('network', JSON.stringify(res.data.network))
        this.$router.push('/dashboard')
      } catch (e) {
        this.error = e.response?.data?.message || 'رمز التفعيل غير صحيح'
        this.otp = Array(6).fill('')
        this.$nextTick(() => this.otpRefs[1]?.focus())
      } finally { this.loading = false }
    },
    async sendOtp() {
      this.loading = true; this.error = ''
      try {
        await axios.post('/api/v1/auth/send-otp', { phone: this.phone })
        this.step = 2
        this.$nextTick(() => this.otpRefs[1]?.focus())
      } catch (e) {
        this.error = e.response?.data?.message || 'فشل إرسال رمز التفعيل'
      } finally { this.loading = false }
    },
    async verifyOtpReset() {
      this.loading = true; this.error = ''
      try {
        const res = await axios.post('/api/v1/auth/verify-otp', { phone: this.phone, otp: this.otp.join('') })
        this.tempToken = res.data.token
        this.step = 3
      } catch (e) {
        this.error = e.response?.data?.message || 'رمز التفعيل غير صحيح'
        this.otp = Array(6).fill('')
        this.$nextTick(() => this.otpRefs[1]?.focus())
      } finally { this.loading = false }
    },
    async resetPasswordFinal() {
      this.loading = true; this.error = ''
      try {
        const res = await axios.post('/api/v1/auth/reset-password', {
          password: this.password,
        }, {
          headers: { Authorization: `Bearer ${this.tempToken}` },
        })
        localStorage.setItem('user_token', this.tempToken)
        const userRes = await axios.get('/api/v1/user/profile', {
          headers: { Authorization: `Bearer ${this.tempToken}` },
        })
        localStorage.setItem('user', JSON.stringify(userRes.data))
        if (userRes.data.network) localStorage.setItem('network', JSON.stringify(userRes.data.network))
        this.$router.push('/dashboard')
      } catch (e) {
        this.error = e.response?.data?.message || 'فشل تغيير كلمة المرور'
      } finally { this.loading = false }
    },
  },
}
</script>
