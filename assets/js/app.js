document.addEventListener("DOMContentLoaded", function () {
    if (window.lucide) {
        window.lucide.createIcons();
    }

    document.querySelectorAll(".js-nav-toggle").forEach(function (button) {
        var target = document.querySelector(button.dataset.target);

        if (!target) {
            return;
        }

        button.addEventListener("click", function () {
            var isOpen = target.classList.toggle("is-open");
            button.setAttribute("aria-expanded", String(isOpen));
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
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            }
        });
    });

    document.querySelectorAll(".js-table-search").forEach(function (input) {
        var table = document.querySelector(input.dataset.target);

        if (!table) {
            return;
        }

        var rows = Array.prototype.slice.call(table.querySelectorAll("tbody tr"));

        input.addEventListener("input", function () {
            var keyword = input.value.trim().toLowerCase();

            rows.forEach(function (row) {
                if (row.dataset.empty === "true") {
                    return;
                }

                row.hidden = keyword !== "" && !row.textContent.toLowerCase().includes(keyword);
            });
        });
    });

    document.querySelectorAll(".js-money-input").forEach(function (input) {
        var output = document.querySelector(input.dataset.preview);
        var formatter = new Intl.NumberFormat("id-ID");

        function updatePreview() {
            var value = Number(input.value || 0);
            output.textContent = value > 0 ? "Rp " + formatter.format(value) : "Masukkan nominal transaksi";
        }

        if (output) {
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
            if (!button || button.getAttribute("aria-disabled") === "true") {
                return;
            }

            button.dataset.originalText = button.innerHTML;
            button.innerHTML = button.dataset.loadingText || "Memproses...";
            button.setAttribute("aria-disabled", "true");
            button.style.pointerEvents = "none";
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
});
