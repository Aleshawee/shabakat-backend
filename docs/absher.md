# 💰 أبشر — ميزة السلفة (Salaf / Loan)

## نظرة عامة
خدمة "أبشر" تسمح للمستخدم باقتراض الفرق بين رصيده وسعر المكافأة ليحصل على كرت المكافأة فوراً. يتم تسجيل الدين في `point_borrows` ويُسدّد تلقائياً من أول نقاط يكسبها المستخدم.

---

## قاعدة البيانات

### جدول `absher_settings` (إعدادات عامة)
| العمود | النوع | الشرح |
|--------|-------|-------|
| `id` | bigint (PK) | |
| `network_id` | FK → networks | الشبكة |
| `is_enabled` | boolean | تفعيل الميزة |
| `starts_at` | datetime (nullable) | بداية النطاق الزمني |
| `ends_at` | datetime (nullable) | نهاية النطاق الزمني |
| `require_recent_activity` | boolean | اشتراط آخر نشاط خلال X يوم |
| `activity_days` | integer | عدد الأيام للنشاط الأخير |
| `auto_reset_points` | boolean | تفعيل التصفير التلقائي أول كل شهر |
| `last_reset_date` | date (nullable) | تاريخ آخر تصفير |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### جدول `category_borrow_settings` (إعدادات كل فئة)
| العمود | النوع | الشرح |
|--------|-------|-------|
| `id` | bigint (PK) | |
| `network_id` | FK → networks | الشبكة |
| `category_id` | FK → categories | الفئة |
| `is_borrowable` | boolean | هل الفئة قابلة للسلفة |
| `max_borrow_amount` | integer | أقصى مبلغ سلفة مسموح لهذه الفئة |
| `min_points_threshold` | integer | الحد الأدنى من النقاط للتأهل |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |
| **UNIQUE** | (network_id, category_id) | |

---

## Backend API

### Admin — إعدادات أبشر

#### `GET /api/admin/v1/absher/settings`
جلب الإعدادات العامة + الفئات مع إعدادات السلفة.

**الاستجابة:**
```json
{
  "setting": { "is_enabled": true, "starts_at": "...", "ends_at": "...", "require_recent_activity": false, "activity_days": 0, "auto_reset_points": false, "last_reset_date": null },
  "categories": [
    {
      "id": 1, "name": "100", "price": 100,
      "reward": { "id": 1, "name": "كرت 100", "points_cost": 150 },
      "borrow_setting": { "id": 1, "is_borrowable": true, "max_borrow_amount": 50, "min_points_threshold": 100 }
    }
  ],
  "stats": { "total_borrowers": 5, "total_loans": 12, ... }
}
```

#### `PUT /api/admin/v1/absher/settings`
تحديث الإعدادات العامة + `category_settings` (مصفوفة).

---

### قيود المستخدمين (🔒 Restrictions)

#### `GET /api/admin/v1/restrictions`
#### `PUT /api/admin/v1/restrictions`
| الحقل | النوع | الشرح |
|-------|-------|-------|
| `daily_card_redeem_limit` | integer | السقف اليومي لاستبدال الكروت (0=غير محدود) |
| `daily_card_exceed_action` | enum | `block` أو `suspend` |
| `auto_reset_enabled` | boolean | تفعيل التصفير التلقائي |
| `reset_keep_debtors` | boolean | إبقاء المديونين عند التصفير |

#### `POST /api/admin/v1/reset-points/by-phone`
تصفير رصيد مستخدم برقم هاتف.
#### `POST /api/admin/v1/reset-points/all`
تصفير جميع المستخدمين. Body: `{ "keep_debtors": true }`

---

### User API

#### `GET /api/v1/absher/settings`
تُرجع الفئات المؤهلة للمستخدم مع حساب `is_eligible` و `needed_amount`.

#### `POST /api/v1/absher/request`
Body: `{ "category_id": 1 }`
- يقترض الفرق: `reward.points_cost - user.points_balance`
- يُنشئ `PointBorrow` + يسجل حركة
- يُسند كرت مكافأة متاح (`RewardCard`) للمستخدم
- الرصيص بعد العملية: `balance - reward.points_cost` (قد يصبح سالباً)

---

## التسديد التلقائي (Auto-Repayment)
أُنشئت دالة `User::addPointsWithRepayment()` تُستدعى في كل مكان تُضاف فيه نقاط:
- LuckyBox / LuckyWheel (نقاط)
- استبدال كرت (`CardController`)
- تحويل نقاط (المستلم)
- إدارة (إضافة نقاط)

الآلية: إذا كان للمستخدم `PointBorrow` نشط ورصيده سالب، أول نقاط تدخل تذهب لتسديد الدين.

---

## المنطق الجديد (v2)
- **كل فئة لها إعداداتها المستقلة** (is_borrowable, max_borrow_amount, min_points_threshold)
- **لا يوجد point_cost ثابت** — المستخدم يقترض فقط الفرق بين رصيده وسعر المكافأة
- **الرصيد يصبح سالباً** بعد السلفة (دين) ويُسدّد من الأرباح القادمة
- **جدولة تصفير تلقائي** أول كل شهر عبر `php artisan points:auto-reset`

---

## العلاقات
```
absher_settings.network_id → networks.id
category_borrow_settings.network_id → networks.id
category_borrow_settings.category_id → categories.id
categories.rewards → reward (through category_id)
```
