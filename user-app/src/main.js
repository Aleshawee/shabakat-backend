import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import axios from 'axios'
import { getNetworkSlug } from './utils/network.js'
import App from './App.vue'
import Login from './pages/Login.vue'
import NetworkSelect from './pages/NetworkSelect.vue'
import Dashboard from './pages/Dashboard.vue'
import LuckyBox from './pages/LuckyBox.vue'
import LuckyWheel from './pages/LuckyWheel.vue'
import Transfer from './pages/Transfer.vue'
import Absher from './pages/Absher.vue'
import Rewards from './pages/Rewards.vue'
import History from './pages/History.vue'
import Profile from './pages/Profile.vue'
import More from './pages/More.vue'
import CardInfo from './pages/CardInfo.vue'
import RedeemCard from './pages/RedeemCard.vue'
import SportPredictions from './pages/SportPredictions.vue'
import './style.css'

axios.defaults.baseURL = import.meta.env.VITE_API_BASE || ''

axios.interceptors.request.use(config => {
  const token = localStorage.getItem('user_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  const slug = getNetworkSlug() || (JSON.parse(localStorage.getItem('network') || '{}')?.slug)
  if (slug) config.headers['X-Tenant'] = slug
  return config
})

const authMeta = { requiresAuth: true, showNav: true }

const routes = [
  { path: '/', redirect: '/select-network' },
  { path: '/select-network', component: NetworkSelect },
  { path: '/login', component: Login },
  { path: '/dashboard', component: Dashboard, meta: authMeta },
  { path: '/lucky-box', component: LuckyBox, meta: authMeta },
  { path: '/lucky-wheel', component: LuckyWheel, meta: authMeta },
  { path: '/transfer', component: Transfer, meta: authMeta },
  { path: '/absher', component: Absher, meta: authMeta },
  { path: '/rewards', component: Rewards, meta: authMeta },
  { path: '/redeem-card', component: RedeemCard, meta: authMeta },
  { path: '/history', component: History, meta: authMeta },
  { path: '/profile', component: Profile, meta: authMeta },
  { path: '/more', component: More, meta: authMeta },
  { path: '/card-info', component: CardInfo, meta: authMeta },
  { path: '/sport-predictions', component: SportPredictions, meta: authMeta },
]

const router = createRouter({
  history: createWebHistory('/user-app/'),
  routes,
})

function getCurrentNetwork() {
  return getNetworkSlug() || (JSON.parse(localStorage.getItem('network') || '{}')?.slug)
}

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('user_token')
  if (to.meta.requiresAuth && !token) { next('/login'); return }
  const publicRoutes = ['/select-network', '/login']
  if (!token && !publicRoutes.includes(to.path) && !getCurrentNetwork()) {
    next('/select-network')
    return
  }
  next()
})

createApp(App).use(router).mount('#app')
