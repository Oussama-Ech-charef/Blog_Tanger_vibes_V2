import { animate, inView } from "motion";

function parseCounterValue(text) {
    let raw = text.trim();
    let suffix = "";
    let multiplier = 1;
    let decimals = 0;

    if (raw.endsWith("%")) {
        suffix = "%";
        raw = raw.slice(0, -1);
    } else if (/[Kk]$/.test(raw)) {
        suffix = "K";
        raw = raw.slice(0, -1);
        multiplier = 1000;
    } else if (/[Mm]$/.test(raw)) {
        suffix = "M";
        raw = raw.slice(0, -1);
        multiplier = 1000000;
    } else if (raw.endsWith("+")) {
        suffix = "+";
        raw = raw.slice(0, -1);
    }

    const hasCommas = /,/.test(raw);
    raw = raw.replace(/,/g, "");

    if (raw.includes(".")) {
        decimals = raw.split(".")[1].length;
    }

    const num = parseFloat(raw);
    if (isNaN(num)) return null;

    return { target: num * multiplier, suffix, decimals, hasCommas };
}

function formatCounterValue(value, info) {
    let display;
    const effective = info.suffix === "K" ? value / 1000 : info.suffix === "M" ? value / 1000000 : value;

    if (info.decimals > 0) {
        display = effective.toFixed(info.decimals);
    } else {
        display = Math.round(effective).toString();
    }

    if (info.hasCommas) {
        const parts = display.split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        display = parts.join(".");
    }

    if (info.suffix) display += info.suffix;
    return display;
}

document.addEventListener("DOMContentLoaded", () => {
    const counters = document.querySelectorAll("[data-counter]");
    if (!counters.length) return;

    counters.forEach((el) => {
        const originalText = el.textContent.trim();
        const info = parseCounterValue(originalText);
        if (!info) return;

        el.textContent = "0";

        inView(
            el,
            () => {
                animate(0, info.target, {
                    duration: 2,
                    ease: "circOut",
                    onUpdate: (latest) => {
                        el.textContent = formatCounterValue(latest, info);
                    },
                });
            },
            { amount: 0.5, once: true }
        );
    });
});
