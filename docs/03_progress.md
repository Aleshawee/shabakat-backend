# 📊 تقدم المشروع — Shabakat Rewards

> **آخر تحديث:** 2 يونيو 2026 — الجلسة 14 (Textbee SMS Gateway + Super Admin Platform + OTP عبر SMS)

---

## 🏗️ Phase 1 — Infrastructure ✅

### Backend
- [x] Laravel 13 + Sanctum + MySQL
- [x] 34 ملف هجرة (جداول) + 26 Model + العلاقات
- [x] Admin Auth (email/password) + User Auth (phone/password + OTP للاستعادة)
- [x] API: `/api/v1/*` للمستخدم، `/api/admin/v1/*` للإدارة
- [x] بيانات Seed: شبكة تجريبية + Super Admin + Network Admin

### Admin Panel (Vue 3 + Vite)
- [x] Tailwind CSS v4 + RTL + Tajawal font
- [x] تسجيل دخول + Dashboard مع Sidebar
- [x] Axios interceptor + Auth guard
- [x] سايدبار متجاوب مع الموبايل (هامبورجر ☰ + سحب)

### User PWA (Vue 3 + Vite + PWA)
- [x] 13 صفحة مكتملة (Login, Dashboard, LuckyBox, LuckyWheel, Rewards, Transfer, Absher, RedeemCard, CardInfo, History, Profile, More, BottomNavigation)

---

## 🚀 Phase 2 — إدارة المحتوى (مكتمل)

| الميزة | الواجهة |
|--------|---------|
| 🏷️ **الفئات** | CRUD + إحصائيات |
| 🎁 **المكافآت** | CRUD + بطاقات شبكة |
| 💳 **الكروت** | استيراد CSV، سلة المحذوفات، إجراءات جماعية |
| 👥 **المستخدمين** | عرض + تعديل + إضافة نقاط (فردي/جماعي) |
| 🔔 **الإشعارات** | CRUD + فلاتر |
| 🖼️ **البانرات** | CRUD + منتقي ألوان |
| 💬 **الرسائل النصية** | إرسال + سجل + إعدادات SMS |

### 🎮 الترفيه (5 تبويبات)

| الميزة | Admin | User API | User PWA |
|--------|-------|----------|----------|
| 🔄 تحويل النقاط | ✅ | ✅ | ✅ Transfer.vue |
| 💰 أبشر (سلفة) | ✅ (v2) | ✅ (v2) | ✅ Absher.vue (v2) |
| 🎁 صندوق الحظ | ✅ | ✅ | ✅ LuckyBox.vue |
| 🎡 عجلة الحظ | ✅ | ✅ | ✅ LuckyWheel.vue |
| ⚽ توقعات الرياضة | ✅ (متكامل) | ✅ (متكامل) | ✅ SportPredictions.vue |
| 📝 مسابقات | ✅ | ✅ | ⏳ مؤجل |

---

## 📋 الجلسة العاشرة — 22 مايو 2026

### 💰 1. إعادة هيكلة أبشر بالكامل

**المشكلة السابقة:**
- `absher_settings` مرتبطة بمكافأة واحدة فقط (`loan_reward_id`)
- `point_cost` يُخصم من الرصيد (120-50=70) بدل إضافته للرصيد
- خطأ `status = 'active'` في `ENUM('available','redeemed','expired')`

**الهيكلة الجديدة:**
- **جدول `category_borrow_settings`**: كل فئة لها إعداداتها (is_borrowable, max_borrow_amount, min_points_threshold)
- **منطق السلفة**: يقترض الفرق فقط (reward.points_cost - balance) ← يحصل على الكرت ← الرصيد يصبح سالباً
- **`User::addPointsWithRepayment()`**: التسديد التلقائي — أي نقاط جديدة تذهب أولاً لتسديد الدين
- رابط في 6 كنترولرات (LuckyBox, LuckyWheel, Card, Transfer, Admin addPoints فردي/جماعي)

**تعديلات أخرى:**
- `AuthController::login()`: إضافة `last_active_at` لتتبع النشاط
- `AbsherSetting`: إضافة `starts_at`/`ends_at` كـ datetime + `require_recent_activity` + `activity_days`
- Fix RewardController: `user_id` → `redeemed_by_user_id` + `status: 'active'` → `'available'`

### 🔒 2. قيود المستخدمين (Restrictions)

**تاب جديد في القائمة الجانبية: `/admin/restrictions`**

| الإعداد | الوصف |
|---------|-------|
| **التصفير التلقائي أول كل شهر** | `php artisan points:auto-reset` — يفحص auto_reset_enabled ويصفر |
| **إبقاء المديونين** | خيار عند التصفير — منع تصفير اللي عليهم ديون (سالب) |
| **تصفير مستخدم برقم هاتف** | إدخال رقم → تأكيد → 0 |
| **تصفير الكل فوراً** | تحذير + خيار إبقاء المديونين → تأكيد |
| **السقف اليومي لاستبدال الكروت** | حد أقصى لاستبدال network_cards يومياً |
| **نوع الإجراء عند التجاوز** | `block` (منع + رسالة) أو `suspend` (حظر حساب) |

**Database:** `settings` table (group: restrictions) — 5 keys

### 🗄️ ملفات جديدة
| الملف | الوظيفة |
|-------|---------|
| `migrations/2026_05_22_000001_create_category_borrow_settings_table.php` | جدول إعدادات الفئات |
| `migrations/2026_05_22_000002_update_absher_settings_add_datetime_and_activity.php` | تعديل absher_settings |
| `migrations/2026_05_22_000003_add_auto_reset_to_absher_settings.php` | auto_reset_points + last_reset_date |
| `app/Models/CategoryBorrowSetting.php` | موديل جديد |
| `app/Http/Controllers/Admin/RestrictionController.php` | قيود المستخدمين (show/update/resetUser/resetByPhone/resetAll) |
| `admin-panel/src/pages/settings/Restrictions.vue` | صفحة القيود كاملة |
| `routes/console.php` (معدل) | أمر `points:auto-reset` + جدولة `monthlyOn(1, '00:00')` |

---

## 📋 الجلسة 11 — 24 مايو 2026 (إصلاحات + توقعات متكاملة)

### 1. إصلاح أخطاء أساسية
| الخطأ | الحل | الملف |
|-------|------|-------|
| `$phone` غير معرف في `sendOtpReset()` عند إنشاء مستخدم جديد | تمرير `$phone` بدلاً من `$request->phone` | `AuthController.php` |
| `VS` بدل `ضد` في كل مكان | تغيير `VS` → `ضد` | user-app + admin-panel |
| 5 مكالمات `PointTransaction::create()` بدون `network_id` | إضافة `network_id ?? 1` | `SportPredictionController` (3) + `SportEventController` (2) |
| كتلة توزيع الجوائز مكررة في `SportEventController::update()` | حذف الكتلة المكررة | `SportEventController.php` |
| `div` بدون إغلاق في `SportPredictions.vue` (كسر Vite) | إضافة `</div>` | `SportPredictions.vue:52` |
| حساب الرصيد محلياً بعد التوقع (غير دقيق) | جلب الرصيد من API (`/api/v1/user/profile`) | `SportPredictions.vue` |
| مستخدم مكرر (777211694 بدون اسم + +967777211694/Yasino) | دمج → حذف user_id=2 | مباشر في DB |

### 2. ميزات جديدة — توقعات الرياضة

**أ. رسوم تعديل التوقع (`edit_fee`)**
- حقل جديد في `prediction_settings`: رسوم تعديل التوقع
- يظهر فقط عند تشغيل "السماح بتعديل التوقع" في إعدادات admin
- إذا `edit_fee > 0` → تخصم الرسوم فقط، النقاط القديمة تبقى، يتغير التوقع فقط
- إذا `edit_fee = 0` → فقط نحدث التوقع، لا نلمس النقاط (أزيلت الـ refund+re-deduct)
- تحذير للمستخدم في نافذة التعديل

**ب. الإغلاق التلقائي للأحداث**
- أمر Artisan: `php artisan sport-events:auto-close`
- مجدول كل دقيقة في `routes/console.php`
- يقفل الأحداث المفتوحة بعد `prediction_deadline`

**ج. منع توزيع الجوائز المكرر**
- حقل `rewards_distributed` في `sport_events`
- `processPredictions()` يتحقق من `rewards_distributed` أولاً
- الزر "صرف الجوائز" معطل إذا سبق الصرف (يظهر ✅ تم الصرف)
- عند تعديل النتيجة بعد completed → يتجاهل `rewards_distributed` ويعيد التوزيع

**د. إحصائيات التوقعات (Dashboard إحصائي)**
- API: `GET /api/admin/v1/sport-events/stats`
- تبويب "📊 الإحصائيات" في admin-panel

**هـ. النتيجة + صرف لكل فائز في admin**
- كرت الحدث: `exact_score` → "النتيجة: 2-1", `winner` → "🏆 الشعلة" (بدون أرقام)
- نافذة التوقعات: زر "صرف المكافأة" → بعد الصرف يتحول إلى ✅ مصروفة
- تأكيد الصرف: يظهر عدد الفائزين والإجمالي

**و. المكافأة في نافذة توقع المستخدم**
- تظهر للمستخدم: رسوم المشاركة (غير مستردة) + المكافأة عند الفوز فقط
- تحذير أحمر: "⚠️ رسوم المشاركة غير مستردة سواء فزت أو خسرت"

**ز. timezone fix**
- Deadline يقارن بـ `Asia/Riyadh` (القيمة المخزنة بتوقيت الرياض، Carbon يقرأها كـ UTC)
- المقارنة: `Carbon::parse($deadline->format('Y-m-d H:i:s'), 'Asia/Riyadh')` — إعادة تفسير القيمة

### 3. إصلاحات منطق العمل (Business Logic)

**أ. رسوم التعديل = 0 → تخطي الـ refund/deduct**
- `SportPredictionController.php`: أزيلت كتلة `refund + re-deduct` بالكامل
- editFee = 0 → فقط تحديث prediction text
- editFee > 0 → خصم الرسوم فقط

**ب. المكافأة فقط (بدون استرداد رسوم المشاركة)**
- `SportEventController.php:processPredictions`: الفائز يحصل على `reward_per_winner` فقط
- أُزيل `+ $prediction->points_bet` من الجائزة
- أُزيلت كتلة `elseif` (استرداد الخاسر) بالكامل
- `rewards_distributed` يُضبط `true` فقط بعد التوزيع الفعلي

**ج. دفع `forceDistribute` للتجاوز عن `auto_distribute_rewards`**
- manual distribute يمرر `$forceDistribute = true` لتوزيع النقاط بغض النظر عن الإعداد

**د. إصلاح مقارنة الوقت (السبب الجذري)**
- المشكلة: `prediction_deadline` مخزن بتوقيت الرياض رقمياً، لكن `Carbon::cast('datetime')` يفسره كـ UTC
- الحل: `Carbon::parse($deadline->format('Y-m-d H:i:s'), 'Asia/Riyadh')` يعيد تفسير القيمة
- AutoCloseCommand: تمرير `nowStr` كـ string بدلاً من Carbon object (لأن Carbon كان يحوله لـ UTC)

**هـ. إصلاح `addPointsWithRepayment`**
- `User.php`: كان ينقصه `$this->increment('points_balance', $repayAmount)` — يسدد الدين لكنه لا يحدث الرصيد
- تمت إضافة `increment` لتحديث `points_balance` فعلياً

**و. تحويل النقاط — بحث بالرقم بدون مفتاح**
- `Transfer.vue`: حذف `'+967' +` من `receiver_phone`
- `TransferController.php`: إضافة تنقية الرقم (`preg_replace('/^\+?967/', '')`) والبحث بالصيغتين

### 4. تحسينات UI
| الصفحة | التغيير |
|--------|---------|
| `admin-panel Home.vue` | إعادة كتابة كاملة — يعرض إحصائيات حقيقية (مستخدمين، كروت، توقعات) + روابط سريعة |
| `admin-panel predictions/Index.vue` | صرف الجوائز من الكرت يجلب عدد الفائزين من API، تأكيد قبل الصرف |
| `user-app RedeemCard.vue` | إصلاح مسار الرصيد (`r.data.points_balance`) |
| `user-app CardInfo.vue` | إعادة تصميم كامل — يتناسق مع باقي الصفحات، عنوان "كرت أبو {name}" |

---

## 📋 الجلسة 12 — 25 مايو 2026 (سجل الاستبدال + تصميم LuckyWheel/LuckyBox + إشعار إعادة التعيين)

### 1. سجل عمليات الاستبدال (Redemptions)
- **Backend:** `RedemptionController` — يعرض سجل استبدال كروت المكافأة مع `redeemed_by_user_id IS NOT NULL`
- **API:** `GET /api/admin/v1/redemptions` مع فلاتر (بحث برقم الجوال، فلتر بمكافأة معينة)
- **إحصائيات:** `total_points_spent` + `total_card_value` (مجموع قيمة الكروت المستبدلة بالريال)
- **Frontend:** صفحة `redemptions/Index.vue` في admin-panel مع بطاقات إحصائية وجدول

### 2. إصلاح `total_card_value` — كروت المكافأة
- كانت `card_value = null` لكروت 100 و 250
- التحديث: `كرت ابو 100 → 100 ريال`, `كرت ابو 250 → 250 ريال`, `كرت ابو 300 → 300 ريال`

### 3. إعادة تصميم LuckyWheel و LuckyBox
- تغيير التصميم ليتناسق مع الثيم الزمردي/الفاتح لكلتا الصفحتين

### 4. إصلاح مشاكل LuckyWheel
- **الكانفاس لا يظهر:** تعديل `v-if` ليتم تشغيل canvas بعد تحميل البيانات
- **Overlay التحميل يمنع الضغط:** إخفاء الـ loading overlay بعد اكتمال الرسم
- **شفافية `opacity-50`:** إزالة opacity-50 من overlay

### 5. إصلاح تحميل LuckyBox
- فصل حالة التحميل (`loading`) عن حالة المكافآت (`loadingRewards`)

### 6. إشعار إعادة التعيين (Reset Notification)
- **Admin toggle:** إعداد في صفحة القيود لتشغيل/إيقاف الإشعار
- **رسالة مخصصة:** حقل نصي للإشعار يظهر للمستخدمين عند التصفير
- **API:** `GET /api/v1/reset-notification` يعرض الإشعار إذا كان مفعّلاً
- **Frontend:** شريط تنبيه أعلى الصفحة الرئيسية للمستخدم (عند التصفير)

### 7. خطأ 500 في Redemptions API
- **السبب:** `Column 'network_id' in where clause is ambiguous` بعد `JOIN` بين `reward_cards` و `rewards`
- **الحل:** إضافة بادئة الجدول: `reward_cards.network_id`
- **أيضاً:** كلون `$totalQuery` (لأن `sum()` ينفذ الاستعلام ويمسح الـ builder)

---

## 📋 الجلسة 13 — 26 مايو 2026 (تحليلات متقدمة + إعادة هيكلة القائمة + لوحة التحكم)

### 1. إصلاح خطأ "Undefined array key network_id" في إضافة النقاط للكل
- **السبب:** الـ form لا يرسل `network_id` (اختياري)، و `$validated['network_id']` يسبب خطأ لأن المفتاح غير موجود
- **الحل:** تغيير `$validated['network_id']` → `!empty($validated['network_id'])` في `UserController.php:181`

### 2. إعادة تصميم الصفحة الرئيسية (Home.vue)
- **Stats Cards:** إجمالي المستخدمين، كروت المكافآت المتاحة، النقاط الممنوحة، إجمالي النقاط المستخدمة
- **مخزون الكروت:** صف أفقي لكل قيمة (100، 250، 300) مع العدد المتبقي
- **مخطط تسجيل المستخدمين الجدد:** Bar chart مع Y-axis labels وتاريخ واضح وفترة قابلة للاختيار (7/14/30 يوم)
- **أكثر المستخدمين نشاطاً:** كارت بفلترين (الأكثر نقاط / الأكثر استبدالاً)
- **Backend:** `DashboardController.php` + route `GET /api/admin/v1/dashboard`

### 3. صفحة التحليلات (Analytics) — جديدة كلياً
- **Backend:** `AnalyticsController.php` + route `GET /api/admin/v1/analytics`
- **فلاتر:** آخر 7/10/30 يوم أو فترة مخصصة
- **7 بطاقات إحصائية:** كروت مستبدلة، نقاط ممنوحة، مستخدمين نشطين، إجمالي المستخدمين، فتح صناديق، تدوير العجلة، التوقعات
- **مخطط استبدال الكروت اليومي:** Bar chart باستخدام Chart.js
- **مخطط توزيع الكروت حسب الفئة:** Doughnut (كروت المكافأة الموزعة لكل فئة)
- **مخطط مصادر النقاط:** Doughnut (إدارة، كروت شحن، صناديق الحظ، عجلة الحظ، توقعات...)
- **جدول أفضل 10 مستخدمين:** مع رتب ذهبية/فضية/برونزية

### 4. إعادة هيكلة القائمة الجانبية (Sidebar)
- تجميع الصفحات في قوائم منسدلة:
  - **المكافآت والكروت ▼:** الفئات، المكافآت، الكروت، سجل الاستبدال
  - **المحتوى ▼:** الإشعارات، البانرات
  - **الترفيه ▼:** التوقعات، صندوق الحظ والعجلة
  - **الخدمات ▼:** أبشر، تحويل النقاط
  - **الإعدادات ▼:** الرسائل النصية، بوابة الرسائل، قيود المستخدمين
- الرئيسية، التحليلات، المستخدمين — روابط مفردة

### 5. ملفات جديدة
| الملف | الوظيفة |
|-------|---------|
| `app/Http/Controllers/Admin/DashboardController.php` | API لوحة التحكم الرئيسية |
| `app/Http/Controllers/Admin/AnalyticsController.php` | API التحليلات والإحصائيات المتقدمة |
| `admin-panel/src/pages/Analytics.vue` | صفحة التحليلات (Chart.js) |
| `admin-panel/src/pages/redemptions/Index.vue` | سجل عمليات الاستبدال |

---

## 📋 الجلسة 14 — 2 يونيو 2026 (Textbee SMS Gateway + Super Admin + OTP)

### 1. Textbee SMS Gateway

**المشكلة:** كان النظام يستخدم Twilio الذي يحتاج اشتراك مدفوع ورصيد. الحل: Textbee — تطبيق أندرويد مجاني يحول الهاتف لـ SMS gateway.

**المكونات الجديدة:**

| الملف | الوظيفة |
|-------|---------|
| `app/Services/TextbeeService.php` | خدمة Textbee — إرسال SMS، إرسال جماعي، سحب الوارد |
| `app/Services/SmsService.php` (مُحدّث) | إضافة `textbee` كمزوّد بجانب Twilio |
| `database/migrations/2026_06_02_000001_*.php` | إضافة `direction`, `sender`, `reference_id`, `received_at` لـ `sms_messages` |
| `app/Models/SmsMessage.php` (مُحدّث) | الحقول الجديدة + casts |
| `app/Http/Controllers/Admin/SettingController.php` (مُحدّث) | دعم `textbee_api_key`, `textbee_device_id` — مقيد للسوبر ادمن فقط |
| `admin-panel/src/pages/settings/Sms.vue` | واجهة إعدادات Textbee + زر اختبار إرسال مع رسائل خطأ تفصيلية |

**API routes الجديدة:**
- `POST /api/admin/v1/sms/test-textbee` — اختبار إرسال عبر Textbee
- `POST /api/admin/v1/sms/fetch-received` — سحب الرسائل الواردة

**تحسين رسائل الخطأ:**
- `TextbeeService::sendSmsWithDetails()` — ترجع رسالة الخطأ الحقيقية من Textbee API
- `fetchReceivedSms()` — تم إصلاح التعامل مع استجابة `{ meta, data }`

**Textbee API endpoints (مُحقّقة):**
- `POST /devices/{id}/send-sms` — إرسال SMS (مع `recipients` و `message`)
- `GET /devices/{id}/get-received-sms` — جلب الوارد (مع `page` و `limit`)

### 2. Unified SMS Dashboard

**التغييرات في `admin-panel/src/pages/sms/Index.vue`:**
- 4 بطاقات إحصائية: إجمالي، مرسلة ناجحة، فاشلة، وارد
- 3 تبويبات: **إرسال** (حملة) — **سجل الرسائل** (جدول موحّد مع فلتر بالاتجاه والحالة) — **الرسائل الواردة** (مع زر سحب من Textbee)
- `sms/stats` و `sms/history` يدعمان `direction` (outgoing/incoming)

### 3. Super Admin Platform

**المشكلة:** لم يكن هناك تفريق حقيقي بين السوبر ادمن (مدير كل الشبكات) و network admin (صاحب شبكة).

**المكونات الجديدة:**

| الملف | الوظيفة |
|-------|---------|
| `app/Http/Controllers/Admin/SuperAdminController.php` | CRUD الشبكات + المدراء + إحصائيات موحّدة (مقيد بـ `role === super_admin`) |
| `app/Models/Network.php` (مُحدّث) | إضافة `users()` و `admins()` relationships |
| `admin-panel/src/pages/SuperDashboard.vue` | `/admin/super` — إحصائيات + جدول الشبكات |
| `admin-panel/src/pages/SuperNetworks.vue` | `/admin/super/networks` — إدارة شبكات + إضافة/حذف مدراء |
| `admin-panel/src/layouts/AdminLayout.vue` (مُحدّث) | ظهور خيارات السوبر ادمن مشروط بـ `role === 'super_admin'` |

**API routes (جميعها تحت `/api/admin/v1/super/*` ومقيدة للسوبر ادمن):**
- `GET /stats` — إحصائيات موحّدة
- `GET/POST/PUT/DELETE /networks` — CRUD الشبكات
- `GET /networks/{id}/admins` — مدراء شبكة
- `POST/PUT/DELETE /admins` — CRUD المدراء

**إعدادات SMS:** مقيدة للسوبر ادمن فقط (`SettingController` يتحقق من `isSuperAdmin()`).

### 4. OTP عبر SMS فقط

**التغيير:** إخفاء كود التحقق من الـ Response — يُرسل فقط عبر SMS.

**الملفات المعدّلة:**
- `AuthController::sendOtpReset()` — إزالة `'otp' => $otp` من الـ JSON response
- `SmsService::sendOtp()` — إرسال OTP عبر Textbee (إذا كان المزوّد `textbee`)

---

## ⏳ ما تبقى

| الميزة | الأولوية | المرحلة | الملاحظات |
|--------|----------|---------|-----------|
| باقات الاشتراك للشبكات | 🔴 عالية | Phase 3 | Subscription plans + network_subscriptions — إدارة اشتراكات العملاء |
| المحافظ والتحويلات المالية | 🔴 عالية | Phase 3 | Wallets + تحويلات Super Admin → Networks + تأكيد SMS |
| المتجر الرقمي | 🟡 متوسطة | Phase 4 | مؤجل |
| المسابقات (User PWA) | 🟢 منخفضة | — | API موجود، يحتاج واجهة مستخدم |
| تحليلات متقدمة | 🟢 منخفضة | — | - |

## 🔗 روابط التشغيل

| الخدمة | الأمر | الرابط |
|--------|-------|--------|
| Laravel API | `php artisan serve --port=8000` | http://localhost:8000/api |
| Admin Panel | `npm run dev` (admin-panel) | http://localhost:5173 |
| User PWA | `npm run dev` (user-app) | http://localhost:5174 |

> **Cron المطلوب للسيرفر:** `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`

## 🧪 حسابات الاختبار

| النوع | البريد/الهاتف | كلمة المرور |
|-------|--------------|-------------|
| Super Admin | admin@shabakat.com | password |
| Network Admin | network@demo.com | password |
| مستخدم | (أي رقم) | (أي كلمة) |
