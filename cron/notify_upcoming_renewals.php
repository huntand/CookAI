<?php
/**
 * CookAI — уведомления о предстоящем автосписании (ежедневный крон).
 * CLI: php cron/notify_upcoming_renewals.php
 * HTTP: /cron/notify_upcoming_renewals.php?token=CRON_SECRET
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/mailer.php';

if (PHP_SAPI !== 'cli') {
    if (!hash_equals(CRON_SECRET, $_GET['token'] ?? '')) { http_response_code(403); exit('Forbidden'); }
    header('Content-Type: text/plain; charset=utf-8');
}

$log = fn(string $m) => print('[' . date('Y-m-d H:i:s') . '] ' . $m . PHP_EOL);
$log('=== Уведомления о продлении: старт ===');

$targetDate = date('Y-m-d', strtotime('+' . RENEW_NOTIFY_DAYS . ' days'));

$rows = db_all(
    "SELECT * FROM subscriptions
     WHERE auto_renew = 1 AND status = 'active' AND renewal_notified = 0
       AND next_charge_date = ?",
    [$targetDate]
);
$log('Найдено к уведомлению: ' . count($rows));

foreach ($rows as $sub) {
    $email  = $sub['user_email'];
    $amount = (float) $sub['amount'];
    $date   = date('d.m.Y', strtotime($sub['next_charge_date']));
    $plan   = ($sub['plan'] ?? 'monthly') === 'yearly' ? 'годовая' : 'месячная';

    $html = render_renewal_email($email, $amount, $date, $plan);

    try {
        send_mail($email, 'CookAI Pro — скоро автопродление подписки', $html);
        db_exec('UPDATE subscriptions SET renewal_notified = 1 WHERE id = ?', [$sub['id']]);

        db_insert(
            'INSERT INTO notifications (user_email, title, message, type, created_at) VALUES (?,?,?,?, NOW())',
            [$email, 'Скоро автопродление 🔔',
             number_format($amount, 0) . "₽ спишется {$date}. Управлять можно в разделе «Платежи».", 'payment']
        );
        $log("  ✅ Отправлено {$email}");
    } catch (Throwable $ex) {
        $log("  ⚠️ Ошибка {$email}: " . $ex->getMessage());
    }
}
$log('=== Уведомления о продлении: конец ===');

function render_renewal_email(string $email, float $amount, string $date, string $plan): string
{
    $manageUrl = SITE_URL . '/billing';
    $sum = number_format($amount, 0);
    return '<div style="font-family:Arial,sans-serif;max-width:520px;margin:auto;color:#374151;">
        <h2 style="color:#7c3aed;">🍳 CookAI Pro — автопродление</h2>
        <p>Здравствуйте!</p>
        <p>Ваша <b>' . $plan . '</b> подписка CookAI Pro будет автоматически продлена
           <b>' . $date . '</b>. К списанию: <b>' . $sum . '₽</b>.</p>
        <p>Если хотите отменить автопродление или подписку — сделайте это до даты списания:</p>
        <p style="text-align:center;margin:24px 0;">
            <a href="' . $manageUrl . '" style="background:#7c3aed;color:#fff;text-decoration:none;
               padding:12px 24px;border-radius:12px;font-weight:bold;">Управлять подпиской</a>
        </p>
        <p style="color:#9ca3af;font-size:12px;">Если всё в порядке — ничего делать не нужно, доступ продлится автоматически.</p>
    </div>';
}