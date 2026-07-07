import { animate, inView } from "motion";

document.addEventListener("DOMContentLoaded", () => {
    const animOptions = { duration: 0.7, easing: [0.17, 0.55, 0.55, 1] };

    document.querySelectorAll(".motion-reveal, .motion-reveal-left").forEach((el) => {
        const isLeft = el.classList.contains("motion-reveal-left");
        inView(
            el,
            () => animate(
                el,
                isLeft ? { opacity: [0, 1], x: [-30, 0] } : { opacity: [0, 1], y: [40, 0] },
                animOptions
            ),
            { amount: 0.2, once: true }
        );
    });
});
