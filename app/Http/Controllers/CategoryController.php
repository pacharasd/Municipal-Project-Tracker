<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\View;
use App\Core\Router;
use App\Core\Session;
use App\Services\AuditLogService;
use Exception;
use Throwable;

class CategoryController
{
    public function index(): void
    {
        if (!Auth::isAdmin()) {
            Session::flash('error', 'เฉพาะผู้ดูแลระบบ (Administrator) เท่านั้นที่สามารถเข้าถึงระบบจัดการประเภทโครงการได้');
            header('Location: ' . Router::url('/dashboard'));
            exit;
        }

        $sql = "
            SELECT c.*,
                   COALESCE(p_stats.project_count, 0) as project_count,
                   COALESCE(p_stats.sub_project_count, 0) as sub_project_count,
                   COALESCE(p_stats.total_budget, 0) as total_budget,
                   COALESCE(p_stats.total_disbursed, 0) as total_disbursed,
                   COALESCE(p_stats.avg_progress, 0) as avg_progress
            FROM project_categories c
            LEFT JOIN (
                SELECT p.category_id,
                       COUNT(p.id) as project_count,
                       (SELECT COUNT(*) FROM projects sub WHERE sub.parent_id IN (SELECT p2.id FROM projects p2 WHERE p2.category_id = p.category_id AND p2.parent_id IS NULL)) as sub_project_count,
                       SUM(p.budget) as total_budget,
                       SUM(p.disbursed_amount) as total_disbursed,
                       AVG(p.progress) as avg_progress
                FROM projects p
                WHERE p.parent_id IS NULL
                GROUP BY p.category_id
            ) p_stats ON c.id = p_stats.category_id
            ORDER BY c.id ASC
        ";

        $categories = Database::query($sql);

        // Calculate summary cards metrics
        $totalCategories = count($categories);
        $grandTotalBudget = 0.0;
        $grandTotalDisbursed = 0.0;
        $totalProjectsAll = 0;
        $weightedProgressSum = 0.0;
        $topCategory = null;

        foreach ($categories as $cat) {
            $pCount = (int)$cat['project_count'];
            $bAmount = (float)$cat['total_budget'];
            $dAmount = (float)$cat['total_disbursed'];
            $prog = (float)$cat['avg_progress'];

            $grandTotalBudget += $bAmount;
            $grandTotalDisbursed += $dAmount;
            $totalProjectsAll += $pCount;
            $weightedProgressSum += ($prog * $pCount);

            if (!$topCategory || $pCount > (int)$topCategory['project_count']) {
                $topCategory = $cat;
            }
        }

        $overallProgress = $totalProjectsAll > 0 ? ($weightedProgressSum / $totalProjectsAll) : 0.0;

        $metrics = [
            'total_categories'     => $totalCategories,
            'top_category'         => $topCategory,
            'grand_total_budget'   => $grandTotalBudget,
            'grand_total_disbursed'=> $grandTotalDisbursed,
            'overall_progress'     => $overallProgress,
            'total_projects'       => $totalProjectsAll,
        ];

        View::render('categories.index', [
            'categories' => $categories,
            'metrics'    => $metrics,
        ]);
    }

    public function store(): void
    {
        try {
            if (!Auth::isAdmin()) {
                Session::flash('error', 'เฉพาะผู้ดูแลระบบ (Administrator) เท่านั้นที่มีสิทธิ์เพิ่มประเภทโครงการ');
                header('Location: ' . Router::url('/categories'));
                exit;
            }

            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                Session::flash('error', 'กรุณาระบุชื่อประเภทโครงการ');
                header('Location: ' . Router::url('/categories'));
                exit;
            }

            if (mb_strlen($name) < 2 || mb_strlen($name) > 255) {
                Session::flash('error', 'ชื่อประเภทโครงการต้องมีความยาวระหว่าง 2 ถึง 255 ตัวอักษร');
                header('Location: ' . Router::url('/categories'));
                exit;
            }

            // Check duplicate name
            $exists = Database::fetchColumn("SELECT COUNT(*) FROM project_categories WHERE name = ?", [$name]);
            if ($exists > 0) {
                Session::flash('error', "ชื่อประเภทโครงการ '{$name}' มีอยู่ในระบบแล้ว กรุณาใช้ชื่ออื่น");
                header('Location: ' . Router::url('/categories'));
                exit;
            }

            $now = date('Y-m-d H:i:s');
            $categoryId = Database::insert('project_categories', [
                'name'        => $name,
                'description' => $description ?: null,
                'icon'        => 'folder',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            try {
                AuditLogService::log(
                    'CREATE',
                    'ProjectCategory',
                    $categoryId,
                    null,
                    ['name' => $name, 'description' => $description]
                );
            } catch (Exception $e) {
                error_log("Audit log notice: " . $e->getMessage());
            }

            Session::flash('success', "เพิ่มประเภทโครงการ '{$name}' เรียบร้อยแล้ว");
            header('Location: ' . Router::url('/categories'));
            exit;
        } catch (\Throwable $e) {
            error_log("Category store error: " . $e->getMessage());
            Session::flash('error', "ไม่สามารถบันทึกประเภทโครงการได้: " . $e->getMessage());
            header('Location: ' . Router::url('/categories'));
            exit;
        }
    }

    public function update(string $id): void
    {
        try {
            if (!Auth::isAdmin()) {
                Session::flash('error', 'เฉพาะผู้ดูแลระบบ (Administrator) เท่านั้นที่มีสิทธิ์แก้ไขประเภทโครงการ');
                header('Location: ' . Router::url('/categories'));
                exit;
            }

            $categoryId = (int)$id;
            $category = Database::fetch("SELECT * FROM project_categories WHERE id = ?", [$categoryId]);
            if (!$category) {
                Session::flash('error', 'ไม่พบประเภทโครงการที่ต้องการแก้ไข');
                header('Location: ' . Router::url('/categories'));
                exit;
            }

            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                Session::flash('error', 'กรุณาระบุชื่อประเภทโครงการ');
                header('Location: ' . Router::url('/categories'));
                exit;
            }

            if (mb_strlen($name) < 2 || mb_strlen($name) > 255) {
                Session::flash('error', 'ชื่อประเภทโครงการต้องมีความยาวระหว่าง 2 ถึง 255 ตัวอักษร');
                header('Location: ' . Router::url('/categories'));
                exit;
            }

            // Check duplicate name excluding self
            $exists = Database::fetchColumn("SELECT COUNT(*) FROM project_categories WHERE name = ? AND id != ?", [$name, $categoryId]);
            if ($exists > 0) {
                Session::flash('error', "ชื่อประเภทโครงการ '{$name}' ซ้ำกับประเภทอื่นที่มีอยู่ในระบบแล้ว");
                header('Location: ' . Router::url('/categories'));
                exit;
            }

            $updateData = [
                'name'        => $name,
                'description' => $description ?: null,
                'updated_at'  => date('Y-m-d H:i:s'),
            ];

            Database::update('project_categories', $updateData, "id = ?", [$categoryId]);

            try {
                AuditLogService::log(
                    'UPDATE',
                    'ProjectCategory',
                    $categoryId,
                    ['name' => $category['name'], 'description' => $category['description']],
                    $updateData
                );
            } catch (Exception $e) {
                error_log("Audit log notice: " . $e->getMessage());
            }

            Session::flash('success', "อัปเดตประเภทโครงการ '{$name}' เรียบร้อยแล้ว");
            header('Location: ' . Router::url('/categories'));
            exit;
        } catch (\Throwable $e) {
            error_log("Category update error: " . $e->getMessage());
            Session::flash('error', "ไม่สามารถอัปเดตประเภทโครงการได้: " . $e->getMessage());
            header('Location: ' . Router::url('/categories'));
            exit;
        }
    }

    public function delete(string $id): void
    {
        try {
            if (!Auth::isAdmin()) {
                Session::flash('error', 'เฉพาะผู้ดูแลระบบ (Administrator) เท่านั้นที่มีสิทธิ์ลบประเภทโครงการ');
                header('Location: ' . Router::url('/categories'));
                exit;
            }

            $categoryId = (int)$id;
            $category = Database::fetch("SELECT * FROM project_categories WHERE id = ?", [$categoryId]);
            if (!$category) {
                Session::flash('error', 'ไม่พบประเภทโครงการที่ต้องการลบ');
                header('Location: ' . Router::url('/categories'));
                exit;
            }

            // Safety check: ensure no projects linked to this category
            $linkedCount = (int)Database::fetchColumn("SELECT COUNT(*) FROM projects WHERE category_id = ?", [$categoryId]);
            if ($linkedCount > 0) {
                Session::flash('error', "ไม่สามารถลบประเภทโครงการ '{$category['name']}' ได้ เนื่องจากมีโครงการหลักผูกอยู่ {$linkedCount} โครงการ กรุณาย้ายหรือเปลี่ยนประเภทโครงการเหล่านั้นก่อนลบ");
                header('Location: ' . Router::url('/categories'));
                exit;
            }

            Database::delete('project_categories', "id = ?", [$categoryId]);

            try {
                AuditLogService::log(
                    'DELETE',
                    'ProjectCategory',
                    $categoryId,
                    ['name' => $category['name']],
                    null
                );
            } catch (Exception $e) {
                error_log("Audit log notice: " . $e->getMessage());
            }

            Session::flash('success', "ลบประเภทโครงการ '{$category['name']}' เรียบร้อยแล้ว");
            header('Location: ' . Router::url('/categories'));
            exit;
        } catch (\Throwable $e) {
            error_log("Category delete error: " . $e->getMessage());
            Session::flash('error', "ไม่สามารถลบประเภทโครงการได้: " . $e->getMessage());
            header('Location: ' . Router::url('/categories'));
            exit;
        }
    }

    public function seedDefaults(): void
    {
        try {
            if (!Auth::isAdmin()) {
                Session::flash('error', 'เฉพาะผู้ดูแลระบบ (Administrator) เท่านั้นที่มีสิทธิ์นำเข้าหมวดหมู่มาตรฐาน');
                header('Location: ' . Router::url('/categories'));
                exit;
            }

            $defaults = [
                ['name' => 'โครงสร้างพื้นฐาน', 'description' => 'งานคมนาคม ไฟฟ้า ประปา ระบายน้ำ และผังเมือง'],
                ['name' => 'สาธารณสุขและคุณภาพชีวิต', 'description' => 'การดูแลสุขภาพ สุขาภิบาล และการดูแลผู้สูงอายุ'],
                ['name' => 'การศึกษาและวัฒนธรรม', 'description' => 'การส่งเสริมการเรียนรู้ ทักษะอาชีพ และศิลปวัฒนธรรม'],
                ['name' => 'สิ่งแวดล้อมและทรัพยากร', 'description' => 'การจัดการขยะ น้ำเสีย พื้นที่สีเขียว และพลังงานทดแทน'],
                ['name' => 'สังคมและเศรษฐกิจชุมชน', 'description' => 'การสงเคราะห์ผู้ด้อยโอกาส วิสาหกิจชุมชน และท่องเที่ยว'],
            ];

            $added = 0;
            foreach ($defaults as $item) {
                $exists = (int)Database::fetchColumn("SELECT COUNT(*) FROM project_categories WHERE name = ?", [$item['name']]);
                if ($exists === 0) {
                    Database::insert('project_categories', [
                        'name' => $item['name'],
                        'description' => $item['description'],
                        'icon' => 'folder',
                    ]);
                    $added++;
                }
            }

            try {
                AuditLogService::log(
                    'SEED_DEFAULTS',
                    'ProjectCategory',
                    0,
                    null,
                    ['added_count' => $added]
                );
            } catch (Exception $e) {
                error_log("Audit log notice: " . $e->getMessage());
            }

            Session::flash('success', "นำเข้าหมวดหมู่มาตรฐานเทศบาล 5 ด้านสำเร็จ (เพิ่ม {$added} หมวดหมู่)");
            header('Location: ' . Router::url('/categories'));
            exit;
        } catch (\Throwable $e) {
            error_log("Category seed defaults error: " . $e->getMessage());
            Session::flash('error', "ไม่สามารถนำเข้าหมวดหมู่มาตรฐานได้: " . $e->getMessage());
            header('Location: ' . Router::url('/categories'));
            exit;
        }
    }
}
