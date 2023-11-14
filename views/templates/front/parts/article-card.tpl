<div class="blog-article-card{if !empty($class)} {$class}{/if}">
    <div class="blog-article-card-text">
        {if $article.main_category.name}
            <div class="mb-2 small">
                <a href="{$article.main_category.url}" class="font-weight-bold">{$article.main_category.name}</a>
            </div>
        {/if}
        <h3 class="mb-1">
            <a href="{$article.url}">{$article.name}</a>
        </h3>
        <a href="{$article.url}" class="blog-article-card-desc">{$article.description nofilter}</a>
        <div class="mt-2 blog-article-card-text-meta">
            <span  class="blog-article-card-text-cat">{$article.date|date_format:"d/m/Y"}</span> &bull;
            {$article.read_time} min de lecture
        </div>
    </div>
    <a href="{$article.url}" aria-label="{$article.name}" class="blog-article-card-thumb">
        {if $article.thumbnail}
            <img class="  lazyload" data-src="{$article.thumbnail}" alt="{$article.name}"  >
        {/if}
    </a>
</div>
