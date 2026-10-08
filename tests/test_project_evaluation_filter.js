const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../resources/views/projects/index.blade.php'), 'utf8');
const template = source.match(/^<script[^\r\n]*\r?\n([\s\S]*?)^<\/script>/m)?.[1];
assert.ok(template, 'Main projects Alpine script was not found');
assert.match(source, /@select-changed="if \(\$event\.detail\.name === 'evaluation'\) setEvaluationFilter\(\$event\.detail\.value\)"/);
assert.match(source, /'ariaLabel' => 'ผลประเมินโครงการหลัก'/);
for (const value of ['all', 'evaluated', 'ungraded']) {
    assert.ok(source.includes(`['value' => '${value}'`));
}

const projects = [
    { id: 1, fiscal_year_id: '1', department_id: '1', status: 'completed', evaluated: true, search_text: 'road alpha', budget: 100, sub_count: 2 },
    { id: 2, fiscal_year_id: '1', department_id: '1', status: 'completed', evaluated: false, search_text: 'road beta', budget: 200, sub_count: 1 },
    { id: 3, fiscal_year_id: '1', department_id: '1', status: 'in_progress', evaluated: true, search_text: 'bridge', budget: 300, sub_count: 0 },
    { id: 4, fiscal_year_id: '1', department_id: '1', status: 'has_problem', evaluated: false, search_text: 'park', budget: 400, sub_count: 1 },
    { id: 5, fiscal_year_id: '1', department_id: '1', status: 'completed', evaluated: true, search_text: 'road gamma', budget: 500, sub_count: 0 },
    { id: 6, fiscal_year_id: '1', department_id: '1', status: 'completed', evaluated: true, search_text: 'road delta', budget: 600, sub_count: 0 },
    { id: 7, fiscal_year_id: '2', department_id: '1', status: 'completed', evaluated: false, search_text: 'road year two', budget: 700, sub_count: 1 },
];

function mount({ url = 'http://localhost/projects', evaluation = 'all', search = '', fiscalYear = '', dataset = projects } = {}) {
    const location = {
        href: url,
        search: new URL(url).search,
        assigned: null,
        assign(next) { this.assigned = next; },
    };
    const window = {
        location,
        history: {
            replaceState(_state, _title, next) {
                location.href = next;
                location.search = new URL(next).search;
            },
        },
    };
    const values = {
        '$projectsJson': dataset,
        "json_encode($filters['search'], JSON_UNESCAPED_UNICODE)": search,
        "json_encode($filters['fiscal_year_id'], JSON_UNESCAPED_UNICODE)": fiscalYear,
        "json_encode($filters['department_id'], JSON_UNESCAPED_UNICODE)": '',
        "json_encode($filters['evaluation'] ?: 'all', JSON_UNESCAPED_UNICODE)": evaluation,
        'json_encode($fiscalYears, JSON_UNESCAPED_UNICODE)': [{ id: 1, year: 2570 }, { id: 2, year: 2571 }],
        "json_encode(\\App\\Core\\Router::url('/projects'), JSON_UNESCAPED_UNICODE)": '/projects',
    };
    const script = template.replace(/<\?=\s*([\s\S]*?)\s*\?>/g, (_match, expression) => {
        assert.ok(Object.hasOwn(values, expression), `Unexpected PHP value: ${expression}`);
        return JSON.stringify(values[expression]);
    });
    vm.runInNewContext(script, {
        window,
        document: { addEventListener() {} },
        URL,
        URLSearchParams,
        Intl,
    });
    const page = window.mainProjectsPage();
    page.$nextTick = callback => callback();
    page.scrollToTop = () => {};
    page.init();
    return { page, location };
}

let { page, location } = mount();
assert.equal(page.filteredProjects.length, 7);
assert.equal(page.totalPages, 2);
page.setPage(2);
assert.equal(page.currentPage, 2);
page.setEvaluationFilter('evaluated');
assert.equal(page.currentPage, 1);
assert.deepEqual(Array.from(page.filteredProjects, p => p.id), [1, 3, 5, 6]);
assert.equal(page.totalPages, 1);
assert.equal(page.totalFilteredSubCount, 2);
assert.equal(page.totalFilteredBudget, new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(1500));
assert.equal(new URL(location.href).searchParams.get('evaluation'), 'evaluated');
assert.equal(new URL(location.href).searchParams.has('page'), false);
assert.equal(page.paginatedIds.has(1), true);
assert.equal(page.paginatedIds.has(2), false);

page.setEvaluationFilter('ungraded');
assert.deepEqual(Array.from(page.filteredProjects, p => p.id), [2, 4, 7]);
assert.equal(page.totalFilteredSubCount, 3);
assert.equal(page.paginatedIds.has(1), false);
assert.equal(page.paginatedIds.has(2), true);
page.setEvaluationFilter('all');
assert.equal(page.filteredProjects.length, 7);
assert.equal(new URL(location.href).searchParams.has('evaluation'), false);

({ page, location } = mount({
    url: 'http://localhost/projects?search=road&status=completed&evaluation=ungraded',
    evaluation: 'ungraded',
    search: 'road',
}));
assert.deepEqual(Array.from(page.filteredProjects, p => p.id), [2, 7]);
assert.equal(page.completedCount, 2);
assert.equal(page.inProgressCount, 0);
page.setFiscalYearFilter(1);
assert.equal(page.currentPage, 1);
let next = new URL(location.assigned);
assert.equal(next.searchParams.get('fiscal_year_id'), '1');
assert.equal(next.searchParams.get('search'), 'road');
assert.equal(next.searchParams.get('status'), 'completed');
assert.equal(next.searchParams.get('evaluation'), 'ungraded');

({ page } = mount({
    url: next.toString(), evaluation: 'ungraded', search: 'road', fiscalYear: '1',
    dataset: projects.filter(project => project.fiscal_year_id === '1'),
}));
assert.deepEqual(Array.from(page.filteredProjects, p => p.id), [2]);
page.setEvaluationFilter('evaluated');
assert.deepEqual(Array.from(page.filteredProjects, p => p.id), [1, 5, 6]);
assert.equal(page.completedCount, 3);
assert.equal(page.totalFilteredBudget, new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(1200));

({ page, location } = mount({ url: 'http://localhost/projects?page=2&evaluation=ungraded', evaluation: 'ungraded' }));
assert.equal(page.currentPage, 1);
assert.equal(new URL(location.href).searchParams.has('page'), false);
page.search = 'no matching project';
page.currentPage = 1;
page.syncUrl();
assert.equal(page.filteredProjects.length, 0);
assert.equal(page.startIndex, 0);
assert.equal(page.endIndex, 0);
assert.equal(page.totalPages, 1);
page.resetFilters();
assert.equal(location.assigned, '/projects');

({ page, location } = mount({ url: 'http://localhost/projects?evaluation=unexpected', evaluation: 'all' }));
assert.equal(new URL(location.href).searchParams.has('evaluation'), false);
page.setEvaluationFilter('unexpected');
assert.equal(page.evaluationFilter, 'all');
assert.equal(page.filteredProjects.length, 7);

({ page } = mount({ url: 'http://localhost/projects?fiscal_year_id=99&evaluation=evaluated', evaluation: 'evaluated', fiscalYear: '99', dataset: [] }));
assert.equal(page.filteredProjects.length, 0);
assert.equal(page.totalPages, 1);
assert.equal(page.totalFilteredSubCount, 0);

console.log('Project evaluation Alpine checks passed');
