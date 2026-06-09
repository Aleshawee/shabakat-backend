<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-violet-800 to-indigo-900">
    <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl">
      <div class="text-center mb-6">
        <span class="text-4xl">🎁</span>
        <h1 class="text-2xl font-bold text-slate-800 mt-2">لوحة التحكم</h1>
        <p class="text-sm text-slate-500">نظام مكافآت شبكات الإنترنت</p>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">البريد الإلكتروني</label>
          <input v-model="email" type="email" required
            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-violet-500 outline-none transition" />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">كلمة المرور</label>
          <input v-model="password" type="password" required
            class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-violet-500 outline-none transition" />
        </div>

        <p v-if="error" class="text-red-500 text-sm text-center">{{ error }}</p>

        <button type="submit" :disabled="loading"
          class="w-full bg-violet-600 hover:bg-violet-700 text-white font-bold py-2.5 rounded-xl transition disabled:opacity-50">
          {{ loading ? 'جاري تسجيل الدخول...' : 'تسجيل الدخول' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { getNetworkSlug } from '../utils/network.js'

export default {
  data() {
    return { email: '', password: '', loading: false, error: '' }
  },
  methods: {
    async handleLogin() {
      this.loading = true
      this.error = ''
      try {
        const res = await axios.post('/api/admin/v1/admin/login', {
          email: this.email,
          password: this.password,
        })
        const admin = res.data.admin
        const network = getNetworkSlug()
        if (network && admin.tenant_id !== network) {
          this.error = 'هذا الحساب غير مسموح له بالدخول لهذه الشبكة'
          return
        }
        localStorage.setItem('admin_token', res.data.token)
        localStorage.setItem('admin', JSON.stringify(admin))
        this.$router.push(admin.role === 'owner' ? '/owner' : '/admin')
      } catch (e) {
        this.error = e.response?.data?.message || 'فشل تسجيل الدخول'
      } finally {
        this.loading = false
      }
    },
  },
}
</script>
