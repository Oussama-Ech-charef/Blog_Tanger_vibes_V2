(function () {
    "use strict";

    function createPlaceholder(img) {
        if (img.hasAttribute("data-fallback")) return;
        img.setAttribute("data-fallback", "1");

        var alt = img.getAttribute("alt") || "";

        var outer = document.createElement("div");
        outer.className = "img-placeholder";
        outer.setAttribute("role", "img");
        outer.setAttribute("aria-label", alt);

        var card = document.createElement("div");
        card.className = "img-placeholder-card";

        var iconWrap = document.createElement("div");
        iconWrap.className = "img-placeholder-icon";

        var icon = document.createElement("i");
        icon.className = "fa-solid fa-image";
        icon.setAttribute("aria-hidden", "true");

        var title = document.createElement("span");
        title.className = "img-placeholder-title";
        title.textContent = "Image Unavailable";

        var desc = document.createElement("span");
        desc.className = "img-placeholder-desc";
        desc.textContent = "This image is currently unavailable.";

        var hint = document.createElement("span");
        hint.className = "img-placeholder-hint";
        hint.textContent = "Please check again later.";

        iconWrap.appendChild(icon);
        card.appendChild(iconWrap);
        card.appendChild(title);
        card.appendChild(desc);
        card.appendChild(hint);
        outer.appendChild(card);

        var parent = img.parentNode;
        if (parent) parent.replaceChild(outer, img);
    }

    document.addEventListener("DOMContentLoaded", function () {
        var imgs = document.querySelectorAll("img");
        for (var i = 0; i < imgs.length; i++) {
            if (imgs[i].complete && imgs[i].naturalWidth === 0) {
                createPlaceholder(imgs[i]);
            }
        }
    });

    window.addEventListener(
        "error",
        function (e) {
            if (e.target instanceof HTMLImageElement) {
                createPlaceholder(e.target);
            }
        },
        true
    );
})();
