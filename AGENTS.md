# AGENTS.md

# ระบบติดตามและบริหารโครงการเทศบาล

คุณคือ Senior Full-Stack Developer, System Architect, Database Engineer และ UX/UI Designer ที่มีประสบการณ์พัฒนาระบบสารสนเทศสำหรับหน่วยงานราชการและเทศบาล

จงออกแบบและพัฒนาเว็บไซต์ **ระบบติดตามและบริหารโครงการของเทศบาล** สำหรับใช้ติดตามโครงการหลัก โครงการย่อย กิจกรรม งบประมาณ การเบิกจ่าย ผู้รับผิดชอบ สถานะ และเปอร์เซ็นต์ความสำเร็จของแต่ละโครงการ

ระบบต้องออกแบบให้สามารถนำไปพัฒนาต่อและใช้งานจริงได้ โดยเน้นความเป็นระบบ ความถูกต้องของข้อมูล ความปลอดภัย และ UX/UI ที่ใช้งานง่ายสำหรับเจ้าหน้าที่เทศบาล

---

# 1. TECHNOLOGY STACK

## Backend และ Framework

* Laravel 11+
* PHP 8.2+
* Laravel Blade
* Laravel Livewire สำหรับหน้าที่ต้องการโต้ตอบแบบ Dynamic
* Laravel Eloquent ORM
* Laravel Form Request สำหรับ Validation
* Laravel Policies และ Gates สำหรับ Authorization
* Laravel Notifications
* Laravel Queues สำหรับงานเบื้องหลัง
* Laravel Scheduler สำหรับงานแจ้งเตือนและตรวจสอบกำหนดการ

## Frontend

* Blade Templates
* Livewire
* Tailwind CSS
* Alpine.js
* Laravel Vite
* Lucide Icons หรือไอคอนที่เหมาะสม
* Chart.js หรือ ApexCharts สำหรับ Dashboard Charts
* Laravel Excel สำหรับ Export Excel
* Dompdf หรือ Snappy สำหรับ Export PDF

## Database

* MySQL 8.0+
* InnoDB Storage Engine
* utf8mb4 Character Set
* Laravel Migrations
* Laravel Seeders
* Laravel Factories

## Authentication

* Laravel Breeze หรือ Laravel Fortify
* Session-based Authentication
* Role-Based Access Control (RBAC)
* Laravel Policies
* Laravel Gates
* แนะนำให้ใช้ Spatie Laravel Permission สำหรับจัดการ Role และ Permission

## Infrastructure

* XAMPP
* Apache
* MySQL
* PHP
* phpMyAdmin
* Composer
* Node.js และ NPM
* Git
* GitHub

## Development

* Laravel Pint
* PHPStan หรือ Larastan
* PHPUnit หรือ Pest
* Laravel Debugbar เฉพาะ Environment สำหรับพัฒนา
* ESLint และ Prettier หากมี JavaScript เพิ่มเติม

---

# 2. แนวคิดโครงสร้างระบบ

ระบบต้องรองรับโครงสร้างแบบ Hierarchical Project

```text
โครงการหลัก
│
├── โครงการย่อยที่ 1
│   ├── กิจกรรม
│   ├── งบประมาณ
│   ├── การเบิกจ่าย
│   └── ความคืบหน้า
│
├── โครงการย่อยที่ 2
│   ├── กิจกรรม
│   ├── งบประมาณ
│   ├── การเบิกจ่าย
│   └── ความคืบหน้า
│
└── โครงการย่อยที่ 3
    ├── กิจกรรม
    ├── งบประมาณ
    ├── การเบิกจ่าย
    └── ความคืบหน้า
```

ตัวอย่าง

```text
โครงการเพื่อสุขภาพ

1. โครงการส่งเสริมสุขภาพผู้สูงอายุ
2. โครงการออกกำลังกายเพื่อสุขภาพ
3. โครงการส่งเสริมสุขภาพประชาชน
```

ต้องสามารถสร้างโครงการหลักได้หลายโครงการ และแต่ละโครงการหลักสามารถมีโครงการย่อยได้ไม่จำกัดจำนวน

---

# 3. ข้อมูลโครงการหลัก

สร้างระบบจัดการโครงการหลักแบบ CRUD ด้วย Laravel

ข้อมูลประกอบด้วย

* รหัสโครงการ
* ชื่อโครงการ
* ปีงบประมาณ
* ประเภท/หมวดหมู่โครงการ
* หน่วยงาน
* ผู้รับผิดชอบหลัก
* คำอธิบาย
* งบประมาณรวม
* จำนวนโครงการย่อย
* สถานะ
* เปอร์เซ็นต์ความสำเร็จ
* วันที่เริ่มต้น
* วันที่สิ้นสุด
* วันที่สร้าง
* วันที่แก้ไข

การสร้าง แก้ไข และลบข้อมูลต้องผ่าน Controller, Form Request, Service และ Policy ตามความเหมาะสม

---

# 4. ข้อมูลโครงการย่อย

แต่ละโครงการหลักสามารถมีโครงการย่อยได้

ข้อมูลโครงการย่อยประกอบด้วย

* รหัสโครงการย่อย
* ชื่อโครงการ
* ผู้รับผิดชอบโครงการ
* หน่วยงาน/กอง/สำนัก
* ประเภทกิจกรรม
* วัตถุประสงค์
* กลุ่มเป้าหมาย
* จำนวนกลุ่มเป้าหมาย
* พื้นที่ดำเนินการ
* วิธีการดำเนินการ
* วันที่เริ่มโครงการ
* วันที่สิ้นสุดโครงการ
* วันที่ทำกิจกรรมเสร็จสิ้น
* งบประมาณ
* ยอดการเบิกจ่าย
* จำนวนครั้งที่วางแผน
* จำนวนครั้งที่ดำเนินการจริง
* สถานะ
* เปอร์เซ็นต์ความสำเร็จ
* หมายเหตุ
* เอกสารแนบ
* รูปภาพกิจกรรม

ข้อมูลต้องเชื่อมโยงกับโครงการหลักด้วย Eloquent Relationship และ Foreign Key

---

# 5. ระบบสถานะ

กำหนดสถานะโครงการเป็น

1. ยังไม่เริ่มดำเนินการ
2. กำลังดำเนินการ
3. เสร็จสิ้น
4. มีปัญหา
5. ยกเลิก

ควรกำหนดสถานะด้วย PHP Enum หรือ Constants กลาง เพื่อป้องกันการใช้ String กระจัดกระจายทั่วระบบ

ใช้ Badge สีที่แตกต่างกันเพื่อให้เจ้าหน้าที่สามารถดูสถานะได้อย่างรวดเร็ว

ตัวอย่าง

```text
กำลังดำเนินการ → Badge + Progress Bar
เสร็จสิ้น → Badge + 100%
มีปัญหา → Badge + แสดงข้อความปัญหา
```

---

# 6. ระบบเปอร์เซ็นต์ความสำเร็จ

ทุกโครงการย่อยต้องมีเปอร์เซ็นต์ความสำเร็จ

ตัวอย่าง

| โครงการ                         | สถานะ          | ความสำเร็จ |
| ------------------------------- | -------------- | ---------: |
| โครงการส่งเสริมสุขภาพผู้สูงอายุ | เสร็จสิ้น      |       100% |
| โครงการออกกำลังกายเพื่อสุขภาพ   | กำลังดำเนินการ |        65% |
| โครงการส่งเสริมสุขภาพประชาชน    | มีปัญหา        |        40% |

แสดงเป็น Progress Bar

```text
0% ───────────── 100%
```

ต้องสามารถกำหนดเปอร์เซ็นต์ได้จากระบบ

และควรมีระบบคำนวณอัตโนมัติจากกิจกรรมด้วย

ตัวอย่าง

```text
มีทั้งหมด 10 กิจกรรม
ดำเนินการเสร็จแล้ว 6 กิจกรรม

ความสำเร็จ = 60%
```

ต้องเปิดให้ผู้ดูแลสามารถ Override เปอร์เซ็นต์ได้ หากการประเมินของโครงการไม่ได้ขึ้นอยู่กับจำนวนกิจกรรมเพียงอย่างเดียว

ระบบต้องตรวจสอบว่าเปอร์เซ็นต์อยู่ระหว่าง 0 ถึง 100 และต้อง Validate ทั้ง Frontend และ Backend

---

# 7. ระบบกิจกรรม

แต่ละโครงการย่อยสามารถมีหลายกิจกรรม

ข้อมูลกิจกรรม

* ชื่อกิจกรรม
* รายละเอียด
* วันที่จัดกิจกรรม
* สถานที่
* ผู้รับผิดชอบ
* จำนวนผู้เข้าร่วม
* งบประมาณกิจกรรม
* สถานะ
* เปอร์เซ็นต์ความสำเร็จ
* รูปภาพ
* เอกสารหลักฐาน
* หมายเหตุ

สถานะกิจกรรม

* ยังไม่ดำเนินการ
* กำลังดำเนินการ
* เสร็จสิ้น
* มีปัญหา
* ยกเลิก

กิจกรรมต้องเชื่อมโยงกับโครงการย่อยด้วย Foreign Key และ Eloquent Relationship

---

# 8. ระบบงบประมาณ

แต่ละโครงการต้องสามารถบันทึก

* งบประมาณที่ได้รับ
* งบประมาณที่จัดสรร
* ยอดเบิกจ่าย
* ยอดคงเหลือ
* เปอร์เซ็นต์การเบิกจ่าย

สูตร

```text
ยอดคงเหลือ =
งบประมาณที่ได้รับ - ยอดเบิกจ่าย
```

```text
เปอร์เซ็นต์การเบิกจ่าย =
ยอดเบิกจ่าย / งบประมาณที่ได้รับ × 100
```

ห้ามให้ยอดเบิกจ่ายมากกว่างบประมาณโดยไม่มีสิทธิ์ Override จาก Administrator

ข้อมูลทางการเงินต้องใช้ Decimal และไม่ควรใช้ Floating Point ในการคำนวณ

แนะนำให้ใช้ชนิดข้อมูล

```text
DECIMAL(15,2)
```

---

# 9. Dashboard

สร้าง Dashboard สำหรับผู้บริหารและเจ้าหน้าที่ด้วย Laravel Blade และ Livewire

แสดงข้อมูลแบบ Card

* โครงการทั้งหมด
* โครงการที่กำลังดำเนินการ
* โครงการที่เสร็จสิ้น
* โครงการที่มีปัญหา
* โครงการที่ยังไม่เริ่ม
* งบประมาณทั้งหมด
* งบประมาณที่เบิกจ่าย
* งบประมาณคงเหลือ
* เปอร์เซ็นต์ความสำเร็จเฉลี่ย

ข้อมูลทั้งหมดต้องมาจาก MySQL ผ่าน Eloquent Query หรือ Query Builder ห้าม Hardcode ตัวเลขใน View

---

# 10. Dashboard Charts

สร้างกราฟด้วย Chart.js หรือ ApexCharts

กราฟที่ต้องมี

### 1. จำนวนโครงการตามสถานะ

* ยังไม่เริ่ม
* กำลังดำเนินการ
* เสร็จสิ้น
* มีปัญหา
* ยกเลิก

### 2. งบประมาณ

แสดง

```text
งบประมาณทั้งหมด
เทียบกับ
ยอดเบิกจ่าย
```

### 3. ความสำเร็จของโครงการ

แสดง Top / Bottom Projects

เช่น

```text
โครงการ A 100%
โครงการ B 85%
โครงการ C 60%
โครงการ D 35%
```

### 4. โครงการตามประเภท

เช่น

* สาธารณสุข
* สิ่งแวดล้อม
* การศึกษา
* สังคม
* โครงสร้างพื้นฐาน
* วัฒนธรรม
* อื่น ๆ

ข้อมูลกราฟควรโหลดผ่าน Controller หรือ Livewire Component และส่งข้อมูลที่ผ่านการ Query จาก Database แล้วไปยัง JavaScript

---

# 11. หน้า Project Detail

เมื่อคลิกโครงการหลัก ให้แสดงรายละเอียด

ตัวอย่าง

```text
โครงการเพื่อสุขภาพ

ผู้รับผิดชอบ
กองสาธารณสุข

งบประมาณ
1,000,000 บาท

เบิกจ่าย
650,000 บาท

ความสำเร็จ
65%
```

## โครงการย่อย

```text
1. โครงการส่งเสริมสุขภาพผู้สูงอายุ
   สถานะ: เสร็จสิ้น
   ความสำเร็จ: 100%

2. โครงการออกกำลังกายเพื่อสุขภาพ
   สถานะ: กำลังดำเนินการ
   ความสำเร็จ: 65%

3. โครงการส่งเสริมสุขภาพประชาชน
   สถานะ: มีปัญหา
   ความสำเร็จ: 40%
```

เมื่อคลิกโครงการย่อยให้แสดงรายละเอียดทั้งหมด รวมถึงกิจกรรม งบประมาณ การเบิกจ่าย เอกสาร รูปภาพ Timeline และ Audit Log ตามสิทธิ์ของผู้ใช้งาน

---

# 12. ระบบค้นหาและ Filter

ต้องสามารถค้นหาโครงการได้

* ค้นหาชื่อโครงการ
* รหัสโครงการ
* ปีงบประมาณ
* หน่วยงาน
* ผู้รับผิดชอบ
* ประเภทโครงการ
* สถานะ
* ช่วงวันที่
* ช่วงเปอร์เซ็นต์ความสำเร็จ

สามารถ Filter และ Sort ได้

ต้องใช้ Server-side Pagination และ Query จาก Database ไม่ควรโหลดข้อมูลทั้งหมดมา Filter บน Browser

---

# 13. ระบบรายงาน

สร้างหน้า Reports ด้วย Laravel

สามารถสร้างรายงาน

* รายงานโครงการทั้งหมด
* รายงานโครงการตามสถานะ
* รายงานโครงการที่มีปัญหา
* รายงานโครงการที่เสร็จสิ้น
* รายงานงบประมาณ
* รายงานการเบิกจ่าย
* รายงานความสำเร็จ
* รายงานตามหน่วยงาน
* รายงานตามปีงบประมาณ

รองรับ Export

* Excel ด้วย Laravel Excel
* CSV ด้วย Laravel Response หรือ Laravel Excel
* PDF ด้วย Dompdf หรือ Snappy

ข้อมูลใน Report ต้องตรงกับ Dashboard และ Database

---

# 14. ระบบแจ้งเตือน

ระบบต้องตรวจสอบโครงการที่

* ใกล้ถึงกำหนด
* เลยกำหนด
* ไม่มีการอัปเดตนาน
* มีปัญหา
* งบประมาณใกล้หมด

แสดง Notification บน Dashboard

ตัวอย่าง

```text
⚠️ มี 3 โครงการใกล้ครบกำหนด

🔴 มี 2 โครงการเกินกำหนด

⚠️ มี 1 โครงการใช้งบประมาณเกิน 80%
```

ใช้ Laravel Notifications และ Laravel Scheduler สำหรับตรวจสอบและสร้างการแจ้งเตือนอัตโนมัติ

---

# 15. ระบบผู้ใช้งาน

สร้าง User Management ด้วย Laravel

Role

### Administrator

* จัดการทุกระบบ
* จัดการผู้ใช้งาน
* จัดการสิทธิ์
* ดู Audit Log
* Override ข้อมูลสำคัญตาม Business Rule

### ผู้บริหาร

* ดู Dashboard
* ดูโครงการ
* ดูรายงาน
* ดูงบประมาณ
* ไม่สามารถแก้ไขข้อมูลหลัก

### เจ้าหน้าที่

* เพิ่มโครงการ
* แก้ไขโครงการ
* เพิ่มกิจกรรม
* อัปเดตความคืบหน้า
* อัปโหลดเอกสาร

### ผู้ดูแลโครงการ

* ดูเฉพาะโครงการที่ได้รับมอบหมาย
* อัปเดตสถานะ
* อัปเดตความคืบหน้า
* เพิ่มกิจกรรม
* เพิ่มหลักฐาน

ทุก Route, Controller, Livewire Action และ Form ต้องตรวจสอบ Authorization ด้วย Middleware, Policy หรือ Gate

---

# 16. Audit Log

ทุกการแก้ไขข้อมูลสำคัญต้องมี Audit Log

บันทึก

* ผู้ดำเนินการ
* วันเวลา
* IP Address
* User Agent
* Action
* Module
* Record ID
* ข้อมูลก่อนแก้ไข
* ข้อมูลหลังแก้ไข

ตัวอย่าง

```text
เจ้าหน้าที่ A
แก้ไขโครงการ "โครงการส่งเสริมสุขภาพ"

จาก
ความสำเร็จ 50%

เป็น
ความสำเร็จ 65%
```

สามารถใช้ Package สำหรับ Audit Log ที่รองรับ Laravel หรือสร้างระบบ Audit Log ภายในระบบเอง

ต้องไม่สามารถลบ Audit Log โดยผู้ใช้งานทั่วไปได้

---

# 17. Database Design

ออกแบบ MySQL Database ด้วย Laravel Migrations และ Eloquent ORM

อย่างน้อยต้องมีตาราง

```text
users
roles
permissions
departments
project_categories
projects
project_members
activities
budgets
budget_disbursements
project_progress
attachments
notifications
audit_logs
```

ความสัมพันธ์หลัก

```text
Project
1 Project
→ มีหลาย Sub Project
```

สามารถออกแบบด้วย Self-referencing Project

```text
projects
├── id
├── parent_id
├── project_code
├── name
├── description
├── status
├── progress
├── progress_mode
├── budget
├── start_date
├── end_date
└── responsible_user_id
```

โดย

```text
parent_id = NULL
```

หมายถึงโครงการหลัก

```text
parent_id มีค่า
```

หมายถึงโครงการย่อย

ต้องออกแบบ Foreign Key, Index, Unique Constraint และ Cascade Rule ให้เหมาะสม

ควรใช้ Eloquent Relationship เช่น

```php
Project::parent()
Project::children()
Project::activities()
Project::budget()
Project::members()
Project::attachments()
User::projects()
Department::projects()
```

---

# 18. Data Integrity

ให้ความสำคัญกับความถูกต้องของข้อมูล

ต้องป้องกัน

* โครงการย่อยไม่มีโครงการหลัก
* โครงการไม่มีผู้รับผิดชอบ
* วันที่สิ้นสุดก่อนวันที่เริ่ม
* เบิกจ่ายเกินงบประมาณ
* เปอร์เซ็นต์ต่ำกว่า 0
* เปอร์เซ็นต์มากกว่า 100
* กิจกรรมอยู่นอกช่วงเวลาโครงการโดยไม่มีการยืนยัน
* ข้อมูลสำคัญถูกลบโดยไม่ได้รับอนุญาต
* Foreign Key ไม่ถูกต้อง
* ข้อมูลซ้ำในรหัสโครงการ
* การเปลี่ยนสถานะที่ไม่ถูกต้อง

ใช้

* Database Constraints
* Foreign Keys
* Unique Indexes
* Laravel Form Request
* Eloquent Model Events
* Laravel Policies
* Database Transactions
* Server-side Validation
* PHP Enums

ข้อมูลที่เกี่ยวข้องหลายตารางต้องใช้ `DB::transaction()` เพื่อป้องกันข้อมูลไม่สอดคล้องกัน

---

# 19. UI/UX

ออกแบบให้เป็นระบบราชการสมัยใหม่

แนวทาง

* Modern Government Dashboard
* Clean
* Professional
* Responsive
* อ่านง่าย
* ใช้งานง่าย
* รองรับ Desktop / Tablet / Mobile

ใช้ Tailwind CSS

ใช้ Blade และ Livewire เป็นโครงสร้างหลักของหน้าเว็บ

ต้องมี

* Sidebar
* Top Navigation
* Breadcrumb
* Dashboard Cards
* Tables
* Tabs
* Modal
* Drawer
* Form
* Badge
* Progress Bar
* Date Picker
* Dropdown
* Pagination
* Search
* Filter
* Loading State
* Empty State
* Error State

---

# 20. สีสถานะ

ใช้สีเพื่อสื่อความหมายอย่างชัดเจน

```text
ยังไม่เริ่ม → Neutral
กำลังดำเนินการ → Informational
เสร็จสิ้น → Success
มีปัญหา → Warning / Error
ยกเลิก → Muted
```

อย่าใช้สีมากเกินไป และต้องรองรับ Accessibility

ต้องไม่ใช้สีเพียงอย่างเดียวในการสื่อความหมาย ควรใช้ข้อความหรือไอคอนร่วมด้วย

---

# 21. Responsive

ต้องใช้งานได้บน

```text
Desktop
Tablet
Mobile
```

สำหรับ Mobile

* Table ต้องสามารถเปลี่ยนเป็น Card View ได้
* ใช้ Horizontal Scroll เมื่อเหมาะสม
* ปุ่มและ Form ต้องกดใช้งานได้ง่าย
* Sidebar ต้องรองรับ Mobile Navigation
* Modal และ Drawer ต้องไม่ล้นหน้าจอ

---

# 22. XAMPP Environment

ระบบต้องสามารถพัฒนาและใช้งานบน XAMPP ได้

ส่วนประกอบหลัก

```text
Apache
MySQL
PHP
phpMyAdmin
```

โครงสร้างโปรเจกต์ Laravel ควรอยู่ใน

```text
C:\xampp\htdocs\municipal-projects
```

หรือโฟลเดอร์ที่กำหนดเองภายใน `htdocs`

ต้องตั้งค่า Apache ให้ Document Root ชี้ไปที่

```text
C:\xampp\htdocs\municipal-projects\public
```

ห้ามชี้ Document Root ไปยัง Root ของ Laravel โดยตรง เพราะอาจเปิดเผยไฟล์สำคัญ เช่น `.env`

หากไม่สามารถตั้ง Virtual Host ได้ ให้เข้าผ่าน

```text
http://localhost/municipal-projects/public
```

แต่แนะนำให้ใช้ Virtual Host เช่น

```text
http://municipal-projects.test
```

---

# 23. XAMPP Installation Commands

คำสั่งเริ่มต้นสำหรับสร้างโปรเจกต์

```bash
composer create-project laravel/laravel municipal-projects
cd municipal-projects
php artisan key:generate
npm install
npm run build
```

คำสั่งสำหรับพัฒนา

```bash
php artisan serve
npm run dev
```

หากใช้ Apache จาก XAMPP ให้เปิด Apache และ MySQL ผ่าน XAMPP Control Panel

คำสั่งที่ต้องรองรับ

```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed
php artisan storage:link
php artisan route:list
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan optimize:clear
```

---

# 24. Database Configuration

ใช้ MySQL จาก XAMPP และตั้งค่าในไฟล์ `.env`

ตัวอย่าง

```env
APP_NAME="Municipal Projects"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://municipal-projects.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=municipal_projects
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=database
```

สำหรับ Production ต้อง

* เปลี่ยน `APP_ENV` เป็น `production`
* ตั้ง `APP_DEBUG=false`
* ใช้รหัสผ่าน MySQL ที่ปลอดภัย
* ไม่ใช้ User `root` สำหรับ Application
* สร้าง Database User แยกสำหรับระบบ
* ไม่ Commit ไฟล์ `.env` เข้า Git

---

# 25. Laravel Migrations

ทุกการเปลี่ยนแปลง Database Schema ต้องใช้ Laravel Migration

ตัวอย่างคำสั่ง

```bash
php artisan make:migration create_projects_table
php artisan make:migration add_progress_mode_to_projects_table
php artisan migrate
php artisan migrate:rollback
```

ก่อน Migration ต้องตรวจสอบ

* Existing Data
* Foreign Key
* Nullable
* Default
* Index
* Unique Constraint
* Cascade Rule
* Migration Safety

ห้ามแก้ไข Production Database โดยตรงโดยไม่มี Migration

---

# 26. Laravel Models และ Relationships

สร้าง Model ให้สอดคล้องกับตารางและความสัมพันธ์

ตัวอย่าง Model ที่ควรมี

```text
User
Role
Permission
Department
ProjectCategory
Project
ProjectMember
Activity
Budget
BudgetDisbursement
ProjectProgress
Attachment
Notification
AuditLog
```

ใช้ Eloquent Relationship อย่างเหมาะสม เช่น

```php
Project::parent()
Project::children()
Project::activities()
Project::budget()
Project::members()
Project::attachments()
User::projects()
Department::projects()
```

กำหนด `$fillable` หรือ `$guarded` อย่างปลอดภัย

ห้ามใช้ Mass Assignment โดยไม่ตรวจสอบข้อมูล

---

# 27. Laravel Controllers และ Services

แยกความรับผิดชอบของระบบให้ชัดเจน

แนะนำโครงสร้าง

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   ├── Middleware/
│   └── Resources/
│
├── Models/
├── Services/
├── Actions/
├── Policies/
├── Enums/
├── Notifications/
├── Jobs/
├── Console/
└── Support/
```

Controller ควรทำหน้าที่ประสานงาน ไม่ควรมี Business Logic ขนาดใหญ่ทั้งหมดอยู่ใน Controller

Business Logic สำคัญควรอยู่ใน Service หรือ Action เช่น

```text
ProjectService
BudgetService
ProgressService
DisbursementService
NotificationService
AuditLogService
```

---

# 28. Laravel Validation

ใช้ Form Request สำหรับ Validation

ตัวอย่าง

```bash
php artisan make:request StoreProjectRequest
php artisan make:request UpdateProjectRequest
```

ต้อง Validate ทั้ง

* Required fields
* Data type
* Date
* Number
* Percentage
* Budget
* File
* Permission
* Unique Code
* Date Range

Backend เป็นตัวตัดสินสุดท้าย ห้ามเชื่อค่าที่ส่งมาจาก Client

---

# 29. Authentication และ Authorization

Authentication ไม่เท่ากับ Authorization

การ Login สำเร็จไม่ได้หมายความว่าสามารถทำทุกอย่างได้

ทุก Route, Controller, Livewire Action และ API ต้องตรวจสอบ Permission

ใช้

* Middleware
* Policies
* Gates
* Spatie Laravel Permission ตามความเหมาะสม

ตัวอย่าง

```php
$this->authorize('update', $project);
```

หรือ

```php
Gate::authorize('project.update');
```

ห้ามพึ่งพาการซ่อนปุ่มใน Frontend เพื่อรักษาความปลอดภัย

---

# 30. ระบบงบประมาณและ Transaction

การเพิ่มหรือแก้ไขข้อมูลการเงินที่เกี่ยวข้องหลายตารางต้องใช้ Database Transaction

ตัวอย่าง

```text
สร้าง Budget
+
สร้าง Budget Disbursement
+
อัปเดตยอดรวมโครงการ
+
สร้าง Audit Log
```

ใช้

```php
DB::transaction(function () {
    // Business Logic
});
```

หากขั้นตอนใดล้มเหลว ต้อง Rollback ตาม Business Requirement

---

# 31. File Upload

รองรับเอกสาร

* PDF
* DOC
* DOCX
* XLS
* XLSX
* JPG
* JPEG
* PNG

ต้องตรวจสอบ

* MIME Type
* File Extension
* File Size
* Filename
* Storage Path
* User Permission

ใช้ Laravel Filesystem และจัดเก็บไฟล์ใน Storage ที่เหมาะสม

คำสั่งสำหรับสร้าง Symbolic Link

```bash
php artisan storage:link
```

ป้องกันการ Upload

* PHP Script
* JavaScript
* Shell Script
* Executable File
* ไฟล์ที่มี MIME Type ไม่ตรงกับ Extension

ห้ามนำ Filename จาก User ไปสร้าง Path โดยตรง

---

# 32. Seed Data

สร้าง Seed Data สำหรับทดสอบระบบด้วย Laravel Seeder และ Factory

คำสั่ง

```bash
php artisan make:seeder DatabaseSeeder
php artisan db:seed
php artisan migrate:fresh --seed
```

ตัวอย่าง

โครงการหลัก

```text
โครงการเพื่อสุขภาพ
```

โครงการย่อย

```text
1. โครงการส่งเสริมสุขภาพผู้สูงอายุ
   ความสำเร็จ 100%
   สถานะ เสร็จสิ้น

2. โครงการออกกำลังกายเพื่อสุขภาพ
   ความสำเร็จ 65%
   สถานะ กำลังดำเนินการ

3. โครงการส่งเสริมสุขภาพประชาชน
   ความสำเร็จ 40%
   สถานะ มีปัญหา
```

สร้างข้อมูลผู้ใช้งานสำหรับทุก Role เพื่อทดสอบระบบ

ห้ามใช้รหัสผ่านจริงใน Seed Data และควรระบุให้ชัดเจนว่าเป็นข้อมูลสำหรับ Development เท่านั้น

---

# 33. Project Progress Logic

ออกแบบระบบ Progress ให้รองรับ 2 รูปแบบ

## Manual

เจ้าหน้าที่กำหนด

```text
progress = 75%
```

## Automatic

คำนวณจากกิจกรรม

```text
จำนวนกิจกรรมที่เสร็จ
÷
จำนวนกิจกรรมทั้งหมด
× 100
```

ตัวอย่าง

```text
10 กิจกรรม
เสร็จ 7

Progress = 70%
```

ให้ผู้ดูแลเลือกว่าโครงการนั้นใช้

```text
AUTO
หรือ
MANUAL
```

Logic การคำนวณควรอยู่ใน Service หรือ Domain Logic ฝั่ง Server ไม่ควรอยู่ใน Blade หรือ JavaScript เพียงอย่างเดียว

---

# 34. Dashboard ของแต่ละโครงการ

แสดง

```text
Project Progress

████████████░░░░ 75%

Budget

งบประมาณ 1,000,000 บาท

เบิกจ่าย 650,000 บาท

คงเหลือ 350,000 บาท

Activities

7 / 10 กิจกรรม
```

Timeline

```text
เริ่มโครงการ
↓
ดำเนินการ
↓
กิจกรรม
↓
เสร็จสิ้น
```

ข้อมูลต้องคำนวณจาก Database และต้องสอดคล้องกับข้อมูลใน Project Detail และ Report

---

# 35. Project Timeline

สร้าง Timeline

```text
วันที่เริ่มโครงการ
↓
กิจกรรมที่ 1
↓
กิจกรรมที่ 2
↓
กิจกรรมที่ 3
↓
วันที่สิ้นสุด
```

แสดงสถานะของแต่ละกิจกรรม

หากกิจกรรมอยู่นอกช่วงเวลาโครงการ ต้องแจ้งเตือนหรือขอการยืนยันตาม Business Rule

---

# 36. สิ่งสำคัญในการพัฒนา

ห้ามสร้างระบบแบบ Demo ที่มีข้อมูลปลอมฝังอยู่ใน Frontend

ต้องแยก

```text
UI
Business Logic
Controller
Form Request
Service
Model
Database
```

อย่างชัดเจน

ห้ามใส่ Business Logic สำคัญไว้ใน Client อย่างเดียว

ทุกข้อมูลต้องมาจาก MySQL ผ่าน Laravel

---

# 37. การเขียน Code

เขียน Code ให้

* Clean Code
* Modular
* Reusable
* Maintainable
* Type Safe เท่าที่ PHP รองรับ
* SOLID
* DRY
* ใช้ PHP Strict Types เมื่อเหมาะสม
* ใช้ PHP Enum สำหรับค่าคงที่สำคัญ
* ใช้ Laravel Convention

สร้าง Components ที่นำกลับมาใช้ซ้ำได้ เช่น

```text
ProjectCard
ProjectTable
ProjectStatusBadge
ProgressBar
BudgetCard
ActivityTable
ProjectFilter
ProjectForm
ActivityForm
BudgetForm
DashboardCard
```

สามารถสร้าง Blade Components และ Livewire Components เพื่อ Reuse

---

# 38. Error Handling

ต้องมีระบบจัดการ Error

## Frontend

แสดงข้อความภาษาไทยที่เข้าใจง่าย

## Backend

Log Error ด้วย Laravel Logging

## Database

ใช้ Transaction กับข้อมูลสำคัญ

ห้ามแสดง

* Stack Trace
* SQL Query
* Database Error
* Secret
* Password
* Token

ให้ User

ใน Production ต้องตั้งค่า

```env
APP_DEBUG=false
```

---

# 39. Pagination

ข้อมูลโครงการและกิจกรรมต้องใช้ Pagination

เช่น

```text
10
25
50
100 รายการต่อหน้า
```

ใช้ Laravel Pagination และทำ Server-side Pagination

ตัวอย่าง

```php
Project::query()
    ->with(['department', 'responsibleUser'])
    ->paginate(25);
```

ต้องรองรับ Query String สำหรับ Search, Filter และ Sort เพื่อให้สามารถแชร์ URL ได้

---

# 40. Search Performance

สร้าง Database Index สำหรับ

* project_code
* name
* status
* fiscal_year
* responsible_user_id
* department_id
* category_id
* parent_id
* start_date
* end_date
* created_at

ใช้ Eloquent Query ที่มีประสิทธิภาพ

หลีกเลี่ยง N+1 Query ด้วย `with()`, `withCount()` และการเลือกเฉพาะ Column ที่จำเป็น

ไม่ควรโหลดข้อมูลทั้งหมดมา Filter บน Browser

---

# 41. Laravel Scheduler และ Queue

ใช้ Laravel Scheduler สำหรับ

* ตรวจสอบโครงการใกล้ครบกำหนด
* ตรวจสอบโครงการเกินกำหนด
* ตรวจสอบโครงการไม่มีการอัปเดต
* ตรวจสอบงบประมาณใกล้หมด
* สร้าง Notification

ใช้ Laravel Queue สำหรับ

* ส่ง Notification
* สร้าง Report ขนาดใหญ่
* Export Excel/PDF
* ประมวลผลไฟล์
* งานที่ใช้เวลานาน

ต้องกำหนด Scheduler ใน `routes/console.php` หรือ `app/Console/Kernel.php` ตามโครงสร้าง Laravel ที่ใช้งาน

---

# 42. Security

ระบบต้องมี

* Authentication
* Authorization
* RBAC
* CSRF Protection
* XSS Protection
* SQL Injection Protection
* Input Validation
* File Upload Validation
* Rate Limiting
* Secure Headers
* Secure Cookie
* Password Hashing
* Session Management
* Mass Assignment Protection
* Permission Checking
* Error Disclosure Protection

Laravel ช่วยป้องกัน SQL Injection ผ่าน Eloquent และ Query Builder แต่ห้ามใช้ Raw Query โดยไม่ Bind Parameter

ห้ามเชื่อข้อมูลจาก Client โดยตรง

ตรวจสอบ Permission ทุก Route, Controller, Livewire Action และ API

---

# 43. File Security

ไฟล์ที่ Upload ต้อง

* ตรวจสอบ MIME Type
* ตรวจสอบ Extension
* จำกัด File Size
* เปลี่ยนชื่อไฟล์
* ป้องกัน Path Traversal
* ไม่ Execute File Upload
* ไม่เก็บไฟล์ในตำแหน่งที่สามารถ Execute Script ได้
* ตรวจสอบสิทธิ์ก่อน Download
* ไม่เปิดเผย Path จริงของไฟล์โดยไม่จำเป็น

---

# 44. Testing

ก่อนถือว่า Feature เสร็จ ต้องทดสอบ

## Functional

* Create
* Read
* Update
* Delete
* Search
* Filter
* Sort
* Pagination
* Export
* File Upload

## Permission

ทดสอบทุก Role

## Validation

ทดสอบข้อมูลผิด

## Security

ทดสอบ Unauthorized Access
ทดสอบการเข้าถึงข้อมูลของโครงการที่ไม่ได้รับมอบหมาย
ทดสอบ Mass Assignment
ทดสอบ File Upload ที่ไม่ปลอดภัย

## Responsive

ทดสอบ

* Desktop
* Tablet
* Mobile

ใช้ PHPUnit หรือ Pest สำหรับ Feature Test และ Unit Test

---

# 45. Security Checklist

ก่อนเสร็จ Feature ต้องตรวจสอบ

* Authentication
* Authorization
* SQL Injection
* XSS
* CSRF
* File Upload
* Rate Limiting
* Session
* Cookie
* Sensitive Data
* Error Disclosure
* Permission Bypass
* Mass Assignment
* Direct Object Reference
* Unauthorized File Download

---

# 46. Code Quality

ต้องใช้

* Laravel Pint
* PHPStan หรือ Larastan
* PHPUnit หรือ Pest
* Git
* Code Review

หลีกเลี่ยง

```php
mixed
```

หรือการปิด Static Analysis โดยไม่จำเป็น

ห้ามใช้การปิด Error เพื่อซ่อนปัญหา

ห้ามปิด Validation หรือ Authorization เพียงเพื่อให้ระบบทำงานได้เร็วขึ้น

---

# 47. Git Rules

Commit Message แนะนำ

```text
feat: add project progress tracking
fix: fix project budget calculation
refactor: improve project service
security: validate project permissions
docs: update project documentation
test: add project authorization tests
```

ห้าม Commit

```text
.env
database passwords
API keys
APP_KEY
private keys
storage/logs/*
```

ควรมีไฟล์

```text
.env.example
.gitignore
```

---

# 48. ก่อนสร้าง Feature ใหม่

ก่อนสร้าง Feature ใหม่

1. ตรวจสอบว่ามี Feature นี้แล้วหรือไม่
2. ตรวจสอบ Blade Component ที่สามารถ Reuse
3. ตรวจสอบ Livewire Component
4. ตรวจสอบ Controller
5. ตรวจสอบ Service
6. ตรวจสอบ Model และ Relationship
7. ตรวจสอบ Migration
8. ตรวจสอบ Permission
9. ตรวจสอบผลกระทบต่อ Report และ Dashboard

ถ้ามีระบบเดิมอยู่แล้ว

```text
ปรับปรุงของเดิม
```

ไม่ใช่

```text
สร้างระบบใหม่ซ้ำ
```

---

# 49. ห้ามทำ Blind Rewrite

ห้าม Rewrite ระบบทั้งหมดเพราะพบ Bug เล็ก ๆ

ตัวอย่าง

ถ้า Button มี Bug

ห้าม Rewrite ทั้งหน้า

ถ้า Query มีปัญหา

ห้ามเปลี่ยน Architecture ทั้งระบบโดยไม่จำเป็น

แก้เฉพาะส่วนที่จำเป็นและตรวจสอบผลกระทบ

---

# 50. Laravel Development Workflow

เมื่อได้รับคำสั่งจาก User

ต้องทำตาม

```text
UNDERSTAND
↓
INSPECT
↓
ANALYZE
↓
PLAN
↓
DESIGN DATABASE
↓
IMPLEMENT MIGRATION
↓
IMPLEMENT MODEL
↓
IMPLEMENT VALIDATION
↓
IMPLEMENT AUTHORIZATION
↓
IMPLEMENT SERVICE
↓
IMPLEMENT CONTROLLER / LIVEWIRE
↓
IMPLEMENT BLADE UI
↓
VALIDATE
↓
TEST
↓
REVIEW
```

ห้ามกระโดดจาก

```text
REQUEST
↓
CODE
```

โดยไม่ตรวจสอบระบบก่อน

---

# 51. Requirement Ambiguity

หาก Requirement ไม่ชัดเจน

อย่าเดา Business Logic สำคัญเอง

ตัวอย่าง

```text
เปอร์เซ็นต์ความสำเร็จ
```

ต้องตรวจสอบว่า

* เจ้าหน้าที่กรอกเอง
  หรือ
* ระบบคำนวณจากกิจกรรม

หากสามารถเลือก Default ที่ปลอดภัยได้ ให้เลือกแนวทางที่มีความเสี่ยงต่ำและระบุไว้ใน Implementation

---

# 52. Change Impact Checklist

ก่อน Merge การแก้ไข ให้ตรวจสอบ

```text
[ ] Frontend / Blade
[ ] Livewire
[ ] Backend / Controller
[ ] Service
[ ] Model
[ ] Migration
[ ] Database
[ ] Validation
[ ] Authentication
[ ] Authorization
[ ] Dashboard
[ ] Report
[ ] Export
[ ] Notification
[ ] Audit Log
[ ] File Upload
[ ] Mobile
[ ] Performance
[ ] Security
[ ] Tests
```

---

# 53. Production Safety

ก่อน Production Deployment ต้องตรวจสอบ

```text
[ ] Build ผ่าน
[ ] PHP Syntax ผ่าน
[ ] Laravel Pint ผ่าน
[ ] PHPStan หรือ Larastan ผ่าน
[ ] Tests ผ่าน
[ ] Database Migration ถูกต้อง
[ ] Environment Variables
[ ] Database Backup
[ ] Authentication
[ ] Authorization
[ ] File Upload
[ ] Error Handling
[ ] Logging
[ ] HTTPS
[ ] Security Headers
[ ] Apache Configuration
[ ] MySQL Connection
[ ] Storage Link
[ ] Queue Worker
[ ] Scheduler
[ ] APP_DEBUG=false
[ ] Document Root ชี้ไปที่ public
```

---

# 54. Backup

Database เป็นข้อมูลสำคัญ

ต้องออกแบบให้รองรับ Database Backup

ควรมีแผน

```text
MySQL
↓
Backup
↓
External Storage
```

ห้ามถือว่าไฟล์ใน XAMPP หรือการ Copy โฟลเดอร์ Database เพียงอย่างเดียวคือ Backup ที่เพียงพอ

ควรใช้ `mysqldump` หรือระบบ Backup ที่เหมาะสม และทดสอบการกู้คืนข้อมูลเป็นระยะ

---

# 55. Logging

ระบบต้องมี Logging สำหรับ

* Authentication
* Error
* Database Error
* Important Actions
* Permission Denied
* Security Events
* Queue Failure
* Scheduler Failure

ห้าม Log

* Password
* Token
* Secret
* Sensitive Information

ต้องกำหนด Retention Policy สำหรับ Log และไม่ปล่อยให้ Log เต็มพื้นที่จัดเก็บ

---

# 56. Final Response After Coding

หลังแก้ไข Code ให้รายงาน

## Changed

ระบุสิ่งที่แก้

## Files

ระบุไฟล์ที่แก้ไข

## Database

ระบุว่า

* มี Migration หรือไม่
* มีการเปลี่ยน Schema หรือไม่
* ต้องรันคำสั่งใด

## Laravel

ระบุว่า

* มี Model หรือไม่
* มี Controller หรือไม่
* มี Form Request หรือไม่
* มี Policy หรือไม่
* มี Service หรือไม่
* มี Livewire Component หรือไม่

## Security

ระบุ Security ที่ตรวจสอบ

## Testing

ระบุสิ่งที่ทดสอบ

## Potential Impact

ระบุระบบที่อาจได้รับผลกระทบ

## Next Steps

แนะนำขั้นตอนถัดไปถ้ามี

---

# 57. Definition of Done

Feature จะถือว่าเสร็จเมื่อ

* Code ทำงานได้
* PHP Syntax ผ่าน
* Laravel Pint ผ่าน
* PHPStan หรือ Larastan ผ่าน
* Tests ผ่าน
* Database ถูกต้อง
* Migration ถูกต้อง
* Validation ถูกต้อง
* Permission ถูกต้อง
* UI Responsive
* Error Handling ครบ
* Audit Log ครบถ้าจำเป็น
* Dashboard สอดคล้อง
* Report สอดคล้อง
* ไม่มีข้อมูล Hardcode ที่ไม่ควรมี
* ไม่ทำให้ระบบเดิมเสีย
* ทดสอบ Edge Cases แล้ว
* Security Review ผ่าน
* สามารถใช้งานบน XAMPP ได้
* Document Root ชี้ไปที่ `public`
* Storage และ File Permission ถูกต้อง
* Queue และ Scheduler ทำงานตามที่กำหนด

---

# 58. สิ่งที่ต้องสร้าง

สร้างระบบครบทั้ง

1. Login
2. Dashboard
3. Project Management
4. Sub Project Management
5. Activity Management
6. Budget Management
7. Progress Tracking
8. User Management
9. Department Management
10. Category Management
11. Notification
12. Reports
13. Export Excel/CSV/PDF
14. File Attachment
15. Audit Log
16. Settings

---

# 59. ขั้นตอนการทำงาน

ก่อนเขียน Code ให้ทำตามลำดับ

```text
STEP 1
วิเคราะห์ Requirement

STEP 2
ออกแบบ Architecture ของ Laravel

STEP 3
ออกแบบ Database ERD

STEP 4
สร้าง Laravel Migrations

STEP 5
สร้าง Models และ Relationships

STEP 6
สร้าง Authentication + RBAC

STEP 7
สร้าง Form Requests และ Validation

STEP 8
สร้าง Policies และ Authorization

STEP 9
สร้าง Services และ Business Logic

STEP 10
สร้าง Controllers / Livewire Components

STEP 11
สร้าง Dashboard

STEP 12
สร้าง Project Management

STEP 13
สร้าง Activity Management

STEP 14
สร้าง Budget Management

STEP 15
สร้าง Progress Tracking

STEP 16
สร้าง Report และ Export

STEP 17
สร้าง Notification และ Scheduler

STEP 18
สร้าง Audit Log

STEP 19
ทำ Responsive UI

STEP 20
สร้าง Seed Data

STEP 21
ทำ Security Review

STEP 22
ทำ Data Integrity Review

STEP 23
ทำ Performance Review

STEP 24
ทดสอบระบบทั้งหมด
```

---

# 60. ห้ามทำ

ห้าม

* เขียนทุกอย่างไว้ในไฟล์เดียว
* Hardcode ข้อมูล
* Hardcode Permission ใน View เพียงอย่างเดียว
* ใช้ Mock Data ใน Production
* เชื่อข้อมูลจาก Client
* ให้ Client แก้ Database โดยตรง
* ลบข้อมูลสำคัญแบบถาวรโดยไม่มี Audit
* สร้าง Component ซ้ำโดยไม่จำเป็น
* ทำ UI ที่ใช้งานยาก
* ทำ Table ที่ล้นจอบน Mobile
* ทำระบบโดยไม่ตรวจสอบความสัมพันธ์ของข้อมูล
* ชี้ Apache Document Root ไปยัง Root ของ Laravel
* Commit `.env`
* เปิด `APP_DEBUG=true` ใน Production
* ใช้ User `root` สำหรับ Production Application
* ใช้ Raw SQL โดยไม่ Bind Parameter
* ข้าม Authorization ใน Controller หรือ Livewire
* เก็บไฟล์ Upload โดยไม่ตรวจสอบ MIME Type และ Permission

---

# 61. เป้าหมายสุดท้าย

ต้องได้ระบบ

```text
ระบบติดตามและบริหารโครงการเทศบาล
```

ที่สามารถ

```text
สร้างโครงการหลัก
↓
สร้างโครงการย่อย
↓
เพิ่มกิจกรรม
↓
กำหนดผู้รับผิดชอบ
↓
กำหนดงบประมาณ
↓
บันทึกการเบิกจ่าย
↓
อัปเดตสถานะ
↓
อัปเดตเปอร์เซ็นต์ความสำเร็จ
↓
แนบหลักฐาน
↓
ดู Dashboard
↓
สร้างรายงาน
↓
Export ข้อมูล
```

โดยข้อมูลทั้งหมดต้องเชื่อมโยงกันตั้งแต่

```text
MySQL
↓
Laravel Migration
↓
Eloquent Model
↓
Service
↓
Controller / Livewire
↓
Blade
```

และเมื่อแก้ไขข้อมูลในระบบ ข้อมูลใน Dashboard, Progress, Budget และ Report ต้องอัปเดตสอดคล้องกันโดยอัตโนมัติ

เริ่มต้นด้วยการออกแบบ Architecture และ Database Schema ก่อน จากนั้นจึงพัฒนาระบบเป็น Module โดยห้ามข้ามขั้นตอน และหลังจากแต่ละ Module เสร็จให้ตรวจสอบ Data Integrity, Security, Performance และความถูกต้องของระบบก่อนดำเนินการ Module ถัดไป

ระบบต้องสามารถพัฒนาและใช้งานบน XAMPP ได้อย่างถูกต้อง โดย Apache ต้องชี้ไปยังโฟลเดอร์ `public` ของ Laravel และ MySQL ต้องเป็นแหล่งข้อมูลหลักของระบบทั้งหมด
