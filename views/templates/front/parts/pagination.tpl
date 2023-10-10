
<ul class="page-list pagination justify-content-center justify-content-lg-end mt-3 mt-lg-0 mb-0">
    {foreach from=$pagination item="page"}
        <li class="page-item{if $page.current} active{/if}{if !$page.clickable && !$page.current} disabled{/if}">
            {if $page.type === 'spacer'}
                <span class="page-link" aria-hidden="true">&hellip;</span>
            {else}
                <a
                        rel="{if $page.type === 'previous'}prev{elseif $page.type === 'next'}next{else}nofollow{/if}"
                        href="{$page.url}"
                        class="page-link {if $page.type === 'previous'}previous {elseif $page.type === 'next'}next {/if}"{if !$page.clickable} tabindex="-1"{/if}
                >
                    {if $page.type === 'previous'}
                        <span class="sr-only">{l s='Previous' d='Shop.Theme.Actions'}</span>
                        <i class="material-icons" aria-hidden="true">&#xE314;</i>
                    {elseif $page.type === 'next'}
                        <span class="sr-only">{l s='Next' d='Shop.Theme.Actions'}</span><i class="material-icons" aria-hidden="true">&#xE315;</i>
                    {else}
                        {$page.page}
                    {/if}
                </a>
            {/if}
        </li>
    {/foreach}
</ul>