import { animate, inView } from "motion";

document.addEventListener("DOMContentLoaded", () => {
    const animOptions = { duration: 0.7, easing: [0.17, 0.55, 0.55, 1] };
    const scaleOptions = { duration: 0.6, easing: [0.17, 0.55, 0.55, 1] };

    document.querySelectorAll(
        ".motion-reveal, .motion-reveal-left, .motion-reveal-right, .motion-reveal-scale"
    ).forEach((el) => {
        let keyframes;
        let options;

        if (el.classList.contains("motion-reveal-left")) {
            keyframes = { opacity: [0, 1], x: [-40, 0] };
            options = animOptions;
        } else if (el.classList.contains("motion-reveal-right")) {
            keyframes = { opacity: [0, 1], x: [40, 0] };
            options = animOptions;
        } else if (el.classList.contains("motion-reveal-scale")) {
            keyframes = { opacity: [0, 1], scale: [0.95, 1] };
            options = scaleOptions;
        } else {
            keyframes = { opacity: [0, 1], y: [40, 0] };
            options = animOptions;
        }

        inView(
            el,
            () => animate(el, keyframes, options),
            { amount: 0.2, once: true }
        );
    });
});
