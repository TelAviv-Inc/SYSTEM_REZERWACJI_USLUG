import "./bootstrap";

import {
    Chart,
    BarController,
    LineController,
    RadarController,
    BarElement,
    LineElement,
    PointElement,
    CategoryScale,
    LinearScale,
    RadialLinearScale,
    Tooltip,
    Legend,
    PieController,
} from "chart.js";

Chart.register(
    BarController,
    LineController,
    RadarController,
    BarElement,
    LineElement,
    PieController,
    PointElement,
    CategoryScale,
    LinearScale,
    RadialLinearScale,
    Tooltip,
    Legend,
);

const pln = new Intl.NumberFormat("pl-PL", {
    style: "currency",
    currency: "PLN",
});
// short axis labels ("1,2 tys. zł") so the y axis doesn't eat half of a phone screen
const plnCompact = new Intl.NumberFormat("pl-PL", {
    style: "currency",
    currency: "PLN",
    notation: "compact",
});
const token = (name) =>
    getComputedStyle(document.documentElement).getPropertyValue(name).trim();
function createAdminChart(
    canvas,
    { type = "bar", labels, values, label, money = false },
) {
    const color = token("--color-brand-accent") || "#3b82f6";
    const format = (v) => (money ? pln.format(v) : v);
    const formatTick = (v) => (money ? plnCompact.format(v) : v);
    return new Chart(canvas, {
        type,
        data: {
            labels,
            datasets: [
                {
                    label,
                    data: values,
                    backgroundColor: color,
                    borderColor: color,
                    borderRadius: 4,
                    tension: 0.3,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            // show the tooltip for the hovered column, no need to hit the bar/point exactly
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: { display: true },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${label}: ${format(ctx.raw)}`,
                    },
                },
            },
            scales:
                type === "radar"
                    ? {
                          r: {
                              beginAtZero: true,
                              ticks: { precision: 0, callback: formatTick },
                          },
                      }
                    : {
                          x: { ticks: { autoSkip: true, maxRotation: 0 } },
                          y: {
                              beginAtZero: true,
                              ticks: { precision: 0, callback: formatTick },
                          },
                      },
        },
    });
}
// Report charts: several series (e.g. reservations by status), optional stacking and
// horizontal bars for rankings. Always one value axis - income and counts get separate charts.
function createReportChart(
    canvas,
    {
        type = "bar",
        labels,
        datasets,
        money = false,
        horizontal = false,
        stacked = false,
    },
) {
    const accent = token("--color-brand-accent") || "#2563eb";
    const grid = token("--color-brand-border") || "#e2e8f0";
    const muted = token("--color-brand-muted") || "#64748b";
    const format = (v) => (money ? pln.format(v) : v);
    const formatTick = (v) => (money ? plnCompact.format(v) : v);
    const valueAxis = {
        beginAtZero: true,
        stacked,
        grid: { color: grid },
        border: { display: false },
        ticks: {
            precision: 0,
            color: muted,
            callback: formatTick,
            maxRotation: 0,
            maxTicksLimit: horizontal ? 5 : 8,
        },
    };
    const categoryAxis = {
        stacked,
        grid: { display: false },
        ticks: { autoSkip: true, maxRotation: 0, color: muted },
    };
    // long names on a horizontal ranking are cut, the tooltip shows them in full
    if (horizontal) {
        categoryAxis.ticks.callback = function (value) {
            const text = this.getLabelForValue(value);
            return text.length > 22 ? `${text.slice(0, 21)}…` : text;
        };
    }

    return new Chart(canvas, {
        type,
        data: {
            labels,
            datasets: datasets.map(({ label, values, color }) => ({
                label,
                data: values,
                backgroundColor: color || accent,
                borderColor: stacked ? "#ffffff" : color || accent,
                // stacked segments get a thin white gap instead of rounded ends
                borderWidth: type === "line" ? 2 : stacked ? 1 : 0,
                borderRadius: stacked ? 0 : 4,
                maxBarThickness: 28,
                pointRadius: type === "line" ? 3 : 0,
                pointHoverRadius: 5,
                // monotone: the curve never swings below 0 zł between two points
                cubicInterpolationMode: "monotone",
            })),
        },
        options: {
            indexAxis: horizontal ? "y" : "x",
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: "index",
                intersect: false,
                axis: horizontal ? "y" : "x",
            },
            plugins: {
                // one series: the card title names it, so no legend box
                legend: {
                    display: datasets.length > 1,
                    position: "bottom",
                    labels: { boxWidth: 12, color: muted },
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) =>
                            `${ctx.dataset.label}: ${format(ctx.raw)}`,
                    },
                },
            },
            scales: horizontal
                ? { x: valueAxis, y: categoryAxis }
                : { x: categoryAxis, y: valueAxis },
        },
    });
}

document.addEventListener("alpine:init", () => {
    window.Alpine.data("reportChart", (config) => {
        let chart = null; // deliberately NOT a reactive Alpine property

        return {
            init() {
                chart = createReportChart(this.$refs.canvas, config);
            },
            destroy() {
                chart?.destroy();
            },
        };
    });

    window.Alpine.data("statsChart", (config) => {
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
