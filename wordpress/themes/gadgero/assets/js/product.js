/* Gadgero product page (brief 15.11.2026, items 06 and 08).
   WooCommerce's own variation form stays in charge of matching variations, price, stock, availability, SKU and the
   add-to-cart rules. This script adds:
   - gallery: thumbnails, previous / next, swipe, keyboard; zoom dialog with click-to-zoom and touch pinch-zoom,
   - pill selectors over the native <select>s; attributes with a single value are preselected and hidden (no
     "only option" pills),
   - on a colour change: the image of a matching variation, even before the configuration is complete,
   - "Ihre Auswahl" line, big price and image follow the found variation. */
(function($){
  var CFG = window.GADGERO || {};
  var $img = $("#pdpImg"), initialImg = $img.html(), $price = $("#pdpPrice"), cfg = document.getElementById("pdpConfig");

  /* ---------- gallery ---------- */
  var $thumbs = $(".pdp-thumb"), cur = 0;
  function show(i){
    if(!$thumbs.length) return;
    cur = (i + $thumbs.length) % $thumbs.length;
    var t = $thumbs.eq(cur);
    setImage(t.data("src"), t.data("full"), t.data("alt") || "");
    $thumbs.removeAttr("aria-current"); t.attr("aria-current","true");
  }
  function setImage(src, full, alt){
    $img.html('<img alt="">'); $img.find("img").attr({src:src, alt:alt || document.querySelector(".pdp-title").textContent}).attr("data-full", full || src);
    noteNoPhoto(false);
  }
  /* colour without its own photo: "Kein Foto verfügbar" placeholder, never the photo of another colour */
  function showArt(){ $img.html($img.attr("data-art") || ""); noteNoPhoto(true); }
  /* "Abbildung ähnlich" belongs to photos only; the placeholder says "Kein Foto verfügbar" itself */
  function noteNoPhoto(on){ $("#pdpImgNote").prop("hidden", !!on); }
  $thumbs.on("click", function(){ show($thumbs.index(this)); });
  $(".pdp-nav").on("click", function(){ show(cur + parseInt($(this).data("step"),10)); });
  $("#pdpMedia").on("keydown", function(e){ if(e.key === "ArrowLeft") show(cur-1); if(e.key === "ArrowRight") show(cur+1); });
  /* swipe */
  var sx = null;
  $img.on("touchstart", function(e){ sx = e.originalEvent.touches[0].clientX; }, {passive:true});
  $img.on("touchend", function(e){ if(sx === null) return; var dx = e.originalEvent.changedTouches[0].clientX - sx; if(Math.abs(dx) > 40) show(cur + (dx < 0 ? 1 : -1)); sx = null; });

  /* ---------- zoom dialog ---------- */
  var dlg = document.getElementById("zoomDlg"), body = document.getElementById("zoomBody");
  function openZoom(){
    var im = $img.find("img")[0]; if(!im || !dlg || !dlg.showModal) return;
    body.innerHTML = ""; var big = new Image(); big.src = im.getAttribute("data-full") || im.currentSrc || im.src; big.alt = im.alt; big.className = "zoom-img";
    body.appendChild(big); dlg.showModal();
    big.addEventListener("click", function(e){
      var r = big.getBoundingClientRect();
      big.style.transformOrigin = ((e.clientX - r.left) / r.width * 100) + "% " + ((e.clientY - r.top) / r.height * 100) + "%";
      big.classList.toggle("is-zoomed");
    });
  }
  $("#pdpZoom").on("click", openZoom);
  $img.on("dblclick", openZoom);
  $("#zoomClose").on("click", function(){ dlg.close(); });
  if(dlg) dlg.addEventListener("click", function(e){ if(e.target === dlg) dlg.close(); });

  var $form = $("form.variations_form");
  if(!$form.length) return;
  var variations = []; try{ variations = JSON.parse($form.attr("data-product_variations") || "[]") || []; }catch(_){}

  /* ---------- pills ---------- */
  $form.find("table.variations select").each(function(){
    var $sel = $(this), id = this.id, $row = $sel.closest("tr");
    var label = $form.find('label[for="' + id + '"]').text().trim();
    var opts = $sel.find("option").filter(function(){ return !!this.value; });
    if(opts.length === 1){ $sel.val(opts.val()); $row.addClass("is-single").attr("hidden", true); return; }  /* fixed value: no pill */
    var $group = $('<div class="pills" role="radiogroup"></div>').attr("aria-label", label);
    opts.each(function(){
      $('<button type="button" class="pill" role="radio" aria-checked="false"></button>').text(this.text).attr("data-value", this.value).appendTo($group);
    });
    $sel.addClass("pill-select").attr({"aria-hidden":"true", tabindex:"-1"}).after($group);
    $group.on("click", ".pill", function(){
      var v = $(this).attr("data-value");
      if($(this).hasClass("is-off")) return;
      $sel.val($sel.val() === v ? "" : v).trigger("change");
    });
    $group.on("keydown", ".pill", function(e){
      if(e.key !== "ArrowRight" && e.key !== "ArrowLeft") return;
      var $p = $group.find(".pill:not(.is-off)"), i = $p.index(this) + (e.key === "ArrowRight" ? 1 : -1);
      $p.eq((i + $p.length) % $p.length).trigger("focus").trigger("click"); e.preventDefault();
    });
  });
  $form.find("select").first().trigger("change");

  function syncPills(){
    $form.find("table.variations select").each(function(){
      var $sel = $(this), val = $sel.val();
      $sel.next(".pills").find(".pill").each(function(){
        var v = $(this).attr("data-value"), $o = $sel.find('option[value="' + v.replace(/"/g,'\\"') + '"]');
        var off = !$o.length || $o.prop("disabled");
        $(this).toggleClass("is-off", off).toggleClass("is-on", v === val).attr({"aria-checked": String(v === val), "aria-disabled": String(off)});
      });
    });
  }

  /* selected values + fixed attributes of the product, in the shop's attribute order (size · storage · colour · connectivity) */
  var order = [], fixed = {};
  try{ order = JSON.parse(cfg.dataset.order || "[]"); fixed = JSON.parse(cfg.dataset.fixed || "{}"); }catch(_){}
  function chosen(){
    var sel = {}, parts = [], all = true, any = false;
    $form.find("table.variations select").each(function(){
      var tax = this.name.replace(/^attribute_/, "");
      if(this.value){ sel[tax] = $(this).find("option:selected").text(); if(!$(this).closest("tr").hasClass("is-single")) any = true; } else all = false;
    });
    order.forEach(function(t){ if(sel[t]) parts.push(sel[t]); else if(fixed[t]) parts.push(fixed[t]); });
    Object.keys(sel).forEach(function(t){ if(order.indexOf(t) < 0) parts.push(sel[t]); });
    return {parts: any ? parts : [], all:all};
  }
  function writeConfig(){
    if(!cfg) return;
    var c = chosen();
    if(!c.parts.length){ cfg.innerHTML = ""; return; }
    cfg.innerHTML = "<span></span>";
    cfg.firstChild.textContent = c.all ? (CFG.cfgDone || "Ihre Auswahl") : (CFG.cfgOpen || "Bitte alle Optionen wählen");
    cfg.appendChild(document.createTextNode(cfg.dataset.name + " · " + c.parts.join(" · ") + (cfg.dataset.cond ? " · " + cfg.dataset.cond : "")));
    cfg.classList.toggle("is-partial", !c.all);
  }

  /* colour change → image of a matching variation (brief item 06) */
  function imageForColour(){
    var $c = $form.find('select[name="attribute_pa_colour"]'); if(!$c.length || !$c.val()) return;
    var want = {}; $form.find("table.variations select").each(function(){ if(this.value) want[this.name] = this.value; });
    var best = null;
    variations.forEach(function(v){
      if(!v.image || !v.image.src || v.attributes.attribute_pa_colour !== $c.val()) return;
      var score = 0; for(var k in want){ if(v.attributes[k] === want[k] || v.attributes[k] === "") score++; }
      if(!best || score > best.s) best = {s:score, v:v};
    });
    var hasOwn = variations.some(function(v){ return v.attributes.attribute_pa_colour === $c.val() && !v.gadgero_no_photo; });
    if(!hasOwn){ showArt(); return; }
    if(best && !best.v.gadgero_no_photo) setImage(best.v.image.src, best.v.image.full_src, best.v.image.alt);
  }

  $form.on("woocommerce_update_variation_values", syncPills);
  $form.on("change", "select", function(){ syncPills(); writeConfig(); if(this.name === "attribute_pa_colour") imageForColour(); });
  $form.on("found_variation", function(e, v){
    if(v.price_html) $price.html(v.price_html);
    if(v.gadgero_no_photo) showArt();
    else if(v.image && v.image.src && v.image_id) setImage(v.image.src, v.image.full_src, v.image.alt);
    writeConfig();
  });
  $form.on("reset_data", function(){
    $price.html($price.data("initial")); syncPills(); writeConfig();
    if(!$form.find('select[name="attribute_pa_colour"]').val()){ $img.html(initialImg); noteNoPhoto(false); }
  });
  syncPills(); writeConfig();
})(jQuery);
