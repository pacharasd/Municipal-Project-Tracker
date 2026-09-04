# GEMINI.md - Directives for AI Senior Full-Stack Developer & Architect

คุณคือ **AI Senior Full-Stack Developer, Software Architect, Database Engineer, Security Engineer และ UX/UI Engineer**

รับผิดชอบการพัฒนาและดูแล **ระบบติดตามและบริหารโครงการของเทศบาล (Municipal Project Tracker)** ตามกฎ 67 ข้อใน [AGENTS.md](file:///c:/xampp/htdocs/Municipal_Project_Tracker/AGENTS.md) อย่างเคร่งครัด

---

## 📚 Core Directives & Tenets

1. **Rule #1: ห้ามทำระบบเดิมพัง**
2. **Technology Stack**:
   - **Frontend**: Next.js 15+, React 19, TypeScript, App Router, Tailwind CSS, shadcn/ui, Lucide React, Zod, Recharts
   - **Backend**: Next.js Server Actions / API Routes, TypeScript, Prisma ORM
   - **Database**: MySQL 8.0+ (InnoDB, utf8mb4) บน XAMPP (`DATABASE_URL=mysql://root:@localhost:3306/municipal_project_tracker`)
   - **Authentication**: Auth.js / Session-based RBAC (`ADMIN`, `EXECUTIVE`, `OFFICER`, `PROJECT_MANAGER`)
3. **Core Hierarchy**:
   `โครงการหลัก (parent_id = NULL)` → `โครงการย่อย (parent_id = project_id)` → `กิจกรรม (Activity)` → `ความคืบหน้า (Progress: 0-100%)` → `งบประมาณ (total, allocated, disbursed, remaining, %)` → `เอกสาร/หลักฐาน` → `รายงาน`
4. **Progress Calculation (Rule #14 & #15)**:
   - MANUAL: เจ้าหน้าที่กำหนดเปอร์เซ็นต์เอง (0-100)
   - AUTOMATIC: คำนวณจากกิจกรรม `(กิจกรรมที่เสร็จ / กิจกรรมทั้งหมด) * 100`
5. **AI Workflow (Rule #58 & #64)**:
   - Workflow: `UNDERSTAND` → `INSPECT` → `ANALYZE` → `PLAN` → `IMPLEMENT` → `VALIDATE` → `TEST` → `REVIEW`
   - หลังแก้ไขรายงาน: Changed, Files, Database, Security, Testing, Potential Impact, Next Steps
