// Dùng window.$ và window.$$ để tránh lỗi declare 2 lần
window.$ = document.querySelector.bind(document);
window.$$ = document.querySelectorAll.bind(document);

/**
 * Hàm tải template
 */
function load(selector, path) {
    const cached = localStorage.getItem(path);
    if (cached) {
        $(selector).innerHTML = cached;
    }

    fetch(path)
        .then((res) => res.text())
        .then((html) => {
            if (html !== cached) {
                $(selector).innerHTML = html;
                localStorage.setItem(path, html);
            }
        })
        .finally(() => {
            window.dispatchEvent(new Event("template-loaded"));
        });
}

/**
 * Hàm kiểm tra một phần tử có bị ẩn không
 */
function isHidden(element) {
    if (!element) return true;
    if (window.getComputedStyle(element).display === "none") return true;
    let parent = element.parentElement;
    while (parent) {
        if (window.getComputedStyle(parent).display === "none") return true;
        parent = parent.parentElement;
    }
    return false;
}

/**
 * Hàm debounce
 */
function debounce(func, timeout = 300) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            func.apply(this, args);
        }, timeout);
    };
}

/**
 * Hàm tính toán vị trí arrow cho dropdown
 */
const calArrowPos = debounce(() => {
    const dropdownList = $(".js-dropdown-list");
    if (!dropdownList || isHidden(dropdownList)) return;
    const items = $$(".js-dropdown-list > li");
    items.forEach((item) => {
        const arrowPos = item.offsetLeft + item.offsetWidth / 2;
        item.style.setProperty("--arrow-left-pos", `${arrowPos}px`);
    });
});

window.addEventListener("resize", calArrowPos);
window.addEventListener("template-loaded", calArrowPos);

/**
 * Giữ active menu khi hover
 * SỬA: Thêm một khoảng trễ nhỏ để tránh việc menu biến mất quá nhanh khi di chuột qua khoảng hở
 */
window.addEventListener("template-loaded", handleActiveMenu);

function handleActiveMenu() {
    const dropdowns = $$(".js-dropdown");
    const activeClass = "menu-column__item--active";
    let timeoutId = null;

    dropdowns.forEach((dropdown) => {
        const menu = dropdown.querySelector(".js-menu-list");
        if (!menu) return;
        const items = Array.from(menu.children);

        const setActive = (item) => {
            items.forEach((i) => i.classList.remove(activeClass));
            item.classList.add(activeClass);
        };

        // Default active
        if (window.innerWidth > 991 && items.length) {
            items[0].classList.add(activeClass);
        }

        // Hover từng item
        items.forEach((item) => {
            item.onmouseenter = () => {
                if (window.innerWidth <= 991) return;
                setActive(item);
            };
        });

        dropdown.onmouseenter = () => clearTimeout(timeoutId);

        dropdown.onmouseleave = (e) => {
            if (window.innerWidth <= 991) return;

            const related = e.relatedTarget;
            if (related && dropdown.contains(related)) return; // vẫn trong dropdown

            // Không reset toàn bộ, chỉ set lại item đầu nếu muốn
            timeoutId = setTimeout(() => {
                if (items.length) setActive(items[0]);
            }, 200);
        };
    });
}
/**
 * JS toggle - SỬA LỖI QUAN TRỌNG:
 * 1. Ngừng dùng button.click() trong document.onclick (gây vòng lặp)
 * 2. Dùng cơ chế kiểm tra e.target chuẩn xác
 */
window.addEventListener("template-loaded", initJsToggle);

function initJsToggle() {
    const toggles = $$(".js-toggle");

    toggles.forEach((button) => {
        const targetSelector = button.getAttribute("toggle-target");
        if (!targetSelector) return;
        const target = $(targetSelector);

        button.onclick = (e) => {
            e.preventDefault();
            e.stopPropagation(); // Chặn nổi bọt để document.onclick không chạy ngay

            if (!target) return;
            const isShowing = target.classList.contains("show");

            // Đóng các dropdown khác đang mở (nếu có)
            $$(".js-toggle").forEach((btn) => {
                const otherTarget = $(btn.getAttribute("toggle-target"));
                if (otherTarget && otherTarget !== target) {
                    otherTarget.classList.replace("show", "hide");
                }
            });

            // Toggle trạng thái của target hiện tại
            if (isShowing) {
                target.classList.replace("show", "hide");
            } else {
                target.classList.replace("hide", "show");
            }
        };
    });

    // Sự kiện Click Outside: Đóng menu nếu click ra ngoài vùng nút và vùng menu
    document.addEventListener("click", (e) => {
        const openedDropdown = $(".show"); // Tìm dropdown nào đang mở
        if (!openedDropdown) return;

        // Nếu click không phải vào nút toggle VÀ không phải vào bên trong dropdown đang mở
        const isClickOnToggle = e.target.closest(".js-toggle");
        const isClickInsideDropdown = e.target.closest(".show");

        if (!isClickOnToggle && !isClickInsideDropdown) {
            openedDropdown.classList.replace("show", "hide");
        }
    });
}

/**
 * Menu mobile toggle cho mobile
 */
window.addEventListener("template-loaded", () => {
    const links = $$(".js-dropdown-list > li > a");
    links.forEach((link) => {
        link.onclick = (e) => {
            if (window.innerWidth > 991) return;
            const item = link.closest("li");
            item.classList.toggle("navbar__item--active");
        };
    });
});

/**
 * Tabs logic
 */
window.addEventListener("template-loaded", () => {
    const tabsSelector = "prod-tab__item";
    const contentsSelector = "prod-tab__content";
    const tabActive = `${tabsSelector}--current`;
    const contentActive = `${contentsSelector}--current`;

    const tabContainers = $$(".js-tabs");
    tabContainers.forEach((tabContainer) => {
        const tabs = tabContainer.querySelectorAll(`.${tabsSelector}`);
        const contents = tabContainer.querySelectorAll(`.${contentsSelector}`);
        tabs.forEach((tab, index) => {
            tab.onclick = () => {
                tabContainer
                    .querySelector(`.${tabActive}`)
                    ?.classList.remove(tabActive);
                tabContainer
                    .querySelector(`.${contentActive}`)
                    ?.classList.remove(contentActive);
                tab.classList.add(tabActive);
                contents[index].classList.add(contentActive);
            };
        });
    });
});

/**
 * Theme Switcher
 */
window.addEventListener("template-loaded", () => {
    const switchBtn = document.querySelector("#switch-theme-btn");
    if (switchBtn) {
        const updateText = (isDark) => {
            const span = switchBtn.querySelector("span");
            if (span) span.textContent = isDark ? "Light mode" : "Dark mode";
        };

        switchBtn.onclick = function () {
            const isDark = localStorage.dark === "true";
            document.documentElement.classList.toggle("dark", !isDark);
            localStorage.setItem("dark", !isDark);
            updateText(!isDark);
        };

        updateText(localStorage.dark === "true");
    }
});

// Khởi tạo theme ban đầu (Dùng documentElement cho chuẩn HTML dark class)
const isDarkInitial = localStorage.dark === "true";
document.documentElement.classList.toggle("dark", isDarkInitial);
