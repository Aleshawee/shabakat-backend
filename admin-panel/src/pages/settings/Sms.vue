<template>
  <div class="p-6" dir="rtl">
    <h1 class="text-2xl font-bold mb-6 text-slate-800">إعدادات الرسائل النصية</h1>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 max-w-2xl">
      <div v-if="loading" class="text-center py-8 text-slate-400">جاري التحميل...</div>
      <form v-else @submit.prevent="save">
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700 mb-1">مزود الخدمة</label>
          <select v-model="form.sms_provider" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">اختر المزود</option>
            <option value="twilio">Twilio</option>
            <option value="textbee">Textbee (تطبيق أندرويد)</option>
            <option value="smsgateway">SMS Gateway (Android)</option>
            <option value="vonage">Vonage (Nexmo)</option>
            <option value="unifonic">Unifonic</option>
            <option value="other">أخرى</option>
          </select>
        </div>

        <div v-if="form.sms_provider === 'textbee'" class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-4 space-y-4">
          <h3 class="font-semibold text-indigo-800 text-sm">إعدادات Textbee</h3>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">مفتاح API</label>
            <input v-model="form.textbee_api_key" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr" placeholder="API Key من تطبيق Textbee">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">معرّف الجهاز (Device ID)</label>
            <input v-model="form.textbee_device_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr" placeholder="Device ID من تطبيق Textbee">
          </div>
        </div>

        <div v-if="form.sms_provider === 'smsgateway'" class="bg-slate-50 border border-slate-300 rounded-lg p-4 mb-4 space-y-4">
          <h3 class="font-semibold text-slate-800 text-sm">إعدادات SMS Gateway</h3>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">اسم المستخدم</label>
            <input v-model="form.smsgateway_username" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr" placeholder="Username من التطبيق">
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">كلمة المرور</label>
            <input v-model="form.smsgateway_password" type="password" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr" placeholder="Password من التطبيق">
          </div>
            <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">رابط الخادم</label>
            <input v-model="form.smsgateway_url" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr" placeholder="https://api.sms-gate.app/3rdparty/v1/messages">
            <p class="text-xs text-slate-400 mt-1">اتركه فارغاً لاستخدام السيرفر السحابي الافتراضي</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">رقم الشريحة</label>
            <select v-model.number="form.smsgateway_sim_number" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
              <option :value="null">افتراضي (OS Default)</option>
              <option :value="1">الشريحة 1</option>
              <option :value="2">الشريحة 2</option>
            </select>
            <p class="text-xs text-slate-400 mt-1">اختر الشريحة التي تريد استخدامها للإرسال</p>
          </div>
        </div>

        <template v-if="form.sms_provider && form.sms_provider !== 'textbee'">
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">مفتاح API</label>
            <input v-model="form.sms_api_key" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr" placeholder="API Key">
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">الرمز السري API</label>
            <input v-model="form.sms_api_secret" type="password" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr" placeholder="API Secret">
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">اسم المرسل</label>
            <input v-model="form.sms_sender_name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="Sender Name">
          </div>
        </template>

        <div class="mb-6">
          <label class="flex items-center gap-2">
            <input type="checkbox" v-model="form.sms_enabled" class="rounded border-slate-300">
            <span class="text-sm text-slate-700">تفعيل إرسال SMS</span>
          </label>
        </div>

        <div v-if="error" class="mb-3 text-sm text-red-600">{{ error }}</div>
        <div v-if="success" class="mb-3 text-sm text-emerald-600">{{ success }}</div>

        <div class="flex gap-3">
          <button type="submit" class="bg-violet-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-violet-700">حفظ الإعدادات</button>
          <button v-if="form.sms_provider === 'textbee' || form.sms_provider === 'smsgateway'" type="button" @click="showTestDialog = true" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-emerald-700">اختبار الإرسال</button>
        </div>
      </form>
    </div>

    <!-- Test SMS Dialog -->
    <Teleport to="body">
      <div v-if="showTestDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showTestDialog = false">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4" dir="rtl">
          <h3 class="text-lg font-bold text-slate-800 mb-4">اختبار إرسال SMS</h3>
          <div class="mb-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">رقم الهاتف</label>
            <input v-model="testPhone" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr" placeholder="+967...">
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">الرسالة</label>
            <textarea v-model="testMessage" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" rows="3" placeholder="نص الرسالة"></textarea>
          </div>
          <div v-if="testResult" class="mb-3 text-sm" :class="testResult.success ? 'text-emerald-600' : 'text-red-600'">{{ testResult.message }}</div>
          <div class="flex gap-2 justify-end">
            <button @click="showTestDialog = false" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">إلغاء</button>
            <button @click="sendTest" :disabled="sending" class="bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-emerald-700 disabled:opacity-50">
              {{ sending ? 'جاري الإرسال...' : 'إرسال' }}
            </button>
          </div>
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
      loading: false,
      showTestDialog: false,
      sending: false,
      testPhone: '',
      testMessage: 'رسالة اختبار من نظام شبكات',
      testResult: null,
      form: { sms_provider: '', sms_api_key: '', sms_api_secret: '', sms_sender_name: '', sms_enabled: false, textbee_api_key: '', textbee_device_id: '', smsgateway_url: '', smsgateway_username: '', smsgateway_password: '', smsgateway_sim_number: null },
      error: '',
      success: '',
    }
  },
  mounted() { this.load() },
  methods: {
    async load() {
      this.loading = true
      try {
        const res = await axios.get('/api/admin/v1/settings/sms')
        this.form = {
          sms_provider: res.data.sms_provider || '',
          sms_api_key: res.data.sms_api_key || '',
          sms_api_secret: res.data.sms_api_secret || '',
          sms_sender_name: res.data.sms_sender_name || '',
          sms_enabled: res.data.sms_enabled === 'true' || res.data.sms_enabled === '1',
          textbee_api_key: res.data.textbee_api_key || '',
          textbee_device_id: res.data.textbee_device_id || '',
          smsgateway_url: res.data.smsgateway_url || '',
          smsgateway_username: res.data.smsgateway_username || '',
          smsgateway_password: res.data.smsgateway_password || '',
          smsgateway_sim_number: res.data.smsgateway_sim_number ? parseInt(res.data.smsgateway_sim_number) : null,
        }
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    async save() {
      this.error = ''
      this.success = ''
      try {
        const res = await axios.put('/api/admin/v1/settings/sms', this.form)
        this.form.sms_provider = res.data.sms_provider || ''
        this.form.sms_api_key = res.data.sms_api_key || ''
        this.form.sms_api_secret = res.data.sms_api_secret || ''
        this.form.sms_sender_name = res.data.sms_sender_name || ''
        this.form.sms_enabled = res.data.sms_enabled === 'true' || res.data.sms_enabled === '1'
        this.form.textbee_api_key = res.data.textbee_api_key || ''
        this.form.textbee_device_id = res.data.textbee_device_id || ''
        this.form.smsgateway_url = res.data.smsgateway_url || ''
        this.form.smsgateway_username = res.data.smsgateway_username || ''
        this.form.smsgateway_password = res.data.smsgateway_password || ''
        this.form.smsgateway_sim_number = res.data.smsgateway_sim_number ? parseInt(res.data.smsgateway_sim_number) : null
        this.success = 'تم حفظ الإعدادات بنجاح'
      } catch (e) {
        this.error = e.response?.data?.message || 'حدث خطأ'
      }
    },
    async sendTest() {
      if (!this.testPhone) return
      this.sending = true
      this.testResult = null
      try {
        const res = await axios.post('/api/admin/v1/sms/test-provider', { provider: this.form.sms_provider, phone: this.testPhone, message: this.testMessage })
        this.testResult = { success: res.data.success, message: res.data.message }
      } catch (e) {
        this.testResult = { success: false, message: 'فشل الاتصال بالخادم' }
      } finally {
        this.sending = false
      }
    },
  },
}
</script>
