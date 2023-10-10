{if empty($class)}
    {assign var="class" value=""}
{/if}

{if $articles|@count >= 1}
    <div class="blog-article-list">
        {foreach item=$article from=$articles}
            {include file='module:mdn_blog/views/templates/front/parts/article-card.tpl' article=$article}
        {/foreach}
    </div>
{else}
    <div class="alert alert-info">
        {l s='Cette catégorie est vide pour le moment'}
    </div>
{/if}