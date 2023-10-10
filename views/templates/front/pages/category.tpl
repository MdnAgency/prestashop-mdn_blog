{extends file='page.tpl'}
{include file='module:mdn_blog/views/templates/front/seo/category.tpl'}

{block name='pageWrapperClass'}{/block}
{block name='pageContentClass'}{/block}

{block name='page_content'}
    {include file='module:mdn_blog/views/templates/front/parts/menu.tpl' categories=$categories current_category=$current_category current_page=$current_page}
    {include file='module:mdn_blog/views/templates/front/parts/blog-header.tpl' title=$category.name content=$category.description}


    {include file='module:mdn_blog/views/templates/front/parts/article-list.tpl' articles=$articles}
    {include file='module:mdn_blog/views/templates/front/parts/pagination.tpl' articles=$pagination}
{/block}