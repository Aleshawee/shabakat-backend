<template>
  <div class="min-h-screen bg-slate-50 pb-20">
    <div class="bg-gradient-to-br from-emerald-700 via-emerald-800 to-slate-900 text-white px-5 pt-5 pb-8 relative overflow-hidden">
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_left,rgba(255,255,255,0.06),transparent_60%)]"></div>
      <div class="relative z-10">
        <div class="flex items-center gap-3 mb-6">
          <button @click="$router.back()" class="text-white/80 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <h1 class="text-xl font-bold">الملف الشخصي</h1>
        </div>

        <div class="text-center">
          <div class="w-20 h-20 rounded-full bg-white/10 backdrop-blur flex items-center justify-center mx-auto mb-3 border-2 border-white/20">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <h2 class="text-lg font-bold">{{ user.name || 'مستخدم' }}</h2>
          <p class="text-sm text-emerald-200">967+{{ user.phone?.replace('+967', '') }}</p>
        </div>
      </div>
    </div>

    <div class="px-4 -mt-4 space-y-4">
      <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.04)] border p-5">
        <h3 class="text-sm font-bold text-slate-700 mb-4">تعديل الاسم</h3>
        <div class="flex gap-3">
          <input v-model="newName" type="text" class="flex-1 px-4 py-3 border-2 border-slate-200 rounded-2xl focus:ring-0 focus:border-emerald-500 outline-none text-base transition" />
          <button @click="updateName" :disabled="saving"
            class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl transition disabled:opacity-50">
            حفظ
          </button>
        </div>
        <p v-if="nameMsg" class="text-xs mt-2 text-emerald-600">{{ nameMsg }}</p>
      </div>

      <div class="bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.04)] border p-5">
        <h3 class="text-sm font-bold text-slate-700 mb-4">تغيير كلمة المرور</h3>
        <form @submit.prevent="changePassword" class="space-y-3">
          <input v-model="currentPassword" type="password" required placeholder="كلمة المرور الحالية"
            class="w-full px-4 py-3 border-2 border-slate-200 rounded-2xl focus:ring-0 focus:border-emerald-500 outline-none text-base transition" />
          <input v-model="newPassword" type="password" required minlength="6" placeholder="كلمة المرور الجديدة"
            class="w-full px-4 py-3 border-2 border-slate-200 rounded-2xl focus:ring-0 focus:border-emerald-500 outline-none text-base transition" />
          <button type="submit" :disabled="saving"
            class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl transition disabled:opacity-50">
            تحديث كلمة المرور
          </button>
          <p v-if="passMsg" class="text-xs text-center" :class="passError ? 'text-red-500' : 'text-emerald-600'">{{ passMsg }}</p>
        </form>
      </div>

      <button @click="logout"
        class="w-full py-3.5 bg-white border border-red-200 text-red-500 font-bold rounded-2xl hover:bg-red-50 transition">
        تسجيل الخروج
      </button>

      <p class="text-center text-xs text-slate-400 pb-4">شبكة المكافآت v1.0</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  data() {
    return {
      user: JSON.parse(localStorage.getItem('user') || '{}'),
      newName: '',
      currentPassword: '',
      newPassword: '',
      saving: false,
      nameMsg: '',
      passMsg: '',
      passError: false,
    }
  },
  mounted() {
    this.newName = this.user.name || ''
  },
  methods: {
    async updateName() {
      this.saving = true; this.nameMsg = ''
      try {
        const r = await axios.post('/api/v1/user/update', { name: this.newName })
        this.user.name = r.data.name
        localStorage.setItem('user', JSON.stringify(this.user))
        this.nameMsg = '✅ تم حفظ الاسم'
      } catch (e) {
        this.nameMsg = '❌ فشل التحديث'
      } finally { this.saving = false }
    },
    async changePassword() {
      this.saving = true; this.passMsg = ''; this.passError = false
      try {
        await axios.post('/api/v1/user/change-password', {
          current_password: this.currentPassword,
          new_password: this.newPassword,
        })
        this.passMsg = '✅ تم تغيير كلمة المرور بنجاح'
        this.currentPassword = ''
        this.newPassword = ''
      } catch (e) {
        this.passMsg = e.response?.data?.message || '❌ فشل تغيير كلمة المرور'
        this.passError = true
      } finally { this.saving = false }
    },
    logout() {
      localStorage.removeItem('user_token')
      localStorage.removeItem('user')
      this.$router.push('/login')
    },
  },
}
</script>
