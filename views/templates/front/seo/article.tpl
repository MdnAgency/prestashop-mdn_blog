{block name='head_seo_title'}{if $article.meta_title}{$article.meta_title}{else}{$article.name}{/if}{/block}
{block name='head_seo_description'}{if $article.meta_description}{$article.meta_description}{else}{$article.description}{/if}{/block}
{block name='head_seo_keywords'}{$article.meta_keywords}{/block}
{block name='hook_extra'}
    <meta property="twitter:title" content="{$article.meta_title}" />
    <meta property="twitter:description" content="{$article.meta_description}" />
    <meta property="og:title" content="{$article.meta_title}" />
    <meta property="og:type" content="article" />
    {if $article.thumbnail}
        <meta property="twitter:image" content="{$article.thumbnail}" />
        <meta property="og:image" content="{$article.thumbnail}" />
    {/if}
    <meta property="article:published_time" content="{$article.date}" />
    <meta property="article:modified_time" content="{$article.date}" />
    <meta property="article:section" content="{$category.name}" />
    <meta property="article:author" content="{$shop.name}" />

    <script type="application/ld+json">
        {
            "@context":"https://schema.org",
            "@type":"NewsArticle",
            "mainEntityOfPage":{
                "@type":"WebPage",
                "@id":"{$article.url}"
            },
            "headline":"{$article.name}",
            "dateCreated":"{$article.date}",
            "datePublished":"{$article.date}",
            "dateModified":"{$article.date}",
            "publisher":{
                "@type":"Organization",
                "name":"{$shop.name}",
                "logo":{
                    "@type":"ImageObject",
                    "url":"{$shop.logo}"
                }
            },
            "description":"{$article.meta_description}"
            {if $article.thumbnail},
            "image":{
                "@type":"ImageObject",
                "url":"{$article.thumbnail}"
            }
            {/if}
        }
  </script>
    <script type="application/ld+json">
        {
            "@context": "https://schema.org/",
            "@type": "Blog",
            "@id": "{url entity='module' name='mdn_blog' controller='home' params = ['page' => null]}",
            "mainEntityOfPage": "{url entity='module' name='mdn_blog' controller='home' params = ['page' => null]}",
            "name": "{$root.meta_title}",
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
