const inner = document.getElementById("slideshowInner");
const slides = document.querySelectorAll(".slideshow__item");
const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");
const currentEl = document.getElementById("current");
const totalEl = document.getElementById("total");

let currentIndex = 1;
const totalRealSlides = slides.length;
let isTransitioning = false;

totalEl.textContent = totalRealSlides;

// === CLONE SLIDE ===
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
    currentEl.textContent = realIndex;
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

    if (currentIndex === totalSlidesWithClones - 1) {
        currentIndex = 1;
    } else if (currentIndex === 0) {
        currentIndex = totalRealSlides;
    }

    inner.style.transition = "none";
    inner.style.transform = `translateX(-${currentIndex * 100}%)`;

    setTimeout(() => {
        inner.style.transition =
            "transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94)";
    }, 20);

    updateCounter();
});

nextBtn.addEventListener("click", () => {
    if (!isTransitioning) goToSlide(currentIndex + 1);
});

prevBtn.addEventListener("click", () => {
    if (!isTransitioning) goToSlide(currentIndex - 1);
});

// AUTO PLAY
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
slideshow.addEventListener("mouseenter", stopAutoPlay);
slideshow.addEventListener("mouseleave", startAutoPlay);

updateCounter();

// ====================== MINI CART ======================
function formatVND(money) {
    return money.toLocaleString("vi-VN") + "đ";
}

function loadMiniCart() {
    const list = document.getElementById("mini-cart-list");

    fetch("index.php?url=get-mini-cart")
        .then((res) => res.json())
        .then((data) => {
            list.innerHTML = "";

            if (data.items.length === 0) {
                list.innerHTML = `<p>Giỏ hàng trống</p>`;
                return;
            }

            data.items.forEach((item) => {
                list.innerHTML += `
                    <div class="col">
                        <article class="cart-preview-item">
                            <div class="cart-preview-item__img-wrap">
                                <img src="assets/img/product/${item.image}"
                                     class="cart-preview-item__thumb"/>
                            </div>
                            <h3 class="cart-preview-item__title">${item.name}</h3>
                            <p class="cart-preview-item__price">${formatVND(item.price)}</p>
                        </article>
                    </div>`;
            });

            document.getElementById("mini-subtotal").innerText = formatVND(
                data.subtotal,
            );
            document.getElementById("mini-total").innerText = formatVND(
                data.total,
            );
        });
}

document
    .querySelector(".top-act__btn-wrap")
    .addEventListener("mouseenter", loadMiniCart);

// ====================== FAVORITE (FIX CHUẨN) ======================
document.querySelectorAll(".like-btn.product-card__like-btn").forEach((btn) => {
    // ❗ tránh bind nhiều lần
    if (btn.dataset.bound) return;
    btn.dataset.bound = "true";

    btn.addEventListener("click", function () {
        if (this.disabled) return;
        this.disabled = true;

        const id = this.dataset.id;
        const liked = this.classList.contains("liked");
        const action = liked ? "remove" : "add";
        const btnEl = this;

        console.log("CLICK FAVORITE:", id);

        fetch("/DoAn/DoAnTotNghiep/public/ajax/favorite-action.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `id=${id}&action=${action}`,
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.status === "success") {
                    btnEl.classList.toggle("liked");

                    // update số lượng favorite ở header
                    const favCountEl =
                        document.querySelector(".top-act__title");
                    if (favCountEl) {
                        favCountEl.textContent = data.count;
                    }
                } else {
                    alert(data.message || "Lỗi");
                }
            })
            .catch((err) => {
                console.error(err);
                alert("Lỗi server");
            })
            .finally(() => {
                btnEl.disabled = false;
            });
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

// function loadFavoriteState() {
//     fetch("/DoAn/DoAnTotNghiep/public/ajax/get-favorites.php")
//         .then((res) => res.json())
//         .then((favoriteIds) => {
//             document
//                 .querySelectorAll(".like-btn.product-card__like-btn")
//                 .forEach((btn) => {
//                     const id = btn.dataset.id;

//                     if (favoriteIds.includes(Number(id))) {
//                         btn.classList.add("liked");
//                     }
//                 });
//         })
//         .catch((err) => console.error("Favorite load error:", err));
// }

// chạy khi load xong
window.addEventListener("DOMContentLoaded", loadFavoriteState);
console.log("JS LOADED");
