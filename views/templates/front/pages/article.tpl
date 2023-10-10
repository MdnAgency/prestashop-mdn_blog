{extends file='page.tpl'}
{include file='module:mdn_blog/views/templates/front/seo/article.tpl'}
{block name='pageWrapperClass'}{/block}
{block name='pageContentClass'}{/block}

{block name='page_content'}
    {include file='module:mdn_blog/views/templates/front/parts/blog-header-article.tpl' article=$article}

    <article class="blog-article-content">
        {$article.article nofilter}
    </article>

    {if $related_articles|@count >= 1}
        <div class="mb-4">
            <h2 class="mb-5 text-center">{l s='Ces articles pourraient aussi vous intéresser'}</h2>
            {include file='module:mdn_blog/views/templates/front/parts/article-list.tpl' articles=$related_articles}
        </div>
    {/if}
    <div class="text-center mt-5 mb-3">
        <a class="btn btn-primary"  href="{url entity='module' name='mdn_blog' controller='home' params = ['page' => null]}" >Retourner au blog</a>
    </div>
{/block}