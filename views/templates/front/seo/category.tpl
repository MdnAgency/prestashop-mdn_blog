
{block name='head_seo_title'}{if $category.meta_title}{$category.meta_title}{else}{$category.name}{/if}{/block}
{block name='head_seo_description'}{if $category.meta_description}{$category.meta_description}{else}{$category.description}{/if}{/block}
{block name='head_seo_keywords'}{$category.meta_keywords}{/block}
{block name='hook_extra'}
    <meta property="og:title" content="{$category.meta_title}" />
    <meta property="og:type" content="website" />
    <script type="application/ld+json">
        {
            "@context": "https://schema.org/",
            "@type": "Blog",
            "@id": "{url entity='module' name='mdn_blog' controller='home' params = ['page' => null]}",
            "mainEntityOfPage": "{url entity='module' name='mdn_blog' controller='home' params = ['page' => null]}",
            "name": "{$root.meta_name}",
            "description": "{$root.meta_description}",
            "publisher": {
                "@type":"Organization",
                "name":"{$shop.name}",
                "logo":{
                    "@type":"ImageObject",
                    "url":"{$shop.logo}"
                }
            }
        }
    </script>
{/block}