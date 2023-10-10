<div class="row">
    <div class="col">
        <h2>Nos derniers articles</h2>
        {include file='module:mdn_blog/views/templates/front/parts/article-list.tpl' articles=$articles}
        <a class="btn btn-primary" href="{url entity='module' name='mdn_blog' controller='home' params = ['page' => null]}">Voir le blog</a>
    </div>
</div>