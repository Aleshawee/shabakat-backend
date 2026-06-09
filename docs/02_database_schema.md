# مخطط قاعدة البيانات (Database Schema)

## إجمالي الجداول: 31 جدولاً

## قائمة الجداول الكاملة مع الأعمدة

### 1. networks — الشبكات
| العمود | النوع | الشرح |
|--------|------|-------|
| id | bigint | المعرف |
| name | string | اسم الشبكة |
| slug | string (unique) | الاسم المختصر للـ subdomain |
| domain | string? | الدومين المخصص |
| owner_name | string? | اسم صاحب الشبكة |
| phone | string? | هاتف صاحب الشبكة |
| email | string? | بريد صاحب الشبكة |
| commission_rate | decimal(5,2) | نسبة عمولة المنصة (%) |
| logo | string? | شعار الشبكة |
| theme_colors | json? | الألوان المخصصة |
| status | enum | active/suspended/trial |

### 2. users — مشتركي الإنترنت
| العمود | النوع | الشرح |
|--------|------|-------|
| id | bigint | المعرف |
| network_id | FK→networks (nullable) | الشبكة التابع لها (nullable للمستخدمين المسجلين خارج الشبكة) |
| name | string? | اسم المستخدم |
| phone | string (unique) | رقم الهاتف (طريقة التسجيل) |
| phone_verified_at | timestamp? | تاريخ توثيق الرقم |
| password | string? | كلمة المرور |
| device_id | string? | معرف الجهاز (لمكافحة الاحتيال) |
| points_balance | integer (0) | رصيد النقاط الحالي |
| wallet_balance | decimal(10,2) (0) | رصيد المحفظة المالية |
| status | enum | active/suspended/banned |

### 3. admins — مدراء النظام
| العمود | النوع | الشرح |
|--------|------|-------|
| id | bigint | المعرف |
| network_id | FK? (nullable) | null = سوبر أدمن |
| name | string | اسم المدير |
| email | string (unique) | البريد الإلكتروني |
| password | string | كلمة المرور |
| role | enum | super_admin / admin |
| permissions | json? | صلاحيات محددة (للمدير العادي) |
| status | enum | active / inactive |

### 4. categories — فئات الكروت
- `name` (100 ريال, 200 ريال...)
- `price` (سعر الكرت)
- `points` (النقاط المكتسبة)
- `is_active` (ظهور/إخفاء للعملاء)

### 5. rewards — المكافآت
- `name`, `description`, `points_cost`, `card_value`, `image`, `is_active`

### 6. reward_cards — كروت المكافأة المخزنة
- `reward_id`, `code` (unique), `status` (available/redeemed/expired)
- `redeemed_by_user_id`, `redeemed_at`
- `deleted_at` (soft delete)

### 7. network_cards — كروت الشبكة الفعلية
- `category_id`, `code` (unique), `status` (active/used/expired)
- `used_by_user_id`, `used_at`, `batch_id`
- `deleted_at` (soft delete)

### 8. point_transactions — كل حركات النقاط
- `user_id`, `type` (earn/spend/transfer/borrow/wheel...)
- `amount` (+/-), `balance_after`
- `reference_type`, `reference_id` (Morph) — Polymorphic relation
- `note`

### 9. card_redemptions — استبدال النقاط
- `user_id`, `reward_card_id`, `reward_id`, `points_spent`

### 10-13. Lucky Box / Lucky Wheel
- LuckyBox: `network_id`, `name`, `cost`, `daily_limit` (0=unlimited), `color`, `is_active`
- LuckyBoxPrize: `lucky_box_id` (FK→lucky_boxes), `name`, `type` (point/card/nothng), `value`, `weight`
- LuckyBoxPlay: `user_id`, `lucky_box_id` (FK→lucky_boxes), `prize_id`, `points_spent`, `result`
- LuckyWheel: `network_id`, `name`, `spin_mode`, `point_cost`, `daily_limit`, `color`, `is_active`
- LuckyWheelPrize: `lucky_wheel_id` (FK→lucky_wheels), `name`, `type` (point/card/nothng), `value`, `weight`, `color`
- LuckyWheelPlay: `user_id`, `lucky_wheel_id` (FK→lucky_wheels), `prize_id`, `points_spent`, `result`

### 14-15. Sports Predictions
- SportEvent: `title`, `match_date`, `entry_fee`, `status`, `winner`
- SportPrediction: `event_id`, `user_id`, `prediction`, `points_bet`, `is_winner`

### 16-18. Quiz Contests
- QuizContest: `title`, `entry_fee`, `prize`, `starts_at`, `ends_at`, `status`
- QuizQuestion: `contest_id`, `question`, `options` (JSON), `correct_answer`, `points`
- QuizAnswer: `question_id`, `user_id`, `answer`, `is_correct`, `points_earned`

### 19-20. Engagement
- PointTransfer: `sender_id`, `receiver_id`, `amount`, `fee`, `gross_amount`, `status`
- PointBorrow: `user_id`, `amount`, `fee`, `total_debt`, `repaid_amount`, `status`

### 21-22. Communication
- Notification: `title`, `body`, `image`, `audience`, `target_user_ids` (JSON), `status`
- Banner: `title`, `image`, `link`, `is_active`, `sort_order`, `expires_at`

### 23-26. Store
- Product: `name`, `description`, `price`, `type` (card/product), `stock`
- Order: `user_id`, `total`, `status`, `payment_method`, `payment_receipt`
- OrderItem: `order_id`, `product_id`, `quantity`, `price`, `card_code`
- WalletTransaction: `user_id`, `type`, `amount`, `balance_after`, `status`

### 27a. otp_codes — رموز التحقق
| العمود | النوع | الشرح |
|--------|------|-------|
| id | bigint | المعرف |
| user_id | FK→users | المستخدم |
| code | string(6) | رمز OTP (6 أرقام) |
| expires_at | timestamp | تاريخ انتهاء الصلاحية (5 دقائق) |
| used_at | timestamp? | تاريخ الاستخدام |
| created_at/updated_at | timestamps | |

### 27b. sms_messages — سجل الرسائل النصية
| العمود | النوع | الشرح |
|--------|------|-------|
| id | bigint | المعرف |
| network_id | FK→networks | الشبكة |
| phone | string(20) | رقم الهاتف المستلم |
| message | text | نص الرسالة |
| status | string(20) | sent/failed |
| sent_at | timestamp? | تاريخ الإرسال |
| created_at/updated_at | timestamps | |

### 28-32. System
- Setting: `network_id`, `key`, `value`, `group` (unique pair: network_id+key)
- FeatureToggle: `feature_key`, `label`, `is_enabled`
- ActivityLog: `network_id`, `admin_id`, `action`, `target_type`, `target_id`, `details`, `ip`
- DeviceFingerprint: `user_id`, `device_id`, `device_name`, `ip`, `risk_level`
- Channel: `name`, `stream_url`, `is_active`, `sort_order`

## العلاقات الرئيسية
```
networks ──┬── users
           ├── admins (nullable)
           ├── categories
           ├── reward_cards
           ├── network_cards
           ├── point_transactions
           └── جميع الجداول الأخرى

users ──┬── card_redemptions
        ├── point_transfers (sender/receiver)
        ├── point_borrows
        ├── lucky_box_plays
        ├── lucky_wheel_plays
        ├── sport_predictions
        ├── quiz_answers
        ├── orders
        ├── wallet_transactions
        └── device_fingerprints

categories ──── rewards ──── reward_cards
categories ──── network_cards

sport_events ──── sport_predictions
quiz_contests ──── quiz_questions ──── quiz_answers

orders ──── order_items ──── products

point_transactions ← polymorphic (reference table)
```
