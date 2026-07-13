<?php
/**
 * Plugin Name: RS Lamp Configurator
 * Description: Native 3D configurator + add-to-basket for the Build Your Lamp product. Shortcode: [lamp_configurator product_id="11850" variation_id="11851"]
 * Version: 1.0
 * Author: The Records Ticking
 */
if (!defined('ABSPATH')) exit;

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
  $price = $product->get_price_html();
  $cart  = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/basket/');

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
    <p class="rs-price"><?php echo wp_kses_post($price); ?></p>
    <p class="rs-label">SHADE PANELS &mdash; TAP A PANEL TO EDIT</p>
    <div class="rs-chips" id="rsChips"></div>
    <div class="rs-picker">
      <select id="rsWho" aria-label="Player"></select>
      <span class="rs-custom" id="rsCustomFields" hidden>
        <input type="text" id="rsName" maxlength="14" placeholder="NAME" aria-label="Custom name">
        <input type="text" id="rsNum" maxlength="2" inputmode="numeric" placeholder="No." aria-label="Number">
        <select id="rsKit" aria-label="Preview colourway"></select>
      </span>
    </div>
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
    <p class="rs-note">Handmade to order. Pick a player for each of the four shade panels,
      or choose <b>YOUR NAME / ANY OTHER&hellip;</b> to add any name &amp; number.</p>
  </div>
</div>

<style>
.rs-lamp{ display:flex; flex-wrap:wrap; gap:34px; align-items:flex-start; margin:8px 0 44px }
.rs-lamp *, .rs-lamp *:before, .rs-lamp *:after{ box-sizing:border-box }

/* ---- stage: real photo below, CSS-3D shade above ---- */
.rs-stage{ position:relative; flex:1 1 320px; max-width:420px; aspect-ratio:420/590;
  border-radius:10px; overflow:hidden; touch-action:pan-y; cursor:grab;
  user-select:none; -webkit-user-select:none;
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
.rs-buy{ flex:1 1 300px; max-width:440px }
.rs-price{ color:#d5281e; font-size:30px; font-weight:700; margin:0 0 16px }
.rs-price del{ color:#999; margin-right:8px }
.rs-label{ font-size:11px; letter-spacing:.16em; color:#777; font-weight:600; margin:0 0 10px }
.rs-chips{ display:flex; flex-wrap:wrap; gap:8px; margin:0 0 14px }
.rs-chips .chip{ flex:none; display:inline-flex; align-items:center; gap:7px;
  border:1px solid #ddd; background:#fff; color:#222; border-radius:999px;
  padding:8px 14px; font-size:12px; letter-spacing:.04em; cursor:pointer; line-height:1.2 }
.rs-chips .chip .idx{ color:#999; font-weight:700 }
.rs-chips .chip.active{ background:#141414; color:#fff; border-color:#141414 }
.rs-chips .chip.active .idx{ color:inherit; opacity:.7 }
.rs-picker{ display:flex; flex-wrap:wrap; gap:8px; margin:0 0 18px }
.rs-picker select, .rs-picker input{ font-size:14px; color:#222; background:#fff;
  border:1px solid #ddd; border-radius:2px; padding:9px 10px; margin:0 }
.rs-picker #rsWho{ flex:1 1 100%; width:100% }
.rs-custom{ display:flex; gap:8px; flex:1 1 100%; flex-wrap:wrap }
.rs-custom[hidden]{ display:none }
.rs-custom #rsName{ flex:1; min-width:120px; text-transform:uppercase }
.rs-custom #rsNum{ width:64px }
.rs-custom #rsKit{ flex:1 1 150px }
.rs-add{ display:block; width:100%; background:#141414; color:#fff; border:0;
  padding:15px 20px; font-size:14px; font-weight:600; letter-spacing:.12em;
  cursor:pointer; border-radius:2px; text-transform:uppercase }
.rs-add:hover{ background:#d5281e; color:#fff }
.rs-note{ font-size:13px; color:#777; margin-top:14px; line-height:1.5 }
@media (max-width:719px){ .rs-stage{ flex-basis:100%; margin:0 auto } .rs-buy{ max-width:none } }
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

  /* ---- chooser ---- */
  var chipsEl = document.getElementById("rsChips");
  var who = document.getElementById("rsWho");
  var customFields = document.getElementById("rsCustomFields");
  var nameIn = document.getElementById("rsName");
  var numIn = document.getElementById("rsNum");
  var kitIn = document.getElementById("rsKit");
  var form = document.getElementById("rsForm");

  who.innerHTML = '<option value="custom">YOUR NAME / ANY OTHER…</option>' +
    Object.keys(ROSTER).map(function(k){
      return '<option value="' + k + '">' + ROSTER[k].label + '</option>';
    }).join("");
  kitIn.innerHTML = KITS.map(function(k){
    return '<option value="' + k + '">' + KIT_LABELS[k] + '</option>';
  }).join("");

  var chipEls = [];
  panels.forEach(function(_, i){
    var b = document.createElement("button");
    b.type = "button"; b.className = "chip";
    b.innerHTML = '<span class="idx">' + (i+1) + '</span><span class="cname"></span>';
    b.addEventListener("click", function(){
      editing = i; syncPicker(); render(); focusPanel(i);
    });
    chipsEl.appendChild(b);
    chipEls.push(b);
  });

  function syncPicker(){
    var st = panels[editing];
    who.value = st.key;
    customFields.hidden = st.key !== "custom";
    if (st.key === "custom"){
      if (document.activeElement !== nameIn) nameIn.value = st.name === "YOUR NAME" ? "" : st.name;
      if (document.activeElement !== numIn) numIn.value = st.num;
      kitIn.value = st.kit;
    }
  }
  function applyCustom(){
    panels[editing] = {key:"custom", name:(nameIn.value||"YOUR NAME").toUpperCase(),
                       num:numIn.value.replace(/\D/g,""), kit:kitIn.value};
    render(); focusPanel(editing);
  }
  who.addEventListener("change", function(){
    if (who.value === "custom"){
      customFields.hidden = false;
      if (!numIn.value) numIn.value = "26";
      applyCustom();
    } else {
      panels[editing] = fromRoster(who.value);
      render(); syncPicker(); focusPanel(editing);
    }
  });
  nameIn.addEventListener("input", applyCustom);
  numIn.addEventListener("input", applyCustom);
  kitIn.addEventListener("change", applyCustom);

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
      var slug = SLUGS[i];
      if (slug && form.elements["attribute_" + slug])
        form.elements["attribute_" + slug].value = optionFor(slug, st);
      form.elements["rs_custom_name_" + (i+1)].value =
        (st.key === "custom" && st.name !== "YOUR NAME") ? st.name : "";
      form.elements["rs_custom_num_" + (i+1)].value =
        st.key === "custom" ? (st.num || "") : "";
    });
    chipEls.forEach(function(b, i){
      var st = panels[i];
      b.querySelector(".cname").textContent =
        st.name + (st.num === "" || st.num == null ? "" : " " + st.num);
      b.classList.toggle("active", i === editing);
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
        else rot += d * 0.11;
      } else {
        rot += vel; vel *= RM ? 0 : 0.94;
        if (Math.abs(vel) < 0.02) vel = 0;
        if (!RM && performance.now() - lastTouch > 2600) rot += 0.12;
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

  fit();
  render();
  syncPicker();
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
        $c['name'] . ($c['num'] !== '' ? ' #' . $c['num'] : ''));
    }
  }
}, 10, 3);
