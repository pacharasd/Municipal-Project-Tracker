const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../resources/views/dashboard/index.blade.php'), 'utf8');
const script = source.match(/^<script[^\r\n]*\r?\n([\s\S]*?)^<\/script>/m)?.[1];
assert.ok(script, 'Dashboard Chart.js script was not found');

function mount(distribution) {
    const charts = new Map();
    const instances = [];
    const events = new Map();
    const gradeMeta = {
        'A+': { label: 'ดีเยี่ยมมาก', color: '#10b981' },
        A: { label: 'ดีเยี่ยม', color: '#0ea5e9' },
        B: { label: 'ดี', color: '#6366f1' },
        C: { label: 'พอใช้', color: '#f59e0b' },
        D: { label: 'ต้องปรับปรุง', color: '#f43f5e' },
        ungraded: { label: 'ยังไม่ประเมิน', color: '#94a3b8' }
    };
    const makeCanvas = counts => ({
        id: 'gradeDonutChart',
        dataset: { gradeDistribution: JSON.stringify(counts), gradeMeta: JSON.stringify(gradeMeta) }
    });
    let canvas = makeCanvas(distribution);
    let dark = false;

    class ChartMock {
        static defaults = {};
        static getChart(target) {
            return charts.get(typeof target === 'string' ? document.getElementById(target) : target);
        }

        constructor(target, config) {
            this.target = target;
            this.data = config.data;
            this.options = config.options;
            this.type = config.type;
            this.destroyed = false;
            instances.push(this);
            charts.set(target, this);
        }

        destroy() {
            this.destroyed = true;
            charts.delete(this.target);
        }

        update() {}
    }

    const window = {
        addEventListener(name, callback) { events.set(name, callback); }
    };
    const document = {
        readyState: 'complete',
        documentElement: { classList: { contains() { return dark; } } },
        getElementById(id) { return id === canvas.id ? canvas : null; }
    };
    vm.runInNewContext(script, {
        Chart: ChartMock,
        document,
        window,
        console,
        setTimeout(callback) { callback(); return 1; },
        clearTimeout() {}
    });

    return {
        chart() { return charts.get(canvas); },
        instances,
        window,
        setDark(value) { dark = value; },
        navigate(counts) { canvas = makeCanvas(counts); window.initDashboardCharts(); },
        events
    };
}

const mounted = mount({ 'A+': 2, A: 1, B: 0, C: 0, D: 0, ungraded: 1 });
assert.equal(mounted.chart().type, 'doughnut');
assert.deepEqual(Array.from(mounted.chart().data.datasets[0].data), [2, 1, 1]);
assert.equal(mounted.chart().options.plugins.tooltip.callbacks.label({ raw: 2, label: 'เกรด A+' }), ' เกรด A+: 2 โครงการ (50.0%)');

const first = mounted.chart();
assert.equal(mounted.window._gradeDonutChartInstance, first);
mounted.setDark(true);
mounted.window.updateChartsThemeSynchronously();
assert.equal(mounted.chart().data.datasets[0].borderColor, '#161922');
mounted.window.initDashboardCharts();
assert.equal(first.destroyed, true);
assert.equal(mounted.instances.filter(chart => !chart.destroyed).length, 1);
const second = mounted.chart();
mounted.navigate({ 'A+': 0, A: 0, B: 1, C: 0, D: 0, ungraded: 0 });
assert.equal(second.destroyed, true);
assert.deepEqual(Array.from(mounted.chart().data.datasets[0].data), [1]);
assert.equal(mounted.instances.filter(chart => !chart.destroyed).length, 1);
mounted.events.get('theme-changed')();
assert.equal(mounted.chart().data.datasets[0].borderColor, '#161922');

const empty = mount({ 'A+': 0, A: 0, B: 0, C: 0, D: 0, ungraded: 0 });
assert.deepEqual(Array.from(empty.chart().data.datasets[0].data), [1]);
assert.equal(empty.chart().options.plugins.tooltip.enabled, false);

console.log('Dashboard grade chart lifecycle tests passed');
