import './bootstrap';

import {
    Chart, BarController, LineController, RadarController, BarElement, LineElement,
    PointElement, CategoryScale, LinearScale, RadialLinearScale, Tooltip, Legend,
    PieController,
} from 'chart.js';

Chart.register(
    BarController, LineController, RadarController, BarElement, LineElement, PieController,
    PointElement, CategoryScale, LinearScale, RadialLinearScale, Tooltip, Legend,
);

const pln = new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'PLN' });
// short axis labels ("1,2 tys. zł") so the y axis doesn't eat half of a phone screen
const plnCompact = new Intl.NumberFormat('pl-PL', { style: 'currency', currency: 'PLN', notation: 'compact' });
const token = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();
function createAdminChart(canvas, {type = 'bar', labels, values, label, money = false}){
    const color = token('--color-brand-accent') || '#3b82f6';
    const format = (v) => (money ? pln.format(v): v);
    const formatTick = (v) => (money ? plnCompact.format(v) : v);
    return new Chart( canvas, {
        type,
        data: {
            labels,
            datasets: [{
                label,
                data: values,
                backgroundColor: color,
                borderColor: color,
                borderRadius: 4,
                tension: 0.3,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            // show the tooltip for the hovered column, no need to hit the bar/point exactly
            interaction: {mode: 'index', intersect: false},
            plugins: {
                legend: {display: true},
                tooltip: {callbacks: {label: (ctx) => `${label}: ${format(ctx.raw)}` } }
            },
            scales: type === 'radar'
                ? {r: {beginAtZero: true, ticks: {precision: 0, callback: formatTick}}}
                : {
                    x: {ticks: {autoSkip: true, maxRotation: 0}},
                    y: {beginAtZero: true, ticks: {precision: 0, callback: formatTick}},
                }
        }
    })
}
document.addEventListener('alpine:init', () => {
    window.Alpine.data('statsChart', (config) => {
        let chart = null; // deliberately NOT a reactive Alpine property

        return {
            init() {
                chart = createAdminChart(this.$refs.canvas, config);
            },
            destroy() {
                chart?.destroy();
            },
        };
    });
});