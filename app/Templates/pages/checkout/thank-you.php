<?php $order_id = $order_id ?? ''; ?>
<div class="thank-you-page">
    <div class="container">
        <h1 class="thank-you-page__title">Takk for bestillingen!</h1>
        <p class="thank-you-page__lead">Vi har mottatt bestillingen din og sender en bekreftelse på e-post.</p>
        <?php if ($order_id !== ''): ?>
        <p class="thank-you-page__id">Ordrenummer: <strong><?= e($order_id) ?></strong></p>
        <?php endif; ?>
        <p><a href="<?= url('/') ?>" class="btn btn--primary">Tilbake til forsiden</a></p>
    </div>
</div>
