document.addEventListener("DOMContentLoaded", function () {
    // ====================== SLIDESHOW ======================
    const inner = document.getElementById("slideshowInner");
    if (inner) {
        const slides = document.querySelectorAll(".slideshow__item");
        const prevBtn = document.getElementById("prevBtn");
        const nextBtn = document.getElementById("nextBtn");
        const currentEl = document.getElementById("current");
        const totalEl = document.getElementById("total");

        let currentIndex = 1;
        const totalRealSlides = slides.length;
        let isTransitioning = false;

        if (totalEl) totalEl.textContent = totalRealSlides;

        const firstClone = slides[0].cloneNode(true);
        const lastClone = slides[slides.length - 1].cloneNode(true);
        inner.appendChild(firstClone);
        inner.insertBefore(lastClone, slides[0]);

        const totalSlidesWithClones = inner.children.length;

        function setInitialPosition() {
            inner.style.transform = `translateX(-${currentIndex * 100}%)`;
        }
        setInitialPosition();

        function updateCounter() {
            let realIndex = currentIndex;
            if (currentIndex === 0) realIndex = totalRealSlides;
            if (currentIndex === totalSlidesWithClones - 1) realIndex = 1;
            if (currentEl) currentEl.textContent = realIndex;
        }

        function goToSlide(index, instant = false) {
            if (isTransitioning) return;
            isTransitioning = true;
            inner.style.transition = instant
                ? "none"
                : "transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94)";
            inner.style.transform = `translateX(-${index * 100}%)`;
            currentIndex = index;
        }

        inner.addEventListener("transitionend", () => {
            isTransitioning = false;
            if (currentIndex === totalSlidesWithClones - 1) currentIndex = 1;
            else if (currentIndex === 0) currentIndex = totalRealSlides;

            inner.style.transition = "none";
            inner.style.transform = `translateX(-${currentIndex * 100}%)`;
            setTimeout(() => {
                inner.style.transition =
                    "transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94)";
            }, 20);
            updateCounter();
        });

        nextBtn?.addEventListener("click", () => {
            if (!isTransitioning) goToSlide(currentIndex + 1);
        });
        prevBtn?.addEventListener("click", () => {
            if (!isTransitioning) goToSlide(currentIndex - 1);
        });

        let autoPlayInterval;
        function startAutoPlay() {
            autoPlayInterval = setInterval(() => {
                if (!isTransitioning) goToSlide(currentIndex + 1);
            }, 6000);
        }
        function stopAutoPlay() {
            clearInterval(autoPlayInterval);
        }
        startAutoPlay();
        const slideshow = document.querySelector(".slideshow");
        if (slideshow) {
            slideshow.addEventListener("mouseenter", stopAutoPlay);
            slideshow.addEventListener("mouseleave", startAutoPlay);
        }
        updateCounter();
    }

    // ====================== MODAL HELPER ======================
    function openModal(id) {
        const modal = document.querySelector(id);
        if (!modal) return;
        modal.classList.remove("hide");
        modal.classList.add("show");
    }
    function closeModal(id) {
        const modal = document.querySelector(id);
        if (!modal) return;
        modal.classList.remove("hide", "show");
        modal.classList.add("hide");
    }

    // ====================== FORMAT MONEY ======================
    function formatVND(money) {
        return money.toLocaleString("vi-VN") + "đ";
    }

    // ====================== UPDATE CART SUMMARY ======================
    function updateCartSummary() {
        let subtotal = 0;
        let itemCount = 0;

        document.querySelectorAll(".cart-item").forEach((article) => {
            const id = article.id.replace("item-", "");
            const qtyEl = document.getElementById(`qty-${id}`);
            const totalEl = document.getElementById(`total-${id}`);

            if (qtyEl && totalEl) {
                const qty = parseInt(qtyEl.textContent) || 0;
                const priceStr = totalEl.textContent.replace(/[^0-9]/g, "");
                const itemTotal = parseInt(priceStr) || 0;

                subtotal += itemTotal;
                itemCount += qty;
            }
        });

        const cartCountEl = document.getElementById("cart-count");
        const cartSubtotalEl = document.getElementById("cart-subtotal");
        const cartTotalEl = document.getElementById("cart-total");

        if (cartCountEl) cartCountEl.textContent = itemCount;
        if (cartSubtotalEl) cartSubtotalEl.innerHTML = formatVND(subtotal);
        if (cartTotalEl) cartTotalEl.innerHTML = formatVND(subtotal + 10000);
    }

    // ====================== PLUS / MINUS ======================
    document.querySelectorAll(".plus, .minus").forEach((btn) => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            const id = this.dataset.id;
            const isMinus = this.classList.contains("minus");
            const qtyEl = document.getElementById(`qty-${id}`);
            const currentQty = parseInt(qtyEl?.textContent || 0);

            if (isMinus && currentQty === 1) {
                deleteId = id;
                const modal = document.querySelector("#delete-confirm");
                modal?.classList.remove("hide");
                return;
            }

            const action = isMinus ? "minus" : "plus";

            fetch("index.php?url=update-cart", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `id=${id}&action=${action}`,
            })
                .then((res) => res.json())
                .then((data) => {
                    if (!data.success) return;
                    if (qtyEl) qtyEl.textContent = data.quantity;
                    const totalEl = document.getElementById(`total-${id}`);
                    if (totalEl) totalEl.innerHTML = formatVND(data.itemTotal);
                    updateCartSummary();
                })
                .catch(() => showToast("Lỗi khi cập nhật giỏ hàng", "error"));
        });
    });

    // ====================== CART DELETE ======================
    let deleteId = null;
    document.querySelectorAll(".btn-delete").forEach((btn) => {
        btn.addEventListener("click", function () {
            deleteId = this.dataset.id;
            openModal("#delete-confirm");
        });
    });

    const confirmDeleteBtn = document.getElementById("confirm-delete-btn");
    if (confirmDeleteBtn) {
        confirmDeleteBtn.onclick = function () {
            if (!deleteId) return;
            fetch(`index.php?url=remove-cart&id=${deleteId}`)
                .then((res) => res.json())
                .then((data) => {
                    if (data.success) {
                        const item = document.getElementById(
                            `item-${deleteId}`,
                        );
                        const list = item?.closest(".cart-info");
                        if (item) item.remove();
                        const remainItems =
                            list?.querySelectorAll(".cart-item");
                        if (!remainItems || remainItems.length === 0) {
                            list.innerHTML = `<p class="text-center py-5 fs-4 cart-info__list--empty">
                                <img src="/DoAn/DoAnTotNghiep/assets/img/empty-cart.png" alt="Empty cart" class="mb-4" />
                                <a href="/index.php" class="btn btn--primary mt-4" style="margin: 20px 0 0;">Tiếp tục mua sắm</a>
                            </p>`;
                        }
                        closeModal("#delete-confirm");
                        updateCartSummary();
                        showToast("Đã xóa 🗑️", "success");
                        deleteId = null;
                    }
                })
                .catch(() => showToast("Lỗi khi xóa", "error"));
        };
    }

    // ====================== DELETE FAVORITE ======================
    let deleteFavId = null;
    document.querySelectorAll(".btn-delete-fav").forEach((btn) => {
        btn.addEventListener("click", function () {
            deleteFavId = this.closest(".cart-item")?.dataset.id;
            openModal("#delete-fav-confirm");
        });
    });

    const confirmDeleteFavBtn = document.getElementById("confirm-delete-fav");
    if (confirmDeleteFavBtn) {
        confirmDeleteFavBtn.addEventListener("click", function () {
            if (!deleteFavId) return;
            fetch(`index.php?url=remove-favorite&id=${deleteFavId}&ajax=1`)
                .then((res) => res.json())
                .then((data) => {
                    if (data.success) {
                        const item = document.querySelector(
                            `.cart-item[data-id='${deleteFavId}']`,
                        );
                        if (item) item.remove();
                        const list = document.querySelector(".cart-info__list");
                        if (list && list.children.length === 0) {
                            const container =
                                document.querySelector(".cart-info");
                            if (container) {
                                container.innerHTML = `<h1 class="cart-info__heading">Favorite List</h1>
                                <div class="favorites-empty text-center" style="padding: 50px 0;">
                                    <img src="/DoAn/DoAnTotNghiep/public/assets/img/empty-favorites.png" style="max-width:200px;margin-bottom:20px;">
                                    <p>Bạn không có sản phẩm yêu thích nào.</p>
                                    <a href="/DoAn/DoAnTotNghiep/public/" class="btn btn--primary">Explore Products</a>
                                </div>`;
                            }
                        }
                        closeModal("#delete-fav-confirm");
                        showToast("Đã xóa khỏi yêu thích 🗑️", "success");
                        deleteFavId = null;
                    }
                })
                .catch(() => showToast("Lỗi khi xóa favorite", "error"));
        });
    }

    document
        .querySelectorAll("#delete-fav-confirm .js-toggle")
        .forEach((btn) => {
            btn.addEventListener("click", () => {
                const modal = document.getElementById("delete-fav-confirm");
                modal.classList.remove("show");
                modal.classList.add("hide");
                deleteFavId = null;
            });
        });

    // ====================== CHECK ALL ======================
    const checkAll = document.getElementById("check-all");
    const deleteAllBtn = document.getElementById("delete-all-btn");
    if (checkAll) {
        checkAll.addEventListener("change", function () {
            const checked = this.checked;
            document
                .querySelectorAll(".cart-item .cart-info__checkbox-input")
                .forEach((cb) => {
                    cb.checked = checked;
                });
            if (deleteAllBtn) deleteAllBtn.classList.toggle("d-none", !checked);
        });
    }

    document
        .querySelectorAll(".cart-item .cart-info__checkbox-input")
        .forEach((cb) => {
            cb.addEventListener("change", () => {
                const allItems = document.querySelectorAll(
                    ".cart-item .cart-info__checkbox-input",
                );
                const checkedItems = document.querySelectorAll(
                    ".cart-item .cart-info__checkbox-input:checked",
                );
                if (deleteAllBtn)
                    deleteAllBtn.classList.toggle(
                        "d-none",
                        checkedItems.length === 0,
                    );
                if (checkAll)
                    checkAll.checked = checkedItems.length === allItems.length;
            });
        });

    if (deleteAllBtn) {
        deleteAllBtn.addEventListener("click", function () {
            const selected = [];
            document.querySelectorAll(".cart-item").forEach((item) => {
                const checkbox = item.querySelector(
                    ".cart-info__checkbox-input",
                );
                if (checkbox && checkbox.checked)
                    selected.push(item.dataset.id);
            });
            if (selected.length === 0) {
                showToast("Chưa chọn sản phẩm", "error");
                return;
            }
            fetch("index.php?url=delete-all-favorite", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ ids: selected }),
            })
                .then((res) => res.json())
                .then((data) => {
                    if (data.success) {
                        selected.forEach((id) => {
                            const el = document.querySelector(
                                `.cart-item[data-id="${id}"]`,
                            );
                            if (el) el.remove();
                        });
                        if (
                            document.querySelectorAll(".cart-item").length === 0
                        ) {
                            document.querySelector(".cart-info").innerHTML =
                                `<div class="favorites-empty text-center" style="padding: 50px 0;">
                                <img src="/DoAn/DoAnTotNghiep/public/assets/img/empty-favorites.png" style="max-width:200px;margin-bottom:20px;">
                                <p>Không còn sản phẩm yêu thích</p>
                                <a href="/DoAn/DoAnTotNghiep/public/" class="btn btn--primary">Explore</a>
                            </div>`;
                        }
                        if (deleteAllBtn) deleteAllBtn.classList.add("d-none");
                        if (checkAll) checkAll.checked = false;
                        showToast("Đã xóa tất cả 🗑️", "success");
                    }
                })
                .catch(() => showToast("Lỗi khi xóa", "error"));
        });
    }

    // ====================== MINI CART ======================
    function loadMiniCart() {
        const list = document.getElementById("mini-cart-list");
        if (!list) return;
        fetch("index.php?url=get-mini-cart")
            .then((res) => res.json())
            .then((data) => {
                list.innerHTML = "";
                if (data.items.length === 0) {
                    list.innerHTML = `<p>Giỏ hàng trống</p>`;
                    return;
                }
                data.items.forEach((item) => {
                    list.innerHTML += `<div class="col"><article class="cart-preview-item">
                        <div class="cart-preview-item__img-wrap"><img src="assets/img/product/${item.image}" class="cart-preview-item__thumb"/></div>
                        <h3 class="cart-preview-item__title">${item.name}</h3>
                        <p class="cart-preview-item__price">${formatVND(item.price)}</p>
                    </article></div>`;
                });
                const miniSubtotalEl = document.getElementById("mini-subtotal");
                const miniTotalEl = document.getElementById("mini-total");
                if (miniSubtotalEl)
                    miniSubtotalEl.innerText = formatVND(data.subtotal);
                if (miniTotalEl) miniTotalEl.innerText = formatVND(data.total);
            });
    }
    document
        .querySelector(".top-act__btn-wrap")
        ?.addEventListener("mouseenter", loadMiniCart);

    // ====================== SAVE → FAVORITE (nút lưu riêng) ======================
    document.querySelectorAll(".btn-save").forEach((btn) => {
        btn.addEventListener("click", function () {
            const id = this.dataset.id;
            fetch("index.php?url=add-favorite", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ product_id: id }),
            })
                .then((res) => res.json())
                .then((data) => {
                    if (data.success) {
                        showToast("Đã thêm vào yêu thích ❤️", "success");
                        if (!btn.classList.contains("liked")) {
                            btn.classList.add("liked");
                            document
                                .querySelectorAll(".fav-count-badge")
                                .forEach((el) => {
                                    let count = parseInt(el.textContent) || 0;
                                    el.textContent = count + 1;
                                });
                        }
                    } else {
                        showToast(
                            data.message || "Sản phẩm đã có trong yêu thích",
                            "error",
                        );
                    }
                })
                .catch(() => showToast("Lỗi kết nối", "error"));
        });
    });

    // ====================== TOGGLE MODAL ======================
    document.querySelectorAll(".js-toggle").forEach((btn) => {
        btn.addEventListener("click", function () {
            const target = this.getAttribute("toggle-target");
            const el = document.querySelector(target);
            if (el) el.classList.toggle("hide");
        });
    });

    document.querySelectorAll(".modal").forEach((modal) => {
        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                modal.classList.remove("show");
                modal.classList.add("hide");
            }
        });
    });

    // ====================== LIKE BTN TOGGLE (QUAN TRỌNG NHẤT) ======================
    // Chỉ một nơi xử lý sự kiện click cho nút like (trái tim)
    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".like-btn");
        if (!btn) return;

        e.preventDefault();
        const productId = btn.dataset.id;
        const isLiked = btn.classList.contains("like-btn--liked");

        // Xác định URL và method
        const url = isLiked
            ? "index.php?url=remove-favorite&id=" + productId
            : "index.php?url=add-favorite";
        const options = {
            method: isLiked ? "GET" : "POST",
            headers: { "Content-Type": "application/json" },
        };
        if (!isLiked) {
            options.body = JSON.stringify({ product_id: productId });
        }

        fetch(url, options)
            .then((res) => res.json())
            .then((res) => {
                // Kiểm tra response có field success không
                if (res.success === true) {
                    // Toggle class trái tim
                    btn.classList.toggle("like-btn--liked");

                    // Cập nhật tất cả badge yêu thích (chỉ class .fav-count-badge)
                    if (res.total_favorites !== undefined) {
                        document
                            .querySelectorAll(".fav-count-badge")
                            .forEach((el) => {
                                el.textContent = res.total_favorites;
                            });
                    } else {
                        // Fallback: tự tăng/giảm 1 nếu server không trả total_favorites
                        const delta = isLiked ? -1 : 1;
                        document
                            .querySelectorAll(".fav-count-badge")
                            .forEach((el) => {
                                let current = parseInt(el.textContent) || 0;
                                let newVal = current + delta;
                                if (newVal < 0) newVal = 0;
                                el.textContent = newVal;
                            });
                    }
                } else {
                    // Lỗi từ server: hiển thị thông báo lỗi
                    showToast(
                        res.message || "Có lỗi xảy ra, vui lòng thử lại",
                        "error",
                    );
                }
            })
            .catch((err) => {
                console.error("Favorite error:", err);
                showToast("Lỗi kết nối đến máy chủ", "error");
            });
    });

    // ====================== PAGINATION AJAX (giữ nguyên) ======================
    document.addEventListener("click", function (e) {
        const link = e.target.closest(".pagination a");
        if (!link) return;
        e.preventDefault();
        const url = link.href;
        const container = document.querySelector("#product-list");
        if (container) container.style.opacity = "0.5";
        fetch(url)
            .then((res) => res.text())
            .then((html) => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");
                const newContent = doc.querySelector("#product-list");
                if (newContent && container)
                    container.innerHTML = newContent.innerHTML;
                history.pushState(null, "", url);
                if (container) container.style.opacity = "1";
            })
            .catch(() => showToast("Lỗi khi chuyển trang", "error"));
    });

    // ====================== SEARCH ======================
    const searchToggle = document.querySelector(".search-toggle");
    if (searchToggle) {
        searchToggle.onclick = function () {
            const box = document.querySelector(".search-box");
            box.classList.toggle("active");
            const input = box.querySelector(".search-input");
            if (box.classList.contains("active")) input.focus();
        };
    }

    const searchInput = document.querySelector(".search-input");
    const suggestBox = document.querySelector(".search-suggest");
    const searchBox = document.querySelector(".search-box");
    let debounceTimer;
    if (searchInput && suggestBox) {
        searchInput.addEventListener("input", function () {
            const keyword = this.value.trim();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                if (!keyword) {
                    suggestBox.style.display = "none";
                    return;
                }
                fetch(`index.php?url=search&q=${keyword}&ajax=1`)
                    .then((res) => res.json())
                    .then((data) => {
                        suggestBox.innerHTML = "";
                        if (data.length === 0) {
                            suggestBox.innerHTML = `<div class="search-suggest-item">Không tìm thấy</div>`;
                        } else {
                            data.forEach((item) => {
                                suggestBox.innerHTML += `<div class="search-suggest-item" onclick="location.href='index.php?url=product&id=${item.id}'">${item.name}</div>`;
                            });
                        }
                        suggestBox.style.display = "block";
                    })
                    .catch(() => (suggestBox.style.display = "none"));
            }, 300);
        });
        document.addEventListener("click", function (e) {
            if (!e.target.closest(".search-box")) {
                if (searchBox) searchBox.classList.remove("active");
                suggestBox.style.display = "none";
            }
        });
    }

    // ====================== SHIPPING ADDRESS ======================
    const addressForm = document.getElementById("add-address-form");
    if (addressForm) {
        addressForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch("index.php?url=add-shipping-address", {
                method: "POST",
                body: formData,
            })
                .then((res) => res.json())
                .then((data) => {
                    if (data.success) {
                        showToast(
                            "Đã thêm địa chỉ mới thành công ✅",
                            "success",
                        );
                        const modal =
                            document.querySelector("#add-new-address");
                        if (modal) {
                            modal.classList.remove("show");
                            modal.classList.add("hide");
                        }
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showToast(
                            data.message || "Thêm địa chỉ thất bại",
                            "error",
                        );
                    }
                })
                .catch(() => showToast("Lỗi kết nối server", "error"));
        });
    }

    // ====================== FILTER ======================
    document.querySelectorAll(".size-option").forEach((btn) => {
        btn.addEventListener("click", function () {
            document
                .querySelectorAll(".size-option")
                .forEach((b) => b.classList.remove("active"));
            this.classList.add("active");
            document.getElementById("size-input").value = this.dataset.size;
        });
    });

    const filterForm = document.getElementById("filter-form");
    if (filterForm) {
        filterForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData).toString();
            const url = `index.php?url=home&ajax=1&${params}`;
            const productList = document.getElementById("product-list");
            let overlay = document.createElement("div");
            overlay.className = "ajax-overlay";
            productList.appendChild(overlay);
            productList.classList.add("loading");
            fetch(url)
                .then((res) => res.text())
                .then((html) => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, "text/html");
                    const newList = doc.querySelector("#product-list");
                    const newPagination = doc.querySelector("#pagination");
                    if (newList) productList.innerHTML = newList.innerHTML;
                    if (newPagination)
                        document.getElementById("pagination").innerHTML =
                            newPagination.innerHTML;
                    productList.classList.remove("loading");
                    overlay.remove();
                    const newUrl = new URL(window.location.href);
                    newUrl.search = params;
                    window.history.pushState({}, "", newUrl);
                });
        });
    }

    // ====================== NOTIFICATIONS ======================
    function loadNotifications() {
        const notiList = document.getElementById("noti-list");
        const badge = document.getElementById("noti-count");
        if (!notiList) return;
        const url =
            (typeof baseUrl !== "undefined" ? baseUrl : "") +
            "index.php?url=api/notifications";
        fetch(url)
            .then((res) => res.json())
            .then((data) => {
                if (data.error) {
                    notiList.innerHTML =
                        '<div class="noti-item">Vui lòng đăng nhập</div>';
                    if (badge) badge.innerText = "0";
                    return;
                }
                const items = data.items || [];
                const unread = data.unread || 0;
                if (items.length === 0) {
                    notiList.innerHTML =
                        '<div class="noti-item">Chưa có thông báo nào</div>';
                    if (badge) badge.innerText = "0";
                    return;
                }
                let html = "";
                items.slice(0, 5).forEach((noti) => {
                    html += `<div class="noti-item ${!noti.is_read ? "unread" : ""}" data-id="${noti.id}">
                        <div class="noti-title">${escapeHtml(noti.title)}</div>
                        <div class="noti-text">${escapeHtml(noti.content)}</div>
                        <div class="noti-time">${formatTime(noti.created_at)}</div>
                    </div>`;
                });
                notiList.innerHTML = html;
                if (badge) badge.innerText = unread > 99 ? "99+" : unread;
            })
            .catch((err) => console.error("Lỗi tải thông báo:", err));
    }

    function escapeHtml(str) {
        if (!str) return "";
        return str.replace(/[&<>]/g, function (m) {
            if (m === "&") return "&amp;";
            if (m === "<") return "&lt;";
            if (m === ">") return "&gt;";
            return m;
        });
    }

    function formatTime(timestamp) {
        if (!timestamp) return "";
        const date = new Date(timestamp);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000);
        if (diff < 60) return "Vừa xong";
        if (diff < 3600) return Math.floor(diff / 60) + " phút trước";
        if (diff < 86400) return Math.floor(diff / 3600) + " giờ trước";
        return date.toLocaleDateString("vi-VN");
    }

    const markAllReadBtn = document.getElementById("mark-all-read");
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener("click", function (e) {
            e.preventDefault();
            const url =
                (typeof baseUrl !== "undefined" ? baseUrl : "") +
                "index.php?url=api/notifications&action=mark-all-read";
            fetch(url, { method: "POST" }).then(() => loadNotifications());
        });
    }

    // Tải thông báo CHỈ khi ở trang notification
    if (
        document.getElementById("noti-list") &&
        window.location.href.includes("notification")
    ) {
        loadNotifications();
        setInterval(loadNotifications, 30000);
    }

    // ====================== TOAST ======================
    function showToast(message, type = "success") {
        const toast = document.createElement("div");
        toast.innerText = message;
        toast.style.position = "fixed";
        toast.style.bottom = "20px";
        toast.style.right = "20px";
        toast.style.padding = "12px 18px";
        toast.style.borderRadius = "8px";
        toast.style.color = "#fff";
        toast.style.fontSize = "14px";
        toast.style.zIndex = 9999;
        toast.style.opacity = "0";
        toast.style.transform = "translateY(20px)";
        toast.style.transition = "all 0.3s ease";
        if (type === "success") toast.style.background = "#28a745";
        else if (type === "error") toast.style.background = "#dc3545";
        else toast.style.background = "#333";
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = "1";
            toast.style.transform = "translateY(0)";
        }, 10);
        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transform = "translateY(20px)";
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }

    console.log("✅ JS đã load thành công");
});
