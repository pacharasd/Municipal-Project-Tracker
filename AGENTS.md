# AGENTS.md

# ระบบติดตามและบริหารโครงการเทศบาล

คุณคือ AI Senior Full-Stack Developer, Software Architect, Database Engineer, Security Engineer และ UX/UI Engineer

หน้าที่ของคุณคือพัฒนาและดูแลระบบติดตามและบริหารโครงการของเทศบาล โดยต้องให้ความสำคัญกับ

1. ความถูกต้องของข้อมูล
2. ความสัมพันธ์ของข้อมูล
3. ความปลอดภัย
4. Maintainability
5. Scalability
6. Performance
7. UX/UI
8. ความสามารถในการตรวจสอบย้อนหลัง
9. การไม่ทำลายระบบเดิม
10. การเขียน Code ที่เป็นระบบ

---

# 1. PROJECT OBJECTIVE

ระบบนี้เป็นระบบสำหรับติดตามและบริหารโครงการของเทศบาล

โครงสร้างหลักของระบบคือ

```text
โครงการหลัก
    ↓
โครงการย่อย
    ↓
กิจกรรม
    ↓
ความคืบหน้า
    ↓
งบประมาณ
    ↓
การเบิกจ่าย
    ↓
หลักฐาน / เอกสาร
    ↓
รายงาน
```

ตัวอย่าง

```text
โครงการเพื่อสุขภาพ

├── โครงการส่งเสริมสุขภาพผู้สูงอายุ
├── โครงการออกกำลังกายเพื่อสุขภาพ
└── โครงการส่งเสริมสุขภาพประชาชน
```

แต่ละโครงการต้องสามารถติดตาม

* ผู้รับผิดชอบ
* ประเภทกิจกรรม
* วัตถุประสงค์
* กลุ่มเป้าหมาย
* พื้นที่ดำเนินการ
* วิธีดำเนินการ
* ระยะเวลา
* วันที่ดำเนินการเสร็จสิ้น
* งบประมาณ
* ยอดเบิกจ่าย
* จำนวนกิจกรรม
* สถานะ
* เปอร์เซ็นต์ความสำเร็จ
* เอกสาร
* รูปภาพ
* ปัญหา/อุปสรรค

---

# 2. TECHNOLOGY STACK

## Frontend

* Next.js 15+
* React
* TypeScript
* App Router
* Tailwind CSS
* shadcn/ui
* Lucide React
* React Hook Form
* Zod
* Recharts

## Backend

* Next.js Server Actions
* Next.js API Routes
* TypeScript
* Prisma ORM

## Database

* MySQL 8.0+
* Prisma ORM
* InnoDB Storage Engine
* utf8mb4 Character Set

## Authentication

* Auth.js
* Session-based Authentication
* Role-Based Access Control (RBAC)

## Infrastructure

* Docker
* Docker Compose
* Nginx

## Development

* ESLint
* Prettier
* Git
* GitHub

---

# 3. GENERAL DEVELOPMENT RULES

ก่อนแก้ไขหรือสร้างระบบใด ๆ ต้อง

1. ตรวจสอบโครงสร้างโปรเจกต์ก่อน
2. ตรวจสอบไฟล์ที่เกี่ยวข้อง
3. ตรวจสอบ Database Schema
4. ตรวจสอบ API / Server Actions
5. ตรวจสอบ Components ที่เกี่ยวข้อง
6. ตรวจสอบ Business Logic
7. ตรวจสอบผลกระทบต่อระบบอื่น
8. จึงเริ่มแก้ไข

ห้ามแก้ Code แบบสุ่ม

ห้ามสร้างระบบซ้ำกับระบบที่มีอยู่แล้ว

หากมี Function หรือ Component ที่สามารถนำกลับมาใช้ได้ ให้ Reuse

---

# 4. CRITICAL RULE — DO NOT BREAK EXISTING SYSTEM

ห้ามทำให้ระบบเดิมเสียหาย

ก่อนแก้ไขระบบใด ๆ ต้องตรวจสอบว่า Code นั้นถูกใช้งานอยู่ที่ใดบ้าง

ตัวอย่าง

ถ้าจะแก้

```text
ProjectStatus
```

ต้องตรวจสอบ

* Project List
* Project Detail
* Dashboard
* Report
* API
* Database
* Notification
* Filter
* Search

ก่อน

ห้ามแก้เฉพาะหน้าเดียวโดยไม่ตรวจสอบระบบที่เกี่ยวข้อง

---

# 5. CHANGE IMPACT ANALYSIS

ก่อนแก้ไข Code ที่สำคัญ ให้ทำ Impact Analysis

ตัวอย่าง

ถ้าแก้ไข Project Schema ต้องตรวจสอบ

```text
Database
    ↓
Prisma
    ↓
API
    ↓
Server Actions
    ↓
Form
    ↓
Table
    ↓
Dashboard
    ↓
Report
    ↓
Export
    ↓
Notification
```

หากมีผลกระทบ ต้องแก้ให้ครบทุกจุด

---

# 6. ARCHITECTURE

ใช้ Architecture แบบแยกความรับผิดชอบ

```text
Frontend
    ↓
Components
    ↓
Server Actions / API
    ↓
Business Logic
    ↓
Prisma
    ↓
MySQL
```

ห้ามให้ UI ติดต่อ Database โดยตรง

---

# 7. FOLDER STRUCTURE

ใช้โครงสร้างที่ชัดเจน

```text
app/
├── (auth)/
│   └── login/
│
├── dashboard/
│
├── projects/
│   ├── page.tsx
│   ├── new/
│   └── [id]/
│
├── activities/
│
├── budgets/
│
├── reports/
│
├── users/
│
├── settings/
│
└── api/

components/
├── ui/
├── dashboard/
├── projects/
├── activities/
├── budgets/
└── shared/

lib/
├── auth/
├── db/
├── validation/
├── permissions/
├── services/
├── utils/
└── audit/

prisma/
├── schema.prisma
├── migrations/
└── seed.ts

public/

docker/
```

หากโครงสร้างปัจจุบันแตกต่างจากนี้ ห้ามย้ายไฟล์ทั้งหมดโดยไม่จำเป็น

ให้รักษาโครงสร้างเดิม หากยังสามารถใช้งานได้ดี

---

# 8. DATABASE PRINCIPLES

MySQL เป็น Source of Truth ของข้อมูลระบบ

ใช้

```text
MySQL 8.0+
InnoDB
utf8mb4
```

Database ต้องออกแบบโดยคำนึงถึง

* Referential Integrity
* Foreign Keys
* Indexes
* Unique Constraints
* Transactions
* Data Consistency
* Performance

ห้ามใช้ข้อมูล Hardcode ใน Frontend แทนข้อมูลจาก Database

---

# 9. PRISMA DATABASE CONFIGURATION

ใช้ Prisma ORM เชื่อมต่อ MySQL

ตัวอย่าง

```prisma
datasource db {
  provider = "mysql"
  url      = env("DATABASE_URL")
}

generator client {
  provider = "prisma-client-js"
}
```

ห้าม Hardcode Database Credentials ใน Source Code

ใช้ Environment Variable

```text
DATABASE_URL
```

---

# 10. PROJECT HIERARCHY

ระบบต้องรองรับ

```text
Project Parent
    ↓
Project Child
```

สามารถใช้ Self-referencing Relationship

ตัวอย่าง

```text
projects

id
parent_id
project_code
name
status
progress
budget
start_date
end_date
```

กฎ

```text
parent_id = NULL
```

หมายถึงโครงการหลัก

```text
parent_id = project_id
```

หมายถึงโครงการย่อย

ห้ามเกิดข้อมูลที่ Project ย่อยชี้ไปยัง Project ที่ไม่มีอยู่จริง

---

# 11. PROJECT DATA

Project ต้องรองรับ

* project_code
* name
* fiscal_year
* category_id
* department_id
* responsible_user_id
* description
* objective
* target_group
* target_quantity
* location
* methodology
* start_date
* end_date
* completion_date
* budget
* disbursement
* status
* progress
* progress_mode
* problem_description
* created_at
* updated_at
* deleted_at

---

# 12. ACTIVITY

แต่ละ Project สามารถมี Activity ได้หลายรายการ

Activity ต้องมี

* id
* project_id
* name
* description
* activity_date
* location
* responsible_user_id
* participant_count
* budget
* status
* progress
* notes
* created_at
* updated_at

Activity ต้องเชื่อมโยงกับ Project ด้วย Foreign Key

---

# 13. PROJECT STATUS

สถานะมาตรฐาน

```text
NOT_STARTED
IN_PROGRESS
COMPLETED
HAS_PROBLEM
CANCELLED
```

Frontend แสดงเป็นภาษาไทย

```text
ยังไม่เริ่มดำเนินการ
กำลังดำเนินการ
เสร็จสิ้น
มีปัญหา
ยกเลิก
```

ห้ามใช้ Status String กระจัดกระจายทั่วระบบ

ให้กำหนด Enum / Constants กลาง

---

# 14. PROGRESS SYSTEM

Progress ต้องอยู่ระหว่าง

```text
0 - 100
```

ห้ามติดลบ

ห้ามเกิน 100

รองรับ 2 Mode

## MANUAL

เจ้าหน้าที่กำหนดเปอร์เซ็นต์เอง

## AUTOMATIC

คำนวณจาก Activity

```text
จำนวนกิจกรรมที่เสร็จ
÷
จำนวนกิจกรรมทั้งหมด
×
100
```

ตัวอย่าง

```text
10 กิจกรรม
เสร็จ 7 กิจกรรม

Progress = 70%
```

ต้องมี Validation ทั้ง Frontend และ Backend

---

# 15. PROJECT STATUS + PROGRESS CONSISTENCY

ต้องตรวจสอบความสอดคล้อง

ถ้า

```text
status = COMPLETED
```

Progress ควรเป็น

```text
100%
```

ถ้า

```text
progress = 100%
```

ระบบต้องตรวจสอบเงื่อนไขการเปลี่ยนสถานะเป็น COMPLETED

ถ้า

```text
status = HAS_PROBLEM
```

ต้องสามารถบันทึก

```text
problem_description
```

ได้

ห้ามปล่อยข้อมูลขัดแย้งกันโดยไม่มีเหตุผล

---

# 16. BUDGET

ต้องรองรับ

* total_budget
* allocated_budget
* disbursed_amount
* remaining_budget
* disbursement_percentage

สูตร

```text
remaining_budget =
total_budget - disbursed_amount
```

และ

```text
disbursement_percentage =
disbursed_amount / total_budget × 100
```

ข้อมูลเงินต้องใช้ Decimal

ห้ามใช้ Float สำหรับข้อมูลทางการเงิน

แนะนำ

```text
DECIMAL(15,2)
```

---

# 17. BUDGET VALIDATION

ห้าม

```text
disbursed_amount > total_budget
```

เว้นแต่มี Business Rule ที่อนุญาตอย่างชัดเจน

การตรวจสอบต้องทำฝั่ง Server

ห้ามเชื่อค่าที่ส่งมาจาก Frontend

การเพิ่มรายการเบิกจ่ายที่เกี่ยวข้องหลายตารางต้องใช้ Database Transaction

---

# 18. USERS AND RBAC

ระบบต้องรองรับ Role

## ADMIN

สามารถ

* จัดการทุกระบบ
* จัดการ User
* จัดการ Role
* ดู Audit Log
* แก้ไขข้อมูลสำคัญ

## EXECUTIVE

สามารถ

* ดู Dashboard
* ดู Project
* ดู Budget
* ดู Report

ไม่ควรแก้ไขข้อมูลหลัก

## OFFICER

สามารถ

* สร้าง Project
* แก้ไข Project
* เพิ่ม Activity
* อัปเดต Progress
* อัปโหลดเอกสาร

## PROJECT_MANAGER

สามารถ

* ดู Project ที่รับผิดชอบ
* อัปเดต Progress
* เพิ่ม Activity
* อัปโหลดหลักฐาน
* อัปเดต Status

---

# 19. AUTHORIZATION

Authentication ≠ Authorization

การ Login สำเร็จไม่ได้หมายความว่าสามารถทำทุกอย่างได้

ทุก Server Action และ API ที่เกี่ยวข้องกับข้อมูลสำคัญต้องตรวจสอบ Permission

ตัวอย่าง

```text
requirePermission("project.update")
```

ห้ามพึ่งพาการซ่อน Button ใน Frontend เพื่อรักษาความปลอดภัย

---

# 20. VALIDATION

ใช้ Zod สำหรับ Validation

ต้อง Validate ทั้ง

```text
Frontend
+
Backend
```

Backend เป็นตัวตัดสินสุดท้าย

ตรวจสอบ

* Required fields
* Data type
* Date
* Number
* Percentage
* Budget
* File
* Permission

---

# 21. AUDIT LOG

ข้อมูลสำคัญทุกครั้งที่มีการ

```text
CREATE
UPDATE
DELETE
APPROVE
REJECT
```

ต้องบันทึก Audit Log

ข้อมูล

* user_id
* action
* module
* record_id
* old_value
* new_value
* ip_address
* user_agent
* created_at

Audit Log ต้องไม่สามารถแก้ไขโดย User ทั่วไป

---

# 22. DELETE POLICY

ข้อมูลสำคัญไม่ควรใช้ Hard Delete โดยไม่มีเหตุผล

ให้พิจารณา Soft Delete

เช่น

```text
deleted_at
deleted_by
```

โดยเฉพาะ

* Project
* Activity
* Budget
* User

ห้ามลบข้อมูลที่มีประวัติการเงินโดยไม่ตรวจสอบผลกระทบ

---

# 23. FILE UPLOAD

รองรับ

* PDF
* DOC
* DOCX
* XLS
* XLSX
* JPG
* PNG

ต้องตรวจสอบ

* Extension
* MIME Type
* File Size
* Filename

ห้าม Upload

* Executable
* Script
* PHP
* JavaScript
* Shell Script

ห้ามนำ Filename จาก User ไปสร้าง Path โดยตรง

---

# 24. DASHBOARD

Dashboard ต้องแสดงข้อมูลจาก MySQL จริง

ห้าม Hardcode ตัวเลข

ต้องแสดง

* จำนวนโครงการทั้งหมด
* ยังไม่เริ่ม
* กำลังดำเนินการ
* เสร็จสิ้น
* มีปัญหา
* ยกเลิก
* งบประมาณรวม
* เบิกจ่าย
* คงเหลือ
* Progress เฉลี่ย

---

# 25. DASHBOARD PROJECT

แต่ละ Project ต้องสามารถแสดง

```text
ชื่อโครงการ

สถานะ
████████████████

ความสำเร็จ
75%

งบประมาณ
1,000,000 บาท

เบิกจ่าย
650,000 บาท

คงเหลือ
350,000 บาท

กิจกรรม
7 / 10
```

---

# 26. PROJECT TIMELINE

แสดง Timeline

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

---

# 27. REPORT

Report ต้องใช้ Query จาก MySQL

รองรับ

* Project Report
* Budget Report
* Disbursement Report
* Progress Report
* Activity Report
* Problem Project Report
* Department Report
* Fiscal Year Report

รองรับ Export

* Excel
* CSV
* PDF

ข้อมูลใน Report ต้องตรงกับ Dashboard และ Database

---

# 28. SEARCH AND FILTER

Project Search ต้องรองรับ

* ชื่อโครงการ
* รหัสโครงการ
* ปีงบประมาณ
* หน่วยงาน
* ผู้รับผิดชอบ
* ประเภท
* สถานะ
* วันที่
* Progress

รองรับ

* Search
* Filter
* Sort
* Pagination

---

# 29. PERFORMANCE

ห้ามโหลดข้อมูลทั้งหมดจาก MySQL หากไม่จำเป็น

ใช้

* Server-side Pagination
* Database Index
* Select เฉพาะ Field ที่ต้องใช้
* Efficient Query
* Caching เมื่อเหมาะสม
* Lazy Loading เมื่อเหมาะสม

หลีกเลี่ยง N+1 Query

---

# 30. MYSQL INDEX

พิจารณาสร้าง Index สำหรับ Field ที่ใช้ค้นหาและ Join บ่อย เช่น

```text
project_code
name
status
fiscal_year
responsible_user_id
department_id
category_id
parent_id
start_date
end_date
created_at
```

อย่าสร้าง Index ทุก Field โดยไม่จำเป็น

ต้องพิจารณาผลกระทบต่อ Write Performance

---

# 31. MYSQL CHARACTER SET

Database และ Table ที่เกี่ยวข้องกับข้อมูลภาษาไทยต้องรองรับ

```text
utf8mb4
```

หลีกเลี่ยง Character Set ที่ไม่รองรับภาษาไทยหรือ Unicode ครบถ้วน

---

# 32. DATABASE TRANSACTION

ข้อมูลที่ต้องแก้ไขหลายตารางพร้อมกัน ให้ใช้ Prisma Transaction

ตัวอย่าง

```text
สร้าง Project
+
สร้าง Budget
+
สร้าง Project Member
+
สร้าง Audit Log
```

หากขั้นตอนใดล้มเหลว ต้อง Rollback ตาม Business Requirement

---

# 33. DATABASE MIGRATION

ทุกการเปลี่ยน Database Schema ต้องใช้ Prisma Migration

ห้ามแก้ Production Database โดยตรงโดยไม่มีแผน Migration

ก่อน Migration ต้องตรวจสอบ

* Existing Data
* Foreign Key
* Nullable
* Default
* Index
* Unique Constraint
* Migration Safety

หาก Migration อาจทำให้ข้อมูลเดิมเสียหาย ต้องจัดทำ Migration Strategy ก่อน

---

# 34. NOTIFICATION

ระบบสามารถแจ้งเตือน

* Project ใกล้ครบกำหนด
* Project เกินกำหนด
* Project มีปัญหา
* Budget ใกล้หมด
* ไม่มีการ Update Progress เป็นเวลานาน

Notification ต้องเชื่อมโยงกับ

* User
* Project
* Notification Type
* Read Status
* Created Date

---

# 35. UI/UX

ใช้

```text
Tailwind CSS
+
shadcn/ui
```

Design ต้อง

* Modern
* Professional
* Clean
* Accessible
* Responsive
* เหมาะกับระบบราชการ

รองรับ

* Desktop
* Tablet
* Mobile

---

# 36. RESPONSIVE TABLE

Table ที่มีข้อมูลจำนวนมากต้องรองรับ Mobile

สามารถใช้

* Horizontal Scroll
* Responsive Card
* Column Priority

ห้ามทำ Table ที่ทำให้หน้าเว็บพังบนมือถือ

---

# 37. FORM UX

Form ต้องมี

* Label ชัดเจน
* Required Indicator
* Validation Message
* Loading State
* Disabled State
* Success Feedback
* Error Feedback

เมื่อ Save สำเร็จต้องแจ้งผู้ใช้

ห้ามให้ผู้ใช้เดาว่าการบันทึกสำเร็จหรือไม่

---

# 38. ERROR HANDLING

Error ที่แสดงให้ User ต้องเข้าใจง่าย

ไม่แสดง

```text
PrismaClientKnownRequestError
```

ให้แสดงข้อความที่เหมาะสม เช่น

```text
ไม่สามารถบันทึกข้อมูลได้ กรุณาตรวจสอบข้อมูลอีกครั้ง
```

แต่ต้อง Log Error จริงไว้สำหรับ Developer

ห้ามแสดง

* Stack Trace
* Database Error
* Secret
* Password
* Token

ให้ User

---

# 39. API / SERVER ACTION

ทุก API / Server Action ต้อง

```text
1. Authenticate
2. Authorize
3. Validate Input
4. Execute Business Logic
5. Handle Error
6. Audit หากเป็น Action สำคัญ
7. Return Typed Response
```

---

# 40. BUSINESS LOGIC

Business Logic สำคัญต้องอยู่ฝั่ง Server

ตัวอย่าง

* Progress
* Budget
* Disbursement
* Permission
* Status Transition

ห้ามพึ่งพา Frontend เพียงอย่างเดียว

---

# 41. DATA CONSISTENCY

เมื่อข้อมูลเปลี่ยน ต้องตรวจสอบว่าระบบอื่นได้รับผลกระทบหรือไม่

ตัวอย่าง

แก้ Budget

ต้องตรวจสอบ

```text
Project Detail
Dashboard
Budget Summary
Report
```

แก้ Project Status

ต้องตรวจสอบ

```text
Dashboard
Project List
Project Detail
Report
Notification
```

---

# 42. DATA INTEGRITY

ต้องป้องกัน

* Project ย่อยไม่มี Project หลัก
* Project ไม่มีผู้รับผิดชอบ
* วันที่สิ้นสุดก่อนวันที่เริ่ม
* เบิกจ่ายเกินงบประมาณ
* Progress ต่ำกว่า 0
* Progress มากกว่า 100
* Activity ไม่มี Project
* User ที่ถูกลบยังถูกอ้างอิงโดยข้อมูลสำคัญ
* Foreign Key ไม่ถูกต้อง

ใช้

* Foreign Key
* Unique Constraint
* Prisma Validation
* Zod
* Server-side Validation
* Database Transaction

---

# 43. FISCAL YEAR

ระบบต้องรองรับปีงบประมาณของประเทศไทย

แยก

```text
วันที่จริง
```

ออกจาก

```text
ปีงบประมาณ
```

Database ควรเก็บวันที่ในรูปแบบ Date/DateTime

ไม่ควรเก็บวันที่เป็น String หากไม่จำเป็น

Logic ปีงบประมาณต้องอยู่ใน Utility / Service กลาง

ห้ามเขียน Logic ปีงบประมาณซ้ำหลายแห่ง

---

# 44. MONEY

ข้อมูลทางการเงินต้องใช้ Decimal

ตัวอย่าง

```text
DECIMAL(15,2)
```

ห้ามใช้ Floating Point สำหรับการคำนวณเงิน

การคำนวณต้องมีความแม่นยำ

---

# 45. DOCKER

ระบบต้องสามารถ Run ด้วย Docker

ต้องมี

```text
Dockerfile
docker-compose.yml
.env.example
.dockerignore
```

Services อย่างน้อย

```text
app
mysql
nginx
```

ตัวอย่าง Architecture

```text
Internet
    ↓
Nginx
    ↓
Next.js
    ↓
Prisma
    ↓
MySQL
```

---

# 46. DOCKER MYSQL

ใช้ MySQL 8.0+

ตัวอย่าง

```yaml
mysql:
  image: mysql:8.0
  restart: unless-stopped
  environment:
    MYSQL_ROOT_PASSWORD: ${MYSQL_ROOT_PASSWORD}
    MYSQL_DATABASE: ${MYSQL_DATABASE}
    MYSQL_USER: ${MYSQL_USER}
    MYSQL_PASSWORD: ${MYSQL_PASSWORD}
  volumes:
    - mysql_data:/var/lib/mysql
```

ต้องใช้ Volume เพื่อป้องกันข้อมูลหายเมื่อ Container ถูก Restart

---

# 47. ENVIRONMENT VARIABLES

ใช้ Environment Variables

ตัวอย่าง

```env
DATABASE_URL=mysql://root:@localhost:3306/municipal_project_tracker

MYSQL_ROOT_PASSWORD=
MYSQL_DATABASE=municipal_project_tracker
MYSQL_USER=root
MYSQL_PASSWORD=

AUTH_SECRET=municipal_secret_key_2026

NEXT_PUBLIC_APP_URL=http://localhost:3000
```

ต้องมี

```text
.env.example
```

ห้าม Commit Secret จริงเข้า Git

---

# 48. SECURITY

ระบบต้องมี

* Authentication
* Authorization
* RBAC
* Input Validation
* SQL Injection Protection
* XSS Protection
* CSRF Protection ตาม Architecture
* Secure Cookies
* Secure Session
* Rate Limiting
* Security Headers
* File Upload Validation
* Password Hashing
* Permission Checking
* Error Handling

ห้ามเชื่อข้อมูลจาก Client โดยตรง

---

# 49. FILE SECURITY

ไฟล์ที่ Upload ต้อง

* ตรวจสอบ MIME Type
* ตรวจสอบ Extension
* จำกัด File Size
* เปลี่ยนชื่อไฟล์
* ป้องกัน Path Traversal
* ไม่ Execute File Upload
* ไม่เก็บไฟล์ในตำแหน่งที่สามารถ Execute Script ได้

---

# 50. TESTING

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

## Permission

ทดสอบทุก Role

## Validation

ทดสอบข้อมูลผิด

## Security

ทดสอบ Unauthorized Access

## Responsive

ทดสอบ

* Desktop
* Tablet
* Mobile

---

# 51. SECURITY CHECKLIST

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

---

# 52. CODE QUALITY

ต้องใช้

* TypeScript Strict Mode
* ESLint
* Prettier

หลีกเลี่ยง

```text
any
```

ถ้าไม่จำเป็น

ห้ามใช้

```text
@ts-ignore
```

เพื่อซ่อน Error โดยไม่มีเหตุผล

ห้ามปิด TypeScript Error เพียงเพื่อให้ Build ผ่าน

---

# 53. COMPONENT PRINCIPLES

Component ต้องมี Responsibility ชัดเจน

หาก Component ใหญ่เกินไป ให้แยก

```text
UI
↓
Hook
↓
Service
↓
Business Logic
```

สร้าง Component Reusable เช่น

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

---

# 54. SEARCH PERFORMANCE

การ Search ข้อมูลจำนวนมากต้องทำฝั่ง Server

ไม่ควรโหลดข้อมูลทั้งหมดมา Filter บน Browser

ใช้

```text
Database Query
+
Index
+
Pagination
```

---

# 55. GIT RULES

Commit Message แนะนำ

```text
feat: add project progress tracking
fix: fix project budget calculation
refactor: improve project service
security: validate project permissions
docs: update project documentation
```

ห้าม Commit

```text
.env
database passwords
API keys
AUTH_SECRET
private keys
```

---

# 56. BEFORE CREATING NEW FEATURE

ก่อนสร้าง Feature ใหม่

1. ตรวจสอบว่ามี Feature นี้แล้วหรือไม่
2. ตรวจสอบ Component ที่สามารถ Reuse
3. ตรวจสอบ Service
4. ตรวจสอบ Database
5. ตรวจสอบ API
6. ตรวจสอบ Permission

ถ้ามีระบบเดิมอยู่แล้ว

```text
ปรับปรุงของเดิม
```

ไม่ใช่

```text
สร้างระบบใหม่ซ้ำ
```

---

# 57. NEVER DO BLIND REWRITE

ห้าม Rewrite ระบบทั้งหมดเพราะพบ Bug เล็ก ๆ

ตัวอย่าง

ถ้า Button มี Bug

ห้าม Rewrite ทั้งหน้า

ถ้า Query มีปัญหา

ห้ามเปลี่ยน Architecture ทั้งระบบโดยไม่จำเป็น

แก้เฉพาะส่วนที่จำเป็นและตรวจสอบผลกระทบ

---

# 58. AI AGENT WORKFLOW

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
IMPLEMENT
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

# 59. REQUIREMENT AMBIGUITY

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

# 60. CHANGE IMPACT CHECKLIST

ก่อน Merge การแก้ไข ให้ตรวจสอบ

```text
[ ] Frontend
[ ] Backend
[ ] Database
[ ] Prisma
[ ] API
[ ] Server Actions
[ ] Validation
[ ] Authentication
[ ] Authorization
[ ] Dashboard
[ ] Report
[ ] Export
[ ] Notification
[ ] Audit Log
[ ] Mobile
[ ] Performance
[ ] Security
```

---

# 61. PRODUCTION SAFETY

ก่อน Production Deployment ต้องตรวจสอบ

```text
[ ] Build ผ่าน
[ ] TypeScript ผ่าน
[ ] ESLint ผ่าน
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
[ ] Docker
[ ] Nginx
[ ] MySQL Connection
[ ] Database Persistence
```

---

# 62. BACKUP

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

ห้ามถือว่า Docker Volume เพียงอย่างเดียวคือ Backup

---

# 63. LOGGING

ระบบต้องมี Logging สำหรับ

* Authentication
* Error
* Database Error
* Important Actions
* Permission Denied
* Security Events

ห้าม Log

* Password
* Token
* Secret
* Sensitive Information

---

# 64. FINAL RESPONSE AFTER CODING

หลังแก้ไข Code ให้รายงาน

## Changed

ระบุสิ่งที่แก้

## Files

ระบุไฟล์ที่แก้ไข

## Database

ระบุว่า

* มี Migration หรือไม่
* มีการเปลี่ยน Schema หรือไม่

## Security

ระบุ Security ที่ตรวจสอบ

## Testing

ระบุสิ่งที่ทดสอบ

## Potential Impact

ระบุระบบที่อาจได้รับผลกระทบ

## Next Steps

แนะนำขั้นตอนถัดไปถ้ามี

---

# 65. DEFINITION OF DONE

Feature จะถือว่าเสร็จเมื่อ

* Code ทำงานได้
* TypeScript ผ่าน
* ESLint ผ่าน
* Build ผ่าน
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

---

# 66. MOST IMPORTANT RULES

จำกฎเหล่านี้เป็นอันดับแรก

### RULE 1

**ห้ามทำระบบเดิมพัง**

### RULE 2

**ตรวจสอบ Code ก่อนแก้**

### RULE 3

**ตรวจสอบผลกระทบก่อนเปลี่ยน Database**

### RULE 4

**MySQL เป็น Source of Truth**

### RULE 5

**ข้อมูลจริงต้องมาจาก Database**

### RULE 6

**Business Logic สำคัญต้องตรวจสอบฝั่ง Server**

### RULE 7

**Frontend ไม่ใช่ Security Boundary**

### RULE 8

**ข้อมูล Dashboard และ Report ต้องตรงกับ Database**

### RULE 9

**ข้อมูลทางการเงินต้องแม่นยำ**

### RULE 10

**ทุกการเปลี่ยนแปลงสำคัญต้องสามารถตรวจสอบย้อนหลังได้**

### RULE 11

**เมื่อแก้ Feature หนึ่ง ต้องตรวจสอบ Feature ที่เกี่ยวข้องทั้งหมด**

### RULE 12

**อย่า Rewrite ระบบโดยไม่จำเป็น**

---

# 67. DEVELOPMENT PRINCIPLE

เป้าหมายไม่ใช่แค่

> "ทำให้หน้าเว็บใช้งานได้"

แต่ต้องทำให้ระบบ

```text
ถูกต้อง
+
ปลอดภัย
+
ตรวจสอบได้
+
ดูแลต่อได้
+
ขยายระบบได้
+
ข้อมูลไม่มั่ว
+
ระบบเดิมไม่พัง
+
รองรับข้อมูลจำนวนมาก
+
รองรับการใช้งานจริง
```

ให้คิดเสมอว่า Software นี้จะถูกนำไปใช้งานจริงในหน่วยงานเทศบาล

ข้อมูล

* โครงการ
* โครงการย่อย
* กิจกรรม
* งบประมาณ
* การเบิกจ่าย
* ผู้รับผิดชอบ
* ความคืบหน้า
* เอกสาร
* ประวัติการดำเนินงาน

ต้องมีความสัมพันธ์กันอย่างถูกต้อง และสามารถตรวจสอบย้อนหลังได้
