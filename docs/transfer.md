# 🔄 تحويل النقاط — Points Transfer

## نظرة عامة
ميزة تحويل النقاط تسمح للمستخدمين بتحويل نقاطهم لبعضهم البعض. يدعم النظام رسوم تحويل بنسبة مئوية، حدود تحويل، والتحقق من رقم الجوال.

---

## قاعدة البيانات

### جدول `transfer_settings`
| العمود | النوع | الشرح |
|--------|-------|-------|
| `id` | bigint (PK) | |
| `network_id` | FK → networks | الشبكة |
| `is_enabled` | boolean | تفعيل التحويل |
| `min_transfer_amount` | integer (def:10) | الحد الأدنى للتحويل |
| `max_transfer_amount` | integer (def:1000) | الحد الأقصى للتحويل |
| `transfer_fee_percent` | decimal(5,1) | نسبة الرسوم (0 = مجاني) |
| `min_balance_required` | integer (def:0) | الحد الأدنى لرصيد المحوِّل |
| `require_phone_verification` | boolean (def:false) | التحقق من رقم المستلم قبل التحويل |

### جدول `point_transfers` (موجود مسبقاً)
| العمود | النوع |
|--------|-------|
| `network_id` | FK |
| `sender_id` | FK → users |
| `receiver_id` | FK → users |
| `amount` | int (بعد خصم العمولة) |
| `fee` | int (العمولة) |
| `gross_amount` | int (قبل العمولة) |
| `status` | enum(completed, failed) |

---

## Backend API

### `GET /api/admin/v1/transfer/settings`
جلب الإعدادات + الإحصائيات.

**الاستجابة:**
```json
{
  "setting": { "is_enabled": true, "min_transfer_amount": 10, "max_transfer_amount": 1000, "transfer_fee_percent": 5.0, "min_balance_required": 0 },
  "stats": {
    "total_transfers": 150,
    "total_amount": 25000,
    "total_fees": 1250,
    "total_senders": 45,
    "total_receivers": 38,
    "recent_transfers": [
      { "id": 1, "amount": 100, "fee": 5, "sender": { "name": "أحمد" }, "receiver": { "name": "محمد" } }
    ]
  }
}
```

### `PUT /api/admin/v1/transfer/settings`
تحديث الإعدادات.

### `GET /api/admin/v1/transfer/lookup-user?phone=XXXXXXXX`
البحث عن مستخدم برقم الجوال. يعيد `404` مع رسالة إن لم يوجد.

---

## التصميم (تميز)

- **لون مميز**: تدرج أخضر/نعناعي (teal) يختلف عن أبشر
- **بطاقات إحصائيات مدمجة في الهيدر**: 5 مؤشرات (تحويل، نقاط، رسوم، محوِّلين، مستقبلين)
- **🔍 التحقق من المستلم**: خيار تفعيل في الإعدادات — عند التفعيل يجب على المستخدم إدخال رقم جوال المستلم والتحقق منه قبل الإرسال

---

## الملفات

| الملف | الوظيفة |
|-------|---------|
| `database/migrations/2026_05_21_000002_create_transfer_settings_table.php` | الهجرة |
| `app/Models/TransferSetting.php` | الموديل |
| `app/Http/Controllers/Admin/TransferSettingController.php` | التحكم (show, update, lookupUser) |
| `routes/admin.php` | المسارات |
| `admin-panel/src/pages/transfer/Index.vue` | صفحة الإعدادات |
| `admin-panel/src/main.js` | تسجيل المسار |
| `admin-panel/src/layouts/AdminLayout.vue` | رابط السايدبار |
