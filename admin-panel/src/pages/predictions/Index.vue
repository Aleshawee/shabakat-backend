<template>
  <div class="p-6" dir="rtl">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-slate-800">
        <span class="ml-2">⚽</span> مسابقات التوقعات
      </h1>
    </div>

    <!-- Tabs -->
    <div class="bg-gradient-to-l from-indigo-900 to-blue-900 rounded-xl p-2 mb-6 shadow-lg">
      <div class="flex gap-2">
        <button @click="tab='matches'" :class="tab==='matches' ? 'bg-white text-indigo-900 shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10'" class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">
          🌍 مباريات اليوم
        </button>
        <button @click="tab='events'; loadEvents()" :class="tab==='events' ? 'bg-white text-indigo-900 shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10'" class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">
          📅 الأحداث ({{ eventsTotal }})
        </button>
        <button @click="tab='settings'; loadSettings()" :class="tab==='settings' ? 'bg-white text-indigo-900 shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10'" class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">
          ⚙️ الإعدادات
        </button>
        <button @click="tab='stats'; loadStats()" :class="tab==='stats' ? 'bg-white text-indigo-900 shadow-md' : 'text-white/80 hover:text-white hover:bg-white/10'" class="px-5 py-2.5 rounded-lg text-sm font-semibold transition-all">
          📊 الإحصائيات
        </button>
      </div>
    </div>

    <!-- ==================== TAB 1: MATCHES ==================== -->
    <div v-if="tab==='matches'">
      <!-- Date selector -->
      <div class="bg-white rounded-xl shadow-sm border p-4 mb-5 flex items-center gap-3 flex-wrap">
        <span class="font-bold text-sm"><span class="ml-1">📅</span>اختر التاريخ:</span>
        <button v-for="d in datePresets" :key="d.offset" @click="selectedDateOffset=d.offset; selectedDate=dateOffset(d.offset); loadMatches()"
          :class="selectedDateOffset===d.offset ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-300 hover:border-indigo-400'"
          class="px-4 py-2 rounded-full text-sm font-semibold border transition-all">
          {{ d.label }}
        </button>
        <input type="date" v-model="customDate" @change="selectedDateOffset=null; selectedDate=customDate; loadMatches()" class="border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
      </div>

      <!-- API quota -->
      <div v-if="apiQuota" class="text-xs text-slate-400 mb-3 text-center">
        رصيد API المتبقي: <span :class="apiQuota < 20 ? 'text-red-500 font-bold' : 'text-emerald-600 font-bold'">{{ apiQuota }}</span>
      </div>

      <!-- Loading -->
      <div v-if="matchesLoading" class="text-center py-16">
        <div class="inline-block w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mb-3"></div>
        <p class="text-slate-500 text-sm">جاري تحميل المباريات...</p>
      </div>

      <!-- Error -->
      <div v-else-if="matchesError" class="bg-red-50 border border-red-200 rounded-xl p-8 text-center">
        <p class="text-red-600 mb-3">{{ matchesError }}</p>
        <button @click="loadMatches" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm">إعادة المحاولة</button>
      </div>

      <!-- No matches -->
      <div v-else-if="!leagues.length" class="bg-white rounded-xl shadow-sm border p-12 text-center">
        <div class="text-6xl mb-4">⚽</div>
        <h3 class="text-lg font-bold text-slate-700 mb-2">لا توجد مباريات</h3>
        <p class="text-slate-400 text-sm">لا توجد مباريات للبطولات الكبرى في هذا التاريخ</p>
        <button @click="openCreateModal()" class="mt-5 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition-colors">
          + إنشاء حدث يدوي
        </button>
      </div>

      <!-- Match cards -->
      <div v-else class="space-y-6">
        <div class="flex justify-between items-center">
          <p class="text-xs text-slate-400">إجمالي {{ matchesTotal }} مباراة</p>
          <button @click="loadMatches" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">🔄 تحديث</button>
        </div>

        <div v-for="league in leagues" :key="league.id" class="bg-white rounded-xl shadow-sm border overflow-hidden">
          <!-- League header -->
          <div class="bg-gradient-to-l from-indigo-800 to-blue-700 text-white px-5 py-3 flex items-center gap-3">
            <img v-if="league.badge_url" :src="league.badge_url" class="w-7 h-7 rounded-full bg-white/20 object-contain p-0.5" @error="$event.target.style.display='none'" />
            <span class="font-bold text-sm">{{ league.name }}</span>
            <span class="text-xs text-indigo-300">({{ league.country }})</span>
            <span v-if="league.matches.some(m => !m.is_finished && !m.is_live)" class="mr-auto">
              <button @click="addAllToEvents(league)" class="text-xs bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full transition-colors">
                + إضافة الكل كأحداث
              </button>
            </span>
          </div>

          <!-- Match cards grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-3">
            <div v-for="match in league.matches" :key="match.id" class="bg-white rounded-xl border border-slate-100 shadow-sm hover:shadow-md transition-all overflow-hidden">
              <!-- Top bar: status + round -->
              <div class="flex items-center gap-2 px-4 pt-3 pb-1">
                <span v-if="match.is_live" class="text-[11px] bg-red-500 text-white px-2.5 py-0.5 rounded-full font-bold animate-pulse flex items-center gap-1">
                  <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span> مباشر
                </span>
                <span v-else-if="match.is_finished" class="text-[11px] bg-slate-400 text-white px-2.5 py-0.5 rounded-full flex items-center gap-1">
                  ✅ انتهت
                </span>
                <span v-else class="text-[11px] bg-emerald-500 text-white px-2.5 py-0.5 rounded-full font-bold">
                  📅 قادمة
                </span>
                <span v-if="match.round" class="text-[11px] mr-auto text-slate-400 bg-slate-50 px-2 py-0.5 rounded-full font-medium">
                  الجولة {{ match.round }}
                </span>
                <span v-else class="mr-auto"></span>
              </div>

              <!-- Teams row -->
              <div class="flex items-center justify-between px-4 py-2 gap-3">
                <!-- Home team -->
                <div class="flex items-center gap-2.5 flex-1 min-w-0">
                  <div class="w-11 h-11 rounded-full bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 shadow-sm">
                    <img v-if="match.home_img" :src="match.home_img" class="w-8 h-8 object-contain" @error="$event.target.style.display='none'" />
                    <span v-else class="text-lg">⚽</span>
                  </div>
                  <span class="font-bold text-sm truncate">{{ match.home_team }}</span>
                </div>

                <!-- Center: time above VS / score -->
                <div class="shrink-0 flex flex-col items-center gap-0.5">
                  <span v-if="!match.is_finished && !match.is_live && match.match_time" class="text-[11px] text-amber-700 font-bold bg-amber-100 border border-amber-200 px-3 py-1 rounded-lg shadow-sm flex items-center gap-1 leading-none">
                    🕒 {{ formatCardTime(match.match_time) }}
                  </span>
                  <div v-if="match.is_finished || match.is_live" class="bg-gradient-to-l from-indigo-50 to-blue-50 border border-indigo-100 text-indigo-700 font-extrabold text-lg px-4 py-1.5 rounded-xl min-w-[70px] text-center" dir="ltr">
                    {{ match.home_score ?? '-' }}<span class="text-indigo-300 mx-1">-</span>{{ match.away_score ?? '-' }}
                  </div>
                  <div v-else class="bg-gradient-to-l from-emerald-50 to-teal-50 border border-emerald-100 text-emerald-600 font-bold text-sm px-5 py-2 rounded-xl text-center">
                    VS
                  </div>
                </div>

                <!-- Away team -->
                <div class="flex items-center gap-2.5 flex-1 min-w-0 justify-end">
                  <span class="font-bold text-sm truncate">{{ match.away_team }}</span>
                  <div class="w-11 h-11 rounded-full bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0 shadow-sm">
                    <img v-if="match.away_img" :src="match.away_img" class="w-8 h-8 object-contain" @error="$event.target.style.display='none'" />
                    <span v-else class="text-lg">⚽</span>
                  </div>
                </div>
              </div>

              <!-- Add event action (upcoming only) -->
              <div v-if="!match.is_finished && !match.is_live" class="px-4 pb-3">
                <button @click="matchToEvent(match, league)" class="w-full text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 hover:border-emerald-300 py-2 rounded-lg font-medium transition-colors flex items-center justify-center gap-1.5">
                  <span class="text-emerald-500 text-sm leading-none">+</span> إضافة كحدث مسابقة
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== TAB 2: EVENTS ==================== -->
    <div v-if="tab==='events'">
      <!-- Guide -->
      <div class="bg-gradient-to-l from-blue-50 to-purple-50 rounded-xl border border-blue-100 p-4 mb-5">
        <div class="flex items-center gap-2 mb-3">
          <span class="text-yellow-500 text-lg">💡</span>
          <strong class="text-sm">كيف تعمل مسابقات التوقعات؟</strong>
        </div>
        <div class="grid grid-cols-4 gap-3 text-xs">
          <div v-for="(step, si) in steps" :key="si" class="flex items-center gap-2">
            <span class="bg-indigo-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0">{{ si+1 }}</span>
            <div><strong>{{ step.title }}</strong><br><span class="text-slate-500">{{ step.desc }}</span></div>
          </div>
        </div>
      </div>

      <!-- Status filters -->
      <div class="bg-white rounded-xl shadow-sm border p-3 mb-5">
        <div class="flex gap-2 flex-wrap">
          <button v-for="f in statusFilters" :key="f.value" @click="statusFilter=f.value; loadEvents()"
            :class="statusFilter===f.value ? f.activeClass : 'bg-white text-slate-600 border-slate-300 hover:border-indigo-400'"
            class="px-4 py-2 rounded-lg text-sm font-medium border transition-all">
            {{ f.icon }} {{ f.label }}
          </button>
          <div class="mr-auto">
            <button @click="openCreateModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
              + حدث جديد
            </button>
          </div>
        </div>
      </div>

      <!-- Events grid -->
      <div v-if="events.length" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div v-for="ev in events" :key="ev.id" class="bg-white rounded-xl shadow-sm border overflow-hidden hover:shadow-md transition-shadow">
          <!-- Status bar -->
          <div :class="statusBarClass(ev.status)" class="px-4 py-2 text-center text-sm font-bold">
            {{ statusLabel(ev.status) }}
          </div>
          <!-- Teams -->
          <div class="flex items-center justify-between p-4 bg-slate-50">
            <div class="text-center flex-1">
              <div class="w-12 h-12 mx-auto rounded-full bg-white border flex items-center justify-center overflow-hidden mb-1">
                <img v-if="ev.option_a_image" :src="ev.option_a_image" class="w-full h-full object-contain" />
                <span v-else class="text-lg">🏠</span>
              </div>
              <p class="text-sm font-bold truncate">{{ ev.home_team || ev.option_a || 'فريق أ' }}</p>
            </div>
            <div class="text-center px-3">
              <div class="bg-slate-700 text-white w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold mx-auto mb-1">ضد</div>
              <span class="text-xs text-slate-500">{{ ev.match_date ? formatTime(ev.match_date) : '--:--' }}</span>
            </div>
            <div class="text-center flex-1">
              <div class="w-12 h-12 mx-auto rounded-full bg-white border flex items-center justify-center overflow-hidden mb-1">
                <img v-if="ev.option_b_image" :src="ev.option_b_image" class="w-full h-full object-contain" />
                <span v-else class="text-lg">✈️</span>
              </div>
              <p class="text-sm font-bold truncate">{{ ev.away_team || ev.option_b || 'فريق ب' }}</p>
            </div>
          </div>
          <!-- Result (if completed) -->
          <div v-if="ev.status==='completed' && ev.winner" class="px-4 py-2 bg-emerald-50 border-t border-b border-emerald-100 text-center">
            <div class="text-sm font-bold text-emerald-700">
              <span v-if="ev.prediction_type==='exact_score' && ev.home_score !== null">
                النتيجة: {{ ev.home_score }} - {{ ev.away_score }}
              </span>
              <span v-else>🏆 {{ ev.winner === 'home' ? ev.home_team : ev.winner === 'away' ? ev.away_team : 'تعادل' }}</span>
            </div>
          </div>
          <!-- Rewards distributed indicator -->
          <div v-if="ev.status==='completed' && ev.winner" class="px-4 py-1.5 text-center text-xs font-medium" :class="ev.rewards_distributed ? 'bg-purple-50 text-purple-600' : 'bg-amber-50 text-amber-700'">
            {{ ev.rewards_distributed ? '✅ تم صرف الجوائز' : '⚠️ لم تصرف الجوائز بعد' }}
          </div>
          <!-- Info -->
          <div class="grid grid-cols-2 gap-2 p-3 text-xs">
            <div><span class="text-slate-400">📊</span> {{ ev.predictions_count || 0 }} توقع</div>
            <div><span class="text-slate-400">💰</span> رسوم: {{ ev.entry_fee || 0 }} نقطة</div>
            <div><span class="text-slate-400">🏆</span> جائزة: {{ ev.reward_per_winner || 0 }} نقطة</div>
            <div><span class="text-slate-400">🎯</span> {{ ev.prediction_type==='exact_score' ? 'نتيجة دقيقة' : 'توقع فائز' }}</div>
          </div>
          <!-- Actions -->
          <div class="flex gap-1 p-2 bg-slate-50 border-t">
            <button @click="openEditModal(ev)" class="flex-1 text-xs bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 rounded-lg py-2 font-medium transition-colors">تعديل</button>
            <button @click="viewPredictions(ev)" class="flex-1 text-xs bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 rounded-lg py-2 font-medium transition-colors">التوقعات</button>
            <button v-if="ev.status==='open'" @click="openCloseModal(ev)" class="flex-1 text-xs bg-amber-50 border border-amber-300 text-amber-700 hover:bg-amber-100 rounded-lg py-2 font-medium transition-colors">إغلاق</button>
            <button v-if="ev.status==='open' || ev.status==='closed' || ev.status==='completed'" @click="openResultModal(ev)" class="flex-1 text-xs bg-emerald-50 border border-emerald-300 text-emerald-700 hover:bg-emerald-100 rounded-lg py-2 font-medium transition-colors">النتيجة</button>
            <button v-if="ev.status==='completed' && !ev.rewards_distributed" @click="distributeRewards(ev)" class="flex-1 text-xs bg-purple-50 border border-purple-300 text-purple-700 hover:bg-purple-100 rounded-lg py-2 font-medium transition-colors">صرف الجوائز</button>
            <span v-else-if="ev.status==='completed' && ev.rewards_distributed" class="flex-1 text-xs bg-slate-100 text-slate-400 rounded-lg py-2 font-medium text-center cursor-default">✅ تم الصرف</span>
            <button @click="deleteEvent(ev)" class="text-xs bg-red-50 border border-red-300 text-red-600 hover:bg-red-100 rounded-lg px-3 py-2 font-medium transition-colors">🗑️</button>
          </div>
        </div>
      </div>
      <p v-else-if="!eventsLoading" class="bg-white rounded-xl shadow-sm border p-10 text-center text-slate-400">لا توجد أحداث حالياً</p>
      <p v-if="eventsLoading" class="text-center text-slate-500 py-10">جاري التحميل...</p>

      <!-- Pagination -->
      <div v-if="eventsTotal > perPage" class="flex justify-center gap-2 mt-5">
        <button @click="page = Math.max(1, page-1); loadEvents()" :disabled="page===1" class="px-4 py-2 rounded-lg text-sm border disabled:opacity-40">السابق</button>
        <span class="px-4 py-2 text-sm text-slate-500">صفحة {{ page }} من {{ Math.ceil(eventsTotal/perPage) }}</span>
        <button @click="page = Math.min(Math.ceil(eventsTotal/perPage), page+1); loadEvents()" :disabled="page >= Math.ceil(eventsTotal/perPage)" class="px-4 py-2 rounded-lg text-sm border disabled:opacity-40">التالي</button>
      </div>
    </div>

    <!-- ==================== TAB 3: SETTINGS ==================== -->
    <div v-if="tab==='settings'" class="max-w-2xl">
      <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="bg-indigo-600 text-white px-5 py-3 font-bold flex items-center gap-2">
          <span>⚙️</span> إعدادات مسابقات التوقعات
        </div>
        <div class="p-5 space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-sm">تفعيل الميزة</p>
              <p class="text-xs text-slate-500">السماح للعملاء بالمشاركة في مسابقات التوقعات</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="settings.is_enabled" class="sr-only peer">
              <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1">أقصى عدد للأحداث النشطة</label>
              <input type="number" v-model.number="settings.max_active_events" min="1" max="20" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">الحد الأدنى للوقت قبل الموعد (دقيقة)</label>
              <input type="number" v-model.number="settings.min_time_before_deadline" min="30" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
          </div>

          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-sm">السماح بتعديل التوقع</p>
              <p class="text-xs text-slate-500">يمكن للعميل تعديل توقعه قبل الموعد النهائي</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="settings.allow_prediction_edit" class="sr-only peer">
              <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
          </div>

          <div v-if="settings.allow_prediction_edit">
            <label class="block text-sm font-medium mb-1">رسوم تعديل التوقع (نقطة)</label>
            <p class="text-xs text-slate-500 mb-2">إذا وضعت 0، سيتم استرداد النقاط القديمة وخصم الجديدة (السلوك الافتراضي)</p>
            <input type="number" v-model.number="settings.edit_fee" min="0" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400">
          </div>

          <div class="flex items-center justify-between">
            <div>
              <p class="font-medium text-sm">توزيع الجوائز تلقائياً</p>
              <p class="text-xs text-slate-500">يتم توزيع النقاط على الفائزين تلقائياً بعد إدخال النتيجة</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="settings.auto_distribute_rewards" class="sr-only peer">
              <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
            </label>
          </div>

          <button @click="saveSettings" :disabled="savingSettings" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-colors disabled:opacity-50">
            {{ savingSettings ? 'جاري الحفظ...' : '💾 حفظ الإعدادات' }}
          </button>
          <p v-if="settingsMsg" class="text-sm" :class="settingsMsg.includes('تم') ? 'text-emerald-600' : 'text-red-500'">{{ settingsMsg }}</p>
        </div>
      </div>
    </div>

    <!-- ==================== TAB 4: STATS ==================== -->
    <div v-if="tab==='stats'" class="max-w-3xl">
      <div v-if="statsLoading" class="text-center py-10"><div class="inline-block w-8 h-8 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div></div>
      <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
          <div class="text-3xl font-bold text-indigo-600">{{ stats.total_predictions }}</div>
          <p class="text-xs text-slate-500 mt-1">إجمالي التوقعات</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
          <div class="text-3xl font-bold text-emerald-600">{{ stats.total_winners }}</div>
          <p class="text-xs text-slate-500 mt-1">توقعات صحيحة</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
          <div class="text-3xl font-bold text-red-500">{{ stats.total_losers }}</div>
          <p class="text-xs text-slate-500 mt-1">توقعات خاطئة</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
          <div class="text-3xl font-bold text-amber-500">{{ stats.win_rate }}%</div>
          <p class="text-xs text-slate-500 mt-1">نسبة النجاح</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
          <div class="text-3xl font-bold text-slate-700">{{ stats.total_participants }}</div>
          <p class="text-xs text-slate-500 mt-1">المشاركون</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
          <div class="text-3xl font-bold text-amber-600">{{ stats.total_fees?.toLocaleString() || 0 }}</div>
          <p class="text-xs text-slate-500 mt-1">إجمالي الرسوم (نقطة)</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
          <div class="text-3xl font-bold text-emerald-600">{{ stats.total_rewards?.toLocaleString() || 0 }}</div>
          <p class="text-xs text-slate-500 mt-1">إجمالي المكافآت (نقطة)</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border p-5 text-center">
          <div class="text-3xl font-bold text-slate-700">{{ stats.completed_events }}</div>
          <p class="text-xs text-slate-500 mt-1">أحداث مكتملة</p>
        </div>
      </div>
    </div>

    <!-- ==================== CREATE/EDIT MODAL ==================== -->
    <div v-if="showFormModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showFormModal=false">
      <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
        <!-- Header -->
        <div class="bg-gradient-to-l from-indigo-600 to-blue-600 text-white px-6 py-4 flex items-center justify-between sticky top-0">
          <h3 class="font-bold">{{ editingEvent ? 'تعديل الحدث' : 'إنشاء حدث جديد' }}</h3>
          <button @click="showFormModal=false" class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <div class="p-6 space-y-5">
          <!-- Event info -->
          <div>
            <h4 class="text-sm font-bold text-indigo-600 mb-3 flex items-center gap-2"><span>📋</span> معلومات الحدث</h4>
            <div class="grid grid-cols-2 gap-3">
              <div class="col-span-2">
                <label class="block text-xs font-medium mb-1">عنوان الحدث <span class="text-red-500">*</span></label>
                <input v-model="form.title" required class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400" placeholder="مثال: مباراة الهلال vs النصر" />
              </div>
              <div class="col-span-2">
                <label class="block text-xs font-medium mb-1">الوصف</label>
                <textarea v-model="form.description" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400" placeholder="وصف إضافي للحدث..."></textarea>
              </div>
              <div>
                <label class="block text-xs font-medium mb-1">نوع الحدث</label>
                <select v-model="form.event_type" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                  <option value="match">⚽ مباراة رياضية</option>
                  <option value="election">🗳️ انتخابات/تصويت</option>
                  <option value="custom">⚙️ مخصص</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-medium mb-1">نوع التوقع <span class="text-red-500">*</span></label>
                <select v-model="form.prediction_type" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                  <option value="winner">🏆 توقع الفائز فقط</option>
                  <option value="exact_score">🎯 توقع النتيجة الدقيقة</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Teams -->
          <div>
            <h4 class="text-sm font-bold text-pink-600 mb-3 flex items-center gap-2"><span>👥</span> المتنافسون</h4>
            <div class="grid grid-cols-5 gap-3 items-center">
              <div class="col-span-2">
                <label class="block text-xs font-medium mb-1">الفريق الأول <span class="text-red-500">*</span></label>
                <input v-model="form.home_team" required class="w-full border rounded-lg px-3 py-2 text-sm text-center outline-none focus:ring-2 focus:ring-indigo-400" placeholder="مثال: الهلال" />
              </div>
              <div class="text-center">
                <span class="bg-slate-700 text-white w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold mx-auto">VS</span>
                <div class="mt-2">
                  <label class="flex items-center gap-1 text-xs cursor-pointer">
                    <input type="checkbox" v-model="form.allow_draw" class="rounded" />
                    تعادل
                  </label>
                </div>
              </div>
              <div class="col-span-2">
                <label class="block text-xs font-medium mb-1">الفريق الثاني <span class="text-red-500">*</span></label>
                <input v-model="form.away_team" required class="w-full border rounded-lg px-3 py-2 text-sm text-center outline-none focus:ring-2 focus:ring-indigo-400" placeholder="مثال: النصر" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-3">
              <div>
                <label class="block text-xs font-medium mb-1">رابط صورة الفريق الأول</label>
                <input v-model="form.option_a_image" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400" placeholder="URL الصورة" />
              </div>
              <div>
                <label class="block text-xs font-medium mb-1">رابط صورة الفريق الثاني</label>
                <input v-model="form.option_b_image" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400" placeholder="URL الصورة" />
              </div>
            </div>
          </div>

          <!-- Timing -->
          <div>
            <h4 class="text-sm font-bold text-sky-600 mb-3 flex items-center gap-2"><span>⏰</span> التوقيت</h4>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium mb-1">آخر موعد للتوقع <span class="text-red-500">*</span></label>
                <input type="datetime-local" v-model="form.prediction_deadline" required class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400" />
                <p class="text-[10px] text-slate-400 mt-0.5">لا يمكن التوقع بعد هذا الموعد</p>
              </div>
              <div>
                <label class="block text-xs font-medium mb-1">موعد بدء المباراة</label>
                <input type="datetime-local" v-model="form.match_date" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400" />
                <p class="text-[10px] text-slate-400 mt-0.5">موعد بدء الحدث الفعلي</p>
              </div>
            </div>
          </div>

          <!-- Prizes -->
          <div>
            <h4 class="text-sm font-bold text-amber-600 mb-3 flex items-center gap-2"><span>💰</span> الجوائز والرسوم</h4>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-medium mb-1">الجائزة لكل فائز (نقطة) <span class="text-red-500">*</span></label>
                <input type="number" v-model.number="form.reward_per_winner" min="0" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400" />
              </div>
              <div>
                <label class="block text-xs font-medium mb-1">رسوم المشاركة (نقطة) <span class="text-red-500">*</span></label>
                <input type="number" v-model.number="form.entry_fee" min="0" class="w-full border rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-indigo-400" />
                <p class="text-[10px] text-slate-400 mt-0.5">0 = مشاركة مجانية</p>
              </div>
            </div>
          </div>

          <p v-if="formError" class="text-red-500 text-sm text-center">{{ formError }}</p>

          <div class="flex gap-3 pt-3">
            <button @click="saveEvent" :disabled="saving" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl text-sm font-bold transition-colors disabled:opacity-50">
              {{ saving ? 'جاري الحفظ...' : (editingEvent ? '💾 حفظ التعديلات' : '🚀 إنشاء الحدث') }}
            </button>
            <button @click="showFormModal=false" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-3 rounded-xl text-sm font-bold transition-colors">إلغاء</button>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== PREDICTIONS MODAL ==================== -->
    <div v-if="showPredictionsModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showPredictionsModal=false">
      <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[80vh] overflow-y-auto shadow-2xl">
        <div class="bg-gradient-to-l from-indigo-600 to-blue-600 text-white px-6 py-4 flex items-center justify-between sticky top-0">
          <h3 class="font-bold">📊 توقعات: {{ viewingEvent?.title }}</h3>
          <button @click="showPredictionsModal=false" class="text-white/80 hover:text-white text-xl leading-none">&times;</button>
        </div>
        <div class="p-5">
          <div v-if="viewingEvent?.winner" class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 mb-4 text-center">
            <div v-if="viewingEvent.prediction_type==='exact_score' && viewingEvent.home_score !== null" class="font-bold text-emerald-700 text-lg">{{ viewingEvent.home_team }} {{ viewingEvent.home_score }} - {{ viewingEvent.away_score }} {{ viewingEvent.away_team }}</div>
            <div class="text-sm text-emerald-600 mt-0.5">🏆 {{ viewingEvent.winner === 'draw' ? 'تعادل' : viewingEvent.winner === 'home' ? viewingEvent.home_team : viewingEvent.away_team }}</div>
          </div>
          <table v-if="viewingEvent?.predictions?.length" class="w-full text-sm">
            <thead class="bg-slate-50">
              <tr>
                <th class="text-right p-2 font-medium text-slate-600">المستخدم</th>
                <th class="text-right p-2 font-medium text-slate-600">التوقع</th>
                <th class="text-center p-2 font-medium text-slate-600">الرسوم</th>
                <th class="text-center p-2 font-medium text-slate-600">فائز</th>
                <th class="text-center p-2 font-medium text-slate-600"></th>
              </tr>
            </thead>
            <tbody class="divide-y">
              <tr v-for="p in viewingEvent.predictions" :key="p.id" class="hover:bg-slate-50">
                <td class="p-2">{{ p.user?.name || p.user?.phone || '#'+p.user_id }}</td>
                <td class="p-2 font-medium">{{ p.prediction }}</td>
                <td class="p-2 text-center">{{ p.points_bet || 0 }}</td>
                <td class="p-2 text-center">
                  <span v-if="p.is_winner" class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full text-xs font-medium">نعم</span>
                  <span v-else-if="p.is_winner===false" class="bg-red-100 text-red-600 px-2 py-0.5 rounded-full text-xs font-medium">لا</span>
                  <span v-else class="text-slate-400">—</span>
                </td>
                <td class="p-2 text-center">
                  <button v-if="p.is_winner && viewingEvent.status==='completed' && !viewingEvent.rewards_distributed"
                    @click="distributeRewards(viewingEvent)" class="text-xs bg-purple-50 border border-purple-300 text-purple-700 hover:bg-purple-100 cursor-pointer rounded-lg px-2 py-1 font-medium transition-colors">
                    صرف المكافأة
                  </button>
                  <span v-else-if="p.is_winner && viewingEvent.rewards_distributed" class="text-xs bg-slate-100 text-slate-400 rounded-lg px-2 py-1 font-medium">
                    ✅ مصروفة
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
          <p v-else class="text-center text-slate-400 py-8">لا توجد توقعات بعد</p>
        </div>
      </div>
    </div>

    <!-- ==================== CLOSE/RESULT MODAL ==================== -->
    <div v-if="showResultModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="showResultModal=false">
      <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl p-6">
        <h3 class="font-bold text-lg mb-4">{{ closeMode === 'close' ? '🔒 إغلاق الحدث' : '🏆 إدخال النتيجة' }}</h3>
        <p class="text-sm text-slate-500 mb-4">{{ closeMode === 'close' ? 'سيتم إغلاق باب التوقعات للحدث.' : 'اختر الفريق الفائز أو النتيجة الدقيقة.' }}</p>

        <div v-if="closeMode === 'result' || closeMode === 'close'">
          <div class="space-y-2">
            <button @click="resultWinner='home'" :class="resultWinner==='home' ? 'ring-2 ring-emerald-500 bg-emerald-50' : 'hover:bg-slate-50'" class="w-full border rounded-xl p-4 text-center transition-all">
              <span class="font-bold">{{ resultEvent?.home_team || 'فريق أ' }}</span>
              <span class="text-emerald-600 text-sm mr-2">← فائز</span>
            </button>
            <div v-if="resultEvent?.allow_draw" class="text-center">
              <button @click="resultWinner='draw'" :class="resultWinner==='draw' ? 'ring-2 ring-amber-500 bg-amber-50' : 'hover:bg-slate-50'" class="w-full border rounded-xl p-3 text-center transition-all font-bold text-amber-600">
                🤝 تعادل
              </button>
            </div>
            <button @click="resultWinner='away'" :class="resultWinner==='away' ? 'ring-2 ring-emerald-500 bg-emerald-50' : 'hover:bg-slate-50'" class="w-full border rounded-xl p-4 text-center transition-all">
              <span class="text-emerald-600 text-sm ml-2">فائز →</span>
              <span class="font-bold">{{ resultEvent?.away_team || 'فريق ب' }}</span>
            </button>
          </div>
          <div v-if="resultEvent?.prediction_type==='exact_score'" class="mt-4">
            <p class="text-sm font-medium mb-2">النتيجة الدقيقة:</p>
            <div class="flex items-center gap-3 justify-center">
              <input type="number" v-model.number="resultHomeScore" min="0" class="w-20 border rounded-lg px-3 py-2 text-center text-lg font-bold outline-none focus:ring-2 focus:ring-indigo-400" placeholder="0" />
              <span class="text-xl font-bold text-slate-400">-</span>
              <input type="number" v-model.number="resultAwayScore" min="0" class="w-20 border rounded-lg px-3 py-2 text-center text-lg font-bold outline-none focus:ring-2 focus:ring-indigo-400" placeholder="0" />
            </div>
          </div>
        </div>

        <p v-if="resultError" class="text-red-500 text-sm text-center mt-3">{{ resultError }}</p>

        <div class="flex gap-3 mt-5">
          <button @click="submitResult" :disabled="savingResult" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl text-sm font-bold transition-colors disabled:opacity-50">
            {{ savingResult ? 'جاري...' : (closeMode==='close' ? '🔒 إغلاق' : '🏆 تأكيد النتيجة') }}
          </button>
          <button @click="showResultModal=false" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 py-2.5 rounded-xl text-sm font-bold transition-colors">إلغاء</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      tab: 'matches',
      // Date
      selectedDateOffset: 0,
      selectedDate: new Date().toISOString().slice(0,10),
      customDate: '',
      datePresets: [
        { offset: -1, label: 'أمس' },
        { offset: 0, label: 'اليوم' },
        { offset: 1, label: 'غداً' },
        { offset: 2, label: 'بعد غد' },
      ],
      // Matches
      matchesLoading: false,
      matchesError: '',
      leagues: [],
      matches: [],
      matchesTotal: 0,
      apiQuota: null,
      // Events
      events: [],
      eventsTotal: 0,
      page: 1,
      perPage: 50,
      eventsLoading: false,
      statusFilter: 'all',
      statusFilters: [
        { value: 'all', label: 'الكل', icon: '📋', activeClass: 'bg-indigo-600 text-white border-indigo-600' },
        { value: 'open', label: 'مفتوح', icon: '🔓', activeClass: 'bg-emerald-600 text-white border-emerald-600' },
        { value: 'closed', label: 'مغلق', icon: '🔒', activeClass: 'bg-amber-600 text-white border-amber-600' },
        { value: 'completed', label: 'مكتمل', icon: '✅', activeClass: 'bg-blue-600 text-white border-blue-600' },
      ],
      steps: [
        { title: 'أنشئ حدث', desc: 'حدد الفرق والموعد والجوائز' },
        { title: 'العملاء يتوقعون', desc: 'يشاركون قبل انتهاء الموعد' },
        { title: 'أدخل النتيجة', desc: 'بعد انتهاء المباراة' },
        { title: 'وزع الجوائز', desc: 'تلقائياً أو يدوياً' },
      ],
      // Form
      showFormModal: false,
      editingEvent: null,
      form: this.emptyForm(),
      formError: '',
      saving: false,
      // Predictions modal
      showPredictionsModal: false,
      viewingEvent: null,
      // Result modal
      showResultModal: false,
      closeMode: 'result',
      resultEvent: null,
      resultWinner: '',
      resultHomeScore: 0,
      resultAwayScore: 0,
      resultError: '',
      savingResult: false,
      // Settings
      settings: { is_enabled: true, max_active_events: 5, min_time_before_deadline: 60, allow_prediction_edit: false, edit_fee: 0, auto_distribute_rewards: true },
      savingSettings: false,
      settingsMsg: '',
      // Stats
      stats: {},
      statsLoading: false,
    }
  },
  mounted() { this.loadMatches(); this.loadEvents(); this.loadSettings() },
  methods: {
    emptyForm() {
      return { title: '', description: '', event_type: 'match', prediction_type: 'winner', allow_draw: true, home_team: '', away_team: '', option_a_image: '', option_b_image: '', match_date: '', prediction_deadline: '', entry_fee: 0, reward_per_winner: 10 }
    },
    dateOffset(offset) {
      const d = new Date(); d.setDate(d.getDate() + offset);
      return d.toISOString().slice(0, 10)
    },
    formatTime(dt) {
      if (!dt) return '';
      // نستخرج الوقت مباشرة من النص (بما أن القيمة مخزنة بتوقيت الرياض)
      const m = dt.match(/T(\d{2}):(\d{2})/);
      if (m) return `${m[1]}:${m[2]}`;
      const d = new Date(dt);
      return d.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit', hour12: false })
    },
    formatDate(dt) {
      if (!dt) return '';
      const d = new Date(dt);
      return d.toLocaleDateString('ar-SA', { day: 'numeric', month: 'short' })
    },
    toLocalDatetime(iso) {
      if (!iso) return ''
      // تأكد من وجود Z للـ UTC
      const utcStr = iso.length === 16 ? iso + ':00Z' : iso
      const d = new Date(utcStr)
      if (isNaN(d.getTime())) return iso
      const y = d.getFullYear()
      const m = String(d.getMonth() + 1).padStart(2, '0')
      const day = String(d.getDate()).padStart(2, '0')
      const h = String(d.getHours()).padStart(2, '0')
      const min = String(d.getMinutes()).padStart(2, '0')
      return `${y}-${m}-${day}T${h}:${min}`
    },
    statusLabel(s) {
      const map = { open: '🟢 مفتوح', closed: '🟡 مغلق', cancelled: '🔴 ملغي', completed: '✅ مكتمل' }
      return map[s] || s
    },
    statusBarClass(s) {
      const map = { open: 'bg-emerald-600 text-white', closed: 'bg-amber-500 text-white', cancelled: 'bg-red-500 text-white', completed: 'bg-blue-600 text-white' }
      return map[s] || 'bg-slate-500 text-white'
    },
    formatCardTime(timeStr) {
      if (!timeStr) return ''
      const [h, m] = timeStr.split(':').map(Number)
      const ampm = h >= 12 ? 'مساءً' : 'صباحاً'
      const h12 = h % 12 || 12
      return `${h12}:${String(m).padStart(2, '0')} ${ampm}`
    },
    subHour(dt, hours) {
      if (!dt || dt.length < 16) return ''
      const [y, mo, d, hh, mm] = [
        parseInt(dt.substring(0, 4)),
        parseInt(dt.substring(5, 7)) - 1,
        parseInt(dt.substring(8, 10)),
        parseInt(dt.substring(11, 13)),
        parseInt(dt.substring(14, 16))
      ]
      const dateObj = new Date(y, mo, d, hh, mm)
      dateObj.setHours(dateObj.getHours() - hours)
      const ny = dateObj.getFullYear()
      const nm = String(dateObj.getMonth() + 1).padStart(2, '0')
      const nd = String(dateObj.getDate()).padStart(2, '0')
      const nh = String(dateObj.getHours()).padStart(2, '0')
      const nmin = String(dateObj.getMinutes()).padStart(2, '0')
      return `${ny}-${nm}-${nd}T${nh}:${nmin}`
    },
    // Matches
    async loadMatches() {
      this.matchesLoading = true
      this.matchesError = ''
      try {
        const params = { date: this.selectedDate }
        const res = await axios.get('/api/admin/v1/predictions/matches', { params })
        this.leagues = res.data.leagues
        this.matches = res.data.matches
        this.matchesTotal = res.data.matches.length
        this.apiQuota = res.data.remaining_requests
      } catch (e) {
        this.matchesError = e.response?.data?.error || 'فشل تحميل المباريات'
      }
      this.matchesLoading = false
    },
    matchToEvent(match, league) {
      this.editingEvent = null
      this.form = this.emptyForm()
      this.form.title = `${match.home_team} ضد ${match.away_team}`
      this.form.description = `مباراة ${league.name}`
      this.form.home_team = match.home_team
      this.form.away_team = match.away_team
      this.form.option_a_image = match.home_img || ''
      this.form.option_b_image = match.away_img || ''
      this.form.event_type = 'match'
      this.form.prediction_type = 'winner'
      this.form.match_date = match.match_date
      this.form.prediction_deadline = this.subHour(match.match_date, 1)
      this.formError = ''
      this.showFormModal = true
    },
    async addAllToEvents(league) {
      if (!confirm(`تأكيد إضافة جميع مباريات ${league.name} كأحداث؟`)) return
      this.saving = true
      let added = 0
      for (const match of league.matches) {
        if (match.is_finished || match.is_live) continue
        try {
          await axios.post('/api/admin/v1/sport-events', {
            title: `${match.home_team} ضد ${match.away_team}`,
            description: `مباراة ${league.name}`,
            home_team: match.home_team,
            away_team: match.away_team,
            option_a_image: match.home_img || '',
            option_b_image: match.away_img || '',
            event_type: 'match',
            prediction_type: 'winner',
            match_date: match.match_date || null,
            prediction_deadline: this.subHour(match.match_date, 1) || null,
            entry_fee: 0,
            reward_per_winner: 10,
            status: 'open',
          })
          added++
        } catch (e) {
          console.error('فشل إضافة مباراة:', match.home_team, match.away_team)
        }
      }
      this.saving = false
      if (added > 0) this.loadEvents()
      alert(`✅ تمت إضافة ${added} حدث بنجاح`)
    },
    // Events CRUD
    async loadEvents() {
      this.eventsLoading = true;
      try {
        const params = { page: this.page, per_page: this.perPage }
        if (this.statusFilter !== 'all') params.status = this.statusFilter
        const res = await axios.get('/api/admin/v1/sport-events', { params })
        this.events = res.data.data
        this.eventsTotal = res.data.total
      } catch (e) { console.error(e) }
      this.eventsLoading = false
    },
    openCreateModal() {
      this.editingEvent = null
      this.form = this.emptyForm()
      this.formError = ''
      this.showFormModal = true
    },
    openEditModal(ev) {
      this.editingEvent = ev
      this.form = {
        title: ev.title || '',
        description: ev.description || '',
        event_type: ev.event_type || 'match',
        prediction_type: ev.prediction_type || 'winner',
        allow_draw: ev.allow_draw ?? true,
        home_team: ev.home_team || '',
        away_team: ev.away_team || '',
        option_a_image: ev.option_a_image || '',
        option_b_image: ev.option_b_image || '',
        match_date: ev.match_date ? ev.match_date.slice(0, 16) : '',
        prediction_deadline: ev.prediction_deadline ? ev.prediction_deadline.slice(0, 16) : '',
        entry_fee: ev.entry_fee || 0,
        reward_per_winner: ev.reward_per_winner || 0,
      }
      this.formError = ''
      this.showFormModal = true
    },
    async saveEvent() {
      if (!this.form.title || !this.form.home_team || !this.form.away_team || !this.form.prediction_deadline) {
        this.formError = 'يرجى تعبئة الحقول المطلوبة'
        return
      }
      this.saving = true
      this.formError = ''
      const payload = { ...this.form, status: this.editingEvent?.status || 'open' }
      try {
        if (this.editingEvent) {
          await axios.put(`/api/admin/v1/sport-events/${this.editingEvent.id}`, payload)
        } else {
          await axios.post('/api/admin/v1/sport-events', payload)
        }
        this.showFormModal = false
        this.loadEvents()
      } catch (e) {
        this.formError = e.response?.data?.message || e.response?.data?.errors?.[Object.keys(e.response?.data?.errors||{})[0]]?.[0] || 'فشل الحفظ'
      }
      this.saving = false
    },
    async deleteEvent(ev) {
      if (!confirm('تأكيد حذف الحدث؟')) return
      try {
        await axios.delete(`/api/admin/v1/sport-events/${ev.id}`)
        this.loadEvents()
      } catch (e) { console.error(e) }
    },
    async viewPredictions(ev) {
      try {
        const res = await axios.get(`/api/admin/v1/sport-events/${ev.id}`)
        this.viewingEvent = res.data
        this.showPredictionsModal = true
      } catch (e) { console.error(e) }
    },
    // Close / Result
    openCloseModal(ev) {
      this.closeMode = 'close'
      this.resultEvent = ev
      this.resultWinner = ''
      this.resultHomeScore = 0
      this.resultAwayScore = 0
      this.resultError = ''
      this.showResultModal = true
    },
    openResultModal(ev) {
      this.closeMode = 'result'
      this.resultEvent = ev
      this.resultWinner = ev.winner || ''
      this.resultHomeScore = ev.home_score ?? 0
      this.resultAwayScore = ev.away_score ?? 0
      this.resultError = ''
      this.showResultModal = true
    },
    async submitResult() {
      if (this.closeMode === 'close') {
        this.savingResult = true
        try {
          await axios.put(`/api/admin/v1/sport-events/${this.resultEvent.id}`, { status: 'closed' })
          this.showResultModal = false
          this.loadEvents()
        } catch (e) { this.resultError = e.response?.data?.message || 'فشل الإغلاق' }
        this.savingResult = false
        return
      }
      if (!this.resultWinner) { this.resultError = 'اختر الفائز'; return }
      this.savingResult = true
      this.resultError = ''

      let winner = this.resultWinner
      if (this.resultEvent.prediction_type === 'exact_score') {
        if (this.resultHomeScore > this.resultAwayScore) winner = 'home'
        else if (this.resultAwayScore > this.resultHomeScore) winner = 'away'
        else winner = 'draw'
      }

      try {
        await axios.put(`/api/admin/v1/sport-events/${this.resultEvent.id}`, {
          status: 'completed',
          winner: winner,
          home_score: this.resultHomeScore,
          away_score: this.resultAwayScore,
          closed_at: new Date().toISOString(),
        })
        this.showResultModal = false
        this.loadEvents()
        } catch (e) { this.resultError = e.response?.data?.message || 'فشل حفظ النتيجة' }
      this.savingResult = false
    },
    async distributeRewards(ev) {
      let count = ev.predictions?.filter(p => p.is_winner).length
      // إذا تم الضغط من الكرت (بدون predictions)، نجلب العدد من API
      if (count === undefined) {
        try {
          const res = await axios.get(`/api/admin/v1/sport-events/${ev.id}`)
          count = res.data.predictions?.filter(p => p.is_winner).length || 0
        } catch (e) {
          count = 0
        }
      }
      if (!confirm(`⚠️ تنبيه: سيؤدي هذا الإجراء إلى صرف الجائزة لجميع الفائزين (${count} فائز/فائزة).\n\nالمكافأة لكل فائز: ${ev.reward_per_winner || 0} نقطة\nالإجمالي: ${(count * (ev.reward_per_winner || 0))} نقطة\n\nهل أنت متأكد؟`)) return
      try {
        await axios.post(`/api/admin/v1/sport-events/${ev.id}/distribute`)
        this.showPredictionsModal = false
        this.loadEvents()
        alert('✅ تم توزيع الجوائز بنجاح')
      } catch (e) {
        alert(e.response?.data?.message || 'فشل توزيع الجوائز')
      }
    },
    // Stats
    async loadStats() {
      this.statsLoading = true
      try {
        const res = await axios.get('/api/admin/v1/sport-events/stats')
        this.stats = res.data
      } catch (e) { console.error(e) }
      this.statsLoading = false
    },
    // Settings
    async loadSettings() {
      try {
        const res = await axios.get('/api/admin/v1/predictions/settings')
        this.settings = res.data
      } catch (e) { console.error(e) }
    },
    async saveSettings() {
      this.savingSettings = true
      this.settingsMsg = ''
      try {
        await axios.put('/api/admin/v1/predictions/settings', this.settings)
        this.settingsMsg = '✅ تم حفظ الإعدادات بنجاح'
      } catch (e) {
        this.settingsMsg = '❌ فشل حفظ الإعدادات'
      }
      this.savingSettings = false
    },
  },
}
</script>
