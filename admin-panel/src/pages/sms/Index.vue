<template>
  <div class="p-6" dir="rtl">
    <h1 class="text-2xl font-bold mb-6 text-slate-800">الرسائل النصية</h1>

    <!-- Stats -->
    <div class="grid grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200">
        <p class="text-sm text-slate-500">إجمالي</p>
        <p class="text-2xl font-bold text-slate-800">{{ stats.total }}</p>
      </div>
      <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200">
        <p class="text-sm text-slate-500">مرسلة ناجحة</p>
        <p class="text-2xl font-bold text-emerald-600">{{ stats.sent }}</p>
      </div>
      <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200">
        <p class="text-sm text-slate-500">فاشلة</p>
        <p class="text-2xl font-bold text-red-600">{{ stats.failed }}</p>
      </div>
      <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200">
        <p class="text-sm text-slate-500">وارد</p>
        <p class="text-2xl font-bold text-blue-600">{{ stats.received }}</p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-4 mb-6 border-b border-slate-200">
      <button @click="tab = 'send'" :class="tab === 'send' ? 'border-b-2 border-violet-600 text-violet-700' : 'text-slate-500'" class="pb-2 text-sm font-medium">إرسال رسالة</button>
      <button @click="tab = 'history'; loadHistory()" :class="tab === 'history' ? 'border-b-2 border-violet-600 text-violet-700' : 'text-slate-500'" class="pb-2 text-sm font-medium">سجل الرسائل</button>
      <button @click="tab = 'received'; loadReceived()" :class="tab === 'received' ? 'border-b-2 border-violet-600 text-violet-700' : 'text-slate-500'" class="pb-2 text-sm font-medium">الرسائل الواردة</button>
    </div>

    <!-- Send Tab -->
    <div v-if="tab === 'send'" class="bg-white rounded-xl p-6 shadow-sm border border-slate-200 max-w-2xl">
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-700 mb-2">الجمهور</label>
        <div class="flex gap-4">
          <label class="flex items-center gap-2">
            <input type="radio" v-model="form.target" value="all" @change="countTarget">
            <span class="text-sm">جميع المستخدمين</span>
          </label>
          <label class="flex items-center gap-2">
            <input type="radio" v-model="form.target" value="filtered" @change="countTarget">
            <span class="text-sm">تصفية</span>
          </label>
          <label class="flex items-center gap-2">
            <input type="radio" v-model="form.target" value="single" @change="countTarget">
            <span class="text-sm">مستخدم محدد</span>
          </label>
        </div>
      </div>

      <div v-if="form.target === 'filtered'" class="grid grid-cols-2 gap-3 mb-4 p-4 bg-slate-50 rounded-lg">
        <div>
          <label class="block text-xs text-slate-500 mb-1">الحالة</label>
          <select v-model="form.status" @change="countTarget" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="">الكل</option>
            <option value="active">نشط</option>
            <option value="suspended">موقوف</option>
            <option value="banned">محظور</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">الحد الأدنى للنقاط</label>
          <input type="number" v-model.number="form.points_min" @input="countTarget" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">الحد الأقصى للنقاط</label>
          <input type="number" v-model.number="form.points_max" @input="countTarget" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">من تاريخ</label>
          <input type="date" v-model="form.from_date" @change="countTarget" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-xs text-slate-500 mb-1">إلى تاريخ</label>
          <input type="date" v-model="form.to_date" @change="countTarget" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
        </div>
      </div>

      <div v-if="form.target === 'single'" class="mb-4">
        <label class="block text-sm font-medium text-slate-700 mb-1">رقم الهاتف</label>
        <input v-model="form.phone" @input="countTarget" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="بحث برقم الهاتف">
      </div>

      <div class="mb-4 text-sm">
        <span class="text-slate-500">المستهدفون: </span>
        <span class="font-bold text-violet-700">{{ targetCount }}</span>
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-700 mb-1">نص الرسالة</label>
        <textarea v-model="form.message" rows="4" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="أكتب رسالتك هنا..." maxlength="500"></textarea>
        <p class="text-xs text-slate-400 mt-1">{{ form.message.length }}/500</p>
      </div>

      <div v-if="sendError" class="mb-3 text-sm text-red-600">{{ sendError }}</div>
      <div v-if="sendSuccess" class="mb-3 text-sm text-emerald-600">{{ sendSuccess }}</div>

      <button @click="sendSms" :disabled="sending || !form.message || targetCount === 0" class="bg-violet-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-violet-700 disabled:opacity-40">
        {{ sending ? 'جاري الإرسال...' : 'إرسال' }}
      </button>
    </div>

    <!-- History Tab (Sent + Received unified) -->
    <div v-if="tab === 'history'">
      <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 mb-4">
        <div class="flex gap-3 flex-wrap items-end">
          <div class="flex-1 min-w-[200px]">
            <label class="block text-xs text-slate-500 mb-1">بحث برقم الهاتف</label>
            <input v-model="historySearch" @input="debouncedLoad" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="هاتف المرسل أو المستلم">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">الاتجاه</label>
            <select v-model="historyDirection" @change="loadHistory" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
              <option value="">الكل</option>
              <option value="outgoing">صادر</option>
              <option value="incoming">وارد</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">الحالة</label>
            <select v-model="historyStatus" @change="loadHistory" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
              <option value="">الكل</option>
              <option value="sent">ناجحة</option>
              <option value="failed">فاشلة</option>
              <option value="received">مستلمة</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">من تاريخ</label>
            <input type="date" v-model="historyFrom" @change="loadHistory" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">إلى تاريخ</label>
            <input type="date" v-model="historyTo" @change="loadHistory" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">عرض</label>
            <select v-model="perPage" @change="loadHistory" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
              <option value="100">100</option>
            </select>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div v-if="loading" class="text-center py-8 text-slate-400">جاري التحميل...</div>
        <table v-else class="w-full">
          <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
            <tr>
              <th class="text-right px-4 py-3">الاتجاه</th>
              <th class="text-right px-4 py-3">رقم الهاتف</th>
              <th class="text-right px-4 py-3">الرسالة</th>
              <th class="text-right px-4 py-3">الحالة</th>
              <th class="text-right px-4 py-3">التاريخ</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr v-for="msg in messages" :key="msg.id" class="border-t border-slate-100">
              <td class="px-4 py-3">
                <span :class="msg.direction === 'incoming' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700'" class="px-2 py-0.5 rounded-full text-xs">{{ msg.direction === 'incoming' ? 'وارد' : 'صادر' }}</span>
              </td>
              <td class="px-4 py-3 font-medium" dir="ltr">{{ msg.phone }}</td>
              <td class="px-4 py-3 text-slate-600 max-w-xs truncate" :title="msg.message">{{ msg.message }}</td>
              <td class="px-4 py-3">
                <span v-if="msg.direction === 'incoming'" class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs">مستلمة</span>
                <span v-else :class="msg.status === 'sent' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'" class="px-2 py-0.5 rounded-full text-xs">{{ msg.status === 'sent' ? 'ناجحة' : 'فاشلة' }}</span>
              </td>
              <td class="px-4 py-3 text-slate-500 text-xs">{{ msg.sent_at || msg.received_at }}</td>
            </tr>
            <tr v-if="messages.length === 0">
              <td colspan="5" class="text-center py-8 text-slate-400">لا توجد رسائل</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="pagination && pagination.last_page > 1" class="flex justify-between items-center mt-4 text-sm">
        <span class="text-slate-500">صفحة {{ pagination.current_page }} من {{ pagination.last_page }} ({{ pagination.total }})</span>
        <div class="flex gap-2">
          <button :disabled="!pagination.prev_page_url" @click="changePage(pagination.current_page - 1)" class="px-3 py-1 rounded border disabled:opacity-30">السابق</button>
          <button :disabled="!pagination.next_page_url" @click="changePage(pagination.current_page + 1)" class="px-3 py-1 rounded border disabled:opacity-30">التالي</button>
        </div>
      </div>
    </div>

    <!-- Received Tab (Fetch from Textbee) -->
    <div v-if="tab === 'received'">
      <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 mb-4 flex items-center justify-between">
        <p class="text-sm text-slate-600">اسحب الرسائل الواردة من Textbee</p>
        <button @click="fetchReceived" :disabled="fetching" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2">
          <svg v-if="fetching" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ fetching ? 'جاري السحب...' : 'سحب الرسائل الواردة' }}
        </button>
      </div>

      <div v-if="fetchResult" class="mb-4 text-sm" :class="fetchResult.success ? 'text-emerald-600' : 'text-red-600'">{{ fetchResult.message }}</div>

      <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div v-if="loading" class="text-center py-8 text-slate-400">جاري التحميل...</div>
        <table v-else class="w-full">
          <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
            <tr>
              <th class="text-right px-4 py-3">رقم المرسل</th>
              <th class="text-right px-4 py-3">الرسالة</th>
              <th class="text-right px-4 py-3">تاريخ الاستلام</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr v-for="msg in receivedMessages" :key="msg.id" class="border-t border-slate-100">
              <td class="px-4 py-3 font-medium" dir="ltr">{{ msg.sender || msg.phone }}</td>
              <td class="px-4 py-3 text-slate-600 max-w-xs truncate" :title="msg.message">{{ msg.message }}</td>
              <td class="px-4 py-3 text-slate-500 text-xs">{{ msg.received_at || msg.sent_at }}</td>
            </tr>
            <tr v-if="receivedMessages.length === 0">
              <td colspan="3" class="text-center py-8 text-slate-400">لا توجد رسائل واردة</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  data() {
    return {
      tab: 'send',
      stats: { total: 0, sent: 0, failed: 0, received: 0 },
      form: { target: 'all', phone: '', message: '', status: '', points_min: null, points_max: null, from_date: '', to_date: '' },
      targetCount: 0,
      sending: false,
      sendError: '',
      sendSuccess: '',
      // history
      messages: [],
      loading: false,
      historySearch: '',
      historyDirection: '',
      historyStatus: '',
      historyFrom: '',
      historyTo: '',
      perPage: 50,
      pagination: null,
      debounceTimer: null,
      // received
      receivedMessages: [],
      fetching: false,
      fetchResult: null,
    }
  },
  mounted() { this.loadStats(); this.countTarget() },
  methods: {
    async loadStats() {
      try {
        const res = await axios.get('/api/admin/v1/sms/stats')
        this.stats = res.data
      } catch (e) { console.error(e) }
    },
    async countTarget() {
      if (this.form.target === 'all') { this.targetCount = 'جميع المستخدمين'; return }
      try {
        const res = await axios.post('/api/admin/v1/sms/count-target', {
          target: this.form.target,
          phone: this.form.phone,
          status: this.form.status,
          points_min: this.form.points_min,
          points_max: this.form.points_max,
          from_date: this.form.from_date,
          to_date: this.form.to_date,
        })
        this.targetCount = res.data.count
      } catch (e) { console.error(e) }
    },
    async sendSms() {
      this.sendError = ''
      this.sendSuccess = ''
      this.sending = true
      try {
        const res = await axios.post('/api/admin/v1/sms/send', this.form)
        this.sendSuccess = res.data.message
        this.loadStats()
      } catch (e) {
        this.sendError = e.response?.data?.message || 'فشل الإرسال'
      } finally {
        this.sending = false
      }
    },
    async loadHistory() {
      this.loading = true
      try {
        const params = { per_page: this.perPage }
        if (this.historySearch) params.search = this.historySearch
        if (this.historyDirection) params.direction = this.historyDirection
        if (this.historyStatus) params.status = this.historyStatus
        if (this.historyFrom) params.from_date = this.historyFrom
        if (this.historyTo) params.to_date = this.historyTo
        if (this.pagination?.current_page) params.page = this.pagination.current_page

        const res = await axios.get('/api/admin/v1/sms/history', { params })
        this.messages = res.data.data
        this.pagination = { current_page: res.data.current_page, last_page: res.data.last_page, total: res.data.total, prev_page_url: res.data.prev_page_url, next_page_url: res.data.next_page_url }
      } catch (e) { console.error(e) }
      finally { this.loading = false }
    },
    debouncedLoad() {
      clearTimeout(this.debounceTimer)
      this.debounceTimer = setTimeout(() => { this.pagination = null; this.loadHistory() }, 400)
    },
    changePage(page) { this.pagination.current_page = page; this.loadHistory() },
    async loadReceived() {
      this.loading = true
      try {
        const res = await axios.get('/api/admin/v1/sms/history', { params: { direction: 'incoming', per_page: 100 } })
        this.receivedMessages = res.data.data
      } catch (e) { console.error(e) }
      finally { this.loading = false }
    },
    async fetchReceived() {
      this.fetching = true
      this.fetchResult = null
      try {
        const res = await axios.post('/api/admin/v1/sms/fetch-received')
        const count = res.data.fetched
        this.fetchResult = { success: true, message: count > 0 ? `تم سحب ${count} رسالة جديدة` : 'لا توجد رسائل جديدة' }
        this.loadReceived()
        this.loadStats()
      } catch (e) {
        this.fetchResult = { success: false, message: 'فشل سحب الرسائل' }
      } finally {
        this.fetching = false
      }
    },
  },
}
</script>
