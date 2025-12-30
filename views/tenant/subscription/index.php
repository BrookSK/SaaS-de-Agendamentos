<?php
/** @var \App\Core\ResolvedTenant $tenant */
/** @var array<string,mixed>|null $subscription */
/** @var \App\Models\Plan|null $currentPlan */
/** @var array<int, \App\Models\Plan> $plans */

$prefix = $tenant->urlPrefix();
$status = is_array($subscription) ? (string)($subscription['status'] ?? '') : '';
$planName = $currentPlan?->name ?? '—';
$price = $currentPlan ? ('R$ ' . number_format($currentPlan->priceCents / 100, 2, ',', '.')) : '—';
$cycle = $currentPlan ? ($currentPlan->billingCycle === 'annual' ? 'ano' : ($currentPlan->billingCycle === 'semiannual' ? 'semestre' : 'mês')) : '';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assinatura</title>
</head>
<body>
    <div class="page">
        <div class="page-header">
            <div>
                <h1 class="page-title">Assinatura</h1>
                <p class="page-subtitle">Gerencie seu plano e compare recursos.</p>
            </div>
            <div class="page-meta">
                <a class="btn" href="<?php echo htmlspecialchars($prefix . '/dashboard'); ?>">Painel</a>
            </div>
        </div>

        <div class="tenant-subscription-layout">
            <div class="tenant-subscription-summary card">
                <div class="tenant-subscription-summary-header">
                    <div class="tenant-subscription-summary-title">Assinatura</div>
                    <a class="btn" href="#">Visualizar Fatura</a>
                </div>

                <div class="tenant-subscription-kv">
                    <div class="tenant-subscription-k">Plano</div>
                    <div class="tenant-subscription-v"><?php echo htmlspecialchars($planName); ?></div>
                </div>
                <div class="tenant-subscription-kv">
                    <div class="tenant-subscription-k">Preço</div>
                    <div class="tenant-subscription-v"><?php echo htmlspecialchars($price . ($cycle !== '' ? ' / ' . $cycle : '')); ?></div>
                </div>
                <div class="tenant-subscription-kv">
                    <div class="tenant-subscription-k">Status</div>
                    <div class="tenant-subscription-v">
                        <span class="badge"><?php echo htmlspecialchars($status !== '' ? $status : '—'); ?></span>
                    </div>
                </div>

                <div class="small" style="margin-top:10px;">
                    Para trocar de plano, selecione um plano ao lado.
                </div>
            </div>

            <div class="tenant-plans-grid">
                <?php foreach ($plans as $p): ?>
                    <?php
                    $pPrice = 'R$ ' . number_format($p->priceCents / 100, 2, ',', '.');
                    $pCycle = $p->billingCycle === 'annual' ? 'ano' : ($p->billingCycle === 'semiannual' ? 'semestre' : 'mês');
                    $isCurrent = $currentPlan !== null && $currentPlan->id === $p->id;
                    ?>
                    <div class="tenant-plan-card<?php echo $isCurrent ? ' is-current' : ''; ?>">
                        <div class="tenant-plan-name"><?php echo htmlspecialchars($p->name); ?></div>
                        <div class="tenant-plan-price"><span><?php echo htmlspecialchars($pPrice); ?></span> <small>/<?php echo htmlspecialchars($pCycle); ?></small></div>

                        <div class="tenant-plan-features">
                            <div class="tenant-plan-feature"><span class="ok">✓</span> Agendamentos ilimitados</div>
                            <div class="tenant-plan-feature"><span class="ok">✓</span> Clientes e serviços</div>
                            <div class="tenant-plan-feature"><span class="ok">✓</span> Notificações</div>
                            <div class="tenant-plan-feature"><span class="ok">✓</span> Relatórios</div>
                        </div>

                        <div class="tenant-plan-actions">
                            <?php if ($isCurrent): ?>
                                <button class="btn" type="button" disabled>Plano atual</button>
                            <?php else: ?>
                                <button class="btn" type="button">Selecionar</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
