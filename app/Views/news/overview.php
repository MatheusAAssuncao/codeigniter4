<h2><?= esc($title) ?></h2>

<?php if (! empty($news) && is_array($news)): ?>

    <?php foreach ($news as $news_item): ?>

        <h3><?= esc($news_item['title']) ?></h3>

        <div class="main">
            <?= esc($news_item['body']) ?>
        </div>
        <p><a href="/news/<?= esc($news_item['slug'], 'url') ?>">Ver notícia</a></p>

    <?php endforeach ?>

<?php else: ?>

    <h3>Nenhuma notícia</h3>

    <p>Não foram encontradas notícias para você.</p>

<?php endif ?>