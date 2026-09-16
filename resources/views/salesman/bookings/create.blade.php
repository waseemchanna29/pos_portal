@extends('layouts.app')
@section('title', 'New Booking')
@section('page-title', 'New Booking Order')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">New Booking Order</div>
        <div class="page-header-sub">Stock is reserved until dispatch</div>
    </div>
    <a href="{{ route('salesman.bookings.index') }}" class="btn-outline btn">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<form action="{{ route('salesman.bookings.store') }}" method="POST" id="bookingForm">
@csrf

{{-- Customer Info --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-user"></i> Customer Information</div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Customer Name <span>*</span></label>
                    <input type="text" name="customer_name"
                           class="form-control {{ $errors->has('customer_name') ? 'is-invalid' : '' }}"
                           value="{{ old('customer_name') }}" placeholder="Full name">
                    @error('customer_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Phone <span>*</span></label>
                    <input type="text" name="customer_phone"
                           class="form-control {{ $errors->has('customer_phone') ? 'is-invalid' : '' }}"
                           value="{{ old('customer_phone') }}" placeholder="0300-1234567">
                    @error('customer_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">City <span>*</span></label>
                    <select name="customer_city" class="form-select {{ $errors->has('customer_city') ? 'is-invalid' : '' }}">
                        <option value="">— Select City —</option>
                        @foreach(['Karachi','Lahore','Islamabad','Rawalpindi','Faisalabad','Multan','Peshawar','Quetta','Sialkot','Gujranwala','Hyderabad','Sukkur','Bahawalpur','Sargodha','Abbottabad'] as $city)
                            <option value="{{ $city }}" {{ old('customer_city') == $city ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Expected Delivery</label>
                    <input type="date" name="expected_delivery_date" class="form-control"
                           value="{{ old('expected_delivery_date') }}" min="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Delivery Address</label>
            <input type="text" name="customer_address" class="form-control"
                   value="{{ old('customer_address') }}" placeholder="Street, block, area">
        </div>
    </div>
</div>

{{-- Product Selection --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-boxes"></i> Order Items</div>
    </div>
    <div class="card-body" style="padding-bottom:0;">

        {{-- Search & filter --}}
        <div style="display:flex; gap:0.8rem; margin-bottom:1rem; flex-wrap:wrap;">
            <input type="text" id="productSearch" class="form-control"
                   placeholder="Search product..." style="max-width:260px;">
            <select id="categoryFilter" class="form-select" style="max-width:200px;">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Product list --}}
        <div class="booking-product-list" id="productList">
            @foreach($products as $product)
            <div class="booking-product-row
                         {{ $product['stock_status'] === 'out_of_stock' ? 'disabled-row' : '' }}"
                 data-id="{{ $product['id'] }}"
                 data-name="{{ $product['name'] }}"
                 data-category="{{ $product['category_id'] }}"
                 data-sku="{{ $product['sku'] }}">
                <div class="booking-product-info">
                    <div class="booking-product-name">{{ $product['name'] }}</div>
                    <div class="booking-product-meta">
                        {{ $product['sku'] }} &nbsp;|&nbsp; {{ $product['category_name'] }}
                    </div>
                </div>
                <div class="booking-product-stock
                    @if($product['stock_status'] === 'out_of_stock') stock-out
                    @elseif($product['stock_status'] === 'low_stock') stock-low
                    @else stock-ok @endif">
                    {{ $product['available_stock'] }} {{ $product['unit'] }}
                </div>
                <div style="font-weight:600; min-width:110px; text-align:right;">
                    PKR {{ number_format($product['sale_price'], 2) }}
                </div>
                @if($product['stock_status'] !== 'out_of_stock')
                <button type="button" class="btn btn-primary btn-sm"
                        onclick="addBookingItem({{ json_encode($product) }})">
                    <i class="fas fa-plus"></i> Add
                </button>
                @else
                <span class="badge badge-status-cancelled">Out of Stock</span>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Cart table --}}
    <div style="padding:0 1.5rem 1.5rem;">
        <table class="po-items-table" id="bookingItemsTable" style="margin-top:1rem;">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Item Discount</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="bookingItemsBody"></tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;">Subtotal:</td>
                    <td id="bookingSubtotal">PKR 0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <div id="bookingItemsEmpty" style="text-align:center; color:var(--text-muted); padding:1.5rem;">
            No items added yet.
        </div>
        @error('items') <div style="color:var(--danger); font-size:0.85rem; margin-top:0.5rem;">{{ $message }}</div> @enderror
        <div id="hiddenItems"></div>
    </div>
</div>

{{-- Financials & Payment --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-money-bill"></i> Pricing & Advance Payment</div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Order Discount (PKR)</label>
                    <input type="number" step="0.01" name="discount_amount" id="bDiscountInput"
                           class="form-control" value="{{ old('discount_amount', 0) }}"
                           min="0" oninput="recalcBookingTotals()">
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Tax (PKR)</label>
                    <input type="number" step="0.01" name="tax_amount" id="bTaxInput"
                           class="form-control" value="{{ old('tax_amount', 0) }}"
                           min="0" oninput="recalcBookingTotals()">
                </div>
            </div>
        </div>

        <div class="pos-totals" style="margin-bottom:1rem;">
            <div class="pos-total-row">
                <span>Subtotal</span><span id="bDisplaySubtotal">PKR 0.00</span>
            </div>
            <div class="pos-total-row">
                <span>Discount</span><span id="bDisplayDiscount">PKR 0.00</span>
            </div>
            <div class="pos-total-row">
                <span>Tax</span><span id="bDisplayTax">PKR 0.00</span>
            </div>
            <div class="pos-total-row pos-grand-total">
                <span>Net Total</span><span id="bDisplayTotal">PKR 0.00</span>
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Advance Payment (PKR)</label>
                    <input type="number" step="0.01" name="paid_amount"
                           class="form-control {{ $errors->has('paid_amount') ? 'is-invalid' : '' }}"
                           value="{{ old('paid_amount', 0) }}" min="0">
                    <span class="form-text">Leave 0 if no advance taken.</span>
                    @error('paid_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" id="bPaymentMethod"
                            class="form-select" onchange="bToggleRef()">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="easypaisa">Easypaisa</option>
                        <option value="jazzcash">JazzCash</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group" id="bRefGroup" style="display:none;">
            <label class="form-label">Reference / TXN No.</label>
            <input type="text" name="reference_number" class="form-control"
                   placeholder="Transaction or cheque number">
        </div>
        <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"
                      placeholder="Any special instructions...">{{ old('notes') }}</textarea>
        </div>
    </div>
</div>

<div style="display:flex; gap:0.8rem;">
    <button type="submit" class="btn btn-primary btn-lg" id="bookingSubmitBtn" disabled>
        <i class="fas fa-clipboard-check"></i> Create Booking
    </button>
    <a href="{{ route('salesman.bookings.index') }}" class="btn-outline btn btn-lg">Cancel</a>
</div>

</form>

<script>
const bookingProducts = @json($products);
let bookingCart = {};

function addBookingItem(product) {
    const id = product.id;
    if (bookingCart[id]) {
        if (bookingCart[id].qty >= product.available_stock) {
            alert(`Only ${product.available_stock} ${product.unit} available.`);
            return;
        }
        bookingCart[id].qty++;
    } else {
        bookingCart[id] = {
            id: id, name: product.name, sku: product.sku,
            price: product.sale_price, stock: product.available_stock,
            unit: product.unit, qty: 1, discount: 0,
        };
    }
    renderBookingCart();
}

function removeBookingItem(id) {
    delete bookingCart[id];
    renderBookingCart();
}

function changeBookingQty(id, val) {
    const qty = parseInt(val);
    if (isNaN(qty) || qty < 1) { removeBookingItem(id); return; }
    if (qty > bookingCart[id].stock) {
        alert(`Only ${bookingCart[id].stock} available.`);
        document.getElementById(`bqty_${id}`).value = bookingCart[id].qty;
        return;
    }
    bookingCart[id].qty = qty;
    renderBookingCart();
}

function changeBookingDiscount(id, val) {
    bookingCart[id].discount = parseFloat(val) || 0;
    renderBookingCart();
}

function renderBookingCart() {
    const tbody  = document.getElementById('bookingItemsBody');
    const hidden = document.getElementById('hiddenItems');
    const empty  = document.getElementById('bookingItemsEmpty');
    const btn    = document.getElementById('bookingSubmitBtn');
    hidden.innerHTML = '';
    tbody.innerHTML  = '';

    const keys = Object.keys(bookingCart);
    empty.style.display = keys.length === 0 ? 'block' : 'none';
    btn.disabled        = keys.length === 0;

    let i = 0;
    keys.forEach(id => {
        const item  = bookingCart[id];
        const eff   = item.price - item.discount;
        const total = eff * item.qty;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${item.name}</strong><br>
                <span style="font-size:0.78rem;color:var(--text-muted);">${item.sku}</span>
            </td>
            <td>
                <input type="number" id="bqty_${id}" value="${item.qty}" min="1" max="${item.stock}"
                       class="form-control" style="width:70px;" onchange="changeBookingQty('${id}', this.value)">
            </td>
            <td>PKR ${item.price.toLocaleString('en-PK',{minimumFractionDigits:2})}</td>
            <td>
                <input type="number" step="0.01" min="0" value="${item.discount}"
                       class="form-control" style="width:90px;"
                       onchange="changeBookingDiscount('${id}', this.value)">
            </td>
            <td><strong>PKR ${total.toLocaleString('en-PK',{minimumFractionDigits:2})}</strong></td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeBookingItem('${id}')">
                    <i class="fas fa-times"></i>
                </button>
            </td>`;
        tbody.appendChild(row);

        hidden.innerHTML += `
            <input type="hidden" name="items[${i}][product_id]"   value="${item.id}">
            <input type="hidden" name="items[${i}][quantity]"      value="${item.qty}">
            <input type="hidden" name="items[${i}][unit_price]"    value="${item.price}">
            <input type="hidden" name="items[${i}][item_discount]" value="${item.discount}">`;
        i++;
    });

    recalcBookingTotals();
}

function recalcBookingTotals() {
    let subtotal = 0;
    Object.values(bookingCart).forEach(item => {
        subtotal += (item.price - item.discount) * item.qty;
    });

    const discount = parseFloat(document.getElementById('bDiscountInput').value) || 0;
    const tax      = parseFloat(document.getElementById('bTaxInput').value) || 0;
    const total    = Math.max(0, subtotal - discount + tax);

    document.getElementById('bDisplaySubtotal').textContent =
        'PKR ' + subtotal.toLocaleString('en-PK',{minimumFractionDigits:2});
    document.getElementById('bDisplayDiscount').textContent =
        'PKR ' + discount.toLocaleString('en-PK',{minimumFractionDigits:2});
    document.getElementById('bDisplayTax').textContent =
        'PKR ' + tax.toLocaleString('en-PK',{minimumFractionDigits:2});
    document.getElementById('bDisplayTotal').textContent =
        'PKR ' + total.toLocaleString('en-PK',{minimumFractionDigits:2});
    document.getElementById('bookingSubtotal').textContent =
        'PKR ' + subtotal.toLocaleString('en-PK',{minimumFractionDigits:2});
}

function bToggleRef() {
    const method = document.getElementById('bPaymentMethod').value;
    document.getElementById('bRefGroup').style.display = method !== 'cash' ? 'block' : 'none';
}

// Search & filter
document.getElementById('productSearch').addEventListener('input', filterBookingProducts);
document.getElementById('categoryFilter').addEventListener('change', filterBookingProducts);

function filterBookingProducts() {
    const search   = document.getElementById('productSearch').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    document.querySelectorAll('.booking-product-row').forEach(row => {
        const matchSearch   = row.dataset.name.toLowerCase().includes(search)
                           || row.dataset.sku.toLowerCase().includes(search);
        const matchCategory = !category || row.dataset.category === category;
        row.style.display   = matchSearch && matchCategory ? 'flex' : 'none';
    });
}
</script>
@endsection