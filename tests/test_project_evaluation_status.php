<?php

declare(strict_types=1);

namespace App\Core {
    final class Auth
    {
        public static function id(): ?int { return 7; }
    }

    final class Database
    {
        private static \PDO $pdo;

        public static function boot(): void
        {
            self::$pdo = new \PDO('sqlite::memory:');
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$pdo->exec('CREATE TABLE projects (
                id INTEGER PRIMARY KEY, parent_id INTEGER NULL, status TEXT NOT NULL,
                progress NUMERIC NOT NULL, evaluation_score NUMERIC NULL,
                evaluation_grade TEXT NULL, evaluation_notes TEXT NULL,
                evaluated_at TEXT NULL, evaluated_by INTEGER NULL
            )');
            self::$pdo->exec('CREATE TABLE audit_logs (
                id INTEGER PRIMARY KEY, user_id INTEGER NULL, action TEXT NOT NULL,
                module TEXT NOT NULL, record_id INTEGER NULL, old_values TEXT NULL,
                new_values TEXT NULL, ip_address TEXT NULL, user_agent TEXT NULL
            )');
        }

        public static function query(string $sql, array $params = []): array
        {
            $statement = self::$pdo->prepare(str_replace(' FOR UPDATE', '', $sql));
            $statement->execute($params);
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        }

        public static function fetch(string $sql, array $params = []): ?array
        {
            return self::query($sql, $params)[0] ?? null;
        }

        public static function execute(string $sql, array $params = []): int
        {
            $statement = self::$pdo->prepare($sql);
            $statement->execute($params);
            return $statement->rowCount();
        }

        public static function update(string $table, array $data, string $where, array $whereParams = []): int
        {
            $fields = implode(', ', array_map(static fn(string $name): string => "{$name} = ?", array_keys($data)));
            return self::execute("UPDATE {$table} SET {$fields} WHERE {$where}", array_merge(array_values($data), $whereParams));
        }

        public static function insert(string $table, array $data): int
        {
            $columns = implode(', ', array_keys($data));
            $marks = implode(', ', array_fill(0, count($data), '?'));
            self::execute("INSERT INTO {$table} ({$columns}) VALUES ({$marks})", array_values($data));
            return (int)self::$pdo->lastInsertId();
        }

        public static function transaction(callable $callback): mixed
        {
            self::$pdo->beginTransaction();
            try {
                $result = $callback();
                self::$pdo->commit();
                return $result;
            } catch (\Throwable $error) {
                self::$pdo->rollBack();
                throw $error;
            }
        }
    }
}

namespace {
    use App\Core\Database;
    use App\Services\ProgressService;
    use App\Services\ProjectEvaluationService;

    require_once __DIR__ . '/../app/Enums/ProjectStatus.php';
    require_once __DIR__ . '/../app/Services/AuditLogService.php';
    require_once __DIR__ . '/../app/Services/ProjectService.php';
    require_once __DIR__ . '/../app/Services/ProjectEvaluationService.php';
    require_once __DIR__ . '/../app/Services/ProgressService.php';

    function assertSameValue(mixed $expected, mixed $actual, string $message): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException("{$message}: expected " . var_export($expected, true) . ', got ' . var_export($actual, true));
        }
    }

    function project(int $id): array
    {
        return Database::fetch('SELECT * FROM projects WHERE id = ?', [$id]);
    }

    Database::boot();
    foreach ([
        [1, null, 'not_started', 0, null],
        [2, 1, 'in_progress', 20, null],
        [3, 1, 'has_problem', 60, null],
        [4, null, 'in_progress', 25, null],
        [5, null, 'cancelled', 30, null],
        [6, null, 'not_started', 15, 0],
        [7, null, 'cancelled', 40, 50],
    ] as $row) {
        Database::execute(
            'INSERT INTO projects (id, parent_id, status, progress, evaluation_score) VALUES (?, ?, ?, ?, ?)',
            $row
        );
    }

    $result = ProjectEvaluationService::evaluate(1, 0, 'คะแนนศูนย์', 7);
    assertSameValue('D', $result['grade'], 'Zero score grade');
    assertSameValue('completed', project(1)['status'], 'Evaluation closes main project');
    assertSameValue(0, (int)project(1)['progress'], 'Evaluation preserves progress');
    assertSameValue(0, (int)project(1)['evaluation_score'], 'Zero counts as evaluated');

    ProgressService::syncParentProjectProgress(1);
    assertSameValue(40, (int)project(1)['progress'], 'Child average keeps updating');
    assertSameValue('completed', project(1)['status'], 'Child sync preserves evaluated status');

    Database::update('projects', ['status' => 'in_progress'], 'id = ?', [1]);
    ProgressService::syncParentProjectProgress(1);
    assertSameValue('in_progress', project(1)['status'], 'Child sync preserves administrator override');
    ProjectEvaluationService::evaluate(1, 100, null, 7);
    assertSameValue('completed', project(1)['status'], 'Re-evaluation closes project again');
    assertSameValue(40, (int)project(1)['progress'], 'Re-evaluation preserves average');

    foreach ([[2, 50], [5, 50], [4, -1], [4, 101], [4, INF]] as [$id, $score]) {
        try {
            ProjectEvaluationService::evaluate($id, $score, null, 7);
            throw new \RuntimeException("Rejected evaluation {$id}/{$score} was accepted");
        } catch (\Exception $error) {
            if (str_starts_with($error->getMessage(), 'Rejected evaluation')) {
                throw $error;
            }
        }
    }
    assertSameValue(null, project(4)['evaluation_score'], 'Invalid score does not write');
    assertSameValue('cancelled', project(5)['status'], 'Cancelled project stays cancelled');

    $candidateIds = array_map('intval', array_column(ProjectEvaluationService::backfillCandidates(), 'id'));
    assertSameValue([6], $candidateIds, 'Backfill selects evaluated non-cancelled main projects');
    assertSameValue(true, ProjectEvaluationService::backfillProject(6), 'First backfill changes row');
    assertSameValue(false, ProjectEvaluationService::backfillProject(6), 'Second backfill is a no-op');
    assertSameValue('completed', project(6)['status'], 'Backfill completes project');
    assertSameValue(15, (int)project(6)['progress'], 'Backfill preserves progress');
    assertSameValue('cancelled', project(7)['status'], 'Backfill skips cancelled project');
    assertSameValue([], ProjectEvaluationService::backfillCandidates(), 'Backfill is idempotent');
    assertSameValue(1, (int)Database::fetch("SELECT COUNT(*) AS count FROM audit_logs WHERE action = 'BACKFILL_EVALUATION_STATUS'")['count'], 'Backfill audits once');

    Database::execute("CREATE TRIGGER fail_eval BEFORE INSERT ON audit_logs WHEN NEW.action = 'EVALUATE' BEGIN SELECT RAISE(ABORT, 'audit failure'); END");
    try {
        ProjectEvaluationService::evaluate(4, 85, null, 7);
        throw new \RuntimeException('Audit failure did not abort evaluation');
    } catch (\Exception $error) {
        if ($error->getMessage() === 'Audit failure did not abort evaluation') {
            throw $error;
        }
    }
    assertSameValue(null, project(4)['evaluation_score'], 'Failed audit rolls back score');
    assertSameValue('in_progress', project(4)['status'], 'Failed audit rolls back status');

    $source = file_get_contents(__DIR__ . '/../resources/views/projects/index.blade.php');
    $firstPhpEnd = strpos($source, '?>');
    $prologue = substr($source, strlen('<?php'), $firstPhpEnd - strlen('<?php'));
    $projects = [[
        'id' => 1, 'name' => 'Main', 'project_code' => 'MAIN-1',
        'fiscal_year_id' => 1, 'department_id' => 1, 'fiscal_year' => 2570,
        'budget' => 100, 'progress' => 40, 'status' => 'completed',
        'evaluation_score' => 100, 'sub_projects' => [[
            'name' => 'Child', 'status' => 'has_problem', 'responsible_name' => 'Officer',
        ]],
    ]];
    $filters = ['evaluation' => ''];
    ob_start();
    eval($prologue);
    ob_end_clean();
    assertSameValue('completed', $projectsSummary[0]['status'], 'List filter uses stored main status');
    assertSameValue(1, $completedCount, 'List counter uses stored main status');

    echo "Project evaluation status tests passed\n";
}
