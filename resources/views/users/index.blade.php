<?php
ob_start();
use App\Core\Auth;
use App\Core\Router;
use App\Core\Session;

$title = 'จัดการผู้ใช้งานและสิทธิ์การเข้าถึง - Municipal Project Tracker';
$currentUserId = Auth::id();
?>

<div class="space-y-6" x-data="{ 
    searchQuery: '', 
    createModal: false,
    editModal: false,
    editUser: { id: '', name: '', email: '', position: '', department_id: '', phone: '', role_id: '' },
    openEdit(u) {
        this.editUser = Object.assign({}, u);
        this.editModal = true;
    }
}">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="p-2 bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 rounded-xl">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">การจัดการผู้ใช้งานระบบ</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">บริหารจัดการบัญชีผู้ใช้งาน เจ้าหน้าที่ และผู้รับผิดชอบโครงการในเทศบาล</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" @click="createModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-medium rounded-xl shadow-md hover:shadow-lg transition-all text-sm">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>เพิ่มผู้ใช้งานใหม่</span>
            </button>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="relative w-full sm:w-80">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            <input type="text" x-model="searchQuery" placeholder="ค้นหาชื่อ, อีเมล, กองสำนัก..."
                   class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none dark:text-white">
        </div>
        <div class="text-xs text-slate-500">
            เจ้าหน้าที่ในระบบทั้งหมด <?= count($users) ?> บัญชี
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/70 text-slate-600 dark:text-slate-400 font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4 w-12 text-center">#</th>
                        <th class="py-3.5 px-4">ผู้ใช้งาน (เจ้าหน้าที่)</th>
                        <th class="py-3.5 px-4">ตำแหน่ง / สังกัด</th>
                        <th class="py-3.5 px-4 text-center">สิทธิ์การใช้งาน</th>
                        <th class="py-3.5 px-4">เบอร์โทรศัพท์</th>
                        <th class="py-3.5 px-4 text-center">สถานะ</th>
                        <th class="py-3.5 px-4 text-center w-28">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                ไม่พบข้อมูลผู้ใช้งาน
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $idx => $u): 
                            $avatarInitial = mb_substr($u['name'], 0, 1, 'UTF-8');
                            $isSelf = ((int)$u['id'] === (int)$currentUserId);
                            $uJson = htmlspecialchars(json_encode([
                                'id'            => $u['id'],
                                'name'          => $u['name'],
                                'email'         => $u['email'],
                                'position'      => $u['position'] ?? '',
                                'department_id' => $u['department_id'] ?? '',
                                'phone'         => $u['phone'] ?? '',
                                'role_id'       => $u['role_id'] ?? 3,
                            ]), ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr class="hover:bg-slate-50/75 dark:hover:bg-slate-800/40 transition-colors"
                                x-show="!searchQuery || '<?= strtolower($u['name'] . ' ' . $u['email'] . ' ' . ($u['department_name'] ?? '') . ' ' . ($u['position'] ?? '')) ?>'.includes(searchQuery.toLowerCase())">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-mono"><?= $idx + 1 ?></td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                            <?= $avatarInitial ?>
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-900 dark:text-white flex items-center gap-1.5">
                                                <span><?= htmlspecialchars($u['name']) ?></span>
                                                <?php if ($isSelf): ?>
                                                    <span class="text-[10px] px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded-md font-normal">คุณ</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono">
                                                <?= htmlspecialchars($u['email']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-slate-800 dark:text-slate-200 font-medium">
                                        <?= htmlspecialchars($u['position'] ?: 'เจ้าหน้าที่') ?>
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        <?= htmlspecialchars($u['department_name'] ?: 'เทศบาล') ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php
                                    $roleBadgeStyle = match($u['role_name'] ?? '') {
                                        'admin'           => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'executive'       => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'officer'         => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'project_manager' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        default           => 'bg-slate-50 text-slate-700 border-slate-200',
                                    };
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border <?= $roleBadgeStyle ?>">
                                        <?= htmlspecialchars($u['role_label'] ?? 'เจ้าหน้าที่') ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-600 dark:text-slate-400">
                                    <?= htmlspecialchars($u['phone'] ?: '-') ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-600 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>เปิดใช้งาน</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" @click="openEdit(<?= $uJson ?>)"
                                                class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/40 rounded-lg transition" title="แก้ไขข้อมูล">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                        <?php if (!$isSelf): ?>
                                            <form action="<?= Router::url('/users/' . $u['id'] . '/delete') ?>" method="POST" 
                                                  onsubmit="return confirm('ยืนยันการลบผู้ใช้ <?= addslashes($u['name']) ?> ออกจากระบบ?');">
                                                <input type="hidden" name="_token" value="<?= $csrfToken ?>">
                                                <button type="submit" 
                                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition" title="ลบผู้ใช้งาน">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create User Modal -->
    <template x-teleport="body">
    <div x-show="createModal" style="display: none;" @click.self="createModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-lg w-full overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-purple-100 dark:bg-purple-950/50 text-purple-600">
                        <i data-lucide="user-plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-slate-900 dark:text-white">เพิ่มผู้ใช้งานใหม่</h3>
                        <p class="text-xs text-slate-400">สร้างบัญชีผู้ใช้งานสำหรับเจ้าหน้าที่เทศบาล</p>
                    </div>
                </div>
                <button type="button" @click="createModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="<?= Router::url('/users') ?>" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        ชื่อ - นามสกุล <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" required placeholder="เช่น นายสมคิด สถิตย์คง"
                           class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none dark:text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            อีเมล (Email) <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" required placeholder="name@municipality.go.th"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            รหัสผ่านเริ่มต้น
                        </label>
                        <input type="text" name="password" value="password" placeholder="password"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none dark:text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            ตำแหน่ง
                        </label>
                        <input type="text" name="position" placeholder="เช่น นักวิเคราะห์นโยบายและแผน"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            เบอร์โทรศัพท์
                        </label>
                        <input type="text" name="phone" placeholder="เช่น 081-2345678"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none dark:text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            สำนัก / กอง
                        </label>
                        <select name="department_id"
                                class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none dark:text-white">
                            <option value="">-- ไม่ระบุ --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            บทบาท / สิทธิ์ (Role) <span class="text-rose-500">*</span>
                        </label>
                        <select name="role_id" required
                                class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-purple-500 focus:outline-none dark:text-white font-semibold">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>" <?= ($r['id'] == 3) ? 'selected' : '' ?>><?= htmlspecialchars($r['display_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" @click="createModal = false"
                            class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 rounded-xl transition">
                        ยกเลิก
                    </button>
                    <button type="submit"
                            class="px-5 py-2 text-xs font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-xl shadow-md transition">
                        บันทึกผู้ใช้งาน
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>

    <!-- Edit User Modal -->
    <template x-teleport="body">
    <div x-show="editModal" style="display: none;" @click.self="editModal = false" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-lg w-full overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-blue-100 dark:bg-blue-950/50 text-blue-600">
                        <i data-lucide="edit-3" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-base text-slate-900 dark:text-white">แก้ไขข้อมูลผู้ใช้งาน</h3>
                        <p class="text-xs text-slate-400">อัปเดตรายละเอียดและสิทธิ์ของผู้ใช้</p>
                    </div>
                </div>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'<?= Router::url('/users/') ?>' + editUser.id + '/update'" method="POST" class="p-6 space-y-4">
                <input type="hidden" name="_token" value="<?= $csrfToken ?>">

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        ชื่อ - นามสกุล <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="name" x-model="editUser.name" required
                           class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            อีเมล (Email) <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" x-model="editUser.email" required
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            เปลี่ยนรหัสผ่าน (เว้นว่างหากไม่เปลี่ยน)
                        </label>
                        <input type="password" name="password" placeholder="••••••••"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            ตำแหน่ง
                        </label>
                        <input type="text" name="position" x-model="editUser.position"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            เบอร์โทรศัพท์
                        </label>
                        <input type="text" name="phone" x-model="editUser.phone"
                               class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            สำนัก / กอง
                        </label>
                        <select name="department_id" x-model="editUser.department_id"
                                class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                            <option value="">-- ไม่ระบุ --</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            บทบาท / สิทธิ์ (Role) <span class="text-rose-500">*</span>
                        </label>
                        <select name="role_id" x-model="editUser.role_id" required
                                class="w-full px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white font-semibold">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['display_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" @click="editModal = false"
                            class="px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 rounded-xl transition">
                        ยกเลิก
                    </button>
                    <button type="submit"
                            class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition">
                        บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>
</div>

<?php
$content = ob_get_clean();
include dirname(__DIR__) . '/layouts/app.blade.php';
?>
