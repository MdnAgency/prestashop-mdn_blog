<div class="blog-article-component-categories">
    {foreach from=$categories item="category"}
        <a target="_blank" href="{url entity='category' id=$category.id}" class="blog-category-item">
            <strong>{$category.name}</strong><br/>
            {l s='Voir les produits'}
        </a>
    {/foreach}
</div>