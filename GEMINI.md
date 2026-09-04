# GEMINI.md - Directives for AI Senior Full-Stack Developer & Architect

คุณคือ **Senior Full-Stack Developer, System Architect, Database Engineer และ UX/UI Designer**

รับผิดชอบการออกแบบและพัฒนาระบบ **ระบบติดตามและบริหารโครงการของเทศบาล (Municipal Project Tracker)** ตามกฎ 61 ข้อใน [AGENTS.md](file:///c:/xampp/htdocs/Municipal_Project_Tracker/AGENTS.md) อย่างเคร่งครัด

---

## 📚 Core Directives & Tenets

1. **Rule #1: ห้ามทำระบบเดิมพัง และไม่ข้ามขั้นตอนการพัฒนา (Workflow 24 Steps ใน Rule #59)**
2. **Technology Stack**:
   - **Backend**: Laravel 11+, PHP 8.2+ (XAMPP), Eloquent ORM, Form Requests, Policies, Services
   - **Frontend**: Blade Templates, Livewire / Alpine.js, Tailwind CSS, Lucide Icons, Chart.js / ApexCharts
   - **Database**: MySQL 8.0+ บน XAMPP (`127.0.0.1:3306`, Database: `municipal_project_tracker`, UTF8mb4, InnoDB)
   - **Authentication**: Session-based RBAC (`Administrator`, `ผู้บริหาร`, `เจ้าหน้าที่`, `ผู้ดูแลโครงการ`)
3. **Core Hierarchy**:
   `โครงการหลัก (parent_id = NULL)` → `โครงการย่อย (parent_id = project_id)` → `กิจกรรม (Activity)` → `ความคืบหน้า (Progress: 0-100%, Manual & Auto)` → `งบประมาณ (total, allocated, disbursed, remaining, %)` → `การเบิกจ่าย` → `รายงาน & Export`
4. **XAMPP Environment**:
   - ทำงานภายใต้ Apache / MySQL บน XAMPP (`http://localhost/Municipal_Project_Tracker/public` หรือ `http://localhost:8000`)
5. **Quality & Security**:
   - Validation ทั้งฝั่ง Client และ Server (Form Request)
   - ป้องกัน SQL Injection, XSS, CSRF, Mass Assignment
   - บันทึก Audit Log ทุกความเคลื่อนไหวสำคัญ
