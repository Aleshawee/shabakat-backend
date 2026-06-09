<template>
  <div class="p-6" dir="rtl">
    <h1 class="text-2xl font-bold mb-6 text-slate-800">لوحة المالك</h1>

    <div v-if="loading" class="text-center py-8 text-slate-400">جاري التحميل...</div>

    <template v-else>
      <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-gradient-to-br from-indigo-500 to-violet-600 rounded-xl p-5 shadow-sm text-white">
          <p class="text-sm opacity-80">عدد الشبكات</p>
          <p class="text-3xl font-bold mt-1">{{ data.total_networks }}</p>
        </div>
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl p-5 shadow-sm text-white">
          <p class="text-sm opacity-80">إجمالي المستخدمين</p>
          <p class="text-3xl font-bold mt-1">{{ data.total_users }}</p>
        </div>
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl p-5 shadow-sm text-white">
          <p class="text-sm opacity-80">المدراء</p>
          <p class="text-3xl font-bold mt-1">{{ data.total_admins }}</p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
          <h2 class="font-semibold text-slate-800">الشبكات</h2>
          <router-link to="/owner/networks" class="text-sm text-violet-600 hover:text-violet-800 font-medium">إدارة الشبكات ←</router-link>
        </div>
        <table class="w-full">
          <thead class="bg-slate-50 text-xs text-slate-500">
            <tr>
              <th class="text-right px-4 py-3">الاسم</th>
              <th class="text-right px-4 py-3">صاحب الشبكة</th>
              <th class="text-right px-4 py-3">المستخدمين</th>
              <th class="text-right px-4 py-3">الحالة</th>
              <th class="text-right px-4 py-3">تاريخ الإنشاء</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr v-for="net in data.networks" :key="net.id" class="border-t border-slate-100">
              <td class="px-4 py-3 font-medium">{{ net.name }}</td>
              <td class="px-4 py-3 text-slate-600">{{ net.owner_name || '—' }}</td>
              <td class="px-4 py-3">{{ net.users_count }}</td>
              <td class="px-4 py-3">
                <span :class="net.status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'" class="px-2 py-0.5 rounded-full text-xs">{{ net.status === 'active' ? 'نشط' : 'غير نشط' }}</span>
              </td>
              <td class="px-4 py-3 text-slate-500 text-xs">{{ net.created_at }}</td>
            </tr>
            <tr v-if="data.networks?.length === 0">
              <td colspan="5" class="text-center py-8 text-slate-400">لا توجد شبكات</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  data() {
    return { loading: false, data: { total_networks: 0, total_users: 0, total_admins: 0, networks: [] } }
  },
  mounted() { this.load() },
  methods: {
    async load() {
      this.loading = true
      try {
        const res = await axios.get('/api/admin/v1/owner/stats')
        this.data = res.data
      } catch (e) { console.error(e) }
      finally { this.loading = false }
    },
  },
}
</script>
