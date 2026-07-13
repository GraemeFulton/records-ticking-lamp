<?php
/**
 * Plugin Name: RS Lamp Configurator
 * Description: Native 3D configurator + add-to-basket for the Build Your Lamp product. Shortcode: [lamp_configurator product_id="11850" variation_id="11851"]
 * Version: 1.5
 * Author: The Records Ticking
 */
if (!defined('ABSPATH')) exit;

/* old Build Your Lamp product page -> the 3D configurator page */
add_action('template_redirect', function(){
  if (function_exists('is_product') && is_product() && (int) get_queried_object_id() === 11850){
    wp_safe_redirect(home_url('/worldcup-custom-lamp/'), 301);
    exit;
  }
});

add_shortcode('lamp_configurator', 'rs_lamp_configurator_render');

function rs_lamp_configurator_render($atts){
  $a = shortcode_atts(array('product_id' => 11850, 'variation_id' => 11851), $atts, 'lamp_configurator');
  if (!function_exists('wc_get_product')) return '';
  $product = wc_get_product((int)$a['product_id']);
  if (!$product) return '<p>Product unavailable.</p>';

  // pipe-list custom attributes flagged as variation dimensions -> exact option strings
  $opts = array();
  foreach ($product->get_attributes() as $slug => $attr){
    if (is_object($attr) && !$attr->is_taxonomy() && $attr->get_variation()){
      $opts[$slug] = array_values(array_map('trim', $attr->get_options()));
    }
  }
  $ball  = plugins_url('ball-strip.jpg', __FILE__);
  $workshop = plugins_url('workshop.jpg', __FILE__);
  $price = $product->get_price_html();
  $cart  = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');

  ob_start(); ?>
<div class="rs-lamp" id="rsLamp">
  <div class="rs-stage" id="rsStage" aria-label="Rotatable lamp preview. Drag to spin the shade.">
    <div class="rs-scene" id="rsScene">
      <div class="rs-glow"></div>
      <img class="rs-ball" alt="" draggable="false" src="<?php echo esc_url($ball); ?>">
      <div class="rs-wrap"><div class="rs-shade" id="rsShade"></div></div>
    </div>
    <div class="rs-hint">Drag to spin</div>
  </div>

  <div class="rs-buy">
    <h1 class="rs-title">BUILD YOUR OWN <span>WORLD CUP</span> TRIONDA LAMP</h1>
    <p class="rs-price"><?php echo wp_kses_post($price); ?></p>
    <p class="rs-tag">Four shade panels on a World Cup style Trionda ball.
      Pick a player for each, or print any name &amp; number.</p>
    <p class="rs-label">CHOOSE YOUR PANELS</p>
    <div class="rs-rows" id="rsRows"></div>
    <form method="post" action="<?php echo esc_url($cart); ?>" id="rsForm">
      <input type="hidden" name="add-to-cart" value="<?php echo (int)$a['product_id']; ?>">
      <input type="hidden" name="product_id" value="<?php echo (int)$a['product_id']; ?>">
      <input type="hidden" name="variation_id" value="<?php echo (int)$a['variation_id']; ?>">
      <input type="hidden" name="quantity" value="1">
      <?php foreach (array_keys($opts) as $slug): ?>
      <input type="hidden" name="attribute_<?php echo esc_attr($slug); ?>" value="">
      <?php endforeach; for ($i = 1; $i <= 4; $i++): ?>
      <input type="hidden" name="rs_custom_name_<?php echo $i; ?>" value="">
      <input type="hidden" name="rs_custom_num_<?php echo $i; ?>" value="">
      <?php endfor; ?>
      <button type="submit" class="rs-add">ADD TO BASKET</button>
    </form>
    <p class="rs-note">Handmade to order. Every panel is lit from inside by the lamp.</p>
  </div>
</div>

<section class="rs-desc">
  <figure class="rs-desc-img">
    <img src="<?php echo esc_url($workshop); ?>" alt="Trionda lamp bases being handmade in The Record's Ticking workshop" loading="lazy">
  </figure>
  <div class="rs-desc-txt">
    <h2 class="rs-desc-title">HANDMADE IN <span>ENGLAND</span></h2>
    <p>Bespoke handmade table lamps, made using an official Fifa Trionda World Cup mini ball.</p>
    <ul>
      <li>The base features a real mini Trionda football, sat on a weighty hand cast mini cone.</li>
      <li>Finished with the highest quality metal lampholder and switch.</li>
      <li>Shirt patterns of most countries have been meticulously replicated.</li>
      <li>Mix and match Allstars shade: any name, number and country.</li>
      <li>Custom names and numbers are added straight from your choices above, so there is no need to leave an order note.</li>
      <li>We only have limited mini footballs, so order quick to avoid missing out.</li>
    </ul>
    <dl class="rs-specs">
      <div><dt>SHADE</dt><dd>(h) 20cm x (w) 20cm x (d) 20cm</dd></div>
      <div><dt>LAMP</dt><dd>(h) 22cm x (w) 13cm x (d) 13cm</dd></div>
      <div><dt>WEIGHT</dt><dd>1.6 kg</dd></div>
      <div><dt>DISPATCH</dt><dd>Please allow 3 working days for your shade to be made</dd></div>
    </dl>
  </div>
</section>
<button type="button" class="rs-topbtn" id="rsTop" aria-label="Scroll back to the lamp preview">&uarr;&nbsp;&nbsp;PREVIEW</button>

<style>
.rs-lamp{ display:flex; flex-wrap:wrap; gap:44px; align-items:flex-start;
  justify-content:center; max-width:960px; margin:8px auto 44px }
.rs-lamp *, .rs-lamp *:before, .rs-lamp *:after{ box-sizing:border-box }

/* ---- stage: real photo below, CSS-3D shade above ---- */
.rs-stage{ position:relative; flex:1 1 320px; max-width:420px; aspect-ratio:420/590;
  border-radius:10px; overflow:hidden; touch-action:pan-y; cursor:grab;
  user-select:none; -webkit-user-select:none;
  box-shadow:0 28px 56px -28px rgba(30,25,20,.5);
  background:radial-gradient(150% 125% at 51% 66%, #ddd8d1 0%, #b5aea5 46%, #837c72 82%, #6e6860 100%) }
.rs-stage.grabbing{ cursor:grabbing }
.rs-scene{ position:absolute; top:0; left:0; width:420px; height:590px; transform-origin:top left }
.rs-ball{ position:absolute; top:317px; left:0; width:420px; max-width:none; display:block; border:0; box-shadow:none;
  -webkit-mask-image:linear-gradient(to bottom, rgba(0,0,0,0), #000 54px);
  mask-image:linear-gradient(to bottom, rgba(0,0,0,0), #000 54px) }
.rs-glow{ position:absolute; top:94px; left:215px; width:360px; height:330px; transform:translateX(-50%);
  background:radial-gradient(closest-side, rgba(255,228,170,.5), rgba(255,228,170,0));
  filter:blur(10px); pointer-events:none }
.rs-wrap{ position:absolute; left:89px; top:46px; width:254px; height:261px; perspective:2000px }
.rs-shade{ position:absolute; inset:0; transform-style:preserve-3d }
.rs-lamp .panel{ position:absolute; inset:0; backface-visibility:hidden;
  display:flex; flex-direction:column; align-items:center; padding-top:17px; overflow:hidden;
  box-shadow:inset 0 0 0 1px rgba(0,0,0,.06);
  font-family:"Arial Narrow","Helvetica Neue",Impact,"Roboto Condensed",sans-serif;
  background:
    linear-gradient(135deg, rgba(255,255,255,.22), rgba(0,0,0,.07)),
    repeating-linear-gradient(0deg, rgba(0,0,0,.02) 0 2px, transparent 2px 4px),
    linear-gradient(var(--pbg1), var(--pbg2)) }
.rs-lamp .panel .name{ font-weight:800; font-size:25px; letter-spacing:.1em; white-space:nowrap;
  color:var(--ptxt); -webkit-text-stroke:var(--pow,0px) var(--pout); transform-origin:50% 50%; line-height:1.3 }
.rs-lamp .panel .num{ font-family:Impact,"Arial Narrow","Helvetica Neue",sans-serif;
  font-size:138px; line-height:1.05; letter-spacing:.02em;
  color:var(--ptxt); -webkit-text-stroke:3px var(--pout) }
.rs-lamp .panel .lightwash{ position:absolute; inset:0; pointer-events:none;
  background:linear-gradient(to top, rgba(255,228,170,.5), rgba(255,228,170,0) 46%); mix-blend-mode:soft-light }
.rs-lamp .panel .falloff{ position:absolute; inset:0; background:#000; opacity:0; pointer-events:none }
.rs-lamp .panel .cbadge{ position:absolute; right:0; bottom:0; width:58px; height:58px;
  background:#141414; clip-path:polygon(100% 0,100% 100%,0 100%); display:none }
.rs-lamp .panel .cbadge span{ position:absolute; right:10px; bottom:2px; color:#fff; font-weight:800; font-size:20px }
.rs-lamp .panel[data-cap="1"] .cbadge{ display:block }
.rs-lamp .panel[data-kit="england"]  { --pbg1:#f6f4ee; --pbg2:#e6e3da; --ptxt:#d5281e; --pout:#181818 }
.rs-lamp .panel[data-kit="keeper"]   { --pbg1:#1e548e; --pbg2:#0e2c52; --ptxt:#eef4f8; --pout:#09182c }
.rs-lamp .panel[data-kit="keeperdark"]{ --pbg1:#2b2b30; --pbg2:#111114; --ptxt:#cdfa3e; --pout:#000000 }
.rs-lamp .panel[data-kit="germany"]  { --pbg1:#f7f6f2; --pbg2:#e8e6df; --ptxt:#181818; --pout:#181818; --pow:0px }
.rs-lamp .panel[data-kit="france"]   { --pbg1:#1b3260; --pbg2:#101f3e; --ptxt:#f5f2ec; --pout:#d5281e }
.rs-lamp .panel[data-kit="spain"]    { --pbg1:#c81f2c; --pbg2:#9d1420; --ptxt:#f3c211; --pout:#1a1a1a }
.rs-lamp .panel[data-kit="portugal"] { --pbg1:#a51c24; --pbg2:#7c1219; --ptxt:#f5f2ec; --pout:#0c5237 }
.rs-lamp .panel[data-kit="brazil"]   { --pbg1:#f8ca00; --pbg2:#e0b400; --ptxt:#1b7a3d; --pout:#123c7a }
.rs-lamp .panel[data-kit="norway"]   { --pbg1:#d32330; --pbg2:#a91622; --ptxt:#f5f2ec; --pout:#123f7c }
.rs-lamp .panel[data-kit="scotland"] { --pbg1:#22345f; --pbg2:#151f3d; --ptxt:#f5f2ec; --pout:#0d1425 }
.rs-lamp .panel[data-kit="argentina"]{ --ptxt:#172a53; --pout:#0c1730;
  background:
    linear-gradient(135deg, rgba(255,255,255,.22), rgba(0,0,0,.07)),
    repeating-linear-gradient(90deg, #a3cfe9 0 38px, #f3f6f8 38px 76px) }
.rs-hint{ position:absolute; bottom:10px; left:0; right:0; text-align:center;
  color:rgba(44,41,36,.6); font-size:12px; letter-spacing:.14em; text-transform:uppercase; pointer-events:none }

/* ---- buy column ---- */
.rs-buy{ flex:1 1 320px; max-width:460px }
.rs-title{ font-family:"Arial Narrow","Helvetica Neue",Impact,"Roboto Condensed",sans-serif;
  font-size:clamp(28px, 3.4vw, 40px); font-weight:800; letter-spacing:.04em;
  line-height:1.08; color:#141414; margin:0 0 10px; text-transform:uppercase }
.rs-title span{ color:#d5281e }
.rs-price{ color:#d5281e; font-size:34px; font-weight:800; margin:0 0 6px; font-variant-numeric:tabular-nums }
.rs-price del{ color:#999; margin-right:8px; font-weight:400 }
.rs-tag{ font-size:14px; color:#555; margin:0 0 22px; line-height:1.55; max-width:40ch }
.rs-label{ font-size:11px; letter-spacing:.18em; color:#888; font-weight:700; margin:0 0 10px }
.rs-rows{ display:flex; flex-direction:column; gap:10px; margin:0 0 20px }
.rs-row{ display:grid; grid-template-columns:52px 1fr; gap:12px; align-items:start;
  padding:10px 12px; background:#fff; border:1px solid #e2e2e2; border-radius:4px; cursor:pointer;
  transition:border-color .15s, box-shadow .15s }
.rs-row.active{ border-color:#141414; box-shadow:inset 3px 0 0 #d5281e }
.rs-mini{ width:52px; height:52px; border-radius:3px; display:flex; align-items:center; justify-content:center;
  box-shadow:inset 0 0 0 1px rgba(0,0,0,.08); overflow:hidden }
.rs-mini b{ font-family:Impact,"Arial Narrow","Helvetica Neue",sans-serif; font-weight:400;
  font-size:28px; line-height:1; letter-spacing:.02em }
.rs-row-main{ display:flex; flex-direction:column; gap:6px; min-width:0 }
.rs-row-label{ font-size:10px; letter-spacing:.18em; color:#999; font-weight:700 }
.rs-selwrap{ display:grid; grid-template-columns:1fr 30px; align-items:center; background:#fff;
  border:1px solid #ddd; border-radius:3px }
.rs-row.active .rs-selwrap{ border-color:#bbb }
.rs-selwrap select{ grid-column:1 / -1; grid-row:1; appearance:none; -webkit-appearance:none;
  border:0; background:transparent; margin:0; padding:9px 30px 9px 10px; width:100%;
  font-size:15px; color:#222; cursor:pointer }
.rs-selwrap svg{ grid-column:2; grid-row:1; justify-self:center; pointer-events:none }
.rs-selwrap select:focus-visible{ outline:2px solid #d5281e; outline-offset:-1px; border-radius:3px }
.rs-custom{ display:flex; gap:8px; flex-wrap:wrap }
.rs-custom[hidden]{ display:none }
.rs-custom input{ font-size:15px; color:#222; background:#fff; border:1px solid #ddd;
  border-radius:3px; padding:9px 10px; margin:0 }
.rs-custom input:focus-visible{ outline:2px solid #d5281e; outline-offset:-1px }
.rs-custom .rs-cname{ flex:1; min-width:110px; text-transform:uppercase }
.rs-custom .rs-cnum{ width:60px }
.rs-custom .rs-selwrap{ flex:1 1 150px }
.rs-add{ display:block; width:100%; background:#141414; color:#fff; border:0;
  padding:16px 20px; font-size:15px; font-weight:700; letter-spacing:.14em;
  cursor:pointer; border-radius:3px; text-transform:uppercase; transition:background .15s }
.rs-add:hover{ background:#d5281e; color:#fff }
.rs-note{ font-size:13px; color:#888; margin-top:12px; line-height:1.5 }
/* ---- description / workmanship section ---- */
.rs-desc{ display:flex; flex-wrap:wrap; gap:44px; align-items:center;
  justify-content:center; max-width:960px; margin:0 auto 56px }
.rs-desc-img{ flex:1 1 320px; max-width:480px; margin:0 }
.rs-desc-img img{ width:100%; height:auto; display:block; border-radius:10px;
  box-shadow:0 28px 56px -28px rgba(30,25,20,.5) }
.rs-desc-txt{ flex:1 1 300px; max-width:440px }
.rs-desc-title{ font-family:"Arial Narrow","Helvetica Neue",Impact,"Roboto Condensed",sans-serif;
  font-size:clamp(24px, 2.8vw, 32px); font-weight:800; letter-spacing:.04em;
  line-height:1.1; color:#141414; margin:0 0 12px; text-transform:uppercase }
.rs-desc-title span{ color:#d5281e }
.rs-desc-txt p{ font-size:14px; color:#555; line-height:1.6; margin:0 0 12px }
.rs-desc-txt ul{ margin:0 0 20px; padding-left:18px; font-size:14px; color:#555; line-height:1.7 }
.rs-desc-txt li{ margin-bottom:4px }
.rs-specs{ margin:0; border-top:1px solid #e2e2e2 }
.rs-specs > div{ display:flex; gap:16px; padding:9px 2px; border-bottom:1px solid #e2e2e2 }
.rs-specs dt{ flex:0 0 84px; font-size:11px; letter-spacing:.16em; color:#999;
  font-weight:700; line-height:2 }
.rs-specs dd{ margin:0; font-size:13px; color:#444; line-height:1.7 }

.rs-topbtn{ position:fixed; right:14px; bottom:14px; z-index:90; display:none;
  align-items:center; background:#141414; color:#fff; border:0; border-radius:999px;
  padding:12px 20px; font-size:13px; font-weight:700; letter-spacing:.12em;
  cursor:pointer; box-shadow:0 6px 20px rgba(0,0,0,.35) }
.rs-topbtn.show{ display:inline-flex }
.rs-topbtn:hover{ background:#d5281e; color:#fff }
@media (max-width:719px){
  .rs-lamp, .rs-desc{ padding:0 16px }
  .rs-stage{ flex-basis:100%; margin:0 auto }
  .rs-buy{ max-width:none }
}
@media (prefers-reduced-motion: reduce){ .rs-lamp *{ transition:none !important; animation:none !important } }
</style>

<script>
(function(){
  "use strict";
  var OPTS = <?php echo wp_json_encode($opts); ?>;
  var SLUGS = Object.keys(OPTS);

  var KITS = ["england","keeper","keeperdark","germany","france","spain",
              "portugal","brazil","norway","argentina","scotland"];
  var KIT_LABELS = { england:"England white", keeper:"Keeper blue", keeperdark:"Keeper dark",
    germany:"Germany white", france:"France navy", spain:"Spain red", portugal:"Portugal red",
    brazil:"Brazil yellow", norway:"Norway red", argentina:"Argentina stripes", scotland:"Scotland navy" };
  var KIT_MINI = {
    england:   {bg:"linear-gradient(#f6f4ee,#e6e3da)", txt:"#d5281e"},
    keeper:    {bg:"linear-gradient(#1e548e,#0e2c52)", txt:"#eef4f8"},
    keeperdark:{bg:"linear-gradient(#2b2b30,#111114)", txt:"#cdfa3e"},
    germany:   {bg:"linear-gradient(#f7f6f2,#e8e6df)", txt:"#181818"},
    france:    {bg:"linear-gradient(#1b3260,#101f3e)", txt:"#f5f2ec"},
    spain:     {bg:"linear-gradient(#c81f2c,#9d1420)", txt:"#f3c211"},
    portugal:  {bg:"linear-gradient(#a51c24,#7c1219)", txt:"#f5f2ec"},
    brazil:    {bg:"linear-gradient(#f8ca00,#e0b400)", txt:"#1b7a3d"},
    norway:    {bg:"linear-gradient(#d32330,#a91622)", txt:"#f5f2ec"},
    argentina: {bg:"repeating-linear-gradient(90deg,#a3cfe9 0 9px,#f3f6f8 9px 18px)", txt:"#172a53"},
    scotland:  {bg:"linear-gradient(#22345f,#151f3d)", txt:"#f5f2ec"}
  };
  var ROSTER = {
    pickford:{label:"Pickford", num:1, kit:"keeper"},
    james:{label:"James", num:2, kit:"england"},
    guehi:{label:"Guéhi", num:6, kit:"england"},
    konsa:{label:"Konsa", num:14, kit:"england"},
    oreilly:{label:"O'Reilly", num:3, kit:"england"},
    anderson:{label:"Anderson", num:8, kit:"england"},
    rice:{label:"Rice", num:4, kit:"england"},
    bellingham:{label:"Bellingham", num:10, kit:"england"},
    madueke:{label:"Madueke", num:20, kit:"england"},
    gordon:{label:"Gordon", num:18, kit:"england"},
    kane:{label:"Kane", num:9, kit:"england", cap:true},
    rashford:{label:"Rashford", num:17, kit:"england"},
    saka:{label:"Saka", num:7, kit:"england"},
    stones:{label:"Stones", num:5, kit:"england"},
    palmer:{label:"Palmer", num:24, kit:"england"},
    foden:{label:"Foden", num:11, kit:"england"},
    rogers:{label:"Rogers", num:16, kit:"england"},
    watkins:{label:"Watkins", num:19, kit:"england"},
    messi:{label:"Messi", num:10, kit:"argentina", cap:true},
    ronaldo:{label:"Ronaldo", num:7, kit:"portugal", cap:true},
    lamineyamal:{label:"Lamine Yamal", num:10, kit:"spain"},
    musiala:{label:"Musiala", num:10, kit:"germany"},
    brauthaaland:{label:"Braut Haaland", num:9, kit:"norway"},
    mbappe:{label:"Mbappe", num:10, kit:"france"},
    neuer:{label:"Neuer", num:1, kit:"keeperdark"},
    neymarjr:{label:"Neymar Jr", num:10, kit:"brazil"},
    robertson:{label:"Robertson", num:3, kit:"scotland"},
    mcginn:{label:"McGinn", num:7, kit:"scotland"},
    mctominay:{label:"McTominay", num:4, kit:"scotland"}
  };
  function norm(s){
    s = String(s||"");
    try { s = s.normalize("NFD").replace(/[̀-ͯ]/g,""); } catch(e){}
    return s.toLowerCase().replace(/[^a-z]/g,"");
  }
  function fromRoster(k){
    var r = ROSTER[k];
    return {key:k, name:r.label.toUpperCase(), num:r.num, kit:r.kit, cap:!!r.cap};
  }
  var panels = ["kane","bellingham","saka","pickford"].map(fromRoster);
  var editing = 0;

  /* ---- shade ---- */
  var shade = document.getElementById("rsShade");
  var panelEls = [];
  for (var p = 0; p < 4; p++){
    var el = document.createElement("div");
    el.className = "panel";
    el.style.transform = "rotateY(" + (p*90) + "deg) translateZ(127px)";
    el.innerHTML = '<div class="name"></div><div class="num"></div>' +
      '<div class="lightwash"></div><div class="falloff"></div>' +
      '<div class="cbadge"><span>C</span></div>';
    shade.appendChild(el);
    panelEls.push(el);
  }
  function fitName(el){
    el.style.transform = "scaleX(1)";
    if (el.scrollWidth > 232) el.style.transform = "scaleX(" + (232/el.scrollWidth).toFixed(3) + ")";
  }

  /* ---- one row per panel, each with its own picker ---- */
  var rowsEl = document.getElementById("rsRows");
  var form = document.getElementById("rsForm");
  var CHEV = '<svg viewBox="0 0 8 5" width="8" height="5" fill="none" aria-hidden="true">' +
             '<path d="M.5.5 4 4 7.5.5" stroke="#666"/></svg>';
  var whoOpts = '<option value="custom">YOUR NAME / ANY OTHER…</option>' +
    Object.keys(ROSTER).map(function(k){
      return '<option value="' + k + '">' + ROSTER[k].label + ' ' + ROSTER[k].num + '</option>';
    }).join("");
  var kitOpts = KITS.map(function(k){
    return '<option value="' + k + '">' + KIT_LABELS[k] + '</option>';
  }).join("");

  var rowUI = [];
  panels.forEach(function(_, i){
    var row = document.createElement("div");
    row.className = "rs-row";
    row.innerHTML =
      '<span class="rs-mini" aria-hidden="true"><b></b></span>' +
      '<div class="rs-row-main">' +
        '<span class="rs-row-label">PANEL ' + (i+1) + '</span>' +
        '<span class="rs-selwrap"><select class="rs-who" aria-label="Panel ' + (i+1) + ' player">' +
          whoOpts + '</select>' + CHEV + '</span>' +
        '<span class="rs-custom" hidden>' +
          '<input type="text" class="rs-cname" maxlength="14" placeholder="NAME" aria-label="Panel ' + (i+1) + ' custom name">' +
          '<input type="text" class="rs-cnum" maxlength="2" inputmode="numeric" placeholder="No." aria-label="Panel ' + (i+1) + ' number">' +
          '<span class="rs-selwrap"><select class="rs-ckit" aria-label="Panel ' + (i+1) + ' colourway">' +
            kitOpts + '</select>' + CHEV + '</span>' +
        '</span>' +
      '</div>';
    rowsEl.appendChild(row);

    var ui = {
      row: row, mini: row.querySelector(".rs-mini"), miniNum: row.querySelector(".rs-mini b"),
      sel: row.querySelector(".rs-who"), cust: row.querySelector(".rs-custom"),
      cname: row.querySelector(".rs-cname"), cnum: row.querySelector(".rs-cnum"),
      ckit: row.querySelector(".rs-ckit")
    };
    rowUI[i] = ui;

    function applyCustom(){
      panels[i] = {key:"custom", name:(ui.cname.value||"YOUR NAME").toUpperCase(),
                   num:ui.cnum.value.replace(/\D/g,""), kit:ui.ckit.value};
      render(); focusPanel(i);
    }
    ui.sel.addEventListener("focus", function(){ setEditing(i); });
    ui.sel.addEventListener("change", function(){
      if (ui.sel.value === "custom"){
        if (!ui.cnum.value) ui.cnum.value = "26";
        applyCustom();
      } else {
        panels[i] = fromRoster(ui.sel.value);
        render(); focusPanel(i);
      }
    });
    ui.cname.addEventListener("input", applyCustom);
    ui.cnum.addEventListener("input", applyCustom);
    ui.ckit.addEventListener("change", applyCustom);
    row.addEventListener("click", function(){ setEditing(i); });
  });

  function setEditing(i){
    if (editing !== i){ editing = i; render(); }
    focusPanel(i);
  }

  /* map a panel to the exact WooCommerce option string */
  function optionFor(slug, st){
    var list = OPTS[slug] || [], i;
    if (st.key === "custom"){
      for (i = 0; i < list.length; i++) if (norm(list[i]).indexOf("yourname") === 0) return list[i];
      for (i = 0; i < list.length; i++) if (norm(list[i]) === "anyother") return list[i];
      return list.length ? list[list.length-1] : "";
    }
    for (i = 0; i < list.length; i++) if (norm(list[i]) === norm(st.name)) return list[i];
    for (i = 0; i < list.length; i++) if (norm(list[i]).indexOf("anyother") === 0) return list[i];
    return "";
  }

  function render(){
    panels.forEach(function(st, i){
      var el = panelEls[i];
      el.dataset.kit = st.kit;
      if (st.cap) el.dataset.cap = "1"; else delete el.dataset.cap;
      el.querySelector(".name").textContent = st.name;
      el.querySelector(".num").textContent = st.num;
      fitName(el.querySelector(".name"));

      var ui = rowUI[i];
      ui.row.classList.toggle("active", i === editing);
      ui.sel.value = st.key;
      ui.cust.hidden = st.key !== "custom";
      if (st.key === "custom"){
        if (document.activeElement !== ui.cname)
          ui.cname.value = st.name === "YOUR NAME" ? "" : st.name;
        if (document.activeElement !== ui.cnum) ui.cnum.value = st.num;
        ui.ckit.value = st.kit;
      }
      var mini = KIT_MINI[st.kit] || KIT_MINI.england;
      ui.mini.style.background = mini.bg;
      ui.miniNum.style.color = mini.txt;
      ui.miniNum.textContent = (st.num === "" || st.num == null) ? "?" : st.num;

      var slug = SLUGS[i];
      if (slug && form.elements["attribute_" + slug])
        form.elements["attribute_" + slug].value = optionFor(slug, st);
      form.elements["rs_custom_name_" + (i+1)].value =
        (st.key === "custom" && st.name !== "YOUR NAME") ? st.name : "";
      form.elements["rs_custom_num_" + (i+1)].value =
        st.key === "custom" ? (st.num || "") : "";
    });
  }

  /* ---- rotation ---- */
  var stage = document.getElementById("rsStage");
  var scene = document.getElementById("rsScene");
  var RM = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var rot = -24, vel = 0, dragging = false, lastX = 0, lastTouch = 0, target = null;

  function focusPanel(i){
    var desired = -90 * i;
    var delta = ((desired - rot) % 360 + 540) % 360 - 180;
    target = rot + delta;
    lastTouch = performance.now();
  }
  stage.addEventListener("pointerdown", function(e){
    dragging = true; target = null; lastX = e.clientX; vel = 0; lastTouch = performance.now();
    stage.classList.add("grabbing"); stage.setPointerCapture(e.pointerId);
  });
  stage.addEventListener("pointermove", function(e){
    if (!dragging) return;
    var dx = e.clientX - lastX; lastX = e.clientX;
    rot += dx * 0.45; vel = dx * 0.45; lastTouch = performance.now();
  });
  function endDrag(){ dragging = false; stage.classList.remove("grabbing"); }
  stage.addEventListener("pointerup", endDrag);
  stage.addEventListener("pointercancel", endDrag);

  function frame(){
    if (!dragging){
      if (target != null){
        var d = target - rot;
        if (RM || Math.abs(d) < 0.4){ rot = target; target = null; lastTouch = performance.now(); }
        else rot += d * 0.16;
      } else {
        rot += vel; vel *= RM ? 0 : 0.94;
        if (Math.abs(vel) < 0.02) vel = 0;
        if (!RM && performance.now() - lastTouch > 2200) rot += 0.24;
      }
    }
    shade.style.transform = "rotateY(" + rot + "deg)";
    for (var i = 0; i < 4; i++){
      var c = Math.cos((rot + i*90) * Math.PI/180);
      panelEls[i].querySelector(".falloff").style.opacity = Math.max(0,(1-c)*0.26).toFixed(3);
    }
    requestAnimationFrame(frame);
  }
  function fit(){ scene.style.transform = "scale(" + (stage.clientWidth/420) + ")"; }
  window.addEventListener("resize", fit);

  /* back-to-preview button once the lamp is scrolled out of view */
  var topBtn = document.getElementById("rsTop");
  if ("IntersectionObserver" in window){
    new IntersectionObserver(function(entries){
      topBtn.classList.toggle("show", !entries[0].isIntersecting);
    }, {threshold: 0.1}).observe(stage);
  } else {
    window.addEventListener("scroll", function(){
      topBtn.classList.toggle("show",
        window.scrollY > stage.getBoundingClientRect().top + window.scrollY + stage.offsetHeight);
    });
  }
  topBtn.addEventListener("click", function(){
    stage.scrollIntoView({behavior: RM ? "auto" : "smooth", block: "start"});
  });

  fit();
  render();
  requestAnimationFrame(frame);
})();
</script>
<?php
  return ob_get_clean();
}

/* ---- carry custom names/numbers into the cart item and order ---- */
add_filter('woocommerce_add_cart_item_data', function($data, $product_id){
  for ($i = 1; $i <= 4; $i++){
    $n = isset($_POST["rs_custom_name_$i"]) ? sanitize_text_field(wp_unslash($_POST["rs_custom_name_$i"])) : '';
    $m = isset($_POST["rs_custom_num_$i"]) ? preg_replace('/\D/', '', wp_unslash($_POST["rs_custom_num_$i"])) : '';
    if ($n !== ''){
      $data['rs_custom'][] = array('panel' => $i, 'name' => $n, 'num' => $m);
    }
  }
  return $data;
}, 10, 2);

add_filter('woocommerce_get_item_data', function($item_data, $cart_item){
  if (!empty($cart_item['rs_custom'])){
    foreach ($cart_item['rs_custom'] as $c){
      $item_data[] = array(
        'key'   => 'Panel ' . $c['panel'] . ' custom',
        'value' => $c['name'] . ($c['num'] !== '' ? ' #' . $c['num'] : ''),
      );
    }
  }
  return $item_data;
}, 10, 2);

add_action('woocommerce_checkout_create_order_line_item', function($item, $cart_item_key, $values){
  if (!empty($values['rs_custom'])){
    foreach ($values['rs_custom'] as $c){
      $item->add_meta_data('Panel ' . $c['panel'] . ' custom',
        $c['num'] !== '' ? $c['name'] . ' #' . $c['num'] : $c['name']);
    }
  }
}, 10, 3);
