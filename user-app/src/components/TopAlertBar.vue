<template>
  <div>
    <div v-if="networkName" class="bg-white/80 backdrop-blur border-b border-slate-200 px-4 py-1.5 text-xs text-slate-500 text-center">
      {{ networkName }}
    </div>
    <div v-if="data.enabled" class="bg-gradient-to-r from-amber-500 to-orange-500 text-white px-4 py-2.5 text-center text-sm font-medium shadow-md relative">
      <p class="max-w-lg mx-auto">{{ data.message }}</p>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  data() {
    return {
      data: { enabled: false, message: '', days_left: 0, reset_date: null },
      networkName: '',
    }
  },
  created() {
    const net = JSON.parse(localStorage.getItem('network') || '{}')
    this.networkName = net.name || ''
  },
  async created() {
    const token = localStorage.getItem('user_token')
    if (!token) return
    try {
      const { data } = await axios.get('/api/v1/user/reset-notification')
      this.data = data
    } catch (e) { /* ignore */ }
  },
}
</script>
