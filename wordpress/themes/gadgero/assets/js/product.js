/* Gadgero product page.
   WooCommerce's own variation form stays in charge of matching variations, price, stock, availability and the
   add-to-cart rules. This script only:
   - shows each attribute <select> as a row of pill buttons (the select stays in the DOM and is what WooCommerce reads),
   - mirrors WooCommerce's disabled / unavailable options onto the pills,
   - writes the "Selected configuration" line and swaps the big price and image when a variation is found,
   - switches gallery images. */
(function($){
  var $form = $("form.variations_form"), $price = $("#pdpPrice"), cfg = document.getElementById("pdpConfig");
  var $img = $("#pdpImg"), initialImg = $img.html();

  /* gallery thumbs */
  $(".pdp-thumbs").on("click", ".pdp-thumb", function(){
    var src = $(this).data("full"); $img.find("img").attr({src:src, srcset:""}).removeAttr("sizes");
    $(".pdp-thumb").attr("aria-pressed","false"); $(this).attr("aria-pressed","true");
  });

  if(!$form.length) return;

  /* pills */
  $form.find("table.variations select").each(function(){
    var $sel = $(this), id = this.id, $cell = $sel.closest("td");
    var label = $form.find('label[for="' + id + '"]').text().trim();
    var $group = $('<div class="pills" role="radiogroup"></div>').attr("aria-label", label);
    $sel.find("option").each(function(){
      if(!this.value) return;
      $('<button type="button" class="pill" role="radio" aria-checked="false"></button>')
        .text(this.text).attr("data-value", this.value).appendTo($group);
    });
    $sel.addClass("pill-select").after($group);
    $group.on("click", ".pill", function(){
      var v = $(this).attr("data-value");
      if($(this).hasClass("is-off")) return;
      $sel.val($sel.val() === v ? "" : v).trigger("change");
    });
  });

  /* an option with only one value is preselected (and stays visible as a selected pill) */
  $form.find("table.variations select").each(function(){
    var opts = $(this).find("option").filter(function(){ return this.value; });
    if(opts.length === 1 && !this.value){ $(this).val(opts[0].value); }
  });
  setTimeout(function(){ $form.find("table.variations select").first().trigger("change"); }, 0);

  function syncPills(){
    $form.find("table.variations select").each(function(){
      var $sel = $(this), val = $sel.val(), $pills = $sel.next(".pills").find(".pill");
      $pills.each(function(){
        var v = $(this).attr("data-value"), $o = $sel.find('option[value="' + v.replace(/"/g,'\\"') + '"]');
        $(this).toggleClass("is-off", !$o.length || $o.prop("disabled")).attr({"aria-checked": String(v === val), "aria-disabled": String(!$o.length || $o.prop("disabled"))});
        $(this).toggleClass("is-on", v === val);
      });
    });
  }

  function configLine(variation){
    var parts = [];
    $form.find("table.variations select").each(function(){
      var t = $(this).find("option:selected"); if(this.value) parts.push(t.text());
    });
    return parts;
  }

  function writeConfig(variation){
    if(!cfg) return;
    var parts = configLine(variation), all = parts.length === $form.find("table.variations select").length;
    var name = cfg.dataset.name, cond = cfg.dataset.cond;
    if(!parts.length){ cfg.innerHTML = ""; return; }
    var txt = name + " · " + parts.join(" · ") + (cond ? " · " + cond : "");
    cfg.innerHTML = '<span></span>';
    cfg.firstChild.textContent = all ? "Selected configuration" : "Choose all options";
    cfg.appendChild(document.createTextNode(txt));
    cfg.classList.toggle("is-partial", !all);
  }

  $form.on("woocommerce_update_variation_values", syncPills);
  $form.on("change", "select", function(){ syncPills(); writeConfig(); });
  $form.on("found_variation", function(e, v){
    if(v.price_html) $price.html(v.price_html);
    if(v.image && v.image.src && v.image_id){ $img.html('<img src="' + v.image.src + '" alt="' + (v.image.alt || "").replace(/"/g,"&quot;") + '">'); }
    writeConfig(v);
  });
  $form.on("reset_data", function(){
    $price.html($price.data("initial")); $img.html(initialImg); syncPills(); writeConfig();
  });
  syncPills(); writeConfig();
})(jQuery);
