<nav class="blog-menu" class="nav d-flex gap-2 mt-2 mb-4">
    <a href="{url entity='module' name='mdn_blog' controller='home' params = ['page' => null]}" class="p-2 {if $current_category != 0}text-muted{/if}">{$root.name}</a>
    {foreach item=$category from=$categories }
        <a class="p-2 {if $current_category != $category.id}text-muted{/if}" href="{$category.url}">{$category.name}</a>
    {/foreach}
</nav>