<?php
// Đảm bảo có session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;

// Kết nối DB (giữ nếu bạn dùng ở nơi khác)
require_once __DIR__ . '/../../../config/database.php';

$cart = $_SESSION['cart'] ?? [];

// Tính số item (unique products) và tổng tiền từ SESSION
$cartCount = count($cart);
$total     = 0;

foreach ($cart as $item) {
    $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
}

$shipping = 10000;

$miniSubtotal = $total;
$miniTotal    = $miniSubtotal + $shipping;


// Lấy favorites từ session
$favorites = $user ? getFavorites() : [];


$totalFav = count($favorites);

?>
<header id="header" class="header">
    <div class="container">
        <div class="top-bar">
            <!-- More -->
            <button class="top-bar__more d-none d-lg-block js-toggle" toggle-target="#navbar">
                <img src="./assets/icons/more.svg" alt="" class="icon top-bar__more-icon" />
            </button>

            <!-- Logo -->
            <a href="<?= $base ?>" class="logo top-bar__logo">
                <img src="./assets/icons/logo.svg" alt="grocerymart" class="logo__img top-bar__logo-img" />
                <h1 class="logo__title top-bar__logo-title">TRQshop</h1>
            </a>

            <!-- Navbar -->
            <nav id="navbar" class="navbar hide">
                <button class="navbar__close-btn js-toggle" toggle-target="#navbar">
                    <img class="icon" src="./assets/icons/arrow-left.svg" alt="" />
                </button>

                <a href="<?= $base ?>index.php?url=checkout" class="nav-btn d-none d-md-flex">
                    <img src="<?= $base ?>assets/icons/buy.svg" class="nav-btn__icon icon" />
                    <span class="nav-btn__title">Cart</span>
                    <span class="nav-btn__qnt">
                        <?= count($_SESSION['cart'] ?? []) ?>
                    </span>
                </a>

                <a href="<?= $base ?>index.php?url=favorite" class="nav-btn d-none d-md-flex">
                    <img src="<?= $base ?>assets/icons/heart.svg" class="nav-btn__icon icon" />
                    <span class="nav-btn__title">Favorite</span>
                    <span class="nav-btn__qnt">
                        <?= count($_SESSION['favorite'] ?? []) ?>
                    </span>
                </a>

                <ul class="navbar__list js-dropdown-list">
                    <li class="navbar__item">
                        <a href="#!" class="navbar__link">
                            Departments
                            <img src="./assets/icons/arrow-down.svg" alt="" class="icon navbar__arrow" />
                        </a>
                        <div class="dropdown js-dropdown">
                            <div class="dropdown__inner">
                                <div class="top-menu">
                                    <div class="top-menu__main">
                                        <!-- Menu column -->
                                        <div class="menu-column">
                                            <div class="menu-column__icon d-lg-none">
                                                <img
                                                    src="./assets/img/category/cate-1.1.svg"
                                                    alt=""
                                                    class="menu-column__icon-1"
                                                />
                                                <img
                                                    src="./assets/img/category/cate-1.2.svg"
                                                    alt=""
                                                    class="menu-column__icon-2"
                                                />
                                            </div>
                                            <div class="menu-column__content">
                                                <h2 class="menu-column__heading d-lg-none">All Departments</h2>
                                                <ul class="menu-column__list js-menu-list">
                                                    <li class="menu-column__item">
                                                        <a href="#!" class="menu-column__link">
                                                            Savings & Featured Shops
                                                        </a>
                                                        <!-- Sub menu for "Savings & Featured Shops" -->

                                                        <div class="sub-menu">
                                                            <div class="sub-menu__column">
                                                                <!-- Menu column 1 -->
                                                                <div class="menu-column">
                                                                    <div class="menu-column__icon">
                                                                        <img
                                                                            src="./assets/img/category/cate-4.1.svg"
                                                                            alt=""
                                                                            class="menu-column__icon-1"
                                                                        />
                                                                        <img
                                                                            src="./assets/img/category/cate-4.2.svg"
                                                                            alt=""
                                                                            class="menu-column__icon-2"
                                                                        />
                                                                    </div>
                                                                    <div class="menu-column__content">
                                                                        <h2 class="menu-column__heading">
                                                                            <a href="#!">Fashion Deals</a>
                                                                        </h2>
                                                                        <ul class="menu-column__list">
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Clothing
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Shoes
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Accessories
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Bags
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Jewelry
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>

                                                                <!-- Menu column 2 -->
                                                                <div class="menu-column">
                                                                    <div class="menu-column__icon">
                                                                        <img
                                                                            src="./assets/img/category/cate-2.1.svg"
                                                                            alt=""
                                                                            class="menu-column__icon-1"
                                                                        />
                                                                        <img
                                                                            src="./assets/img/category/cate-2.2.svg"
                                                                            alt=""
                                                                            class="menu-column__icon-2"
                                                                        />
                                                                    </div>
                                                                    <div class="menu-column__content">
                                                                        <h2 class="menu-column__heading">
                                                                            <a href="#!">Electronics Discounts</a>
                                                                        </h2>
                                                                        <ul class="menu-column__list">
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Smartphones
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Laptops
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Headphones
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Cameras
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Tablets
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Speakers
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Wearable Tech
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="sub-menu__column">
                                                                <!-- Menu column 1 -->
                                                                <div class="menu-column">
                                                                    <div class="menu-column__icon">
                                                                        <img
                                                                            src="./assets/img/category/cate-1.1.svg"
                                                                            alt=""
                                                                            class="menu-column__icon-1"
                                                                        />
                                                                        <img
                                                                            src="./assets/img/category/cate-1.2.svg"
                                                                            alt=""
                                                                            class="menu-column__icon-2"
                                                                        />
                                                                    </div>
                                                                    <div class="menu-column__content">
                                                                        <h2 class="menu-column__heading">
                                                                            <a href="#!">Home & Living Specials</a>
                                                                        </h2>
                                                                        <ul class="menu-column__list">
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Furniture
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Kitchenware
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Decor
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Bedding
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Appliances
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Lighting
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Outdoor Furniture
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Home Office
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Bathroom
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Storage
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Cleaning Supplies
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="sub-menu__column">
                                                                <!-- Menu column 1 -->
                                                                <div class="menu-column">
                                                                    <div class="menu-column__icon">
                                                                        <img
                                                                            src="./assets/img/category/cate-6.1.svg"
                                                                            alt=""
                                                                            class="menu-column__icon-1"
                                                                        />
                                                                        <img
                                                                            src="./assets/img/category/cate-6.2.svg"
                                                                            alt=""
                                                                            class="menu-column__icon-2"
                                                                        />
                                                                    </div>
                                                                    <div class="menu-column__content">
                                                                        <h2 class="menu-column__heading">
                                                                            <a href="#!">Beauty Bargains</a>
                                                                        </h2>
                                                                        <ul class="menu-column__list">
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Skincare
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Makeup
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Haircare
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Fragrances
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Nail Care
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Beauty Tools
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Men's Grooming
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>

                                                                <!-- Menu column 2 -->
                                                                <div class="menu-column">
                                                                    <div class="menu-column__icon">
                                                                        <img
                                                                            src="./assets/img/category/cate-5.1.svg"
                                                                            alt=""
                                                                            class="menu-column__icon-1"
                                                                        />
                                                                        <img
                                                                            src="./assets/img/category/cate-5.2.svg"
                                                                            alt=""
                                                                            class="menu-column__icon-2"
                                                                        />
                                                                    </div>
                                                                    <div class="menu-column__content">
                                                                        <h2 class="menu-column__heading">
                                                                            <a href="#!">Sports & Outdoors Deals</a>
                                                                        </h2>
                                                                        <ul class="menu-column__list">
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Fitness Equipment
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Outdoor Gear
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Sportswear
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Camping
                                                                                </a>
                                                                            </li>
                                                                            <li class="menu-column__item">
                                                                                <a href="#!" class="menu-column__link">
                                                                                    Biking
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="menu-column__item">
                                                        <a href="#!" class="menu-column__link">Electronics</a>
                                                        <!-- Sub menu for "Electronics" -->

                                                    </li>
                                                    <li class="menu-column__item">
                                                        <a href="#!" class="menu-column__link">
                                                            Clothing, Shoes & Accessories
                                                        </a>
                                                        <!-- Sub menu for "Clothing, Shoes & Accessories" -->

                                                    </li>
                                                    <li class="menu-column__item">
                                                        <a href="#!" class="menu-column__link">
                                                            Home, Furniture & Appliances
                                                        </a>
                                                        <!-- Sub menu for "Home, Furniture & Appliancess" -->

                                                    </li>
                                                    <li class="menu-column__item">
                                                        <a href="#!" class="menu-column__link">Toys & Video Games</a>
                                                        <!-- Sub menu for "Toys & Video Games" -->

                                                    </li>
                                                    <li class="menu-column__item">
                                                        <a href="#!" class="menu-column__link">Home Improvement</a>
                                                        <!-- Sub menu for "Home Improvement" -->

                                                    </li>
                                                    <li class="menu-column__item">
                                                        <a href="#!" class="menu-column__link">Baby</a>
                                                        <!-- Sub menu for "Baby" -->

                                                    </li>
                                                    <li class="menu-column__item">
                                                        <a href="#!" class="menu-column__link">Household Essentials</a>
                                                        <!-- Sub menu for "Household Essentials" -->

                                                    </li>
                                                    <li class="menu-column__item">
                                                        <a href="#!" class="menu-column__link">Personal Care</a>
                                                        <!-- Sub menu for "Personal Care" -->

                                                    </li>
                                                    <li class="menu-column__item">
                                                        <a href="#!" class="menu-column__link">Patio & Garden</a>
                                                        <!-- Sub menu for "Patio & Garden" -->

                                                    </li>
                                                    <li class="menu-column__item">
                                                        <a href="#!" class="menu-column__link">Sports & Outdoors</a>
                                                        <!-- Sub menu for "Sports & Outdoors" -->

                                                    </li>
                                                    <li class="menu-column__item">
                                                        <a href="#!" class="menu-column__link">Gift Cards</a>
                                                        <!-- Sub menu for "Gift Cards" -->

                                                    </li>
                                                    <li class="menu-column__item">
                                                        <a href="#!" class="menu-column__link">
                                                            Auto, Tires and Industrial
                                                        </a>
                                                        <!-- Sub menu for "Auto, Tires and Industrial" -->

                                                    </li>
                                                    <li class="menu-column__item">
                                                        <a href="#!" class="menu-column__link">Movies, Music & Books</a>
                                                        <!-- Sub menu for "Movies, Music & Books" -->

                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="navbar__item">
                        <a href="#!" class="navbar__link">
                            Grocery
                            <img src="./assets/icons/arrow-down.svg" alt="" class="icon navbar__arrow" />
                        </a>
                        <div class="dropdown js-dropdown">
                            <div class="dropdown__inner">
                                <div class="top-menu">
                                    <div class="sub-menu sub-menu--not-main">
                                        <div class="sub-menu__column">
                                            <!-- Menu column 1 -->
                                            <div class="menu-column">
                                                <div class="menu-column__icon">
                                                    <img
                                                        src="./assets/img/category/cate-13.1.svg"
                                                        alt=""
                                                        class="menu-column__icon-1"
                                                    />
                                                    <img
                                                        src="./assets/img/category/cate-13.2.svg"
                                                        alt=""
                                                        class="menu-column__icon-2"
                                                    />
                                                </div>
                                                <div class="menu-column__content">
                                                    <h2 class="menu-column__heading">
                                                        <a href="#!">Cocktails & Mixes Coffee</a>
                                                    </h2>
                                                    <ul class="menu-column__list">
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Ground Coffee</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Whole Bean Coffee</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Coffee Pods</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Instant Coffee</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <!-- Menu column 2 -->
                                            <div class="menu-column">
                                                <div class="menu-column__icon">
                                                    <img
                                                        src="./assets/img/category/cate-14.1.svg"
                                                        alt=""
                                                        class="menu-column__icon-1"
                                                    />
                                                    <img
                                                        src="./assets/img/category/cate-14.2.svg"
                                                        alt=""
                                                        class="menu-column__icon-2"
                                                    />
                                                </div>
                                                <div class="menu-column__content">
                                                    <h2 class="menu-column__heading">
                                                        <a href="#!">Beverages</a>
                                                    </h2>
                                                    <ul class="menu-column__list">
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Shop All</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Water</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Soft Drinks</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Fruit Juice</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Sports Drinks</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Energy Drinks</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Tea</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Hot Chocolate & Cocoa</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="navbar__item">
                        <a href="#!" class="navbar__link">
                            Beauty
                            <img src="./assets/icons/arrow-down.svg" alt="" class="icon navbar__arrow" />
                        </a>
                        <div class="dropdown js-dropdown">
                            <div class="dropdown__inner">
                                <div class="top-menu">
                                    <div class="sub-menu sub-menu--not-main">

                                        <div class="sub-menu__column">
                                            <!-- Menu column 1 -->
                                            <div class="menu-column">
                                                <div class="menu-column__icon">
                                                    <img
                                                        src="./assets/img/category/cate-19.1.svg"
                                                        alt=""
                                                        class="menu-column__icon-1"
                                                    />
                                                    <img
                                                        src="./assets/img/category/cate-19.2.svg"
                                                        alt=""
                                                        class="menu-column__icon-2"
                                                    />
                                                </div>
                                                <div class="menu-column__content">
                                                    <h2 class="menu-column__heading">
                                                        <a href="#!">Beauty</a>
                                                    </h2>
                                                    <ul class="menu-column__list">
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Shop All</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Men's Grooming</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Bath & Body</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Beauty Tech & Tools</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Makeup</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Fragrance</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Nail Care</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Hair Care</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Hair Color</a>
                                                        </li>
                                                        <li class="menu-column__item">
                                                            <a href="#!" class="menu-column__link">Skincare</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="navbar__overlay js-toggle" toggle-target="#navbar"></div>

            <!-- Actions -->
            <div class="top-act">
                <div class="top-act__group d-md-none top-act__group--single search-box">
                    <button class="top-act__btn search-toggle">
                        <img src="<?= $base ?>assets/icons/search.svg" class="icon top-act__icon" />
                    </button>

                    <!-- 🔥 Ô search -->
                    <input type="text" class="top-act__search search-input" placeholder="Tìm sản phẩm..." />

                    <!-- 🔥 Gợi ý realtime -->
                    <div class="search-suggest"></div>
                </div>

                 <!-- Thông báo (chung cho mọi thiết bị) -->
                <div class="top-act__group  d-md-none top-act__group--bell">
                    <div class="top-act__btn-wrap">
                        <button class="top-act__btn js-toggle" toggle-target="#noti-dropdown">
                            <img src="<?= $base ?>assets/icons/bell.svg" class="icon top-act__icon" />
                            <span class="top-act__title" id="noti-count">0</span>
                        </button>

                        <div id="noti-dropdown" class="act-dropdown hide">
                            <div class="act-dropdown__inner">
                                <img src="./assets/icons/arrow-up.png" alt="" class="act-dropdown__arrow" />
                                <div class="act-dropdown__top">
                                    <h2 class="act-dropdown__title">Thông báo</h2>
                                    <a href="#" class="act-dropdown__view-all" id="mark-all-read">Đánh dấu đã đọc</a>
                                </div>
                                <div class="noti-list" id="noti-list">
                                    <!-- JS sẽ load danh sách thông báo -->
                                    <div class="noti-item">Đang tải...</div>
                                </div>
                                <div class="act-dropdown__separate"></div>
                                <div class="act-dropdown__checkout">
                                    <a href="<?= $base ?>index.php?url=notifications"
                                    class="btn btn--primary btn--rounded act-dropdown__checkout-btn">
                                        Check Out All
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="top-act__group d-md-none">

                    <div class="top-act__btn-wrap">
                        <a class="top-act__btn" href="<?= $base ?>index.php?url=favorite">
                            <img src="<?= $base ?>assets/icons/heart.svg" alt="" class="icon top-act__icon" />
                            <span class="top-act__title"><?= $totalFav ?></span>
                        </a>

                        <!-- Dropdown -->
                        <div class="act-dropdown">
                            <div class="act-dropdown__inner">
                                <img src="./assets/icons/arrow-up.png" alt="" class="act-dropdown__arrow" />
                                <div class="act-dropdown__top">
                                    <h2 class="act-dropdown__title">
                                        You have <?= $totalFav ?> item<?= $totalFav != 1 ? 's' : '' ?>
                                    </h2>
                                    <a href="<?= $base ?>index.php?url=favorite" class="act-dropdown__view-all">See All</a>
                                </div>
                                <div class="row row-cols-3 gx-2 act-dropdown__list">
                                    <?php if($totalFav > 0): ?>
                                        <?php foreach($favorites as $item): ?>
                                            <div class="col">
                                                <article class="cart-preview-item">
                                                    <div class="cart-preview-item__img-wrap">
                                                        <img src="<?= $base ?>assets/img/product/<?= htmlspecialchars($item['image'] ?? 'placeholder.png') ?>"
                                                            alt="<?= htmlspecialchars($item['name']) ?>"
                                                            class="cart-preview-item__thumb" />
                                                    </div>
                                                    <h3 class="cart-preview-item__title"><?= htmlspecialchars($item['name']) ?></h3>
                                                    <p class="cart-preview-item__price"><?= vnd($item['base_price'] ?? 0) ?></p>
                                                </article>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p style="padding:10px; color:#777;">No favorite items yet.</p>
                                    <?php endif; ?>
                                </div>

                                <div class="act-dropdown__separate"></div>
                                <div class="act-dropdown__checkout">
                                    <a href="<?= $base ?>index.php?url=favorite"
                                    class="btn btn--primary btn--rounded act-dropdown__checkout-btn">
                                        Check Out All
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="top-act__separate"></div>


                    <div class="top-act__btn-wrap">
                            <a href="<?= $base ?>index.php?url=checkout" class="top-act__btn">
                                <img src="<?= $base ?>assets/icons/buy.svg" class="icon" />
                                <span class="top-act__title">
                                    <?= number_format($total) ?>đ
                                </span>
                            </a>

                            <!-- Dropdown Cart -->
                            <div class="act-dropdown">
                                <div class="act-dropdown__inner">

                                    <div class="act-dropdown__top">
                                        <h2 class="act-dropdown__title">
                                            You have <?= $cartCount ?> item(s)
                                        </h2>
                                        <a href="<?= $base ?>index.php?url=checkout" class="act-dropdown__view-all">See All</a>
                                    </div>

                                    <div class="row row-cols-3 gx-2 act-dropdown__list" id="cart-list">
                                        <?php foreach ($cart as $item): ?>
                                            <div class="col">
                                                <article class="cart-preview-item">
                                                    <div class="cart-preview-item__img-wrap">
                                                        <img
                                                            src="<?= $base ?>assets/img/product/<?= $item['image'] ?>"
                                                            class="cart-preview-item__thumb"
                                                        />
                                                    </div>
                                                    <h3 class="cart-preview-item__title">
                                                        <?= $item['name'] ?>
                                                    </h3>
                                                    <p class="cart-preview-item__price">
                                                        <?= number_format($item['price']) ?>đ
                                                    </p>
                                                </article>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="act-dropdown__bottom">
                                        <div class="act-dropdown__row">
                                            <span>Subtotal</span>
                                            <span id="mini-subtotal"><?= vnd($miniSubtotal) ?></span>
                                        </div>

                                        <div class="act-dropdown__row">
                                            <span>Shipping</span>
                                            <span><?= vnd($shipping) ?></span>
                                        </div>

                                        <div class="act-dropdown__row act-dropdown__row--bold">
                                            <span>Total</span>
                                            <span id="mini-total"><?= vnd($miniTotal) ?></span>
                                        </div>
                                    </div>

                                    <div class="act-dropdown__checkout">
                                        <a
                                            href="./checkout.html"
                                            class="btn btn--primary btn--rounded act-dropdown__checkout-btn"
                                        >
                                            Check Out All
                                        </a>
                                    </div>

                                </div>
                            </div>
                    </div>

                </div>

                <div class="top-act__user">

                    <?php if ($user): ?>

                        <img src="<?= $base ?>assets/img/avatars/<?= htmlspecialchars($_SESSION['user']['avatar'] ?? 'default.png') ?>" alt="Avatar" class="top-act__avatar" />

                        <!-- Dropdown -->
                        <div class="act-dropdown top-act__dropdown">
                            <div class="act-dropdown__inner user-menu">
                                <img
                                    src="<?= $base ?>assets/icons/arrow-up.png"
                                    alt=""
                                    class="act-dropdown__arrow top-act__dropdown-arrow"
                                />

                                <div class="user-menu__top">
                                    <img src="<?= $base ?>assets/img/avatars/<?= htmlspecialchars($_SESSION['user']['avatar'] ?? 'default.png') ?>" alt="Avatar" class="user-menu__avatar" />
                                    <div>
                                        <p class="user-menu__name"><?= $user['name'] ?? 'User' ?></p>
                                        <p>@<?= explode('@', $user['email'])[0] ?? 'No email' ?></p>
                                    </div>
                                </div>

                                <ul class="user-menu__list">
                                    <li>
                                        <a href="<?= $base ?>index.php?url=profile" class="user-menu__link">Profile</a>
                                    </li>
                                    <li>
                                        <a href="<?= $base ?>index.php?url=favorite" class="user-menu__link">Favorite list</a>
                                    </li>
                                    <li class="user-menu__separate">
                                        <a href="#!" class="user-menu__link" id="switch-theme-btn">
                                            <span>Dark mode</span>
                                            <img src="<?= $base ?>assets/icons/sun.svg" alt="" class="icon user-menu__icon" />
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?= $base ?>index.php?url=settings" class="user-menu__link">Settings</a>
                                    </li>
                                    <li class="user-menu__separate">
                                        <a href="<?= $base ?>index.php?url=logout" class="user-menu__link">Logout</a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</header>
<script>
    window.dispatchEvent(new Event("template-loaded"));
</script>
