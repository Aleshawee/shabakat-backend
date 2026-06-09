# Shabakat Rewards — Multi-Tenant Architecture

## Overview
Multi-tenant backend serving two frontends (admin panel + user PWA) from a single Laravel app deployed on Render. Tenancy via [stancl/tenancy](https://tenancyforlaravel.com/). Database on TiDB Serverless.

## Hosting
- **Backend**: Render (Docker, PHP 8.3-cli, `php -S` built-in server)
- **Database**: TiDB Serverless (MySQL 8 compatible, TLS required)
- **Central DB**: `shabakat_central` — admins, tenants, domains, tokens
- **Tenant DBs**: `shabakat_{slug}` — users, settings, rewards, etc.

## URLs
| What | URL |
|---|---|
| Backend API | `https://shabakat-backend.onrender.com` |
| Admin panel | `https://shabakat-backend.onrender.com/admin/...` |
| User PWA | `https://shabakat-backend.onrender.com/user-app/...` |

## Admin Types
- **Owner** — role=owner, tenant_id=null, manages all networks. Email: `admin@shabakat.com`
- **Network Admin** — role=admin, tenant_id=networkId, manages one network. Created per network.

## Tenancy Mechanism
| Route Group | Middleware | How Tenancy Initializes |
|---|---|---|
| `/api/v1/*` (user API) | `InitializeTenancyByDomainOrSkip` | 1. `X-Tenant` header → find tenant by slug<br>2. Domain match via `Host` header<br>3. Skipped if neither (API works without tenancy for public endpoints) |
| `/api/admin/v1/*` (admin API) | `InitializeTenancyByAdmin` | 1. Domain match via `Host`<br>2. Admin's `tenant_id` → find tenant<br>3. `X-Tenant` header<br>4. Owner default → first active tenant |

## Credentials (Seeded via bootstrap.php)
| Role | Email / Phone | Password |
|---|---|---|
| Owner | `admin@shabakat.com` | `password` |
| Network Admin (sama) | `network@demo.com` | `password` |
| Test User (sama) | `0597100889` | `password` |

---

## الحل المؤقت — Temporary Workaround

### المشكلة
Render لا يدعم wildcard subdomains (`*.onrender.com`) على الخطة المجانية. لذلك `sama.shabakat-backend.onrender.com` لا يعمل.

### الحل الحالي
تم إضافة صفحة **اختيار الشبكة** في تطبيق العملاء:

1. المستخدم يزور `shabakat-backend.onrender.com/user-app/`
2. إذا لم يكن هناك شبكة مختارة → يظهر له قائمة بالشبكات المتاحة
3. يختار الشبكة → تخزن في `localStorage`
4. يتم إرسال `X-Tenant` مع كل طلب API تلقائياً

**الملفات المعدلة:**
- `user-app/src/pages/NetworkSelect.vue` — صفحة اختيار الشبكة الجديدة
- `user-app/src/main.js` — إضافة المسار و `beforeEach` guard

### كيف يعمل
- الـ axios interceptor يتحقق أولاً من `getNetworkSlug()` (من subdomain)
- إذا كانت null → يقع على `localStorage.getItem('network')`
- `getNetworkSlug()` يتخطى الـ subdomains التي تحتوي على hyphen (مثل `shabakat-backend`)

---

## الهدف المؤجل — Deferred Goal: Custom Domain + Wildcard Subdomains

### الرؤية النهائية
كل شبكة لها رابط خاص بها:
- `https://sama.shabakat.net/user-app/login`
- `https://hala.shabakat.net/user-app/login`
- `https://shabakat.net/admin/...` (لوحة التحكم الرئيسية)

### المتطلبات للوصول للهدف
1. **شراء دومين** (مثل `shabakat.net`)
2. **ترقية Render** إلى خطة مدفوعة (Starter أو أعلى) لدعم:
   - Wildcard SSL (`*.shabakat.net`)
   - Custom domains متعددة
3. **إعداد DNS**: `*.shabakat.net` →指向 Render
4. **تعديل tenancy**: الاعتماد كلياً على subdomain (بدون الحاجة لـ X-Tenant header)
5. **إزالة صفحة اختيار الشبكة** المؤقتة واستخدام subdomain فقط

### الفرق بين الحالي والهدف
| الخاصية | الحالي (مؤقت) | الهدف |
|---|---|---|
| دخول المستخدم | يختار الشبكة من قائمة | يدخل عبر subdomain مباشرة |
| X-Tenant header | مطلوب (من localStorage) | غير مطلوب (من subdomain) |
| SSL | متاح للمدخل الرئيسي فقط | wildcard لكل الشبكات |
| شبكات جديدة | تضاف عبر owner panel | تضاف + subdomain يعمل تلقائياً |

### ملاحظات
- الـ API مهيأ بالكامل للعمل بـ subdomain (middleware جاهز)
- دوال `getNetworkSlug()` في كلا الفرونتاند جاهزة
- بمجرد توفر custom domain + wildcard SSL → فقط نعدل الـ DNS ونوقف `NetworkSelect.vue`
