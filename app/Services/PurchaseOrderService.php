<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    /**
     * List purchase orders for an outlet.
     */
    public function list(int $outletId, array $filters = []): LengthAwarePaginator
    {
        $query = PurchaseOrder::with('createdBy')
                              ->where('outlet_id', $outletId)
                              ->latest();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Create a purchase order with items.
     * Items = [['product_id', 'quantity_ordered', 'unit_cost'], ...]
     */
    public function create(array $data, int $outletId, int $createdBy): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $outletId, $createdBy) {

            $po = PurchaseOrder::create([
                'po_number'        => PurchaseOrder::generatePoNumber($outletId),
                'outlet_id'        => $outletId,
                'created_by'       => $createdBy,
                'supplier_name'    => $data['supplier_name'],
                'supplier_phone'   => $data['supplier_phone'] ?? null,
                'supplier_address' => $data['supplier_address'] ?? null,
                'status'           => 'ordered',
                'order_date'       => $data['order_date'],
                'expected_date'    => $data['expected_date'] ?? null,
                'notes'            => $data['notes'] ?? null,
                'total_amount'     => 0,
            ]);

            $total = 0;

            foreach ($data['items'] as $item) {
                $lineTotal = $item['quantity_ordered'] * $item['unit_cost'];
                $total    += $lineTotal;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $item['product_id'],
                    'quantity_ordered'  => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_cost'         => $item['unit_cost'],
                ]);
            }

            $po->update(['total_amount' => $total]);

            return $po;
        });
    }

    /**
     * Mark a PO as received — update product stock quantities.
     */
    public function receive(PurchaseOrder $po): PurchaseOrder
    {
        if (!$po->isOrdered()) {
            throw new \Exception('Only ordered purchase orders can be marked as received.');
        }

        return DB::transaction(function () use ($po) {

            foreach ($po->items as $item) {
                // Add ordered quantity to product stock
                Product::where('id', $item->product_id)
                       ->increment('stock_quantity', $item->quantity_ordered);

                // Also update purchase price on product to latest cost
                Product::where('id', $item->product_id)
                       ->update(['purchase_price' => $item->unit_cost]);

                $item->update(['quantity_received' => $item->quantity_ordered]);
            }

            $po->update([
                'status'        => 'received',
                'received_date' => now()->toDateString(),
            ]);

            return $po->fresh();
        });
    }

    /**
     * Cancel a PO (only if not yet received).
     */
    public function cancel(PurchaseOrder $po): PurchaseOrder
    {
        if ($po->isReceived()) {
            throw new \Exception('Cannot cancel a purchase order that has already been received.');
        }

        $po->update(['status' => 'cancelled']);

        return $po->fresh();
    }

    /**
     * Authorize outlet access.
     */
    public function authorize(User $actor, PurchaseOrder $po): void
    {
        if ($po->outlet_id !== $actor->outlet_id) {
            throw new \Exception('You do not have access to this purchase order.');
        }
    }
}