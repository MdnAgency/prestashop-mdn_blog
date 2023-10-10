<div class="blog-article-component-products">
    {foreach from=$products item="product"}
        {block name='product_miniature'}
            {include file='catalog/_partials/miniatures/product.tpl' product=$product}
        {/block}
    {/foreach}
</div>