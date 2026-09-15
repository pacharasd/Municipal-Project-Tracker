<?php

namespace App\Http\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Session;
use App\Core\Router;
use App\Services\AuditLogService;
use Exception;

class AttachmentController
{
    public function upload(): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์อัปโหลดเอกสาร');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/projects')));
            exit;
        }

        $projectId = (int)($_POST['project_id'] ?? 0);
        $activityId = !empty($_POST['activity_id']) ? (int)$_POST['activity_id'] : null;
        $caption = trim($_POST['caption'] ?? '');

        if (empty($_FILES['file']['name'])) {
            Session::flash('error', 'กรุณาเลือกไฟล์ที่ต้องการอัปโหลด');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/sub-projects/' . $projectId)));
            exit;
        }

        $originalName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];
        if (!in_array($ext, $allowedExts)) {
            Session::flash('error', 'ไม่อนุญาตให้อัปโหลดไฟล์ประเภทนี้ (รองรับเฉพาะ รูปภาพ, PDF, Word, Excel)');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/sub-projects/' . $projectId)));
            exit;
        }

        // Deep Content Inspection via finfo (MIME Type verification)
        if (function_exists('finfo_open') && !empty($_FILES['file']['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = strtolower((string)finfo_file($finfo, $_FILES['file']['tmp_name']));
            finfo_close($finfo);

            $allowedMimes = [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/zip', // docx and xlsx are zip-compressed XML
                'application/octet-stream', // defensive for some windows mime handlers
            ];

            $dangerousMimes = [
                'text/x-php', 'application/x-php', 'application/x-httpd-php', 'application/x-httpd-php-source',
                'text/html', 'application/javascript', 'text/javascript', 'application/x-sh', 'text/x-shellscript',
                'application/x-executable', 'application/x-msdownload'
            ];

            if (in_array($mimeType, $dangerousMimes, true) || (!in_array($mimeType, $allowedMimes, true) && !str_starts_with($mimeType, 'image/'))) {
                Session::flash('error', 'รูปแบบข้อมูลภายในไฟล์ไม่ถูกต้องหรือไม่ได้รับอนุญาตตามนโยบายความปลอดภัย');
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/sub-projects/' . $projectId)));
                exit;
            }
        }

        if ($fileSize > 20 * 1024 * 1024) { // 20 MB max
            Session::flash('error', 'ขนาดไฟล์ต้องไม่เกิน 20MB');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/sub-projects/' . $projectId)));
            exit;
        }

        $newFileName = 'att_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $destPath = $uploadDir . '/' . $newFileName;
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $destPath)) {
            Session::flash('error', 'เกิดข้อผิดพลาดในการบันทึกไฟล์');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/sub-projects/' . $projectId)));
            exit;
        }

        $fileType = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'image' : 'document';

        $attId = Database::insert('attachments', [
            'project_id'  => $projectId,
            'activity_id' => $activityId,
            'file_name'   => $originalName,
            'file_path'   => $newFileName,
            'file_type'   => $fileType,
            'file_size'   => $fileSize,
            'caption'     => $caption ?: $originalName,
            'uploaded_by' => Auth::id() ?: 1,
        ]);

        AuditLogService::log('UPLOAD_ATTACHMENT', 'Attachment', $attId, null, [
            'file_name' => $originalName,
            'project_id' => $projectId,
            'type' => $fileType
        ]);

        Session::flash('success', "อัปโหลดไฟล์ '{$originalName}' เรียบร้อยแล้ว");
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/sub-projects/' . $projectId)));
        exit;
    }

    public function delete(string $id): void
    {
        if (!Auth::canManageProjects()) {
            Session::flash('error', 'คุณไม่มีสิทธิ์ลบไฟล์แนบ');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/projects')));
            exit;
        }

        $attId = (int)$id;
        $att = Database::fetch("SELECT * FROM attachments WHERE id = ?", [$attId]);
        if (!$att) {
            Session::flash('error', 'ไม่พบไฟล์แนบที่ต้องการลบ');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/projects')));
            exit;
        }

        $filePath = dirname(__DIR__, 2) . '/public/uploads/' . $att['file_path'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        Database::execute("DELETE FROM attachments WHERE id = ?", [$attId]);
        AuditLogService::log('DELETE_ATTACHMENT', 'Attachment', $attId, ['file_name' => $att['file_name']]);

        Session::flash('success', "ลบไฟล์แนบ '{$att['file_name']}' เรียบร้อยแล้ว");
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? Router::url('/sub-projects/' . $att['project_id'])));
        exit;
    }
}
