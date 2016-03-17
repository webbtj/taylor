                {if $[[opposite]]}
                    <p>[[opposite]]:</p>
                    <ul>
                        {foreach from=$[[opposite]] item=o}
                            <li>
                                <p>post_title: {$o->post_title}</p>
                                <p>url: {$o->url}</p>
                            </li>
                        {/foreach}
                    </ul>
                {/if}