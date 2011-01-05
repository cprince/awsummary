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
{literal}
<script type="text/javascript" charset="utf-8">
    window.onload = function () {
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

        awswitch('awindex');
    };

    function awswitch(dest) {

      switch(dest) {
      case 'awindex':
        document.getElementById('awindex').style.display='block';
        document.getElementById('searchenginesholder').style.display='none';
        document.getElementById('geographicholder').style.display='none';
        document.getElementById('incominglinks').style.display='none';
        break;
      case 'searchenginesholder':
        document.getElementById('awindex').style.display='none';
        document.getElementById('searchenginesholder').style.display='block';
        document.getElementById('geographicholder').style.display='none';
        document.getElementById('incominglinks').style.display='none';
        break;
      case 'geographicholder':
        document.getElementById('awindex').style.display='none';
        document.getElementById('searchenginesholder').style.display='none';
        document.getElementById('geographicholder').style.display='block';
        document.getElementById('incominglinks').style.display='none';
        break;
      case 'incominglinks':
        document.getElementById('awindex').style.display='none';
        document.getElementById('searchenginesholder').style.display='none';
        document.getElementById('geographicholder').style.display='none';
        document.getElementById('incominglinks').style.display='block';
        break;
      default:
        //code to be executed
      }
    }
</script>
{/literal}

<button onclick="awswitch('awindex')">Index</button>
<button onclick="awswitch('searchenginesholder')">Incoming Searches</button>
<button onclick="awswitch('geographicholder')">Geographic</button>
<button onclick="awswitch('incominglinks')">Incoming Links</button>

<p><strong>Date of statistics:</strong> {$datedisplay}</p>



<div id="awindex">

<table style="width: 120px">
{foreach from=$general item=gen key=section}
  {if array_key_exists($section, $metrics)}
    <tr><th>{$metrics[$section]}</th><td align="right">{$gen}</td></tr>
  {/if}
{/foreach}
</table>

<p><strong>Total pages viewed:</strong> {$totalpages}</p>

<table class="awtable" summary="Popular Pages">
<tr><th>Popular Pages</th><th align="right">Count</th></tr>
{foreach from=$toppages item=inc key=ikey}
    <tr><td><a target="_blank" href="{$ikey}">{$ikey}</a></td><td align="right">{$inc}</td></tr>
{/foreach}
</table>

<table class="awtable" summary="Article Pages">
<tr><th>Popular Article Pages</th><th align="right">Count</th></tr>
{foreach from=$toparticles item=inc key=ikey}
    <tr><td><a target="_blank" href="{$ikey}">{$toparticlesnames[$ikey]}</a></td><td align="right">{$inc}</td></tr>
{/foreach}
</table>

</div>



<div id="incominglinks">

<table class="awtable" summary="Incoming Links">
<tr><th>Incoming Links</th><th align="right">Count</th></tr>
{foreach from=$topincoming item=inc key=ikey}
    <tr><td><a target="_blank" href="{$ikey}">{$ikey}</a></td><td align="right">{$inc}</td></tr>
{/foreach}
</table>

</div>



<div id="geographicholder">
<div id="geographic" class="awchart">
</div>

<table class="awtable" summary="Cities">
<tr><th>Top Cities</th></tr>
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
