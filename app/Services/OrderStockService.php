<?php

namespace App\Services;

use App\Order;
use App\OrderProducts;
use App\Stock;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Stock deducted ONLY when an order status is changed to Completed.
 * Call inside a DB::transaction(). Uses row locks to prevent overselling.
 */
class OrderStockService
{
    public function reserve(Order $order): void
    {
        $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
        if ((bool) $lockedOrder->stock_deducted) {
            return;
        }

        $quantities = $this->quantities($lockedOrder);
        if (!$quantities) {
            throw new RuntimeException('Cannot reserve stock for an empty order.');
        }

        // Always acquire stock locks in product-id order to reduce deadlocks.
        foreach ($quantities as $productId => $quantity) {
            $stock = Stock::where('product_id', $productId)->lockForUpdate()->first();
            if (!$stock || (int) $stock->stock < $quantity) {
                throw new RuntimeException('Insufficient stock for product ID ' . $productId . '. Please update your cart.');
            }
            $stock->stock = (int) $stock->stock - $quantity;
            $stock->save();
        }

        $lockedOrder->stock_deducted = true;
        $lockedOrder->save();
        $order->stock_deducted = true;
    }

    public function release(Order $order): void
    {
        $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
        if (!(bool) $lockedOrder->stock_deducted) {
            return;
        }

        foreach ($this->quantities($lockedOrder) as $productId => $quantity) {
            $stock = Stock::where('product_id', $productId)->lockForUpdate()->first();
            if (!$stock) {
                throw new RuntimeException('Missing inventory row for product ID ' . $productId . '.');
            }
            $stock->stock = (int) $stock->stock + $quantity;
            $stock->save();
        }

        $lockedOrder->stock_deducted = false;
        $lockedOrder->save();
        $order->stock_deducted = false;
    }

    /**
     * Check stock before calling an external courier API. This does NOT deduct it.
     * $queued tracks quantities of other orders about to be submitted in the
     * same courier batch so we do not knowingly over-allocate one product.
     * The definitive check still happens under a row lock in reserve().
     *
     * @return array Product ID => quantity required by this order.
     */
    public function checkAvailability(Order $order, array $queued = []): array
    {
        $current = Order::findOrFail($order->id);
        if ((bool) $current->stock_deducted) {
            return [];
        }

        $quantities = $this->quantities($current);
        if (empty($quantities)) {
            throw new RuntimeException('Cannot send an empty order to courier.');
        }

        foreach ($quantities as $productId => $quantity) {
            $stock = Stock::where('product_id', $productId)->first();
            $available = $stock ? (int) $stock->stock : 0;
            $requested = $quantity + (int) ($queued[$productId] ?? 0);
            if ($requested > $available) {
                throw new RuntimeException(
                    'Insufficient stock for product ID ' . $productId .
                    '. Available: ' . $available . ', required: ' . $requested .
                    '. Update stock or correct order quantity before sending to courier.'
                );
            }
        }

        return $quantities;
    }

    /**
     * A courier API has confirmed a parcel. Update stock and order together.
     * When stock cannot be deducted, do not falsely mark the order Completed.
     * Keep remote identifiers where available to prevent accidental resending.
     */
    public function completeCourierOrder(
        Order $order,
        int $courierId,
        $consignmentId = null,
        $trackingCode = null
    ): void {
        try {
            DB::transaction(function () use ($order, $courierId, $consignmentId, $trackingCode) {
                $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                $this->onStatusChange($locked, 'Completed');
                $locked->courier_id = $courierId;
                $locked->status = 'Completed';
                if ($consignmentId !== null) {
                    $locked->consignment_id = $consignmentId;
                }
                if ($trackingCode !== null) {
                    $locked->tracking_code = $trackingCode;
                }
                $locked->save();
            }, 3);
        } catch (RuntimeException $exception) {
            // The shipment may already exist outside our database. Persist its
            // IDs for manual reconciliation, without marking stock as deducted.
            if ($consignmentId !== null || $trackingCode !== null) {
                DB::transaction(function () use ($order, $courierId, $consignmentId, $trackingCode) {
                    $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                    $locked->courier_id = $courierId;
                    if ($consignmentId !== null) {
                        $locked->consignment_id = $consignmentId;
                    }
                    if ($trackingCode !== null) {
                        $locked->tracking_code = $trackingCode;
                    }
                    $locked->save();
                }, 3);
            }
            throw new RuntimeException(
                'Courier accepted order #' . $order->id . ', but stock was NOT deducted: ' .
                $exception->getMessage() .
                ' Do not resend the parcel; correct stock and complete this order manually.'
            );
        }
    }

    /** Stock changes are idempotent across all Order Status controllers. */
    public function onStatusChange(Order $order, string $newStatus): void
    {
        if (in_array($newStatus, ['Canceled', 'Cancelled', 'Return'], true)) {
            $this->release($order);
            return;
        }

        // Completed is the ONLY status that deducts inventory.
        // Previously deducted orders keep stock_deducted=1 and are never deducted again.
        if ($newStatus === 'Completed') {
            $this->reserve($order);
        }
    }

    private function quantities(Order $order): array
    {
        $rows = OrderProducts::where('order_id', $order->id)
            ->selectRaw('product_id, SUM(quantity) as total_quantity')
            ->groupBy('product_id')
            ->orderBy('product_id')
            ->get();
        $quantities = [];
        foreach ($rows as $row) {
            $qty = (int) $row->total_quantity;
            if ($qty <= 0) {
                throw new RuntimeException('Invalid ordered quantity for product ID ' . $row->product_id . '.');
            }
            $quantities[(int) $row->product_id] = $qty;
        }
        return $quantities;
    }
}
