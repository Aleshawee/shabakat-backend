<template>
  <div class="p-6" dir="rtl">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
      <h1 class="text-2xl font-bold text-slate-800">إدارة مدراء الشبكة</h1>
      <button @click="openAdd = true" class="bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
        + إضافة مدير
      </button>
    </div>

    <div v-if="loading" class="text-center py-12 text-slate-400">جاري التحميل...</div>

    <template v-else>
      <!-- Desktop table -->
      <div class="hidden md:block bg-white rounded-xl shadow-sm border overflow-x-auto">
        <table class="w-full min-w-[600px]">
          <thead class="bg-slate-50 text-xs text-slate-500">
            <tr>
              <th class="text-right px-4 py-3">الاسم</th>
              <th class="text-right px-4 py-3">البريد الإلكتروني</th>
              <th class="text-right px-4 py-3">الحالة</th>
              <th class="text-right px-4 py-3">تاريخ التسجيل</th>
              <th class="text-left px-4 py-3">إجراءات</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr v-for="admin in pageItems" :key="admin.id" class="border-t border-slate-100">
              <td class="px-4 py-3 font-medium">{{ admin.name }}</td>
              <td class="px-4 py-3 text-slate-600">{{ admin.email }}</td>
              <td class="px-4 py-3">
                <span :class="admin.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'" class="px-2 py-0.5 rounded-full text-xs">
                  {{ admin.status === 'active' ? 'نشط' : 'غير نشط' }}
                </span>
              </td>
              <td class="px-4 py-3 text-slate-500 text-xs">{{ formatDate(admin.created_at) }}</td>
              <td class="px-4 py-3 text-left whitespace-nowrap">
                <button @click="editAdmin(admin)" class="text-violet-600 hover:text-violet-800 text-xs font-medium ml-2">تعديل</button>
                <button @click="confirmDelete(admin)" class="text-red-500 hover:text-red-700 text-xs font-medium">حذف</button>
              </td>
            </tr>
            <tr v-if="admins.length === 0">
              <td colspan="5" class="text-center py-8 text-slate-400">لا يوجد مدراء</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile cards -->
      <div class="md:hidden space-y-3">
        <div v-for="admin in pageItems" :key="admin.id" class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="font-bold text-sm">{{ admin.name }}</span>
            <span :class="admin.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'" class="px-2 py-0.5 rounded-full text-xs">
              {{ admin.status === 'active' ? 'نشط' : 'غير نشط' }}
            </span>
          </div>
          <div class="text-xs text-slate-500 mb-1 ltr" dir="ltr">{{ admin.email }}</div>
          <div class="text-xs text-slate-400 mb-3">{{ formatDate(admin.created_at) }}</div>
          <div class="flex gap-2">
            <button @click="editAdmin(admin)" class="flex-1 text-xs bg-violet-50 text-violet-700 border border-violet-200 rounded-lg py-2 font-medium">تعديل</button>
            <button @click="confirmDelete(admin)" class="flex-1 text-xs bg-red-50 text-red-600 border border-red-200 rounded-lg py-2 font-medium">حذف</button>
          </div>
        </div>
        <div v-if="admins.length === 0" class="bg-white rounded-xl shadow-sm border p-8 text-center text-slate-400">لا يوجد مدراء</div>
      </div>
    </template>

    <!-- Pagination -->
    <div v-if="admins.length > perPage" class="flex items-center justify-between mt-4">
      <div class="text-xs text-slate-400">{{ admins.length }} مدير</div>
      <div class="flex items-center gap-2">
        <button @click="page = Math.max(1, page - 1)" :disabled="page <= 1" class="px-3 py-1 rounded border text-sm disabled:opacity-30">السابق</button>
        <span class="text-sm text-slate-500">{{ page }} / {{ lastPage }}</span>
        <button @click="page = Math.min(lastPage, page + 1)" :disabled="page >= lastPage" class="px-3 py-1 rounded border text-sm disabled:opacity-30">التالي</button>
      </div>
    </div>

    <!-- Add / Edit Dialog -->
    <Teleport to="body">
      <div v-if="openAdd || editTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="closeDialog">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4" dir="rtl">
          <h3 class="text-lg font-bold text-slate-800 mb-4">{{ editTarget ? 'تعديل مدير' : 'إضافة مدير جديد' }}</h3>

          <div class="mb-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">الاسم</label>
            <input v-model="form.name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="اسم المدير">
          </div>
          <div class="mb-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">البريد الإلكتروني</label>
            <input v-model="form.email" type="email" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr" placeholder="email@example.com">
          </div>
          <div class="mb-3">
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ editTarget ? 'كلمة مرور جديدة (اختياري)' : 'كلمة المرور' }}</label>
            <input v-model="form.password" type="password" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr" :placeholder="editTarget ? 'اتركه فارغاً بدون تغيير' : 'كلمة المرور'">
          </div>
          <div v-if="editTarget" class="mb-4">
            <label class="block text-sm font-medium text-slate-700 mb-1">الحالة</label>
            <select v-model="form.status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
              <option value="active">نشط</option>
              <option value="inactive">غير نشط</option>
            </select>
          </div>

          <div v-if="formError" class="mb-3 text-sm text-red-600">{{ formError }}</div>

          <div class="flex gap-2 justify-end">
            <button @click="closeDialog" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">إلغاء</button>
            <button @click="saveAdmin" :disabled="saving" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-violet-700 disabled:opacity-50">
              {{ saving ? 'جاري الحفظ...' : (editTarget ? 'حفظ التعديلات' : 'إضافة') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Delete Confirm Dialog -->
    <Teleport to="body">
      <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="deleteTarget = null">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4" dir="rtl">
          <h3 class="text-lg font-bold text-slate-800 mb-2">تأكيد الحذف</h3>
          <p class="text-sm text-slate-600 mb-4">هل أنت متأكد من حذف المدير "{{ deleteTarget.name }}"؟</p>
          <div class="flex gap-2 justify-end">
            <button @click="deleteTarget = null" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">إلغاء</button>
            <button @click="deleteAdmin" :disabled="deleting" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 disabled:opacity-50">
              {{ deleting ? 'جاري الحذف...' : 'حذف' }}
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
      admins: [],
      loading: false,
      page: 1,
      perPage: 20,
      openAdd: false,
      editTarget: null,
      deleteTarget: null,
      saving: false,
      deleting: false,
      formError: '',
      form: { name: '', email: '', password: '', status: 'active' },
    }
  },
  computed: {
    pageItems() {
      const start = (this.page - 1) * this.perPage
      return this.admins.slice(start, start + this.perPage)
    },
    lastPage() { return Math.ceil(this.admins.length / this.perPage) || 1 },
  },
  watch: {
    admins() { this.page = 1 },
  },
  mounted() { this.fetchAdmins() },
  methods: {
    formatDate(d) {
      if (!d) return '—'
      const dt = new Date(d)
      if (isNaN(dt.getTime())) return d.slice(0, 10)
      return dt.toLocaleDateString('ar-SA', { year: 'numeric', month: 'short', day: 'numeric' })
    },
    async fetchAdmins() {
      this.loading = true
      try {
        const res = await axios.get('/api/admin/v1/network-admins')
        this.admins = res.data
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    editAdmin(admin) {
      this.editTarget = admin
      this.form = { name: admin.name, email: admin.email, password: '', status: admin.status || 'active' }
      this.formError = ''
    },
    closeDialog() {
      this.openAdd = false
      this.editTarget = null
      this.form = { name: '', email: '', password: '', status: 'active' }
      this.formError = ''
    },
    async saveAdmin() {
      if (!this.form.name || !this.form.email) return
      if (!this.editTarget && !this.form.password) return
      this.saving = true
      this.formError = ''
      try {
        if (this.editTarget) {
          const payload = { name: this.form.name, email: this.form.email, status: this.form.status }
          if (this.form.password) payload.password = this.form.password
          await axios.put('/api/admin/v1/network-admins/' + this.editTarget.id, payload)
        } else {
          await axios.post('/api/admin/v1/network-admins', this.form)
        }
        this.closeDialog()
        this.fetchAdmins()
      } catch (e) {
        this.formError = e.response?.data?.message || 'حدث خطأ'
      } finally {
        this.saving = false
      }
    },
    confirmDelete(admin) {
      this.deleteTarget = admin
    },
    async deleteAdmin() {
      if (!this.deleteTarget) return
      this.deleting = true
      try {
        await axios.delete('/api/admin/v1/network-admins/' + this.deleteTarget.id)
        this.deleteTarget = null
        this.fetchAdmins()
      } catch (e) {
        alert(e.response?.data?.message || 'حدث خطأ')
      } finally {
        this.deleting = false
      }
    },
  },
}
</script>
