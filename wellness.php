<?php
$pageTitle = 'Mindful everyday living';
require __DIR__ . '/includes/header.php';

$cards = [
    ['bowl-food',    'Make room for variety',    'Build meals around a variety of foods that suit your needs and preferences. A single product does not replace a balanced diet.'],
    ['person-walking','Move in ways you enjoy',  'Find movement that fits your ability and schedule. A short walk or a gentle stretch can be a welcome change of pace.'],
    ['moon',         'Create space for rest',    'Make time to unwind. A calm evening routine and a consistent schedule can help you prioritize rest.'],
    ['glass-water',  'Keep water within reach',  'Keep drinking water accessible through the day. Ask your healthcare professional about your individual hydration needs.'],
    ['seedling',     'Start with one small habit','Choose a realistic routine and give yourself time to settle into it. Progress can look different for everyone.'],
    ['heart',        'Ask for guidance',         'For personal health questions, speak with a qualified healthcare professional who understands your situation.'],
];
?>
<main id="main" class="page-wrap">
    <div class="page-heading">
        <span class="eyebrow">THE WELLNESS JOURNAL</span>
        <h1>Grow at your own pace.</h1>
        <p>Simple ideas for a more mindful everyday.</p>
    </div>

    <div class="why-grid">
        <?php foreach ($cards as $c): ?>
            <article class="why-card">
                <i class="fa-solid fa-<?= $c[0] ?>"></i>
                <h3><?= $c[1] ?></h3>
                <p><?= $c[2] ?></p>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="notice" style="margin-top:30px">
        General lifestyle information only. Consult a qualified healthcare professional regarding medical conditions, medications, pregnancy, allergies, or other concerns.
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>