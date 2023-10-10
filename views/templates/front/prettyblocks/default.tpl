<div class="row">
    <div class="col">
        {if !empty($block.settings.title)}
            <h2>{$block.settings.title}</h2>
        {/if}
        {include file='module:mdn_blog/views/templates/front/parts/article-list.tpl' articles=$block.extra.articles}
        <a class="btn btn-primary" href="{url entity='module' name='mdn_blog' controller='home' params = ['page' => null]}">Voir le blog</a>
    </div>
</div>