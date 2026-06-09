<template>
  <div class="p-6" dir="rtl">
    <h1 class="text-2xl font-bold mb-6 text-slate-800">إدارة الشبكات</h1>

    <div v-if="loading" class="text-center py-8 text-slate-400">جاري التحميل...</div>

    <template v-else>
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
          <h2 class="font-semibold text-slate-800">جميع الشبكات</h2>
          <button @click="openCreate" class="bg-violet-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-violet-700">+ إضافة شبكة</button>
        </div>
        <table class="w-full">
          <thead class="bg-slate-50 text-xs text-slate-500">
            <tr>
              <th class="text-right px-4 py-3">الاسم</th>
              <th class="text-right px-4 py-3">الرابط</th>
              <th class="text-right px-4 py-3">صاحبها</th>
              <th class="text-right px-4 py-3">المستخدمين</th>
              <th class="text-right px-4 py-3">المدراء</th>
              <th class="text-right px-4 py-3">الحالة</th>
              <th class="text-left px-4 py-3">إجراءات</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr v-for="net in networks" :key="net.id" class="border-t border-slate-100">
              <td class="px-4 py-3 font-medium">{{ net.name }}</td>
              <td class="px-4 py-3 text-slate-500 text-xs" dir="ltr">{{ net.slug }}</td>
              <td class="px-4 py-3 text-slate-600">{{ net.owner_name || '—' }}</td>
              <td class="px-4 py-3">{{ net.users_count || 0 }}</td>
              <td class="px-4 py-3">{{ net.admins?.length || 0 }}</td>
              <td class="px-4 py-3">
                <span :class="net.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'" class="px-2 py-0.5 rounded-full text-xs">{{ net.status === 'active' ? 'نشط' : 'غير نشط' }}</span>
              </td>
              <td class="px-4 py-3 text-left">
                <button @click="openEdit(net)" class="text-indigo-600 hover:text-indigo-800 text-sm ml-2">تعديل</button>
                <button @click="openAdmins(net)" class="text-emerald-600 hover:text-emerald-800 text-sm ml-2">المدراء</button>
                <button @click="confirmDelete(net)" class="text-red-600 hover:text-red-800 text-sm">حذف</button>
              </td>
            </tr>
            <tr v-if="networks.length === 0">
              <td colspan="7" class="text-center py-8 text-slate-400">لا توجد شبكات</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>

    <!-- Network Form Dialog -->
    <Teleport to="body">
      <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showForm = false">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4" dir="rtl">
          <h3 class="text-lg font-bold text-slate-800 mb-4">{{ editing ? 'تعديل شبكة' : 'إضافة شبكة جديدة' }}</h3>
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">اسم الشبكة</label>
              <input v-model="form.name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">الرابط (slug)</label>
              <input v-model="form.slug" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr">
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">اسم صاحب الشبكة</label>
              <input v-model="form.owner_name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">الهاتف</label>
                <input v-model="form.phone" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr">
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">البريد</label>
                <input v-model="form.email" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr">
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">نسبة العمولة (%)</label>
                <input v-model.number="form.commission_rate" type="number" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">الحالة</label>
                <select v-model="form.status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                  <option value="active">نشط</option>
                  <option value="inactive">غير نشط</option>
                </select>
              </div>
            </div>
          </div>
          <div v-if="formError" class="mt-3 text-sm text-red-600">{{ formError }}</div>
          <div class="flex gap-2 justify-end mt-6">
            <button @click="showForm = false" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">إلغاء</button>
            <button @click="save" :disabled="saving" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-violet-700 disabled:opacity-50">{{ saving ? 'جاري الحفظ...' : 'حفظ' }}</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Admins Dialog -->
    <Teleport to="body">
      <div v-if="showAdmins" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showAdmins = false">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg mx-4" dir="rtl">
          <h3 class="text-lg font-bold text-slate-800 mb-4">مدراء: {{ adminNetwork?.name }}</h3>

          <div class="space-y-2 mb-4">
            <div v-for="adm in adminsList" :key="adm.id" class="flex items-center justify-between bg-slate-50 rounded-lg px-3 py-2">
              <div>
                <p class="text-sm font-medium">{{ adm.name }}</p>
                <p class="text-xs text-slate-500">{{ adm.email }}</p>
              </div>
              <button @click="deleteAdmin(adm)" class="text-red-500 hover:text-red-700 text-xs">حذف</button>
            </div>
            <p v-if="adminsList.length === 0" class="text-sm text-slate-400 text-center py-3">لا يوجد مدراء</p>
          </div>

          <div class="border-t border-slate-200 pt-4">
            <h4 class="text-sm font-semibold text-slate-700 mb-3">إضافة مدير جديد</h4>
            <div class="space-y-2">
              <input v-model="adminForm.name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="الاسم">
              <input v-model="adminForm.email" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr" placeholder="البريد الإلكتروني">
              <input v-model="adminForm.password" type="password" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" dir="ltr" placeholder="كلمة المرور">
            </div>
            <div v-if="adminError" class="mt-2 text-sm text-red-600">{{ adminError }}</div>
            <button @click="addAdmin" :disabled="addingAdmin" class="mt-3 bg-emerald-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-emerald-700 disabled:opacity-50">{{ addingAdmin ? 'جاري...' : 'إضافة مدير' }}</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Delete Confirmation -->
    <Teleport to="body">
      <div v-if="showDelete" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showDelete = false">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4" dir="rtl">
          <h3 class="text-lg font-bold text-slate-800 mb-2">تأكيد الحذف</h3>
          <p class="text-sm text-slate-600 mb-4">هل أنت متأكد من حذف الشبكة "{{ deleting?.name }}"؟</p>
          <div class="flex gap-2 justify-end">
            <button @click="showDelete = false" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">إلغاء</button>
            <button @click="deleteNetwork" :disabled="saving" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700">حذف</button>
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
      loading: false, saving: false, addingAdmin: false,
      networks: [], showForm: false, editing: false, formError: '',
      form: { name: '', slug: '', owner_name: '', phone: '', email: '', commission_rate: 0, status: 'active' },
      showAdmins: false, adminNetwork: null, adminsList: [], adminError: '',
      adminForm: { name: '', email: '', password: '' },
      showDelete: false, deleting: null,
    }
  },
  mounted() { this.load() },
  methods: {
    async load() {
      this.loading = true
      try {
        const res = await axios.get('/api/admin/v1/owner/networks')
        this.networks = res.data
      } catch (e) { console.error(e) }
      finally { this.loading = false }
    },
    openCreate() {
      this.editing = false
      this.form = { name: '', slug: '', owner_name: '', phone: '', email: '', commission_rate: 0, status: 'active' }
      this.formError = ''
      this.showForm = true
    },
    openEdit(net) {
      this.editing = true
      this.form = { ...net }
      this.formError = ''
      this.showForm = true
    },
    async save() {
      this.saving = true
      this.formError = ''
      try {
        if (this.editing) {
          await axios.put(`/api/admin/v1/owner/networks/${this.form.id}`, this.form)
        } else {
          await axios.post('/api/admin/v1/owner/networks', {
            ...this.form,
            subdomain: this.form.slug,
          })
        }
        this.showForm = false
        this.load()
      } catch (e) {
        this.formError = e.response?.data?.message || 'حدث خطأ'
      } finally { this.saving = false }
    },
    async openAdmins(net) {
      this.adminNetwork = net
      this.adminsList = []
      this.adminForm = { name: '', email: '', password: '' }
      this.adminError = ''
      this.showAdmins = true
      try {
        const res = await axios.get(`/api/admin/v1/owner/networks/${net.id}/admins`)
        this.adminsList = res.data
      } catch (e) { console.error(e) }
    },
    async addAdmin() {
      if (!this.adminForm.name || !this.adminForm.email || !this.adminForm.password) {
        this.adminError = 'يرجى تعبئة جميع الحقول'
        return
      }
      this.addingAdmin = true
      this.adminError = ''
      try {
        const res = await axios.post(`/api/admin/v1/owner/networks/${this.adminNetwork.id}/admins`, this.adminForm)
        this.adminsList.push(res.data)
        this.adminForm = { name: '', email: '', password: '' }
      } catch (e) {
        this.adminError = e.response?.data?.message || 'فشل الإضافة'
      } finally { this.addingAdmin = false }
    },
    async deleteAdmin(adm) {
      if (!confirm(`حذف المدير ${adm.name}?`)) return
      try {
        await axios.delete(`/api/admin/v1/owner/networks/${this.adminNetwork.id}/admins/${adm.id}`)
        this.adminsList = this.adminsList.filter(a => a.id !== adm.id)
      } catch (e) { console.error(e) }
    },
    confirmDelete(net) {
      this.deleting = net
      this.showDelete = true
    },
    async deleteNetwork() {
      this.saving = true
      try {
        await axios.delete(`/api/admin/v1/owner/networks/${this.deleting.id}`)
        this.showDelete = false
        this.load()
      } catch (e) { console.error(e) }
      finally { this.saving = false }
    },
  },
}
</script>
