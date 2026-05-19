{*
 * Product Features module for PrestaShop.
 * @author APLINE Arkadiusz Pielechowski
 *}
{if $items|@count}
  <div class="apline-productfeatures">
    {foreach from=$items item=item}
      {if $item.url}<a href="{$item.url|escape:'html':'UTF-8'}" class="apf-row"{if $item.new_tab} target="_blank" rel="noopener noreferrer"{/if}>{else}<div class="apf-row">{/if}
        <span class="apf-icon">
          {if $item.image}
            <img src="{$item.image|escape:'html':'UTF-8'}" alt="{$item.alt|escape:'html':'UTF-8'}">
          {elseif $item.icon}
            <span class="apf-icon-entity">{$item.icon nofilter}</span>
          {/if}
        </span>
        <span class="apf-text" style="color:{$textColor|escape:'html':'UTF-8'};">{$item.text|escape:'html':'UTF-8'}</span>
      {if $item.url}</a>{else}</div>{/if}
    {/foreach}
  </div>
{/if}
