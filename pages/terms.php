<?php
/**
 * CookAI — Пользовательское соглашение и условия подписки.
 */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Условия использования';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="max-w-3xl mx-auto px-4 sm:px-6 py-10">
    <h1 class="text-3xl font-extrabold text-gray-800 mb-2" data-aos="fade-up">Пользовательское соглашение</h1>
    <p class="text-sm text-gray-400 mb-8" data-aos="fade-up" data-aos-delay="50">
        Редакция от <?= date('d.m.Y') ?>
    </p>

    <div class="bg-white rounded-3xl shadow-md p-6 sm:p-8 space-y-6 text-gray-600 leading-relaxed" data-aos="fade-up" data-aos-delay="100">

        <div>
            <h2 class="text-lg font-extrabold text-gray-800 mb-2">1. Общие положения</h2>
            <p>Настоящее Соглашение регулирует отношения между сервисом CookAI (далее — «Сервис») и пользователем при использовании функций Сервиса, включая платную подписку CookAI Pro. Используя Сервис, вы подтверждаете согласие с условиями.</p>
        </div>

        <div>
            <h2 class="text-lg font-extrabold text-gray-800 mb-2">2. Подписка CookAI Pro</h2>
            <p>Подписка предоставляет доступ к расширенным возможностям: безлимитная генерация рецептов, AI-фото блюд, сканер калорий, персональная AI-книга рецептов и приоритетная поддержка.</p>
            <ul class="list-disc list-inside mt-2 space-y-1">
                <li>Месячная подписка — <?= number_format(PLAN_MONTHLY_PRICE, 0) ?>₽ за 1 месяц.</li>
                <li>Годовая подписка — <?= number_format(PLAN_YEARLY_PRICE, 0) ?>₽ за 12 месяцев.</li>
            </ul>
        </div>

        <div>
            <h2 class="text-lg font-extrabold text-gray-800 mb-2">3. Автоматическое продление (рекуррентные платежи)</h2>
            <p>При включённом автопродлении оплата за следующий период списывается автоматически с привязанной банковской карты в дату окончания текущего периода.</p>
            <ul class="list-disc list-inside mt-2 space-y-1">
                <li>Согласие на рекуррентные списания даётся при оформлении подписки (отметка «Включить автопродление»).</li>
                <li>Уведомление о предстоящем списании направляется на e-mail за <?= RENEW_NOTIFY_DAYS ?> дня.</li>
                <li>Отключить автопродление можно в любой момент в разделе «Платежи» — доступ сохранится до конца оплаченного периода.</li>
                <li>При неуспешном списании выполняется до <?= MAX_RENEW_ATTEMPTS ?> повторных попыток; затем автопродление отключается.</li>
            </ul>
        </div>

        <div>
            <h2 class="text-lg font-extrabold text-gray-800 mb-2">4. Возврат средств</h2>
            <p>Пользователь вправе запросить возврат средств за оплаченную подписку в течение <?= REFUND_WINDOW_DAYS ?> дней с даты оплаты через раздел «Платежи». После возврата подписка отменяется, доступ к CookAI Pro прекращается.</p>
            <p class="mt-2">Возврат осуществляется на ту же карту, с которой производилась оплата. Срок зачисления зависит от банка (обычно до 10 рабочих дней).</p>
        </div>

        <div>
            <h2 class="text-lg font-extrabold text-gray-800 mb-2">5. Промокоды и скидки</h2>
            <ul class="list-disc list-inside space-y-1">
                <li>Промокоды предоставляют скидку на первую подписку (если не указано иное).</li>
                <li>Один промокод применяется к одному заказу; суммирование промокодов не допускается.</li>
                <li>Промокоды имеют ограниченный срок действия и количество использований.</li>
                <li>Скидка по промокоду не распространяется на последующие автопродления — они оплачиваются по полной стоимости тарифа.</li>
            </ul>
        </div>

        <div>
            <h2 class="text-lg font-extrabold text-gray-800 mb-2">6. Платежи и квитанции</h2>
            <p>Приём платежей осуществляется через сертифицированный платёжный сервис ЮKassa. Сервис не хранит полные данные банковских карт. После успешной оплаты пользователю доступна электронная квитанция в формате PDF в разделе «Платежи».</p>
        </div>

        <div>
            <h2 class="text-lg font-extrabold text-gray-800 mb-2">7. Ограничение ответственности</h2>
            <p>Рецепты, значения калорийности и рекомендации, генерируемые искусственным интеллектом, носят информационный характер и могут содержать неточности. Пользователь самостоятельно оценивает пригодность блюд с учётом аллергий, диет и состояния здоровья.</p>
        </div>

        <div>
            <h2 class="text-lg font-extrabold text-gray-800 mb-2">8. Контакты</h2>
            <p>По вопросам подписки, возвратов и работы Сервиса обращайтесь в поддержку: <a href="mailto:support@cookai.ru" class="text-violet-600 font-semibold underline">support@cookai.ru</a>.</p>
        </div>

    </div>

    <div class="text-center mt-8" data-aos="fade-up">
        <a href="<?= url('payment') ?>" class="inline-block px-6 py-3 rounded-xl bg-gradient-to-r from-violet-300 to-purple-300 text-violet-900 font-bold hover:shadow-md transition">
            Перейти к оформлению подписки
        </a>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>