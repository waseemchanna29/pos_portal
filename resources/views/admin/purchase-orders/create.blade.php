@extends('layouts.app')
@section('title', 'New Purchase Order')
@section('page-title', 'New Purchase Order')

@section('content')
<div class="page-header">
    <div>
        <div class="page-header-title">New Purchase Order</div>
        <div class="page-header-sub">Create a purchase order to restock products</div>
    </div>
    <a href="{{ route('admin.purchase-orders.index') }}" class="btn-outline btn">
        <i class="fa-arrow-left fas"></i> Back
    </a>
</div>

<form action="{{ route('admin.purchase-orders.store') }}" method="POST" id="poForm">
@csrf

{{-- Supplier Info --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-truck"></i> Supplier Information</div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Supplier Name <span>*</span></label>
                    <input type="text" name="supplier_name"
                           class="form-control {{ $errors->has('supplier_name') ? 'is-invalid' : '' }}"
                           value="{{ old('supplier_name') }}" placeholder="e.g. Al-Fatah Distributors">
                    @error('supplier_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Supplier Phone</label>
                    <input type="text" name="supplier_phone" class="form-control"
                           value="{{ old('supplier_phone') }}" placeholder="e.g. 0300-1234567">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Supplier Address</label>
            <input type="text" name="supplier_address" class="form-control"
                   value="{{ old('supplier_address') }}" placeholder="City, Area">
        </div>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Order Date <span>*</span></label>
                    <input type="date" name="order_date"
                           class="form-control {{ $errors->has('order_date') ? 'is-invalid' : '' }}"
                           value="{{ old('order_date', date('Y-m-d')) }}">
                    @error('order_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label class="form-label">Expected Delivery Date</label>
                    <input type="date" name="expected_date" class="form-control"
                           value="{{ old('expected_date') }}">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="2"
                      placeholder="Any additional notes...">{{ old('notes') }}</textarea>
        </div>
    </div>
</div>

{{-- Order Items --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header">
        <div class="card-header-title"><i class="fas fa-boxes"></i> Order Items</div>
        <button type="button" class="btn btn-primary btn-sm" onclick="addItem()">
            <i class="fas fa-plus"></i> Add Item
        </button>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="po-items-table" id="itemsTable">
            <thead>
                <tr>
                    <th style="width:40%">Product</th>
                    <th style="width:15%">Qty</th>
                    <th style="width:20%">Unit Cost (PKR)</th>
                    <th style="width:15%">Line Total</th>
                    <th style="width:10%"></th>
                </tr>
            </thead>
            <tbody id="itemsBody">
                {{-- JS rows injected here --}}
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right; padding-right:1rem;">Grand Total:</td>
                    <td id="grandTotal">PKR 0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        @error('items') <div style="padding:0.8rem 1rem; color:var(--danger); font-size:0.85rem;">{{ $message }}</div> @enderror
    </div>
</div>

<div style="display:flex; gap:0.8rem;">
    <button type="submit" class="btn btn-primary btn-lg">
        <i class="fas fa-save"></i> Create Purchase Order
    </button>
    <a href="{{ route('admin.purchase-orders.index') }}" class="btn-outline btn btn-lg">Cancel</a>
</div>

</form>

<script>
const products = @json($products);
let itemCount  = 0;

function addItem() {
    const tbody = document.getElementById('itemsBody');
    const idx   = itemCount++;
    const opts  = products.map(p =>
        `<option value="${p.id}" data-price="${p.purchase_price}">${p.name} (${p.category ? p.category.name : ''})</option>`
    ).join('');

    const row = document.createElement('tr');
    row.id    = `row_${idx}`;
    row.innerHTML = `
        <td>
            <select name="items[${idx}][product_id]" class="form-select" onchange="updateLine(${idx})" required>
                <option value="">— Select Product —</option>
                ${opts}
            </select>
        </td>
        <td>
            <input type="number" name="items[${idx}][quantity_ordered]" class="form-control"
                   value="1" min="1" onchange="updateLine(${idx})" required>
        </td>
        <td>
            <input type="number" step="0.01" name="items[${idx}][unit_cost]" class="form-control"
                   value="0.00" min="0" onchange="updateLine(${idx})" required id="cost_${idx}">
        </td>
        <td id="line_${idx}" style="font-weight:600;">PKR 0.00</td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${idx})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
    updateTotal();
}

function removeItem(idx) {
    const row = document.getElementById(`row_${idx}`);
    if (row) row.remove();
    updateTotal();
}

function updateLine(idx) {
    const row  = document.getElementById(`row_${idx}`);
    if (!row) return;

    // Auto-fill purchase price when product is selected
    const sel   = row.querySelector('select');
    const opt   = sel.options[sel.selectedIndex];
    if (opt && opt.dataset.price) {
        document.getElementById(`cost_${idx}`).value = parseFloat(opt.dataset.price).toFixed(2);
    }

    const qty   = parseFloat(row.querySelector('input[type=number]').value) || 0;
    const cost  = parseFloat(document.getElementById(`cost_${idx}`).value) || 0;
    const total = qty * cost;

    document.getElementById(`line_${idx}`).textContent = 'PKR ' + total.toLocaleString('en-PK', { minimumFractionDigits: 2 });
    updateTotal();
}

function updateTotal() {
    let grand = 0;
    document.querySelectorAll('[id^="line_"]').forEach(el => {
        const val = el.textContent.replace('PKR ', '').replace(/,/g, '');
        grand += parseFloat(val) || 0;
    });
    document.getElementById('grandTotal').textContent = 'PKR ' + grand.toLocaleString('en-PK', { minimumFractionDigits: 2 });
}

// Add one row by default
addItem();
</script>
@endsection