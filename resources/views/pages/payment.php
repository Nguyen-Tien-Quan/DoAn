<?php global $base; ?>
<main class="checkout-page">
    <div class="container">
        <!-- Search bar -->
        <div class="checkout-container">
            <div class="search-bar d-none d-md-flex">
                <input type="text" placeholder="Search for item" class="search-bar__input" />
                <button class="search-bar__submit">
                    <img src="./assets/icons/search.svg" alt="" class="search-bar__icon icon" />
                </button>
            </div>
        </div>

        <!-- Breadcrumbs -->
        <div class="checkout-container">
            <ul class="breadcrumbs checkout-page__breadcrumbs">
                <li>
                    <a href="./" class="breadcrumbs__link">
                        Home <img src="./assets/icons/arrow-right.svg" alt="" />
                    </a>
                </li>
                <li>
                    <a href="./checkout.html" class="breadcrumbs__link">
                        Checkout <img src="./assets/icons/arrow-right.svg" alt="" />
                    </a>
                </li>
                <li>
                    <a href="./shipping.html" class="breadcrumbs__link">
                        Shipping <img src="./assets/icons/arrow-right.svg" alt="" />
                    </a>
                </li>
                <li>
                    <a href="#!" class="breadcrumbs__link breadcrumbs__link--current">Payment method</a>
                </li>
            </ul>
        </div>

        <div class="checkout-container">
            <div class="row gy-xl-3">
                <!-- Left Column -->
                <div class="col-8 col-xl-8 col-lg-12">
                    <!-- Shipping Info -->
                    <div class="cart-info cart-info--shadow">
                        <div class="cart-info__top">
                            <h2 class="cart-info__heading cart-info__heading--lv2">
                                1. Shipping, arrives between Mon, May 16—Tue, May 24
                            </h2>
                            <a class="cart-info__edit-btn" href="./shipping.html">
                                <img class="icon" src="./assets/icons/edit.svg" alt="" /> Edit
                            </a>
                        </div>

                        <!-- Shipping Address -->
                        <article class="payment-item payment-item--card">
                            <div class="payment-item__info">
                                <h3 class="payment-item__title">Imran Khan</h3>
                                <p class="payment-item__desc">Museum of Rajas, Sylhet Sadar, Sylhet 3100.</p>
                                <span class="payment-item__badge">Default</span>
                            </div>
                        </article>

                        <!-- Items Details -->
                        <article class="payment-item payment-item--card">
                            <div class="payment-item__info">
                                <h3 class="payment-item__title">Items details</h3>
                                <p class="payment-item__desc">2 items</p>
                            </div>
                            <a href="./shipping.html" class="payment-item__detail">View details</a>
                        </article>
                    </div>

                    <!-- Shipping Method -->
                    <div class="cart-info cart-info--shadow">
                        <h2 class="cart-info__heading cart-info__heading--lv2">2. Shipping method</h2>
                        <div class="cart-info__separate"></div>
                        <h3 class="cart-info__sub-heading">Available Shipping method</h3>

                        <div id="shipping-methods">
                            <label>
                                <article class="payment-item payment-item--pointer payment-item--highlight">
                                    <img src="./assets/img/payment/delivery-1.png" alt="" class="payment-item__thumb" />
                                    <div class="payment-item__content">
                                        <div class="payment-item__info">
                                            <h3 class="payment-item__title">Fedex Delivery</h3>
                                            <p class="payment-item__desc payment-item__desc--low">Delivery: 2-3 days work</p>
                                            <small class="payment-item__note">Free shipping for orders over $100</small>
                                        </div>
                                        <span class="cart-info__checkbox payment-item__checkbox">
                                            <input type="radio" name="delivery-method" value="Fedex" checked class="cart-info__checkbox-input payment-item__checkbox-input" />
                                            <span class="payment-item__cost">Free</span>
                                        </span>
                                    </div>
                                </article>
                            </label>

                            <label>
                                <article class="payment-item payment-item--pointer">
                                    <img src="./assets/img/payment/delivery-2.png" alt="" class="payment-item__thumb" />
                                    <div class="payment-item__content">
                                        <div class="payment-item__info">
                                            <h3 class="payment-item__title">DHL Delivery</h3>
                                            <p class="payment-item__desc payment-item__desc--low">Delivery: 2-3 days work</p>
                                            <small class="payment-item__note">Standard shipping</small>
                                        </div>
                                        <span class="cart-info__checkbox payment-item__checkbox">
                                            <input type="radio" name="delivery-method" value="DHL" class="cart-info__checkbox-input payment-item__checkbox-input" />
                                            <span class="payment-item__cost">$12.00</span>
                                        </span>
                                    </div>
                                </article>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Payment Details -->
                <div class="col-4 col-xl-4 col-lg-12">
                    <div class="cart-info cart-info--shadow">
                        <h2 class="cart-info__heading cart-info__heading--lv2">Payment Details</h2>
                        <p class="cart-info__desc">Complete your purchase by providing your payment details.</p>

                        <form id="payment-form" class="form cart-info__form">
                            <div class="form__group">
                                <label for="email" class="form__label form__label--medium">Email Address</label>
                                <div class="form__text-input">
                                    <input type="email" name="email" id="email" placeholder="Email" class="form__input" required />
                                    <img src="./assets/icons/form-error.svg" alt="" class="form__input-icon-error" />
                                </div>
                            </div>

                            <div class="form__group">
                                <label for="card-holder" class="form__label form__label--medium">Card Holder</label>
                                <div class="form__text-input">
                                    <input type="text" name="card-holder" id="card-holder" placeholder="Card Holder" class="form__input" required />
                                </div>
                            </div>

                            <div class="form__group">
                                <label for="card-details" class="form__label form__label--medium">Card Details</label>
                                <div class="form__text-input">
                                    <input type="text" name="card-details" id="card-details" placeholder="Card Details" class="form__input" required />
                                </div>
                            </div>

                            <div class="form__row cart-info__form-row">
                                <div class="form__group form__group--flex">
                                    <input type="text" name="card-expire" id="card-expire" placeholder="MM/YY" class="form__input" required />
                                    <input type="text" name="card-cvc" id="card-cvc" placeholder="CVC" class="form__input" required />
                                </div>
                            </div>
                        </form>

                        <div class="cart-info__summary">
                            <div class="cart-info__row">
                                <span>Subtotal <span class="cart-info__sub-label">(items)</span></span>
                                <span id="subtotal">3</span>
                            </div>
                            <div class="cart-info__row">
                                <span>Price <span class="cart-info__sub-label">(Total)</span></span>
                                <span id="total-price">$191.65</span>
                            </div>
                            <div class="cart-info__row">
                                <span>Shipping</span>
                                <span id="shipping-cost">$10.00</span>
                            </div>
                            <div class="cart-info__separate"></div>
                            <div class="cart-info__row cart-info__row--highlight">
                                <span>Estimated Total</span>
                                <span id="estimated-total">$201.65</span>
                            </div>
                        </div>

                        <a href="#!" id="pay-btn" class="cart-info__next-btn btn btn--primary btn--rounded">
                            Pay $201.65
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal: confirm remove shopping cart item -->
<div id="delete-confirm" class="modal modal--small hide">
    <div class="modal__content">
        <p class="modal__text">Do you want to remove this item from shopping cart?</p>
        <div class="modal__bottom">
            <button class="btn btn--small btn--outline modal__btn js-toggle" toggle-target="#delete-confirm">
                Cancel
            </button>
            <button class="btn btn--small btn--danger btn--primary modal__btn btn--no-margin js-toggle" toggle-target="#delete-confirm">
                Delete
            </button>
        </div>
    </div>
    <div class="modal__overlay js-toggle" toggle-target="#delete-confirm"></div>
</div>

<!-- Modal: add new shipping address -->
<div id="add-new-address" class="modal hide" style="--content-width: 650px">
    <div class="modal__content">
        <form action="" class="form">
            <h2 class="modal__heading">Add new shipping address</h2>
            <div class="modal__body">
                <div class="form__row">
                    <div class="form__group form__group--half">
                        <label for="name" class="form__label form__label--small">Name</label>
                        <input type="text" name="name" id="name" placeholder="Name" class="form__input" required minlength="2" />
                    </div>
                    <div class="form__group form__group--half">
                        <label for="phone" class="form__label form__label--small">Phone</label>
                        <input type="tel" name="phone" id="phone" placeholder="Phone" class="form__input" required minlength="10" />
                    </div>
                </div>
                <div class="form__group">
                    <label for="address" class="form__label form__label--small">Address</label>
                    <textarea name="address" id="address" placeholder="Address (Area and street)" class="form__text-area-input" required></textarea>
                </div>
                <div class="form__group">
                    <label for="city" class="form__label form__label--small">City/District/Town</label>
                    <input type="text" id="city" placeholder="City/District/Town" class="form__input js-toggle" toggle-target="#city-dialog" />
                    <div id="city-dialog" class="form__select-dialog hide">
                        <h2 class="form__dialog-heading d-none d-sm-block">City/District/Town</h2>
                        <button class="form__close-dialog d-none d-sm-block js-toggle" toggle-target="#city-dialog">&times</button>
                        <div class="form__search">
                            <input type="text" placeholder="Search" class="form__search-input" />
                            <img src="./assets/icons/search.svg" alt="" class="form__search-icon icon" />
                        </div>
                        <ul class="form__options-list">
                            <li class="form__option form__option--current">Ho Chi Minh</li>
                            <li class="form__option">Ha Noi</li>
                            <li class="form__option">Da Nang</li>
                        </ul>
                    </div>
                </div>
                <div class="form__group form__group--inline">
                    <label class="form__checkbox">
                        <input type="checkbox" class="form__checkbox-input d-none" />
                        <span class="form__checkbox-label">Set as default address</span>
                    </label>
                </div>
            </div>
            <div class="modal__bottom">
                <button class="btn btn--small btn--text modal__btn js-toggle" toggle-target="#add-new-address">Cancel</button>
                <button class="btn btn--small btn--primary modal__btn btn--no-margin">Create</button>
            </div>
        </form>
    </div>
    <div class="modal__overlay"></div>
</div>

<script>
document.getElementById('pay-btn').addEventListener('click', async function(e) {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const cardHolder = document.getElementById('card-holder').value;
    const cardDetails = document.getElementById('card-details').value;
    const expire = document.getElementById('card-expire').value;
    const cvc = document.getElementById('card-cvc').value;
    const deliveryMethod = document.querySelector('input[name="delivery-method"]:checked').value;
    const amount = parseFloat(document.getElementById('estimated-total').innerText.replace('$',''));

    const res = await fetch('index.php?url=payment&action=create', {
        method: 'POST',
        body: JSON.stringify({
            order_id: 123,
            method: deliveryMethod,
            amount: amount,
            transaction_code: cardDetails
        }),
        headers: { 'Content-Type': 'application/json' }
    });
    const data = await res.json();
    if (data.payment_id) {
        alert('Payment created successfully! Payment ID: ' + data.payment_id);
    } else {
        alert('Payment failed!');
    }
});
</script>
