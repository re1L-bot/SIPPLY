<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\View\Factory;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

class OrderConfirmationMailer
{
    public function __construct(private readonly Factory $views) {}

    public function send(int $orderId, string $recipientEmail): void
    {
        $order = DB::table('orders')
            ->join('payments', 'payments.order_id', '=', 'orders.order_id')
            ->join('payment_methods', 'payment_methods.payment_method_id', '=', 'payments.payment_method_id')
            ->where('orders.order_id', $orderId)
            ->where('orders.customer_email', $recipientEmail)
            ->first([
                'orders.order_number',
                'orders.recipient_name',
                'orders.delivery_address',
                'orders.subtotal',
                'orders.delivery_fee',
                'orders.discount_amount',
                'orders.coupon_code',
                'orders.grand_total',
                'orders.placed_at',
                'payment_methods.method_name',
            ]);

        if ($order === null) {
            throw new RuntimeException('The order could not be found for its confirmation email.');
        }

        $items = DB::table('order_items')
            ->where('order_id', $orderId)
            ->orderBy('order_item_id')
            ->get([
                'product_name',
                'size_name',
                'quantity',
                'unit_price',
                'line_total',
            ]);
        $this->sendHtmlEmail(
            $recipientEmail,
            $order->recipient_name,
            'Order confirmation '.$order->order_number,
            'emails.orders.confirmation',
            [
                'order' => $order,
                'items' => $items,
            ],
        );
    }

    public function sendDelivered(int $orderId): void
    {
        $order = DB::table('orders')
            ->leftJoin('users', 'users.user_id', '=', 'orders.user_id')
            ->where('orders.order_id', $orderId)
            ->first([
                'orders.order_number',
                'orders.recipient_name',
                'orders.delivery_address',
                DB::raw('COALESCE(orders.customer_email, users.email) as recipient_email'),
            ]);

        if ($order === null || $order->recipient_email === null) {
            throw new RuntimeException('The order and its customer email are required for a delivery email.');
        }

        $this->sendHtmlEmail(
            $order->recipient_email,
            $order->recipient_name,
            'Your order has been delivered '.$order->order_number,
            'emails.orders.delivered',
            ['order' => $order],
        );
    }

    public function sendCancelled(int $orderId, ?string $reason = null): void
    {
        $order = DB::table('orders')
            ->leftJoin('users', 'users.user_id', '=', 'orders.user_id')
            ->where('orders.order_id', $orderId)
            ->first([
                'orders.order_number',
                'orders.recipient_name',
                'orders.delivery_address',
                DB::raw('COALESCE(orders.customer_email, users.email) as recipient_email'),
            ]);

        if ($order === null || $order->recipient_email === null) {
            throw new RuntimeException('The order and its customer email are required for a cancellation email.');
        }

        if ($reason === null || trim($reason) === '') {
            $reason = (string) DB::table('order_status_history')
                ->where('order_id', $orderId)
                ->where('status', 'cancelled')
                ->orderByDesc('changed_at')
                ->value('notes');
        }

        $this->sendHtmlEmail(
            $order->recipient_email,
            $order->recipient_name,
            'Your order has been cancelled '.$order->order_number,
            'emails.orders.cancelled',
            [
                'order' => $order,
                'cancellationReason' => $reason,
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function sendHtmlEmail(
        string $recipientEmail,
        string $recipientName,
        string $subject,
        string $view,
        array $data,
    ): void {
        $host = (string) config('mail.mailers.smtp.host');
        $fromAddress = (string) config('mail.from.address');

        if ($host === '' || $fromAddress === '') {
            throw new RuntimeException('SMTP host and sender address must be configured to send order emails.');
        }

        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $host;
        $mailer->Port = (int) config('mail.mailers.smtp.port', 587);
        $mailer->Timeout = 15;
        $mailer->CharSet = PHPMailer::CHARSET_UTF8;
        $mailer->SMTPAuth = (string) config('mail.mailers.smtp.username', '') !== '';

        if ($mailer->SMTPAuth) {
            $mailer->Username = (string) config('mail.mailers.smtp.username');
            $mailer->Password = (string) config('mail.mailers.smtp.password');
        }

        $encryption = strtolower((string) config('mail.mailers.smtp.encryption', ''));
        if ($encryption === 'tls' || $encryption === 'starttls') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl' || $encryption === 'smtps') {
            $mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        $mailer->setFrom($fromAddress, (string) config('mail.from.name'));
        $mailer->addAddress($recipientEmail, $recipientName);
        $mailer->isHTML();
        $mailer->Subject = $subject;
        $mailer->Body = $this->views->make($view, $data)->render();
        $mailer->AltBody = strip_tags(str_replace(
            ['</p>', '</div>', '</tr>', '<br>', '<br/>', '<br />'],
            ["\n", "\n", "\n", "\n", "\n", "\n"],
            $mailer->Body,
        ));

        $mailer->send();
    }
}
