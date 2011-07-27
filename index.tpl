{**
 * index.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * awSummary plugin index
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.awsummary"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript" src="{$fullpath}/raphael-min.js"></script>
<script type="text/javascript" src="{$fullpath}/g.raphael-min.js"></script>
<script type="text/javascript" src="{$fullpath}/g.pie-min.js"></script>
<script type="text/javascript" src="{$fullpath}/g.bar-min.js"></script>
<script type="text/javascript" src="{$fullpath}/citylatlng.js"></script>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
{literal}
<script type="text/javascript" charset="utf-8">
    window.onload = function () {
        var r = Raphael("trendholder");
        r.g.txtattr.font = "13px 'Fontin Sans', Fontin-Sans, sans-serif";

        r.g.text(200, 22, "Total Visits History").attr({"font-size": 18});

{/literal}
				visitsHistoryjson = {$visitsHistoryjson};
{literal}

        var trendchart = r.g.barchart(26, 26, 372, 198, [visitsHistoryjson], 0, {type: "sharp"});


{/literal}
{assign var="perpos" value="42"}
{foreach from=$visitsHistory item=visit key=period}
  {assign var="perout" value=$period}
  {if substr($period,0,2) eq 'nd'}
    {assign var="perout" value='nd\n-'}
  {/if}
          var t = r.text( {$perpos}, 220, "{$perout}");
  {assign var="perpos" value=$perpos+28}
{/foreach}
{literal}

        // make the last bar stand out
        var bar = trendchart.bars[0][12];
        bar.attr("fill", "#8aa717");




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

/* ============================================== */

{/literal}
var cities_mappable = {$cities_mappable};
{literal}
        var latlng = new google.maps.LatLng(70, -179);
        var myOptions = {
          zoom: 1,
          center: latlng,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        // map is a global variable so that it will be available in awswitch()
        map = new google.maps.Map(document.getElementById("mapplace"), myOptions);

        for (var i in cities_mappable) {
          if (i in citylatlng) {
            var city_latlng = new google.maps.LatLng(citylatlng[i].lat,citylatlng[i].lng);
            var marker = new google.maps.Circle({
                  strokeColor: "#8822DD",
                  strokeOpacity: 0.3,
                  strokeWeight: 1,
                  fillColor: "#8822DD",
                  fillOpacity: 0.22,
                  map: map,
                  center: city_latlng,
                  radius: Math.log(cities_mappable[i] * 14) * 70000,
                  title: i
                  });
          }
        }

/* ============================================== */

        awswitch('awindex');
    };

    function awswitch(dest) {
      document.getElementById('awindex').style.display='none';
      document.getElementById('pages').style.display='none';
      document.getElementById('searchenginesholder').style.display='none';
      document.getElementById('geographicholder').style.display='none';
      document.getElementById('incoming').style.display='none';
      document.getElementById('mapholder').style.display='none';

      document.getElementById(dest).style.display='block';
      if (dest == 'mapholder') google.maps.event.trigger(map, "resize");
    }
</script>
{/literal}

<div class="awnav">
<button onclick="awswitch('awindex')">Visits</button>
<button onclick="awswitch('pages')">Pages</button>
<button onclick="awswitch('incoming')">Incoming Traffic</button>
<button onclick="awswitch('searchenginesholder')">Incoming Searches</button>
<button onclick="awswitch('geographicholder')">Geographic</button>
<button onclick="awswitch('mapholder')">Map</button>
</div>

<p><strong>{translate key="plugins.generic.awsummary.dateofstatistics"}</strong> {$datedisplay}</p>



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

<table class="awtable" summary="{translate key="plugins.generic.awsummary.toparticles"}">
<tr align="left"><th>{translate key="plugins.generic.awsummary.toparticles"}</th><th align="right">Count</th></tr>
{foreach from=$toparticles item=inc key=ikey}
    <tr><td><a target="_blank" href="{$ikey}">{$toparticlesnames[$ikey]}</a></td><td align="right">{$inc}</td></tr>
{/foreach}
<tr><td align="right" colspan="2" class="dnldr"><a href="{url op="download" report="toparticles"}">{translate key="plugins.generic.awsummary.downloadresults"}</a></td></tr>
</table>

<table class="awtable" summary="{translate key="plugins.generic.awsummary.toppages"}">
<tr align="left"><th>{translate key="plugins.generic.awsummary.toppages"}</th><th align="right">Count</th></tr>
{foreach from=$toppages item=inc key=ikey}
    <tr><td><a target="_blank" href="{$ikey}">{$ikey}</a></td><td align="right">{$inc}</td></tr>
{/foreach}
<tr><td align="right" colspan="2" class="dnldr"><a href="{url op="download" report="toppages"}">{translate key="plugins.generic.awsummary.downloadresults"}</a></td></tr>
</table>

</div>



<div id="incoming">
<div id="originchartholder" class="awchart">
</div>

<table class="awtable" summary="{translate key="plugins.generic.awsummary.topincoming"}">
<tr align="left"><th>{translate key="plugins.generic.awsummary.topincoming"}</th><th align="right">Count</th></tr>
{foreach from=$topincoming item=inc key=ikey}
    <tr><td><a target="_blank" href="{$ikey}">{$ikey}</a></td><td align="right">{$inc}</td></tr>
{/foreach}
<tr><td align="right" colspan="2" class="dnldr"><a href="{url op="download" report="topincoming"}">{translate key="plugins.generic.awsummary.downloadresults"}</a></td></tr>
</table>

</div>



<div id="geographicholder">
<div id="geographic" class="awchart">
</div>

<table class="awtable" summary="{translate key="plugins.generic.awsummary.cities"}">
<tr align="left"><th>{translate key="plugins.generic.awsummary.cities"}</th><th align="right">Percent</th></tr>
{foreach from=$cities item=inc key=ikey}
    <tr><td>{$ikey}</td><td align="right">{$inc}</td></tr>
{/foreach}
<tr><td align="right" colspan="2" class="dnldr"><a href="{url op="download" report="cities"}">{translate key="plugins.generic.awsummary.downloadresults"}</a></td></tr>
</table>

</div>



<div id="searchenginesholder">
<div id="searchengines" class="awchart">
</div>

<table class="awtable" summary="{translate key="plugins.generic.awsummary.searchwords"}">
<tr><th>{translate key="plugins.generic.awsummary.searchwords"}</th><th align="right">Count</th></tr>
{foreach from=$searchwords item=inc key=ikey}
    <tr><td>{$ikey}</td><td align="right">{$inc}</td></tr>
{/foreach}
<tr><td align="right" colspan="2" class="dnldr"><a href="{url op="download" report="searchwords"}">{translate key="plugins.generic.awsummary.downloadresults"}</a></td></tr>
</table>

</div>



<div id="mapholder">
<div id="mapplace" class="awmap">
</div>

</div>



{include file="common/footer.tpl"}
