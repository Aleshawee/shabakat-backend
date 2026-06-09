# Shabakat Rewards — خطة المشروع الشاملة

## وصف المشروع
نظام مكافآت SaaS لشبكات الإنترنت المحلية. كل شبكة إنترنت لها منصتها (subdomain مستقل).
المستخدمون يشترون كروت إنترنت، يسجلونها في النظام، يجمّعون نقاطاً، ويستبدلونها بمكافآت.

الهيكل: **Super Admin** ← **Network Admins (عملاء/مدراء شبكات)** ← **Users (عملاء الشبكات)**

## التقنيات المستخدمة
- **Backend:** Laravel 13 (PHP 8.3)
- **Admin Dashboard:** Vue.js 3 + Vite + Tailwind CSS (RTL)
- **User App:** Vue.js 3 PWA (Service Worker + Manifest) — لاحقاً Flutter
- **Database:** MySQL 8.4 (MariaDB)
- **API Auth:** Sanctum (Personal Access Tokens)
- **SMS Gateway:** Textbee (تطبيق أندرويد كـ gateway) — Twilio كبديل
- **OTP:** إرسال عبر Textbee SMS (كود التحقق لا يظهر في الـ response)
- **RTL:** Tailwind RTL + خط Tajawal

## هيكل المشروع (الفعلي)
```
Shabakat_Rewards/
├── backend/                  ← Laravel API (port 8000)
│   ├── app/
│   │   ├── Models/           ← 25+ Model
│   │   ├── Http/Controllers/
│   │   │   ├── Api/          ← Auth, LuckyBox, LuckyWheel, Absher, Transfer
│   │   │   └── Admin/        ← CRUD لكل الجداول + Absher/Transfer/Prediction Settings
│   ├── database/migrations/  ← 34 ملف Migration
│   ├── routes/
│   │   ├── api.php           ← /api/v1/* نقاط API المستخدم
│   │   └── admin.php         ← /api/admin/v1/* نقاط API الإدارة
│   └── bootstrap/app.php     ← تسجيل المسارات + Sanctum
├── admin-panel/              ← Vue.js لوحة الإدارة (port 5173)
│   ├── src/pages/
│   │   ├── Login.vue         ← دخول المدراء
│   │   ├── Home.vue          ← الرئيسية (إحصائيات)
│   │   ├── categories/
│   │   │   └── Index.vue     ← إدارة الفئات والنقاط
│   │   ├── rewards/
│   │   │   └── Index.vue     ← إدارة المكافآت
│   │   ├── cards/
│   │   │   └── Index.vue     ← إدارة الكروت (كروت مكافأة + كروت شبكة + استيراد)
│   │   ├── predictions/
│   │   │   └── Index.vue     ← توقعات الرياضة (3 تبويبات + API خارجي)
│   │   ├── absher/
│   │   │   └── Index.vue     ← إعدادات أبشر (سلفة)
│   │   └── transfer/
│   │       └── Index.vue     ← إعدادات تحويل النقاط
│   └── dist/                 ← مبنية وجاهزة
├── user-app/                 ← Vue.js PWA (port 5174)
│   ├── src/pages/
│   │   ├── Login.vue         ← دخول برقم الهاتف + OTP
│   │   ├── Dashboard.vue     ← رصيد النقاط + Quick Actions (يحتاج تطوير)
│   │   ├── LuckyBox.vue      ← صندوق الحظ
│   │   ├── LuckyWheel.vue    ← عجلة الحظ
│   │   └── ...               ← أبشر، تحويل، توقعات، مسابقات (قيد الإنشاء)
│   └── dist/                 ← مبنية وجاهزة (مع Service Worker)
├── docs/                     ← التوثيق والخطط
├── images/                   ← 50 صورة من PRD
├── PRD.html                  ← وثيقة المتطلبات
└── start.bat                 ← ملف تشغيل البيئة
```

## مراحل التنفيذ

| المرحلة | الحالة | المدة |
|---------|--------|-------|
| 1. Infrastructure (Laravel + DB + Migrations + Models + Auth + Vue Admin + User PWA) | ✅ **مكتملة** | يوم 1 |
| 2. Admin CRUD APIs (Categories, Rewards, Cards, Users, Notifications, SMS) | ✅ **مكتملة** | يوم 2-3 |
| 3. Admin Settings (Sports Predictions, Absher, Transfer Settings) | ✅ **مكتملة** | يوم 4-5 |
| 4. User API (Absher + Transfer + Sports) | ✅ **مكتملة** | يوم 5-6 |
| 5. **User PWA Pages (Login, Dashboard, Absher, Transfer, Sports, Quizzes, Rewards, History, Profile)** | ✅ **مكتملة** | يوم 6-7 |
| 6. Admin Dashboard + Analytics | ✅ **مكتملة** | يوم 8 |
| 7. Super Admin Platform (Multi-Network Management, SMS Control) | ✅ **مكتملة** | يوم 10 |
| 8. Textbee SMS Gateway (Send + Receive + Unified Dashboard) | ✅ **مكتملة** | يوم 11 |
| 9. OTP عبر SMS (إخفاء الكود من الـ Response) | ✅ **مكتملة** | يوم 11 |
| 10. Subscription Plans & Billing for Networks | ⏳ **قادمة** | يوم 12 |
| 11. Wallets & Financial Transfers (Super Admin → Networks) | ⏳ **قادمة** | يوم 13 |
| 12. MikroTik Hotspot Integration | ⏳ قادمة | بعد توفر تفاصيل |
| 13. User PWA — Quiz Contests + Missing Pages | ⏳ قادمة | يوم 9 |
| 14. Testing + Deployment | ⏳ قادمة | - |

## Multi-Tenant Architecture
- حالياً: `network_id` في كل جدول (للسرعة)
- مستقبلاً: قاعدة بيانات منفصلة لكل شبكة (خطة هجرة موجودة)
- 3 مستويات صلاحية:
  1. **Super Admin** — يدير كل الشبكات، نسب الأرباح، التحويلات المالية
  2. **Network Admin** (صاحب الشبكة) — يدير فئاته، كروته، مستخدميه
  3. **User** (عميل الشبكة) — يشحن كروت، يلعب، يستبدل

## API Endpoints

### المسارات العامة
| الطريقة | المسار | الوصف |
|---------|--------|-------|
| POST | `/api/v1/auth/register` | تسجيل مستخدم جديد (phone + password + name) — network_slug يُكتشف من subdomain |
| POST | `/api/v1/auth/login` | دخول برقم الهاتف + كلمة المرور |
| GET | `/api/v1/auth/networks` | قائمة الشبكات المتاحة (للاستخدام الداخلي) |
| POST | `/api/v1/auth/send-otp` | إرسال OTP عبر SMS (لا يُعاد الكود) |
| POST | `/api/v1/auth/verify-otp` | تأكيد OTP (يُعيد توكن) |
| POST | `/api/v1/auth/reset-password` | [محمي] إعادة تعيين كلمة المرور (يتطلب توكن من verify-otp) |

### مسارات الإدارة (Admin)
| الطريقة | المسار | الوصف |
|---------|--------|-------|
| POST | `/api/admin/v1/admin/login` | دخول المدير (email + password) |
| GET | `/api/admin/v1/admin/profile` | عرض الملف الشخصي |
| POST | `/api/admin/v1/admin/logout` | تسجيل خروج |
| GET/POST/PUT/DELETE | `/api/admin/v1/categories` | CRUD الفئات |
| GET/POST/PUT/DELETE | `/api/admin/v1/rewards` | CRUD المكافآت |
| GET | `/api/admin/v1/cards/stats` | إحصائيات الكروت |
| GET | `/api/admin/v1/reward-cards` | عرض كروت المكافأة (مع فلاتر) |
| GET | `/api/admin/v1/reward-cards/stats` | إحصائيات كروت المكافأة |
| POST | `/api/admin/v1/reward-cards/import` | استيراد كروت مكافأة (نص/CSV) |
| DELETE | `/api/admin/v1/reward-cards/{id}` | حذف ناعم لكارت مكافأة |
| POST | `/api/admin/v1/reward-cards/{id}/restore` | استعادة كارت مكافأة |
| DELETE | `/api/admin/v1/reward-cards/{id}/force` | حذف نهائي لكارت مكافأة |
| POST | `/api/admin/v1/reward-cards/bulk-delete` | حذف جماعي لكروت مكافأة |
| POST | `/api/admin/v1/reward-cards/bulk-restore` | استعادة جماعية لكروت مكافأة |
| POST | `/api/admin/v1/reward-cards/bulk-force-delete` | حذف نهائي جماعي |
| GET | `/api/admin/v1/network-cards` | عرض كروت الشبكة (مع فلاتر) |
| GET | `/api/admin/v1/network-cards/stats` | إحصائيات كروت الشبكة |
| POST | `/api/admin/v1/network-cards/import` | استيراد كروت شبكة (نص/CSV) |
| DELETE | `/api/admin/v1/network-cards/{id}` | حذف ناعم لكارت شبكة |
| POST | `/api/admin/v1/network-cards/{id}/restore` | استعادة كارت شبكة |
| DELETE | `/api/admin/v1/network-cards/{id}/force` | حذف نهائي لكارت شبكة |
| POST | `/api/admin/v1/network-cards/bulk-delete` | حذف جماعي لكروت شبكة |
| POST | `/api/admin/v1/network-cards/bulk-restore` | استعادة جماعية لكروت شبكة |
| POST | `/api/admin/v1/network-cards/bulk-force-delete` | حذف نهائي جماعي |
| GET | `/api/admin/v1/users` | عرض المستخدمين (فلاتر/بحث/فرز) |
| GET | `/api/admin/v1/users/stats` | إحصائيات المستخدمين |
| GET | `/api/admin/v1/users/{id}` | عرض مستخدم (مع المعاملات، الأجهزة، الكروت) |
| PUT | `/api/admin/v1/users/{id}` | تحديث بيانات مستخدم |
| GET | `/api/admin/v1/users/{id}/point-transactions` | حركات نقاط المستخدم |
| GET | `/api/admin/v1/users/{id}/network-cards` | كروت الشبكة التي استخدمها المستخدم |
| GET | `/api/admin/v1/users/{id}/reward-cards` | كروت المكافأة التي استبدلها المستخدم |
| POST | `/api/admin/v1/users/{id}/add-points` | [جديد] إضافة نقاط يدوية لمستخدم |
| POST | `/api/admin/v1/users/add-points-bulk` | [جديد] إضافة نقاط لكل المستخدمين |
| GET/POST/PUT/DELETE | `/api/admin/v1/notifications` | CRUD الإشعارات (مع /stats) |
| POST | `/api/v1/auth/verify-otp` | التحقق من OTP المخزن في `otp_codes` |
| — | `otp_codes` (جدول) | يخزن رمز OTP لمدة 5 دقائق، مع user_id و expires_at و used_at |
| — | `SmsService` (كلاس) | يقرأ إعدادات Twilio من جدول settings ويرسل SMS (أو يسجلها في log) |
| GET/POST/PUT/DELETE | `/api/admin/v1/banners` | CRUD البانرات (مع /stats) |
| GET | `/api/admin/v1/settings/sms` | عرض إعدادات SMS |
| PUT | `/api/admin/v1/settings/sms` | تحديث إعدادات SMS |
| GET | `/api/admin/v1/sms/stats` | إحصائيات الرسائل النصية |
| GET | `/api/admin/v1/sms/history` | سجل الرسائل النصية (مع فلاتر) |
| POST | `/api/admin/v1/sms/count-target` | عدّ المستخدمين المستهدفين |
| POST | `/api/admin/v1/sms/send` | إرسال رسائل SMS للجمهور المستهدف |
| GET | `/api/admin/v1/lucky-boxes` | عرض كل صناديق الحظ مع جوائزها + الفئات |
| POST | `/api/admin/v1/lucky-boxes/save` | حفظ جميع الصناديق والجوائز (JSON upsert) |
| GET | `/api/v1/lucky-boxes` | [مستخدم] عرض الصناديق النشطة مع today_plays + remaining_plays |
| POST | `/api/v1/lucky-boxes/{box}/play` | [مستخدم] فتح صندوق + خصم نقاط + اختيار عشوائي موزون + حد يومي |
| GET | `/api/admin/v1/lucky-wheels` | عرض كل عجلات الحظ مع جوائزها + الفئات |
| POST | `/api/admin/v1/lucky-wheels/save` | حفظ جميع العجلات والجوائز (JSON upsert) |
| GET | `/api/v1/lucky-wheels` | [مستخدم] عرض العجلات النشطة مع today_spins + next_spin_cost + can_spin_free |
| POST | `/api/v1/lucky-wheels/{wheel}/spin` | [مستخدم] لفة عجلة + التحقق من spin_mode (مجاني/مدفوع) + حد يومي |
| GET/POST/PUT/DELETE | `/api/admin/v1/sport-events` | CRUD أحداث رياضية + عرض |
| GET | `/api/admin/v1/sport-events/stats` | إحصائيات التوقعات (التوقعات، الفائزون، الرسوم، المكافآت) |
| POST | `/api/admin/v1/sport-events/{id}/distribute` | صرف جوائز يدوي |
| GET/POST/PUT/DELETE | `/api/admin/v1/quiz-contests` | CRUD مسابقات |
| GET/POST/PUT/DELETE | `/api/admin/v1/quiz-contests/{id}/questions` | CRUD أسئلة المسابقات (nested) |
| GET/PUT | `/api/admin/v1/absher/settings` | عرض/تحديث إعدادات أبشر |
| GET/PUT | `/api/admin/v1/transfer/settings` | عرض/تحديث إعدادات تحويل النقاط |
| GET | `/api/admin/v1/transfer/lookup-user` | بحث مستخدم برقم الجوال |
| GET/PUT | `/api/admin/v1/predictions/settings` | عرض/تحديث إعدادات التوقعات (مع edit_fee) |
| POST | `/api/admin/v1/sms/test-textbee` | [سوبر ادمن] اختبار إرسال عبر Textbee |
| POST | `/api/admin/v1/sms/fetch-received` | سحب الرسائل الواردة من Textbee |
| GET | `/api/admin/v1/super/stats` | [سوبر ادمن] إحصائيات موحّدة (الشبكات، المستخدمين...) |
| GET/POST/PUT/DELETE | `/api/admin/v1/super/networks` | [سوبر ادمن] CRUD الشبكات |
| GET | `/api/admin/v1/super/networks/{id}/admins` | [سوبر ادمن] مدراء شبكة معينة |
| POST/PUT/DELETE | `/api/admin/v1/super/admins` | [سوبر ادمن] CRUD المدراء |
| GET | `/api/v1/absher/settings` | [مستخدم] عرض إعدادات أبشر |
| POST | `/api/v1/absher/request` | [مستخدم] طلب سلفة |
| GET | `/api/v1/transfer/settings` | [مستخدم] عرض إعدادات التحويل |
| GET | `/api/v1/transfer/lookup-user` | [مستخدم] بحث مستخدم برقم الجوال |
| POST | `/api/v1/transfer/send` | [مستخدم] إرسال نقاط |
| GET | `/api/v1/user/transactions` | [مستخدم] حركات النقاط (pagination) |
| GET | `/api/v1/user/history` | [مستخدم] آخر 100 حركة + الرصيد |
| POST | `/api/v1/user/change-password` | [مستخدم] تغيير كلمة المرور |
| GET | `/api/v1/rewards` | [مستخدم] المكافآت المتاحة |
| POST | `/api/v1/rewards/{reward}/redeem` | [مستخدم] استبدال مكافأة |
| GET | `/api/v1/user/reward-cards` | [مستخدم] بطاقات المكافآت الخاصة بي |

## حسابات الاختبار
- **Super Admin:** admin@shabakat.com / password
- **Network Admin:** network@demo.com / password
- **OTP:** يُرسل عبر Textbee SMS — لا يُعرض في الـ response
