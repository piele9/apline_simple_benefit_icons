{*
 * APLINE Simple Benefit Icons module for PrestaShop 9.
 * @author APLINE Arkadiusz Pielechowski
 *}
{if $items|@count}
  <div class="apline-simple-benefit-icons">
    {foreach from=$items item=item}
      {if $item.url}<a href="{$item.url|escape:'html':'UTF-8'}" class="asbi-row"{if $item.new_tab} target="_blank" rel="noopener noreferrer"{/if}>{else}<div class="asbi-row">{/if}
        <span class="asbi-icon">
          {if $item.image}
            <img src="{$item.image|escape:'html':'UTF-8'}" alt="{$item.alt|escape:'html':'UTF-8'}">
          {elseif $item.icon}
            <span class="asbi-icon-entity">{$item.icon nofilter}</span>
          {/if}
        </span>
        <span class="asbi-text" style="color:{$textColor|escape:'html':'UTF-8'};">{$item.text|escape:'html':'UTF-8'}</span>
      {if $item.url}</a>{else}</div>{/if}
    {/foreach}
  </div>
{/if}
