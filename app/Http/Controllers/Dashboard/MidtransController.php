<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\ResourceController;
use App\Http\Requests\Dashboard\MidtransRefundRequest;
use App\Models\Charge;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class MidtransController extends ResourceController
{
    protected static string $permissionResource = 'payments';

    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public static function middleware(): array
    {
        $resource = static::$permissionResource;

        if (! $resource) {
            return [];
        }

        $allPermissions = implode('|', [
            "view-{$resource}",
            "create-{$resource}",
            "edit-{$resource}",
            "delete-{$resource}",
        ]);

        return [
            // Don't apply global role_or_permission middleware
            // Webhook notification doesn't need auth
            new Middleware(PermissionMiddleware::using("view-{$resource}"), only: ['index', 'show', 'getStatus', 'getSnapToken', 'getPaymentMethods']),
            new Middleware(PermissionMiddleware::using("create-{$resource}"), only: ['create', 'store']),
            new Middleware(PermissionMiddleware::using("edit-{$resource}"), only: ['edit', 'update', 'cancel', 'refund']),
            new Middleware(PermissionMiddleware::using("delete-{$resource}"), only: ['destroy']),
        ];
    }

    /**
     * Get snap token for payment
     */
    public function getSnapToken(Payment $paymentRecord)
    {
        try {
            $result = $this->midtransService->createTransaction($paymentRecord);

            if (! $result['success']) {
                return $this->error($result['message'], null, 400);
            }

            return $this->success([
                'snap_token' => $result['snap_token'],
                'order_id' => $result['order_id'],
            ], 'Snap token generated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to generate snap token: '.$e->getMessage(), null, 500);
        }
    }

    /**
     * Handle Midtrans payment status notification
     */
    public function notification(Request $request)
    {
        try {
            $data = $request->all();

            // Process callback
            $result = $this->midtransService->processCallback($data);

            if (! $result['success']) {
                return $this->error($result['message'], null, 400);
            }

            return $this->success($result, 'Notification processed');
        } catch (\Exception $e) {
            return $this->error('Notification processing failed: '.$e->getMessage(), null, 500);
        }
    }

    /**
     * Get transaction status
     */
    public function getStatus(string $chargeId)
    {
        try {
            $charge = Charge::where('order_id', $chargeId)->firstOrFail();
            $result = $this->midtransService->getTransactionStatus($charge->order_id);

            if (! $result['success']) {
                return $this->error($result['message'], null, 400);
            }

            return $this->success($result['data'], 'Transaction status retrieved');
        } catch (\Exception $e) {
            return $this->error('Failed to get status: '.$e->getMessage(), null, 500);
        }
    }

    /**
     * Cancel payment
     */
    public function cancel(Payment $paymentRecord)
    {
        try {
            $this->authorize('update', $paymentRecord);

            $charge = $paymentRecord->charges()->first();

            if (! $charge) {
                return $this->error('Charge record not found', null, 404);
            }

            $result = $this->midtransService->cancelTransaction($charge->order_id);

            if (! $result['success']) {
                return $this->error($result['message'], null, 400);
            }

            // Update payment status
            $paymentRecord->update(['status' => 'cancelled']);

            return $this->success($paymentRecord, $result['message']);
        } catch (\Exception $e) {
            return $this->error('Failed to cancel payment: '.$e->getMessage(), null, 500);
        }
    }

    /**
     * Refund payment
     */
    public function refund(MidtransRefundRequest $request, Payment $paymentRecord)
    {
        try {
            $this->authorize('update', $paymentRecord);

            $validated = $request->validated();
            $charge = $paymentRecord->charges()->latest()->first();

            if (! $charge) {
                return $this->error('Charge record not found', null, 404);
            }

            $refundAmount = $validated['amount'] ?? null;

            $result = $this->midtransService->refundTransaction($charge->order_id, $refundAmount);

            if (! $result['success']) {
                return $this->error($result['message'], null, 400);
            }

            // Update payment status
            $paymentRecord->update(['status' => 'refunded']);

            return $this->success($paymentRecord, $result['message']);
        } catch (\Exception $e) {
            return $this->error('Failed to refund payment: '.$e->getMessage(), null, 500);
        }
    }

    /**
     * Payment success callback (from client)
     */
    public function paymentSuccess(string $chargeId)
    {
        try {
            $charge = Charge::where('order_id', $chargeId)->firstOrFail();
            $payment = $charge->payment;

            // Verify final status with Midtrans
            $statusResult = $this->midtransService->getTransactionStatus($charge->order_id);

            if ($statusResult['success'] && in_array($statusResult['transaction_status'], ['settlement', 'capture'])) {
                return redirect()
                    ->route('dashboard.payments.show', $payment)
                    ->with('success', 'Payment successful!');
            }

            return redirect()
                ->route('dashboard.payments.show', $payment)
                ->with('info', 'Payment is being processed');
        } catch (\Exception $e) {
            return redirect()
                ->route('dashboard.payments.show')
                ->with('error', 'Error verifying payment: '.$e->getMessage());
        }
    }

    /**
     * Payment pending callback
     */
    public function paymentUnfinish(string $chargeId)
    {
        try {
            $charge = Charge::where('order_id', $chargeId)->firstOrFail();
            $payment = $charge->payment;

            return redirect()
                ->route('dashboard.payments.show', $payment)
                ->with('info', 'Payment is still pending. Please complete the process.');
        } catch (\Exception $e) {
            return redirect()
                ->route('dashboard.payments.show')
                ->with('error', 'Error: '.$e->getMessage());
        }
    }

    /**
     * Payment error callback
     */
    public function paymentError(string $chargeId)
    {
        try {
            $charge = Charge::where('order_id', $chargeId)->firstOrFail();
            $payment = $charge->payment;

            $payment->update(['status' => 'failed']);

            return redirect()
                ->route('dashboard.payments.show', $payment)
                ->with('error', 'Payment failed. Please try again.');
        } catch (\Exception $e) {
            return redirect()
                ->route('dashboard.payments.show')
                ->with('error', 'Error: '.$e->getMessage());
        }
    }

    /**
     * Get available payment methods
     */
    public function getPaymentMethods()
    {
        try {
            $methods = $this->midtransService->getPaymentMethods();

            return $this->success($methods, 'Payment methods retrieved');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve payment methods: '.$e->getMessage(), null, 500);
        }
    }
}
