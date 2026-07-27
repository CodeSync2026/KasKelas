document.addEventListener("DOMContentLoaded", function () {
    function refreshIcons() {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    function addSkipLink() {
        var main = document.querySelector("main");

        if (!main || document.querySelector(".skip-link")) {
            return;
        }

        if (!main.id) {
            main.id = "main-content";
        }

        var skipLink = document.createElement("a");
        skipLink.className = "skip-link";
        skipLink.href = "#" + main.id;
        skipLink.textContent = "Langsung ke konten utama";
        document.body.insertBefore(skipLink, document.body.firstChild);
    }

    function addAmbientArtwork() {
        var artwork = "" +
            '<svg class="ui-ambient-svg" viewBox="0 0 420 260" fill="none" aria-hidden="true" focusable="false" data-ui-ambient>' +
                '<circle class="ambient-glow" cx="286" cy="140" r="118"></circle>' +
                '<path class="ambient-track" d="M24 194C78 188 92 112 145 130C192 146 208 199 254 169C305 136 320 61 393 76"></path>' +
                '<path class="ambient-track" d="M26 218H394"></path>' +
                '<path class="ambient-trace" d="M24 194C78 188 92 112 145 130C192 146 208 199 254 169C305 136 320 61 393 76"></path>' +
                '<circle class="ambient-node" cx="145" cy="130" r="8"></circle>' +
                '<circle class="ambient-node is-delay-one" cx="254" cy="169" r="8"></circle>' +
                '<circle class="ambient-node is-delay-two" cx="393" cy="76" r="8"></circle>' +
                '<path class="ambient-spark" d="M99 56L103 68L115 72L103 76L99 88L95 76L83 72L95 68Z"></path>' +
                '<path class="ambient-spark" d="M322 188L325 197L334 200L325 203L322 212L319 203L310 200L319 197Z"></path>' +
            "</svg>";

        document.querySelectorAll(".hero-copy, .auth-visual").forEach(function (container) {
            if (!container.querySelector("[data-ui-ambient]")) {
                container.insertAdjacentHTML("afterbegin", artwork);
            }
        });
    }

    function syncNavToggle(button, isOpen) {
        var label = isOpen ? "Tutup menu" : "Buka menu";
        var icon = button.querySelector("[data-lucide]");

        button.setAttribute("aria-expanded", String(isOpen));
        button.setAttribute("aria-label", label);
        button.setAttribute("title", label);

        if (icon) {
            icon.setAttribute("data-lucide", isOpen ? "x" : "menu");
            refreshIcons();
        }
    }

    function markActiveNavigation() {
        var currentPage = window.location.pathname.split("/").pop() || "";

        document.querySelectorAll(".nav-links a[href]").forEach(function (link) {
            var href = link.getAttribute("href");

            if (!href || href.charAt(0) === "#" || href.indexOf("logout.php") !== -1) {
                return;
            }

            var linkPage = href.split("?")[0].split("#")[0].split("/").pop();

            if (linkPage && linkPage === currentPage) {
                link.classList.add("is-active");
                link.setAttribute("aria-current", "page");
            }
        });
    }

    refreshIcons();
    addSkipLink();
    addAmbientArtwork();
    markActiveNavigation();

    document.querySelectorAll(".js-nav-toggle").forEach(function (button) {
        var targetSelector = button.dataset.target;
        var target = targetSelector ? document.querySelector(targetSelector) : null;

        if (!target) {
            return;
        }

        if (!target.id) {
            target.id = targetSelector.replace("#", "");
        }

        button.setAttribute("aria-controls", target.id);
        syncNavToggle(button, target.classList.contains("is-open"));

        button.addEventListener("click", function () {
            var isOpen = target.classList.toggle("is-open");
            syncNavToggle(button, isOpen);
        });

        target.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                if (target.classList.contains("is-open")) {
                    target.classList.remove("is-open");
                    syncNavToggle(button, false);
                }
            });
        });

        document.addEventListener("click", function (event) {
            if (target.classList.contains("is-open") && !target.contains(event.target) && !button.contains(event.target)) {
                target.classList.remove("is-open");
                syncNavToggle(button, false);
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape" && target.classList.contains("is-open")) {
                target.classList.remove("is-open");
                syncNavToggle(button, false);
                button.focus();
            }
        });
    });

    document.querySelectorAll(".js-password-toggle").forEach(function (button) {
        var input = document.querySelector(button.dataset.target);

        if (!input) {
            return;
        }

        button.addEventListener("click", function () {
            var isPassword = input.type === "password";
            input.type = isPassword ? "text" : "password";
            button.setAttribute("aria-label", isPassword ? "Sembunyikan password" : "Tampilkan password");
            button.setAttribute("title", isPassword ? "Sembunyikan password" : "Tampilkan password");

            var icon = button.querySelector("[data-lucide]");
            if (icon) {
                icon.setAttribute("data-lucide", isPassword ? "eye-off" : "eye");
                refreshIcons();
            }
        });
    });

    document.querySelectorAll(".js-table-search").forEach(function (input) {
        var table = document.querySelector(input.dataset.target);

        if (!table) {
            return;
        }

        var rows = Array.prototype.slice.call(table.querySelectorAll("tbody tr"));
        var status = document.createElement("p");
        var statusId = (table.id || "table") + "-search-status";
        var existingDescription = input.getAttribute("aria-describedby");

        input.setAttribute("aria-label", input.getAttribute("placeholder") || "Cari data tabel");
        input.setAttribute("aria-describedby", [existingDescription, statusId].filter(Boolean).join(" "));
        status.className = "table-search-status";
        status.id = statusId;
        status.setAttribute("role", "status");
        status.setAttribute("aria-live", "polite");
        table.parentElement.insertAdjacentElement("afterend", status);

        function updateSearch() {
            var keyword = input.value.trim().toLowerCase();
            var matchingRows = 0;
            var hasData = false;

            rows.forEach(function (row) {
                if (row.dataset.empty === "true") {
                    row.hidden = keyword !== "";
                    return;
                }

                hasData = true;
                var isMatch = keyword === "" || row.textContent.toLowerCase().includes(keyword);
                row.hidden = !isMatch;

                if (isMatch) {
                    matchingRows += 1;
                }
            });

            if (keyword === "") {
                status.textContent = "";
                status.classList.remove("is-visible");
                return;
            }

            if (!hasData) {
                status.textContent = "Belum ada data untuk dicari.";
            } else if (matchingRows === 0) {
                status.textContent = "Tidak ada hasil. Coba kata kunci lain.";
            } else {
                status.textContent = matchingRows + " hasil ditemukan.";
            }

            status.classList.add("is-visible");
        }

        input.addEventListener("input", updateSearch);
        input.addEventListener("search", updateSearch);
    });

    document.querySelectorAll(".js-money-input").forEach(function (input) {
        var output = document.querySelector(input.dataset.preview);
        var formatter = new Intl.NumberFormat("id-ID");

        input.setAttribute("inputmode", "numeric");

        function updatePreview() {
            var value = Number(input.value || 0);
            output.textContent = value > 0 ? "Rp " + formatter.format(value) : "Masukkan nominal transaksi";
        }

        if (output) {
            output.setAttribute("role", "status");
            output.setAttribute("aria-live", "polite");
            updatePreview();
            input.addEventListener("input", updatePreview);
        }
    });

    document.querySelectorAll(".js-file-input").forEach(function (input) {
        var preview = document.querySelector(input.dataset.preview);

        if (!preview) {
            return;
        }

        var image = preview.querySelector("img");
        var name = preview.querySelector("[data-file-name]");
        var size = preview.querySelector("[data-file-size]");

        preview.setAttribute("aria-live", "polite");

        input.addEventListener("change", function () {
            var file = input.files && input.files[0];

            if (!file) {
                preview.classList.remove("is-visible");
                return;
            }

            if (image && file.type.startsWith("image/")) {
                image.src = URL.createObjectURL(file);
                image.onload = function () {
                    URL.revokeObjectURL(image.src);
                };
            }

            if (name) {
                name.textContent = file.name;
            }

            if (size) {
                size.textContent = Math.ceil(file.size / 1024) + " KB";
            }

            preview.classList.add("is-visible");
        });
    });

    document.querySelectorAll(".js-confirm").forEach(function (link) {
        link.addEventListener("click", function (event) {
            var message = link.dataset.message || "Lanjutkan aksi ini?";
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll("form[data-loading]").forEach(function (form) {
        form.addEventListener("submit", function () {
            var button = form.querySelector("[type='submit']");

            if (!button || button.disabled || button.getAttribute("aria-disabled") === "true") {
                return;
            }

            button.dataset.originalText = button.innerHTML;
            button.innerHTML = button.dataset.loadingText || "Memproses...";
            button.setAttribute("aria-disabled", "true");
            form.setAttribute("aria-busy", "true");
            setTimeout(function () {
                button.disabled = true;
            }, 0);
        });
    });

    document.querySelectorAll(".js-clock").forEach(function (clock) {
        var formatter = new Intl.DateTimeFormat("id-ID", {
            weekday: "long",
            day: "2-digit",
            month: "long",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit"
        });

        function tick() {
            clock.textContent = formatter.format(new Date());
        }

        tick();
        window.setInterval(tick, 30000);
    });

    var prefersReducedMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (!prefersReducedMotion) {
        window.requestAnimationFrame(function () {
            document.body.classList.add("is-enhanced");
        });
    }
});
