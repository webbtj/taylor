<h1>{$title}</h1>

{$content}

{if $[[type]]}
    <ul>
        {foreach from=$[[type]] item = o}
            <li>
                <a href="{$o->permalink}">
                    {$o->post_title}
                </a>
            </li>
        {/foreach}
    </ul>
{/if}
