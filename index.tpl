{**
 * index.tpl
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * awSummary plugin index
 *
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.awsummary"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript" src="{$fullpath}/raphael-min.js"></script>
<script type="text/javascript" src="{$fullpath}/g.raphael-min.js"></script>
<script type="text/javascript" src="{$fullpath}/g.pie-min.js"></script>
<script type="text/javascript" src="{$fullpath}/g.bar-min.js"></script>
{literal}
<script type="text/javascript" charset="utf-8">
    window.onload = function () {
        var r = Raphael("trendholder");
        r.g.txtattr.font = "13px 'Fontin Sans', Fontin-Sans, sans-serif";

        r.g.text(200, 30, "Total Visits History").attr({"font-size": 18});

        var trendchart = r.g.barchart(40, 50, 370, 170, [[
{/literal}
{foreach from=$visitsHistory item=visit key=period}"{$visit}",{/foreach}null
{literal}
        ]], 0, {type: "sharp"});

{/literal}
{assign var="perpos" value="56"}
{foreach from=$visitsHistory item=visit key=period}
  {assign var="perout" value=$period}
  {if substr($period,0,2) eq 'nd'}
    {assign var="perout" value='nd\n-'}
  {/if}
          var t = r.text( {$perpos}, 216, "{$perout}");
  {assign var="perpos" value=$perpos+28}
{/foreach}
{literal}

        // make the last bar stand out
        var bar = trendchart.bars[0][11];
        bar.attr("fill", "#00aa00");




        var r = Raphael("geographic");
        r.g.txtattr.font = "13px 'Fontin Sans', Fontin-Sans, sans-serif";
        r.g.text(120, 20, "Geographic Areas").attr({"font-size": 18});
        r.g.piechart(120, 136, 92, [
{/literal}
{foreach from=$dpages item=page}{$page},{/foreach}null
{literal}
        ],{legend: [
{/literal}
{foreach from=$dpages item=page key=domain}"{$domains[$domain]} ({$page}%)",{/foreach}null
{literal}
        ]});

        var r = Raphael("searchengines");
        r.g.txtattr.font = "13px 'Fontin Sans', Fontin-Sans, sans-serif";
        r.g.text(120, 20, "Incoming Searches").attr({"font-size": 18});
        r.g.piechart(120, 136, 92, [
{/literal}
{foreach from=$incomingsearch item=queries}{$queries},{/foreach}null
{literal}
        ],{legend: [
{/literal}
{foreach from=$incomingsearch item=queries key=engine}"{$engine} ({$queries}%)",{/foreach}null
{literal}
        ]});


        var r = Raphael("originchartholder");
        r.g.txtattr.font = "13px 'Fontin Sans', Fontin-Sans, sans-serif";
        r.g.text(120, 20, "Origins of Incoming Traffic").attr({"font-size": 18});
        r.g.piechart(120, 136, 92, [
{/literal}
{foreach from=$origin item=queries key=qkey}{if array_key_exists($qkey, $originLabels)}{$queries},{/if}{/foreach}null
{literal}
        ],{legend: [
{/literal}
{foreach from=$origin item=queries key=qkey}{if array_key_exists($qkey, $originLabels)}"{$originLabels[$qkey]} ({$queries}%)",{/if}{/foreach}null
{literal}
        ]});

        awswitch('awindex');
    };

    function awswitch(dest) {
      document.getElementById('awindex').style.display='none';
      document.getElementById('pages').style.display='none';
      document.getElementById('searchenginesholder').style.display='none';
      document.getElementById('geographicholder').style.display='none';
      document.getElementById('incoming').style.display='none';

      document.getElementById(dest).style.display='block';
    }
</script>
{/literal}

<div class="awnav">
<button onclick="awswitch('awindex')">Visits</button>
<button onclick="awswitch('pages')">Pages</button>
<button onclick="awswitch('incoming')">Incoming Traffic</button>
<button onclick="awswitch('searchenginesholder')">Incoming Searches</button>
<button onclick="awswitch('geographicholder')">Geographic</button>
</div>

<p><strong>Date of Statistics:</strong> {$datedisplay}</p>



<div id="awindex">

<ul class="awlist">
{foreach from=$general item=gen key=section}
  {if array_key_exists($section, $metrics)}
    <li><strong>{$metrics[$section]}:</strong> {$gen}</li>
  {/if}
{/foreach}
<li><strong>Total Pages Viewed:</strong> {$totalpages}</li>
</ul>

<div id="trendholder" class="awchart">
</div>

</div>

<div id="pages">

<table class="awtable" summary="Article Pages">
<tr align="left"><th>Popular Article Pages</th><th align="right">Count</th></tr>
{foreach from=$toparticles item=inc key=ikey}
    <tr><td><a target="_blank" href="{$ikey}">{$toparticlesnames[$ikey]}</a></td><td align="right">{$inc}</td></tr>
{/foreach}
</table>

<table class="awtable" summary="Popular Pages">
<tr align="left"><th>Other Popular Pages</th><th align="right">Count</th></tr>
{foreach from=$toppages item=inc key=ikey}
    <tr><td><a target="_blank" href="{$ikey}">{$ikey}</a></td><td align="right">{$inc}</td></tr>
{/foreach}
</table>

</div>



<div id="incoming">
<div id="originchartholder" class="awchart">
</div>

<table class="awtable" summary="Incoming Links">
<tr align="left"><th>Incoming Links</th><th align="right">Count</th></tr>
{foreach from=$topincoming item=inc key=ikey}
    <tr><td><a target="_blank" href="{$ikey}">{$ikey}</a></td><td align="right">{$inc}</td></tr>
{/foreach}
</table>

</div>



<div id="geographicholder">
<div id="geographic" class="awchart">
</div>

<table class="awtable" summary="Cities">
<tr align="left"><th>Top Cities</th></tr>
{foreach from=$cities item=inc key=ikey}
    <tr><td>{$ikey}</td></tr>
{/foreach}
</table>

</div>



<div id="searchenginesholder">
<div id="searchengines" class="awchart">
</div>

<table class="awtable" summary="Search Keywords">
<tr><th>Search Keywords</th><th align="right">Count</th></tr>
{foreach from=$searchwords item=inc key=ikey}
    <tr><td>{$ikey}</td><td align="right">{$inc}</td></tr>
{/foreach}
</table>

</div>



{include file="common/footer.tpl"}
