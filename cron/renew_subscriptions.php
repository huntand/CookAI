<?php
/**
 * CookAI — автопродление подписок (ежедневный крон).
 * CLI: php cron/renew_subscriptions.php
 * HTTP: /cron/renew_subscriptions.php?token=CRON_SECRET
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/yookassa.php';

if (PHP_SAPI !== 'cli') {
    if (!hash_equals(CRON_SECRET, $_GET['token'] ?? '')) { http_response_code(403); exit('Forbidden'); }
    header('Content-Type: text/plain; charset=utf-8');
}

$log = fn(string $m) => print('[' . date('Y-m-d H:i:s') . '] ' . $m . PHP_EOL);
$log('=== Автопродление: старт ===');

$due = db_all(
    "SELECT * FROM subscriptions
     WHERE auto_renew = 1 AND status = 'active'
       AND payment_method_id IS NOT NULL
       AND next_charge_date IS NOT NULL AND next_charge_date <= CURDATE()
       AND renew_attempts < ?",
    [MAX_RENEW_ATTEMPTS]
);
$log('К продлению: ' . count($due));

foreach ($due as $sub) {
    $email  = $sub['user_email'];
    $months = max(1, (int) $sub['months']);
    $amount = (float) $sub['amount'];
    $plan   = $sub['plan'] ?? 'monthly';
    $log("→ #{$sub['id']} {$email} на {$amount}₽");

    try {
        $payment = yookassa_charge_recurring(
            $sub['payment_method_id'], $amount,
            'CookAI Pro — автопродление (' . ($plan === 'yearly' ? '1 год' : '1 месяц') . ')',
            ['email'=>$email,'plan'=>$plan,'months'=>(string)$months,'recurring'=>'1',
             'parent_id'=>(string)($sub['parent_id'] ?: $sub['id'])]
        );
        $status = $payment['status'] ?? '';

        if ($status === 'succeeded') {
            renew_success($sub, $months, $amount, $payment['id'] ?? null, $log);
        } elseif ($status === 'pending') {
            $log('  ⏳ pending — проверим позже');
        } else {
            renew_failed($sub, $log, 'status=' . $status);
        }
    } catch (Throwable $ex) {
        renew_failed($sub, $log, $ex->getMessage());
    }
}
$log('=== Автопродление: конец ===');

function renew_success(array $sub, int $months, float $amount, ?string $payId, callable $log): void
{
    $email  = $sub['user_email'];
    $newEnd = date('Y-m-d', strtotime($sub['subscription_end_date'] . " +{$months} months"));

    db_insert(
        'INSERT INTO subscriptions
         (parent_id, user_email, status, plan, months, amount, auto_renew,
          payment_id, payment_method_id, subscription_end_date, next_charge_date,
          renewal_notified, paid_at, created_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,0, NOW(), NOW())',
        [$sub['parent_id'] ?: $sub['id'], $email, 'active', $sub['plan'], $months, $amount, 1,
         $payId, $sub['payment_method_id'], $newEnd, $newEnd]
    );
    db_exec('UPDATE subscriptions SET auto_renew=0, next_charge_date=NULL, status=? WHERE id=?',
        ['expired', $sub['id']]);

    db_insert(
        'INSERT INTO notifications (user_email, title, message, type, created_at) VALUES (?,?,?,?, NOW())',
        [$email, 'Подписка продлена 🔄',
         'CookAI Pro продлён до ' . date('d.m.Y', strtotime($newEnd)), 'payment']
    );
    $log("  ✅ Продлено до {$newEnd}");
}

function renew_failed(array $sub, callable $log, string $reason): void
{
    $attempts = (int) $sub['renew_attempts'] + 1;
    $email    = $sub['user_email'];

    if ($attempts >= MAX_RENEW_ATTEMPTS) {
        db_exec('UPDATE subscriptions SET renew_attempts=?, auto_renew=0, next_charge_date=NULL WHERE id=?',
            [$attempts, $sub['id']]);
        db_insert(
            'INSERT INTO notifications (user_email, title, message, type, created_at) VALUES (?,?,?,?, NOW())',
            [$email, 'Не удалось продлить подписку ⚠️',
             'Автопродление отключено после ' . MAX_RENEW_ATTEMPTS . ' попыток. Продлите вручную.', 'payment']
        );
        $log("  ❌ {$reason}. Попытки исчерпаны.");
    } else {
        db_exec('UPDATE subscriptions SET renew_attempts=?, next_charge_date=? WHERE id=?',
            [$attempts, date('Y-m-d', strtotime('+1 day')), $sub['id']]);
        $log("  ⚠️ {$reason}. Попытка {$attempts}/" . MAX_RENEW_ATTEMPTS . ", повтор завтра.");
    }
}