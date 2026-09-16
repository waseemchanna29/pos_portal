@extends('layouts.app')
@section('title', 'POS')
@section('page-title', 'Point of Sale')

@section('content')
<div class="pos-wrapper">

    {{-- LEFT: Product Grid --}}
    <div class="pos-products-panel">

        {{-- Search & Filter --}}
        <div class="pos-search-bar">
            <input type="text" id="productSearch" class="form-control"
                   placeholder="Search product name or SKU...">
            <select id="categoryFilter" class="form-select">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Product Cards --}}
        <div class="pos-product-grid" id="productGrid">
            @foreach($products as $product)
            <div class="pos-product-card {{ $product['stock_status'] === 'out_of_stock' ? 'out-of-stock' : '' }}"
                 data-id="{{ $product['id'] }}"
                 data-name="{{ $product['name'] }}"
                 data-sku="{{ $product['sku'] }}"
                 data-price="{{ $product['sale_price'] }}"
                 data-stock="{{ $product['available_stock'] }}"
                 data-unit="{{ $product['unit'] }}"
                 data-category="{{ $product['category_id'] }}"
                 onclick="addToCart(this)">
                <div class="pos-product-name">{{ $product['name'] }}</div>
                <div class="pos-product-sku">{{ $product['sku'] }}</div>
                <div class="pos-product-price">PKR {{ number_format($product['sale_price'], 2) }}</div>
                <div class="pos-product-stock
                    @if($product['stock_status'] === 'out_of_stock') stock-out
                    @elseif($product['stock_status'] === 'low_stock') stock-low
                    @else stock-ok @endif">
                    @if($product['stock_status'] === 'out_of_stock')
                        Out of Stock
                    @else
                        {{ $product['available_stock'] }} {{ $product['unit'] }}
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- RIGHT: Cart & Checkout --}}
    <div class="pos-cart-panel">

        <div class="pos-cart-header">
            <span><i class="fas fa-shopping-cart"></i> Cart</span>
            <button type="button" class="btn btn-danger btn-sm" onclick="clearCart()">
                <i class="fas fa-trash"></i> Clear
            </button>
        </div>

        {{-- Cart Items --}}
        <div class="pos-cart-items" id="cartItems">
            <div class="pos-cart-empty" id="cartEmpty">
                <i class="fas fa-shopping-basket"></i>
                <p>No items added yet.<br>Click a product to add.</p>
            </div>
        </div>

        {{-- Totals --}}
        <div class="pos-totals">
            <div class="pos-total-row">
                <span>Subtotal</span>
                <span id="displaySubtotal">PKR 0.00</span>
            </div>
            <div class="pos-total-row">
                <span>Discount</span>
                <span id="displayDiscount">PKR 0.00</span>
            </div>
            <div class="pos-total-row">
                <span>Tax</span>
                <span id="displayTax">PKR 0.00</span>
            </div>
            <div class="pos-total-row pos-grand-total">
                <span>Total</span>
                <span id="displayTotal">PKR 0.00</span>
            </div>
        </div>

        {{-- Checkout Form --}}
        <form action="{{ route('salesman.pos.store') }}" method="POST" id="posForm">
        @csrf

        {{-- Hidden cart items filled by JS --}}
        <div id="hiddenItems"></div>

        {{-- Customer (optional for walk-in) --}}
        <div class="pos-section-label">Customer (Optional)</div>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <input type="text" name="customer_name" class="form-control"
                           placeholder="Customer name" value="{{ old('customer_name') }}">
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <input type="text" name="customer_phone" class="form-control"
                           placeholder="Phone e.g. 0300-1234567" value="{{ old('customer_phone') }}">
                </div>
            </div>
        </div>
        <div class="form-group">
            <select name="customer_city" class="form-select">
                <option value="">— City (Optional) —</option>
                @foreach(['Karachi','Lahore','Islamabad','Rawalpindi','Faisalabad','Multan','Peshawar','Quetta','Sialkot','Gujranwala','Hyderabad','Sukkur','Bahawalpur','Sargodha','Abbottabad'] as $city)
                    <option value="{{ $city }}" {{ old('customer_city') == $city ? 'selected' : '' }}>
                        {{ $city }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Discount & Tax --}}
        <div class="pos-section-label">Adjustments</div>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Discount (PKR)</label>
                    <input type="number" step="0.01" name="discount_amount" id="discountInput"
                           class="form-control" value="0" min="0" oninput="recalcTotals()">
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Tax (PKR)</label>
                    <input type="number" step="0.01" name="tax_amount" id="taxInput"
                           class="form-control" value="0" min="0" oninput="recalcTotals()">
                </div>
            </div>
        </div>

        {{-- Payment --}}
        <div class="pos-section-label">Payment</div>
        <div class="form-group">
            <label class="form-label">Amount Received (PKR)</label>
            <input type="number" step="0.01" name="paid_amount" id="paidInput"
                   class="form-control" value="0" min="0" oninput="recalcChange()">
        </div>
        <div class="pos-change-display" id="changeDisplay" style="display:none;">
            Change: <strong id="changeAmount">PKR 0.00</strong>
        </div>
        <div class="form-group">
            <label class="form-label">Payment Method</label>
            <select name="payment_method" id="paymentMethod" class="form-select" onchange="toggleRef()">
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="easypaisa">Easypaisa</option>
                <option value="jazzcash">JazzCash</option>
                <option value="cheque">Cheque</option>
            </select>
        </div>
        <div class="form-group" id="refGroup" style="display:none;">
            <label class="form-label">Reference / TXN No.</label>
            <input type="text" name="reference_number" class="form-control"
                   placeholder="Transaction or cheque number">
        </div>
        <div class="form-group">
            <textarea name="notes" class="form-control" rows="2"
                      placeholder="Notes (optional)..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-full btn-lg" id="checkoutBtn" disabled>
            <i class="fas fa-check-circle"></i> Complete Sale
        </button>

        </form>
    </div>
</div>

<script>
const products = @json($products);
let cart = {};

// ── Add to cart ────────────────────────────────────────────────
function addToCart(el) {
    const id    = el.dataset.id;
    const stock = parseInt(el.dataset.stock);

    if (stock <= 0) return;

    if (cart[id]) {
        if (cart[id].qty >= stock) {
            alert(`Only ${stock} ${el.dataset.unit} available for "${el.dataset.name}".`);
            return;
        }
        cart[id].qty++;
    } else {
        cart[id] = {
            id:    id,
            name:  el.dataset.name,
            sku:   el.dataset.sku,
            price: parseFloat(el.dataset.price),
            stock: stock,
            unit:  el.dataset.unit,
            qty:   1,
            discount: 0,
        };
    }

    renderCart();
}

// ── Remove from cart ───────────────────────────────────────────
function removeFromCart(id) {
    delete cart[id];
    renderCart();
}

// ── Change quantity ────────────────────────────────────────────
function changeQty(id, val) {
    const qty = parseInt(val);
    if (isNaN(qty) || qty < 1) { removeFromCart(id); return; }
    if (qty > cart[id].stock) {
        alert(`Only ${cart[id].stock} ${cart[id].unit} available.`);
        document.getElementById(`qty_${id}`).value = cart[id].qty;
        return;
    }
    cart[id].qty = qty;
    renderCart();
}

// ── Change item discount ───────────────────────────────────────
function changeItemDiscount(id, val) {
    cart[id].discount = parseFloat(val) || 0;
    renderCart();
}

// ── Clear cart ─────────────────────────────────────────────────
function clearCart() {
    cart = {};
    renderCart();
}

// ── Render cart ────────────────────────────────────────────────
function renderCart() {
    const container = document.getElementById('cartItems');
    const empty     = document.getElementById('cartEmpty');
    const hidden    = document.getElementById('hiddenItems');
    hidden.innerHTML = '';

    const keys = Object.keys(cart);

    if (keys.length === 0) {
        empty.style.display  = 'flex';
        recalcTotals();
        document.getElementById('checkoutBtn').disabled = true;
        return;
    }

    empty.style.display = 'none';
    document.getElementById('checkoutBtn').disabled = false;

    let html = '';
    let i    = 0;

    keys.forEach(id => {
        const item     = cart[id];
        const effPrice = item.price - item.discount;
        const lineTotal = effPrice * item.qty;

        html += `
        <div class="pos-cart-item">
            <div class="pos-cart-item-info">
                <div class="pos-cart-item-name">${item.name}</div>
                <div class="pos-cart-item-sku">${item.sku}</div>
            </div>
            <div class="pos-cart-item-controls">
                <input type="number" value="${item.qty}" min="1" max="${item.stock}"
                       class="pos-qty-input" onchange="changeQty('${id}', this.value)"
                       id="qty_${id}">
                <span class="pos-cart-item-price">
                    PKR ${lineTotal.toLocaleString('en-PK', {minimumFractionDigits:2})}
                </span>
                <button type="button" class="pos-remove-btn" onclick="removeFromCart('${id}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div style="display:flex; align-items:center; gap:0.4rem; margin-top:0.3rem;">
                <span style="font-size:0.78rem; color:var(--text-muted);">Item Discount PKR</span>
                <input type="number" step="0.01" min="0" value="${item.discount}"
                       class="form-control" style="width:90px; padding:0.3rem 0.5rem; font-size:0.82rem;"
                       onchange="changeItemDiscount('${id}', this.value)">
            </div>
        </div>`;

        // Hidden inputs for form submission
        hidden.innerHTML += `
            <input type="hidden" name="items[${i}][product_id]"    value="${item.id}">
            <input type="hidden" name="items[${i}][quantity]"       value="${item.qty}">
            <input type="hidden" name="items[${i}][unit_price]"     value="${item.price}">
            <input type="hidden" name="items[${i}][item_discount]"  value="${item.discount}">
        `;
        i++;
    });

    container.innerHTML = html + (document.getElementById('cartEmpty').outerHTML);
    document.getElementById('cartEmpty').style.display = 'none';

    recalcTotals();
}

// ── Recalculate totals ─────────────────────────────────────────
function recalcTotals() {
    let subtotal = 0;

    Object.values(cart).forEach(item => {
        subtotal += (item.price - item.discount) * item.qty;
    });

    const discount = parseFloat(document.getElementById('discountInput').value) || 0;
    const tax      = parseFloat(document.getElementById('taxInput').value) || 0;
    const total    = Math.max(0, subtotal - discount + tax);

    document.getElementById('displaySubtotal').textContent =
        'PKR ' + subtotal.toLocaleString('en-PK', {minimumFractionDigits:2});
    document.getElementById('displayDiscount').textContent =
        'PKR ' + discount.toLocaleString('en-PK', {minimumFractionDigits:2});
    document.getElementById('displayTax').textContent =
        'PKR ' + tax.toLocaleString('en-PK', {minimumFractionDigits:2});
    document.getElementById('displayTotal').textContent =
        'PKR ' + total.toLocaleString('en-PK', {minimumFractionDigits:2});

    recalcChange();
}

// ── Change calculation ─────────────────────────────────────────
function recalcChange() {
    const totalEl = document.getElementById('displayTotal').textContent;
    const total   = parseFloat(totalEl.replace('PKR ', '').replace(/,/g, '')) || 0;
    const paid    = parseFloat(document.getElementById('paidInput').value) || 0;
    const change  = paid - total;

    const display = document.getElementById('changeDisplay');
    const amount  = document.getElementById('changeAmount');

    if (paid > 0) {
        display.style.display = 'block';
        display.style.color   = change >= 0 ? 'var(--success)' : 'var(--danger)';
        amount.textContent = 'PKR ' + Math.abs(change).toLocaleString('en-PK', {minimumFractionDigits:2});
        amount.textContent += change >= 0 ? ' (Change)' : ' (Remaining)';
    } else {
        display.style.display = 'none';
    }
}

// ── Toggle reference field ─────────────────────────────────────
function toggleRef() {
    const method   = document.getElementById('paymentMethod').value;
    const refGroup = document.getElementById('refGroup');
    refGroup.style.display = method !== 'cash' ? 'block' : 'none';
}

// ── Search & filter ────────────────────────────────────────────
document.getElementById('productSearch').addEventListener('input', filterProducts);
document.getElementById('categoryFilter').addEventListener('change', filterProducts);

function filterProducts() {
    const search   = document.getElementById('productSearch').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const cards    = document.querySelectorAll('.pos-product-card');

    cards.forEach(card => {
        const matchSearch   = card.dataset.name.toLowerCase().includes(search)
                           || card.dataset.sku.toLowerCase().includes(search);
        const matchCategory = !category || card.dataset.category === category;
        card.style.display  = matchSearch && matchCategory ? 'flex' : 'none';
    });
}
</script>
@endsection