<template>
  <div :dir="'rtl'" class="font-tajawal">
    <TopAlertBar v-if="$route.meta.requiresAuth" />
    <router-view />
    <BottomNavigation v-if="$route.meta.showNav" />
  </div>
</template>

<script>
import BottomNavigation from './components/BottomNavigation.vue'
import TopAlertBar from './components/TopAlertBar.vue'
export default {
  components: { BottomNavigation, TopAlertBar },
}
</script>
