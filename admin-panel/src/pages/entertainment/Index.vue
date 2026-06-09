<template>
  <div class="p-6" dir="rtl">
    <h1 class="text-2xl font-bold mb-6 text-slate-800">الترفيه</h1>

    <!-- Tabs -->
    <div class="flex gap-4 mb-6 border-b border-slate-200 overflow-x-auto">
      <button @click="tab = 'lucky-box'" :class="tab === 'lucky-box' ? 'border-b-2 border-violet-600 text-violet-700' : 'text-slate-500'" class="pb-2 text-sm font-medium whitespace-nowrap">🎁 صندوق الحظ</button>
      <button @click="tab = 'lucky-wheel'; loadWheels()" :class="tab === 'lucky-wheel' ? 'border-b-2 border-violet-600 text-violet-700' : 'text-slate-500'" class="pb-2 text-sm font-medium whitespace-nowrap">🎡 عجلة الحظ</button>
      <button @click="tab = 'quizzes'; loadContests()" :class="tab === 'quizzes' ? 'border-b-2 border-violet-600 text-violet-700' : 'text-slate-500'" class="pb-2 text-sm font-medium whitespace-nowrap">📝 المسابقات</button>
    </div>

    <!-- ==================== LUCKY BOX TAB ==================== -->
    <div v-if="tab === 'lucky-box'">
      <!-- Box Tabs -->
      <div class="flex gap-2 mb-4 flex-wrap">
        <button v-for="(b, bi) in boxes" :key="bi" @click="boxTab = bi"
          :class="boxTab === bi ? 'bg-slate-100 shadow-sm' : 'bg-white hover:bg-slate-50'"
          class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border transition"
          :style="{ borderColor: b.color || '#4a4a8a' }">
          <span class="w-3 h-3 rounded-full inline-block shrink-0" :style="{ backgroundColor: b.color || '#4a4a8a' }"></span>
          {{ b.name || 'صندوق ' + (bi + 1) }}
        </button>
        <button v-if="boxes.length < 4" @click="addBox" class="px-4 py-2 rounded-lg text-sm border border-dashed text-slate-500 hover:text-violet-600">+ صندوق</button>
      </div>

      <!-- Active Box Panel -->
      <div v-if="boxes.length" class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <!-- Box Fields -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-5">
          <div>
            <label class="block text-xs text-slate-500 mb-1">اسم الصندوق</label>
            <input v-model="boxes[boxTab].name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">نقاط اللعب</label>
            <input type="number" v-model.number="boxes[boxTab].cost" min="0" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">الحد اليومي</label>
            <input type="number" v-model.number="boxes[boxTab].daily_limit" min="0" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="0 = غير محدود">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">اللون</label>
            <input type="color" v-model="boxes[boxTab].color" class="w-full h-9 border border-slate-300 rounded-lg cursor-pointer">
          </div>
          <div class="flex items-end pb-1">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="boxes[boxTab].is_active" class="w-4 h-4 accent-violet-600">
              <span class="text-sm">مفعل</span>
            </label>
          </div>
        </div>

        <!-- Prizes Table -->
        <div class="flex justify-between items-center mb-3">
          <h3 class="text-sm font-bold text-slate-600">الجوائز</h3>
          <button @click="addPrize" class="text-sm text-violet-600 hover:text-violet-800">+ جائزة</button>
        </div>
        <table class="w-full text-sm">
          <thead>
            <tr class="text-xs text-slate-500 border-b">
              <th class="text-right px-2 py-2 w-[30%]">الاسم</th>
              <th class="text-right px-2 py-2 w-[18%]">النوع</th>
              <th class="text-right px-2 py-2 w-[22%]">القيمة</th>
              <th class="text-right px-2 py-2 w-[18%]">الوزن</th>
              <th class="text-center px-2 py-2 w-[12%]"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(p, pi) in boxes[boxTab].prizes" :key="pi" class="border-b border-slate-100">
              <td class="px-2 py-1.5">
                <input v-model="p.name" class="w-full border border-slate-300 rounded-lg px-2 py-1.5 text-sm" placeholder="اسم الجائزة">
              </td>
              <td class="px-2 py-1.5">
                <select v-model="p.type" @change="onPrizeTypeChange(boxTab, pi)" class="w-full border border-slate-300 rounded-lg px-2 py-1.5 text-sm">
                  <option value="point">نقاط</option>
                  <option value="card">كارت</option>
                  <option value="nothng">لا شيء</option>
                </select>
              </td>
              <td class="px-2 py-1.5">
                <input v-if="p.type !== 'card'" v-model="p.value" class="w-full border border-slate-300 rounded-lg px-2 py-1.5 text-sm" placeholder="القيمة">
                <select v-else v-model="p.value" class="w-full border border-slate-300 rounded-lg px-2 py-1.5 text-sm">
                  <option value="">اختر فئة</option>
                  <option v-for="cat in categories" :key="cat.id" :value="String(cat.id)">{{ cat.name }}</option>
                </select>
              </td>
              <td class="px-2 py-1.5">
                <input type="number" v-model.number="p.weight" min="1" class="w-full border border-slate-300 rounded-lg px-2 py-1.5 text-sm">
              </td>
              <td class="px-2 py-1.5 text-center">
                <button @click="removePrize(boxTab, pi)" class="text-red-500 hover:text-red-700 text-xs">حذف</button>
              </td>
            </tr>
            <tr v-if="boxes[boxTab].prizes.length === 0">
              <td colspan="5" class="text-center py-4 text-slate-400 text-xs">لا توجد جوائز — أضف جائزة</td>
            </tr>
          </tbody>
        </table>

        <!-- Save -->
        <div class="flex gap-2 justify-end mt-5 pt-4 border-t">
          <button @click="loadBoxes" class="px-4 py-2 text-sm border rounded-lg text-slate-600">إلغاء</button>
          <button @click="saveBoxes" :disabled="savingBoxes" class="px-6 py-2 text-sm text-white bg-violet-600 rounded-lg disabled:opacity-50">
            {{ savingBoxes ? 'جاري الحفظ...' : 'حفظ الكل' }}
          </button>
        </div>
        <div v-if="boxError" class="mt-3 text-sm text-red-600">{{ boxError }}</div>
      </div>
    </div>

    <!-- ==================== LUCKY WHEEL TAB ==================== -->
    <div v-if="tab === 'lucky-wheel'">
      <!-- Wheel Tabs -->
      <div class="flex gap-2 mb-4 flex-wrap">
        <button v-for="(w, wi) in wheels" :key="wi" @click="wheelTab = wi"
          :class="wheelTab === wi ? 'bg-slate-100 shadow-sm' : 'bg-white hover:bg-slate-50'"
          class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border transition"
          :style="{ borderColor: w.color || '#6366f1' }">
          <span class="w-3 h-3 rounded-full inline-block shrink-0" :style="{ backgroundColor: w.color || '#6366f1' }"></span>
          {{ w.name || 'عجلة ' + (wi + 1) }}
        </button>
        <button v-if="wheels.length < 4" @click="addWheel" class="px-4 py-2 rounded-lg text-sm border border-dashed text-slate-500 hover:text-violet-600">+ عجلة</button>
      </div>

      <!-- Active Wheel Panel -->
      <div v-if="wheels.length" class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <!-- Wheel Fields -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-5">
          <div>
            <label class="block text-xs text-slate-500 mb-1">اسم العجلة</label>
            <input v-model="wheels[wheelTab].name" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">نظام اللف</label>
            <select v-model="wheels[wheelTab].spin_mode" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
              <option value="none">لا يوجد مجاني</option>
              <option value="daily_then_points">أول لفة يوميًا مجانية ثم بالنقاط</option>
              <option value="daily_free_only">لفة يومية مجانية فقط</option>
            </select>
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">تكلفة اللفة</label>
            <input type="number" v-model.number="wheels[wheelTab].point_cost" min="0" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
          <div>
            <label class="block text-xs text-slate-500 mb-1">الحد اليومي</label>
            <input type="number" v-model.number="wheels[wheelTab].daily_limit" min="0" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="0 = غير محدود">
          </div>
          <div class="flex gap-2 items-end pb-1">
            <div>
              <label class="block text-xs text-slate-500 mb-1">اللون</label>
              <input type="color" v-model="wheels[wheelTab].color" class="w-12 h-9 border border-slate-300 rounded-lg cursor-pointer">
            </div>
            <label class="flex items-center gap-2 cursor-pointer mt-auto">
              <input type="checkbox" v-model="wheels[wheelTab].is_active" class="w-4 h-4 accent-violet-600">
              <span class="text-sm">مفعل</span>
            </label>
          </div>
        </div>

        <!-- Prizes Table -->
        <div class="flex justify-between items-center mb-3">
          <h3 class="text-sm font-bold text-slate-600">المكافآت</h3>
          <button @click="addWheelPrize" class="text-sm text-violet-600 hover:text-violet-800">+ مكافأة</button>
        </div>
        <table class="w-full text-sm">
          <thead>
            <tr class="text-xs text-slate-500 border-b">
              <th class="text-right px-2 py-2 w-[25%]">الاسم</th>
              <th class="text-right px-2 py-2 w-[15%]">النوع</th>
              <th class="text-right px-2 py-2 w-[20%]">القيمة</th>
              <th class="text-right px-2 py-2 w-[10%]">الوزن</th>
              <th class="text-right px-2 py-2 w-[15%]">اللون</th>
              <th class="text-center px-2 py-2 w-[15%]"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(p, pi) in wheels[wheelTab].prizes" :key="pi" class="border-b border-slate-100">
              <td class="px-2 py-1.5">
                <input v-model="p.name" class="w-full border border-slate-300 rounded-lg px-2 py-1.5 text-sm" placeholder="اسم المكافأة">
              </td>
              <td class="px-2 py-1.5">
                <select v-model="p.type" @change="onWheelPrizeTypeChange(wheelTab, pi)" class="w-full border border-slate-300 rounded-lg px-2 py-1.5 text-sm">
                  <option value="point">نقاط</option>
                  <option value="card">كرت</option>
                  <option value="nothng">حظ أوفر</option>
                </select>
              </td>
              <td class="px-2 py-1.5">
                <input v-if="p.type !== 'card'" v-model="p.value" class="w-full border border-slate-300 rounded-lg px-2 py-1.5 text-sm" placeholder="القيمة">
                <select v-else v-model="p.value" class="w-full border border-slate-300 rounded-lg px-2 py-1.5 text-sm">
                  <option value="">اختر فئة</option>
                  <option v-for="cat in categories" :key="cat.id" :value="String(cat.id)">{{ cat.name }}</option>
                </select>
              </td>
              <td class="px-2 py-1.5">
                <input type="number" v-model.number="p.weight" min="1" class="w-full border border-slate-300 rounded-lg px-2 py-1.5 text-sm">
              </td>
              <td class="px-2 py-1.5">
                <input type="color" v-model="p.color" class="w-12 h-8 border border-slate-300 rounded-lg cursor-pointer">
              </td>
              <td class="px-2 py-1.5 text-center">
                <button @click="removeWheelPrize(wheelTab, pi)" class="text-red-500 hover:text-red-700 text-xs">حذف</button>
              </td>
            </tr>
            <tr v-if="wheels[wheelTab].prizes.length === 0">
              <td colspan="6" class="text-center py-4 text-slate-400 text-xs">لا توجد مكافآت — أضف مكافأة</td>
            </tr>
          </tbody>
        </table>

        <!-- Reset Free Spins -->
        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t">
          <button @click="resetFreeSpinsAll"
            class="px-3 py-2 text-sm bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
            🔄 تصفير اللفات المجانية للكل
          </button>
          <div class="flex items-center gap-2">
            <input v-model="resetUserPhone" type="text" placeholder="رقم الهاتف"
              class="w-32 border border-slate-300 rounded-lg px-2 py-1.5 text-sm" dir="ltr" />
            <button @click="resetFreeSpinsUser"
              class="px-3 py-2 text-sm bg-amber-50 text-amber-700 border border-amber-200 rounded-lg hover:bg-amber-100 transition">
              تصفير لمستخدم
            </button>
          </div>
          <p v-if="resetMsg" class="w-full text-sm" :class="resetError ? 'text-red-500' : 'text-emerald-600'">{{ resetMsg }}</p>
        </div>

        <!-- Save -->
        <div class="flex gap-2 justify-end mt-4 pt-4 border-t">
          <button @click="loadWheels" class="px-4 py-2 text-sm border rounded-lg text-slate-600">إلغاء</button>
          <button @click="saveWheels" :disabled="savingWheels" class="px-6 py-2 text-sm text-white bg-violet-600 rounded-lg disabled:opacity-50">
            {{ savingWheels ? 'جاري الحفظ...' : 'حفظ الكل' }}
          </button>
        </div>
        <div v-if="wheelError" class="mt-3 text-sm text-red-600">{{ wheelError }}</div>
      </div>
    </div>

    <!-- ==================== QUIZZES TAB ==================== -->
    <div v-if="tab === 'quizzes'">
      <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200 mb-4">
        <div class="flex gap-3 flex-wrap items-end">
          <div class="flex-1 min-w-[200px]">
            <input v-model="quizSearch" @input="debouncedQuiz" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm" placeholder="بحث...">
          </div>
          <div>
            <select v-model="quizStatus" @change="loadContests" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
              <option value="">الكل</option>
              <option value="draft">مسودة</option>
              <option value="open">مفتوحة</option>
              <option value="closed">مغلقة</option>
            </select>
          </div>
          <button @click="openContestModal()" class="bg-violet-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-violet-700">+ مسابقة جديدة</button>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div v-if="contestsLoading" class="text-center py-8 text-slate-400">جاري التحميل...</div>
        <table v-else class="w-full">
          <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
            <tr>
              <th class="text-right px-4 py-3">العنوان</th>
              <th class="text-right px-4 py-3">رسوم الدخول</th>
              <th class="text-right px-4 py-3">الجائزة</th>
              <th class="text-right px-4 py-3">الأسئلة</th>
              <th class="text-right px-4 py-3">يبدأ</th>
              <th class="text-right px-4 py-3">ينتهي</th>
              <th class="text-right px-4 py-3">الحالة</th>
              <th class="text-center px-4 py-3">إجراءات</th>
            </tr>
          </thead>
          <tbody class="text-sm">
            <tr v-for="c in contests" :key="c.id" class="border-t border-slate-100 hover:bg-slate-50">
              <td class="px-4 py-3 font-medium">{{ c.title }}</td>
              <td class="px-4 py-3">{{ c.entry_fee }}</td>
              <td class="px-4 py-3">{{ c.prize || '—' }}</td>
              <td class="px-4 py-3 text-slate-500">{{ c.questions_count }}</td>
              <td class="px-4 py-3 text-slate-500">{{ c.starts_at }}</td>
              <td class="px-4 py-3 text-slate-500">{{ c.ends_at }}</td>
              <td class="px-4 py-3"><span :class="statusClass(c.status)" class="px-2 py-0.5 rounded-full text-xs">{{ c.status }}</span></td>
              <td class="px-4 py-3 text-center">
                <button @click="viewContest(c)" class="text-violet-600 hover:text-violet-800 ml-2">إدارة</button>
                <button @click="openContestModal(c)" class="text-violet-600 hover:text-violet-800 ml-2">تعديل</button>
                <button @click="confirmDelete(c, 'contest')" class="text-red-500 hover:text-red-700">حذف</button>
              </td>
            </tr>
            <tr v-if="contests.length === 0"><td colspan="8" class="text-center py-8 text-slate-400">لا توجد مسابقات</td></tr>
          </tbody>
        </table>
      </div>
      <div v-if="contestPagination && contestPagination.last_page > 1" class="flex justify-between items-center mt-4 text-sm">
        <span class="text-slate-500">صفحة {{ contestPagination.current_page }} من {{ contestPagination.last_page }} ({{ contestPagination.total }})</span>
        <div class="flex gap-2">
          <button :disabled="!contestPagination.prev_page_url" @click="contestPage(contestPagination.current_page - 1)" class="px-3 py-1 rounded border disabled:opacity-30">السابق</button>
          <button :disabled="!contestPagination.next_page_url" @click="contestPage(contestPagination.current_page + 1)" class="px-3 py-1 rounded border disabled:opacity-30">التالي</button>
        </div>
      </div>
    </div>

    <!-- ==================== MODALS ==================== -->

    <!-- Contest Modal -->
    <div v-if="contestModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" @click.self="contestModal = false">
      <div class="bg-white rounded-2xl p-6 w-full max-w-lg mx-4">
        <h2 class="text-lg font-bold mb-4">{{ editingContest ? 'تعديل المسابقة' : 'مسابقة جديدة' }}</h2>
        <form @submit.prevent="saveContest">
          <div class="mb-3">
            <label class="block text-sm text-slate-600 mb-1">العنوان</label>
            <input v-model="contestForm.title" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
          </div>
          <div class="mb-3">
            <label class="block text-sm text-slate-600 mb-1">الوصف</label>
            <textarea v-model="contestForm.description" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"></textarea>
          </div>
          <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
              <label class="block text-sm text-slate-600 mb-1">رسوم الدخول (نقاط)</label>
              <input type="number" v-model.number="contestForm.entry_fee" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">الجائزة</label>
              <input v-model="contestForm.prize" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3 mb-3">
            <div>
              <label class="block text-sm text-slate-600 mb-1">يبدأ</label>
              <input type="datetime-local" v-model="contestForm.starts_at" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
              <label class="block text-sm text-slate-600 mb-1">ينتهي</label>
              <input type="datetime-local" v-model="contestForm.ends_at" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
          </div>
          <div class="mb-3">
            <label class="block text-sm text-slate-600 mb-1">الحالة</label>
            <select v-model="contestForm.status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
              <option value="draft">مسودة</option>
              <option value="open">مفتوحة</option>
              <option value="closed">مغلقة</option>
            </select>
          </div>
          <div v-if="contestError" class="mb-3 text-sm text-red-600">{{ contestError }}</div>
          <div class="flex gap-2 justify-end">
            <button type="button" @click="contestModal = false" class="px-4 py-2 text-sm text-slate-600 border rounded-lg">إلغاء</button>
            <button type="submit" class="px-4 py-2 text-sm text-white bg-violet-600 rounded-lg">{{ editingContest ? 'حفظ' : 'إضافة' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Contest Manage Modal (questions) -->
    <div v-if="contestManageModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" @click.self="contestManageModal = false">
      <div class="bg-white rounded-2xl p-6 w-full max-w-2xl mx-4 max-h-[80vh] overflow-y-auto">
        <h2 class="text-lg font-bold mb-2">{{ managedContest?.title }}</h2>
        <p class="text-sm text-slate-500 mb-4">إدارة الأسئلة</p>

        <div v-for="(q, i) in managedQuestions" :key="q.id || i" class="border border-slate-200 rounded-lg p-4 mb-3">
          <div class="flex justify-between items-start mb-2">
            <span class="text-sm font-medium">س{{ i + 1 }}</span>
            <button @click="removeQuestion(i)" class="text-xs text-red-500">حذف</button>
          </div>
          <input v-model="q.question" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm mb-2" placeholder="السؤال">
          <div class="grid grid-cols-2 gap-2 mb-2">
            <input v-for="(o, oi) in q.options" :key="oi" v-model="q.options[oi]" class="border border-slate-300 rounded-lg px-3 py-2 text-sm" :placeholder="`خيار ${oi + 1}`">
          </div>
          <div class="flex gap-2">
            <div class="flex-1">
              <label class="block text-xs text-slate-500">الإجابة الصحيحة</label>
              <input v-model="q.correct_answer" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="w-24">
              <label class="block text-xs text-slate-500">النقاط</label>
              <input type="number" v-model.number="q.points" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
            </div>
          </div>
        </div>

        <button @click="addQuestion" class="text-sm text-violet-600 hover:text-violet-800 mb-4">+ أضف سؤالاً</button>

        <div v-if="quizError" class="mb-3 text-sm text-red-600">{{ quizError }}</div>
        <div class="flex gap-2 justify-end">
          <button @click="contestManageModal = false" class="px-4 py-2 text-sm text-slate-600 border rounded-lg">إلغاء</button>
          <button @click="saveQuestions" class="px-4 py-2 text-sm text-white bg-violet-600 rounded-lg">حفظ الأسئلة</button>
        </div>
      </div>
    </div>

    <!-- Delete Confirm -->
    <div v-if="showDelete" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm mx-4 text-center">
        <p class="mb-4">حذف "{{ deleteItem?.name || deleteItem?.title }}"؟</p>
        <div class="flex gap-2 justify-center">
          <button @click="showDelete = false" class="px-4 py-2 text-sm border rounded-lg">إلغاء</button>
          <button @click="doDelete" class="px-4 py-2 text-sm text-white bg-red-600 rounded-lg">حذف</button>
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
      tab: 'lucky-box',
      boxTab: 0,

      // Boxes
      boxes: [], categories: [], savingBoxes: false, boxError: '',
      defaultBox: () => ({ name: '', cost: 0, daily_limit: 0, color: '#4a4a8a', is_active: true, prizes: [] }),
      defaultPrize: () => ({ name: '', type: 'point', value: '', weight: 1 }),

      // Wheels
      wheels: [], wheelTab: 0, savingWheels: false, wheelError: '',
      resetUserPhone: '', resetMsg: '', resetError: false,
      defaultWheel: () => ({ name: '', spin_mode: 'none', point_cost: 10, daily_limit: 0, color: '#6366f1', is_active: true, prizes: [] }),
      defaultWheelPrize: () => ({ name: '', type: 'point', value: '', weight: 1, color: '#6366f1' }),

      // Quizzes
      contests: [], contestsLoading: false,
      quizSearch: '', quizStatus: '',
      contestModal: false, editingContest: false, contestError: '',
      contestForm: { title: '', description: '', entry_fee: 0, prize: '', starts_at: '', ends_at: '', status: 'draft' },
      contestPagination: null, contestPageNum: 1,
      contestManageModal: false, managedContest: null, managedQuestions: [], quizError: '',

      // Delete
      showDelete: false, deleteItem: null, deleteType: '',

      debounceTimer: null,
    }
  },
  mounted() { this.loadBoxes() },
  methods: {
    // ===== Lucky Boxes =====
    async loadBoxes() {
      try {
        const r = await axios.get('/api/admin/v1/lucky-boxes')
        this.categories = r.data.categories || []
        if (r.data.boxes.length) {
          this.boxes = r.data.boxes.map(b => ({ ...b, prizes: b.prizes || [] }))
        } else {
          this.boxes = [this.defaultBox()]
        }
        this.boxTab = 0
      } catch (e) { console.error(e) }
    },
    addBox() { if (this.boxes.length < 4) { this.boxes.push(this.defaultBox()); this.boxTab = this.boxes.length - 1 } },
    addPrize() { this.boxes[this.boxTab].prizes.push(this.defaultPrize()) },
    removePrize(bi, pi) { this.boxes[bi].prizes.splice(pi, 1) },
    onPrizeTypeChange(bi, pi) { if (this.boxes[bi].prizes[pi].type !== 'card') this.boxes[bi].prizes[pi].value = '' },
    async saveBoxes() {
      this.savingBoxes = true; this.boxError = ''
      try {
        await axios.post('/api/admin/v1/lucky-boxes/save', { boxes: this.boxes })
        await this.loadBoxes()
      } catch (e) { this.boxError = e.response?.data?.message || 'حدث خطأ' }
      finally { this.savingBoxes = false }
    },

    // ===== Lucky Wheels =====
    async loadWheels() {
      try {
        const r = await axios.get('/api/admin/v1/lucky-wheels')
        this.categories = r.data.categories || []
        if (r.data.wheels.length) {
          this.wheels = r.data.wheels.map(w => ({ ...w, prizes: w.prizes || [] }))
        } else {
          this.wheels = [this.defaultWheel()]
        }
        this.wheelTab = 0
      } catch (e) { console.error(e) }
    },
    addWheel() { if (this.wheels.length < 4) { this.wheels.push(this.defaultWheel()); this.wheelTab = this.wheels.length - 1 } },
    addWheelPrize() { this.wheels[this.wheelTab].prizes.push(this.defaultWheelPrize()) },
    removeWheelPrize(bi, pi) { this.wheels[bi].prizes.splice(pi, 1) },
    onWheelPrizeTypeChange(bi, pi) { if (this.wheels[bi].prizes[pi].type !== 'card') this.wheels[bi].prizes[pi].value = '' },
    async resetFreeSpinsAll() {
      if (!this.wheels[this.wheelTab]?.id) { this.resetMsg = 'احفظ العجلة أولاً'; this.resetError = true; return }
      if (!confirm('هل أنت متأكد من تصفير اللفات المجانية لجميع المستخدمين؟')) return
      this.resetMsg = ''; this.resetError = false
      try {
        const r = await axios.post(`/api/admin/v1/lucky-wheels/${this.wheels[this.wheelTab].id}/reset-free-spins`)
        this.resetMsg = r.data.message
      } catch (e) { this.resetMsg = e.response?.data?.message || 'فشل التصفير'; this.resetError = true }
    },
    async resetFreeSpinsUser() {
      if (!this.wheels[this.wheelTab]?.id) { this.resetMsg = 'احفظ العجلة أولاً'; this.resetError = true; return }
      if (!this.resetUserPhone) { this.resetMsg = 'أدخل رقم الهاتف'; this.resetError = true; return }
      if (!confirm(`هل أنت متأكد من تصفير اللفات المجانية للرقم ${this.resetUserPhone}؟`)) return
      this.resetMsg = ''; this.resetError = false
      try {
        const r = await axios.post(`/api/admin/v1/lucky-wheels/${this.wheels[this.wheelTab].id}/reset-free-spins/user`, {
          phone: this.resetUserPhone,
        })
        this.resetMsg = r.data.message
        this.resetUserPhone = ''
      } catch (e) { this.resetMsg = e.response?.data?.message || 'فشل التصفير'; this.resetError = true }
    },
    async saveWheels() {
      this.savingWheels = true; this.wheelError = ''
      try {
        await axios.post('/api/admin/v1/lucky-wheels/save', { wheels: this.wheels })
        await this.loadWheels()
      } catch (e) { this.wheelError = e.response?.data?.message || 'حدث خطأ' }
      finally { this.savingWheels = false }
    },

    // ===== Contests =====
    async loadContests() {
      this.contestsLoading = true
      try {
        const params = { page: this.contestPageNum }
        if (this.quizSearch) params.search = this.quizSearch
        if (this.quizStatus) params.status = this.quizStatus
        const r = await axios.get('/api/admin/v1/quiz-contests', { params })
        this.contests = r.data.data
        this.contestPagination = { current_page: r.data.current_page, last_page: r.data.last_page, total: r.data.total, prev_page_url: r.data.prev_page_url, next_page_url: r.data.next_page_url }
      } catch (e) { console.error(e) }
      finally { this.contestsLoading = false }
    },
    debouncedQuiz() { clearTimeout(this.debounceTimer); this.debounceTimer = setTimeout(() => { this.contestPageNum = 1; this.loadContests() }, 400) },
    contestPage(p) { this.contestPageNum = p; this.loadContests() },
    openContestModal(item) {
      this.editingContest = !!item; this.contestError = ''
      this.contestForm = item ? { ...item } : { title: '', description: '', entry_fee: 0, prize: '', starts_at: '', ends_at: '', status: 'draft' }
      this.contestModal = true
    },
    async saveContest() {
      this.contestError = ''
      try {
        if (this.editingContest) { await axios.put(`/api/admin/v1/quiz-contests/${this.contestForm.id}`, this.contestForm) }
        else { await axios.post('/api/admin/v1/quiz-contests', this.contestForm) }
        this.contestModal = false; this.loadContests()
      } catch (e) { this.contestError = e.response?.data?.message || 'حدث خطأ' }
    },
    async viewContest(item) {
      try {
        const r = await axios.get(`/api/admin/v1/quiz-contests/${item.id}`)
        this.managedContest = r.data
        this.managedQuestions = (r.data.questions || []).map(q => ({ ...q, _existing: true }))
        this.contestManageModal = true
      } catch (e) { console.error(e) }
    },
    addQuestion() { this.managedQuestions.push({ question: '', options: ['', ''], correct_answer: '', points: 10, sort_order: this.managedQuestions.length }) },
    removeQuestion(i) { this.managedQuestions.splice(i, 1) },
    async saveQuestions() {
      this.quizError = ''
      if (!this.managedContest) return
      try {
        const contestId = this.managedContest.id
        for (const q of this.managedQuestions) {
          if (q._existing) {
            await axios.put(`/api/admin/v1/quiz-contests/${contestId}/questions/${q.id}`, q)
          } else {
            await axios.post(`/api/admin/v1/quiz-contests/${contestId}/questions`, q)
          }
        }
        this.contestManageModal = false
      } catch (e) { this.quizError = e.response?.data?.message || 'حدث خطأ' }
    },

    // ===== Delete =====
    confirmDelete(item, type) { this.deleteItem = item; this.deleteType = type; this.showDelete = true },
    async doDelete() {
      if (this.deleteType !== 'contest') return
      try {
        await axios.delete(`/api/admin/v1/quiz-contests/${this.deleteItem.id}`)
        this.showDelete = false
        this.loadContests()
      } catch (e) { console.error(e) }
    },

    statusClass(s) {
      const m = { open: 'bg-emerald-100 text-emerald-700', draft: 'bg-amber-100 text-amber-700', closed: 'bg-slate-100 text-slate-600', cancelled: 'bg-red-100 text-red-700' }
      return m[s] || 'bg-slate-100 text-slate-600'
    },
  },
}
</script>
