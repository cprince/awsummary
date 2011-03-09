{**
 * download.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * awSummary plugin download
 *
 *}
{translate key="plugins.generic.awsummary.dateofstatistics"} {$datedisplay}

{if $report eq 'toparticles'}
{translate key="plugins.generic.awsummary.toparticles"}

{foreach from=$toparticles_full item=inc key=ikey}
"{$baseUrl}{$ikey}"{$separator}"{$toparticlesnames_full[$ikey]}"{$separator}{$inc}
{/foreach}

{elseif $report eq 'toppages'}
{translate key="plugins.generic.awsummary.toppages"}

{foreach from=$toppages_full item=inc key=ikey}
"{$baseUrl}{$ikey}"{$separator}{$inc}
{/foreach}

{elseif $report eq 'topincoming'}
{translate key="plugins.generic.awsummary.topincoming"}

{foreach from=$topincoming_full item=inc key=ikey}
"{$ikey}"{$separator}{$inc}
{/foreach}

{elseif $report eq 'cities'}
{translate key="plugins.generic.awsummary.cities"}

{foreach from=$cities_full item=inc key=ikey}
"{$ikey}"{$separator}{$inc}
{/foreach}

{elseif $report eq 'searchwords'}
{translate key="plugins.generic.awsummary.searchwords"}

{foreach from=$searchwords_full item=inc key=ikey}
"{$ikey}"{$separator}{$inc}
{/foreach}

{/if}
