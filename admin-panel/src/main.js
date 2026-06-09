import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'
import { getNetworkSlug } from './utils/network.js'
import App from './App.vue'
import Login from './pages/Login.vue'
import AdminLayout from './layouts/AdminLayout.vue'
import OwnerLayout from './layouts/OwnerLayout.vue'
import Home from './pages/Home.vue'
import Categories from './pages/categories/Index.vue'
import Rewards from './pages/rewards/Index.vue'
import Cards from './pages/cards/Index.vue'
import Users from './pages/users/Index.vue'
import Notifications from './pages/notifications/Index.vue'
import Banners from './pages/banners/Index.vue'
import Sms from './pages/settings/Sms.vue'
import SmsCampaign from './pages/sms/Index.vue'
import Entertainment from './pages/entertainment/Index.vue'
import Predictions from './pages/predictions/Index.vue'
import Absher from './pages/absher/Index.vue'
import Restriction from './pages/settings/Restrictions.vue'
import NetworkAdmins from './pages/settings/NetworkAdmins.vue'
import Transfer from './pages/transfer/Index.vue'
import Redemptions from './pages/redemptions/Index.vue'
import Analytics from './pages/Analytics.vue'
import OwnerDashboard from './pages/OwnerDashboard.vue'
import OwnerNetworks from './pages/OwnerNetworks.vue'
import './style.css'

axios.defaults.baseURL = import.meta.env.VITE_API_BASE || ''

axios.interceptors.request.use(config => {
  const token = localStorage.getItem('admin_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  const network = getNetworkSlug()
  if (network) {
    config.headers['X-Tenant'] = network
  } else {
    const admin = JSON.parse(localStorage.getItem('admin') || '{}')
    if (admin.tenant_id) config.headers['X-Tenant'] = admin.tenant_id
  }
  return config
})

function getAdmin() {
  try { return JSON.parse(localStorage.getItem('admin') || '{}') }
  catch { return {} }
}

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      { path: '', component: Home },
      { path: 'categories', component: Categories },
      { path: 'rewards', component: Rewards },
      { path: 'cards', component: Cards },
      { path: 'users', component: Users },
      { path: 'notifications', component: Notifications },
      { path: 'banners', component: Banners },
      { path: 'sms', component: SmsCampaign },
      { path: 'entertainment', component: Entertainment },
      { path: 'predictions', component: Predictions },
      { path: 'absher', component: Absher },
      { path: 'restrictions', component: Restriction },
      { path: 'transfer', component: Transfer },
      { path: 'redemptions', component: Redemptions },
      { path: 'analytics', component: Analytics },
      { path: 'network-admins', component: NetworkAdmins },
    ],
  },
  {
    path: '/owner',
    component: OwnerLayout,
    meta: { requiresAuth: true, role: 'owner' },
    children: [
      { path: '', component: OwnerDashboard },
      { path: 'networks', component: OwnerNetworks },
      { path: 'settings/sms', component: Sms },
    ],
  },
]

const router = createRouter({
  history: createWebHistory('/admin/'),
  routes,
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('admin_token')
  if (to.meta.requiresAuth && !token) {
    next('/login')
    return
  }
  if (to.meta.role) {
    const admin = getAdmin()
    if (admin.role !== to.meta.role) {
      next('/login')
      return
    }
  }
  next()
})

createApp(App).use(router).mount('#app')
