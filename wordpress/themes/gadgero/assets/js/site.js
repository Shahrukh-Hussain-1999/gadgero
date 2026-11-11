/* Gadgero site script: header, mobile menu, mini-cart drawer, toast, homepage chips, buyback message,
   scroll reveal and themed dropdowns (the last two ported unchanged from the approved static site). */
(function(){
  var CFG = window.GADGERO || {};
  var $ = function(id){ return document.getElementById(id); };

  /* header turns solid after the hero */
  var topEl = $("top");
  function onScroll(){ if(topEl) topEl.classList.toggle("scrolled", window.scrollY > 80); }
  window.addEventListener("scroll", onScroll, {passive:true}); onScroll();

  /* toast */
  function toast(m){ var t = $("toast"); if(!t) return; t.textContent = m; t.classList.add("on"); clearTimeout(toast.t); toast.t = setTimeout(function(){ t.classList.remove("on"); }, 2200); }
  window.gadgeroToast = toast;

  /* mobile menu (the approved design hides the nav below 820px; this button brings it back) */
  var menuBtn = $("menuBtn"), mnav = $("mnav");
  if(menuBtn && mnav){
    menuBtn.addEventListener("click", function(){ var open = mnav.hidden; mnav.hidden = !open; menuBtn.setAttribute("aria-expanded", String(open)); });
    mnav.addEventListener("click", function(e){ if(e.target.closest("a")){ mnav.hidden = true; menuBtn.setAttribute("aria-expanded","false"); } });
  }

  /* mini-cart drawer */
  var drawer = $("drawer"), veil = $("veil"), openBtn = $("openCart");
  function openCart(v){
    if(!drawer) return;
    drawer.classList.toggle("on", v); veil.classList.toggle("on", v);
    drawer.setAttribute("aria-hidden", String(!v)); if(v) drawer.removeAttribute("inert"); else drawer.setAttribute("inert","");
    if(openBtn) openBtn.setAttribute("aria-expanded", String(v));
    if(v){ var x = $("closeCart"); if(x) x.focus(); } else if(openBtn && drawer.contains(document.activeElement)) openBtn.focus();
  }
  if(openBtn) openBtn.addEventListener("click", function(){ openCart(true); });
  document.addEventListener("click", function(e){ if(e.target.closest("#closeCart")) openCart(false); });
  if(veil) veil.addEventListener("click", function(){ openCart(false); });
  document.addEventListener("keydown", function(e){ if(e.key === "Escape") openCart(false); });

  /* WooCommerce AJAX add-to-cart feedback (jQuery events from WooCommerce) */
  function wireWoo(){
    if(!window.jQuery) return;
    jQuery(document.body).on("added_to_cart", function(e, fragments, hash, btn){
      var name = btn && btn.length ? (btn.data("name") || "") : "";
      toast((name ? name + " " : "") + (CFG.added || "added to cart"));
      if(btn && btn.length){ btn.removeClass("added"); btn.next(".added_to_cart").remove(); }
    });
  }

  /* homepage chips: client-side filter of the server-rendered grid (approved behaviour) */
  var chips = $("chips"), grid = $("grid");
  if(chips && grid && chips.tagName !== "NAV"){
    chips.addEventListener("click", function(e){
      var b = e.target.closest("[data-f]"); if(!b) return;
      var f = b.dataset.f;
      Array.prototype.forEach.call(chips.querySelectorAll("[data-f]"), function(c){ c.setAttribute("aria-pressed", String(c === b)); });
      Array.prototype.forEach.call(grid.children, function(card){ card.hidden = !(f === "all" || (" " + card.dataset.cat + " ").indexOf(" " + f + " ") > -1); });
    });
  }

  /* clipboard */
  function copy(text, msg){
    function fallback(){ var t = document.createElement("textarea"); t.value = text; document.body.appendChild(t); t.select(); try{ document.execCommand("copy"); toast(msg); }catch(_){ toast(CFG.copyFail); } t.remove(); }
    if(navigator.clipboard && navigator.clipboard.writeText) navigator.clipboard.writeText(text).then(function(){ toast(msg); }, fallback); else fallback();
  }

  /* buyback: device type, model, storage and condition go into the WhatsApp message and the copied text */
  var form = $("sellForm");
  if(form){
    var hints = {}; try{ hints = JSON.parse(form.dataset.hints || "{}"); }catch(_){}
    var dev = $("sDev"), model = $("sModel"), store = $("sStore"), cond = $("sCond"), wa = $("sellWa"), prev = $("sPreview");
    function sellText(){
      var m = model.value.trim(), s = store.value;
      var what = (m || CFG.noModel) + (s ? " · " + s : "");
      return "Hi " + CFG.shop + ", I want to sell my " + dev.options[dev.selectedIndex].value + ": " + what + ". Condition: " + cond.value + ". What price can you offer?";
    }
    function sellLink(){
      if(hints[dev.value]) model.placeholder = hints[dev.value];
      wa.href = "https://wa.me/" + (CFG.wa || "") + "?text=" + encodeURIComponent(sellText());
      if(prev) prev.textContent = sellText();
    }
    [dev, model, store, cond].forEach(function(el){ el.addEventListener("input", sellLink); el.addEventListener("change", sellLink); });
    form.addEventListener("submit", function(e){ e.preventDefault(); });
    $("sellCopy").addEventListener("click", function(){ copy(sellText(), CFG.copied); });
    sellLink();
    window.gadgeroSellText = sellText;
  }

  /* checkout: keep the "Zahlungsart" summary row in sync with the selected payment method */
  document.addEventListener("change", function(e){
    if(e.target && e.target.name === "payment_method"){
      var cell = document.querySelector(".gadgero-paymethod td"), lbl = document.querySelector('label[for="' + e.target.id + '"]');
      if(cell && lbl) cell.textContent = lbl.textContent.trim();
    }
  });

  if(document.readyState === "loading") document.addEventListener("DOMContentLoaded", wireWoo); else wireWoo();
})();

/* ---- scroll reveal (approved) ---- */
(function(){
  var reduce = window.matchMedia && matchMedia("(prefers-reduced-motion: reduce)").matches;
  var els = document.querySelectorAll(".rv");
  if(reduce || !("IntersectionObserver" in window) || !els.length) return;
  document.documentElement.classList.add("rvjs");
  var io = new IntersectionObserver(function(es){
    es.forEach(function(e){ if(!e.isIntersecting) return; e.target.classList.add("in"); io.unobserve(e.target); });
  }, {threshold:.15, rootMargin:"0px 0px -6% 0px"});
  els.forEach(function(el){ io.observe(el); });
})();

/* themed dropdowns: the browser's own list shows white text on white, so the form selects are replaced by this component */
(function(){
  var CHEV = '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6l5 5 5-5"/></svg>';
  var TICK = '<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 8.5l3.2 3.2L13 4.8"/></svg>';
  var open = null;
  function closeAll(){ if(open){ open.close(); } }
  document.addEventListener("pointerdown", function(e){ if(open && !open.root.contains(e.target)) open.close(); });
  document.addEventListener("keydown", function(e){ if(e.key === "Escape") closeAll(); });
  document.querySelectorAll(".form select").forEach(function(sel){
    var root = document.createElement("div"); root.className = "dd";
    sel.parentNode.insertBefore(root, sel); root.appendChild(sel);
    sel.tabIndex = -1; sel.setAttribute("aria-hidden","true");
    var btn = document.createElement("button"); btn.type = "button"; btn.className = "dd-btn"; btn.setAttribute("aria-haspopup","listbox"); btn.setAttribute("aria-expanded","false");
    var list = document.createElement("ul"); list.className = "dd-list"; list.setAttribute("role","listbox"); list.hidden = true; list.tabIndex = -1;
    var lbl = sel.closest("label"); if(lbl){ var t = lbl.firstChild; btn.setAttribute("aria-label", (t && t.textContent ? t.textContent.trim() : "Choose") + ": " + sel.value); }
    var opts = Array.prototype.slice.call(sel.options), hot = sel.selectedIndex;
    opts.forEach(function(o,i){
      var li = document.createElement("li"); li.className = "dd-opt"; li.setAttribute("role","option"); li.dataset.i = i;
      li.innerHTML = "<span>"+o.text+"</span>"+TICK; list.appendChild(li);
    });
    root.appendChild(btn); root.appendChild(list);
    function paint(){
      btn.innerHTML = "<span>"+sel.options[sel.selectedIndex].text+"</span>"+CHEV;
      Array.prototype.forEach.call(list.children, function(li,i){ li.setAttribute("aria-selected", String(i===sel.selectedIndex)); li.classList.toggle("hot", i===hot); });
    }
    function pick(i){
      sel.selectedIndex = i; hot = i; paint();
      sel.dispatchEvent(new Event("input",{bubbles:true})); sel.dispatchEvent(new Event("change",{bubbles:true}));
    }
    var api = { root:root, close:function(){ list.hidden = true; btn.setAttribute("aria-expanded","false"); if(open === api) open = null; } };
    function show(){ closeAll(); hot = sel.selectedIndex; paint(); list.hidden = false; btn.setAttribute("aria-expanded","true"); open = api;
      var cur = list.children[hot]; if(cur && cur.scrollIntoView) cur.scrollIntoView({block:"nearest"}); }
    btn.addEventListener("click", function(){ if(list.hidden) show(); else api.close(); });
    list.addEventListener("pointerover", function(e){ var li = e.target.closest(".dd-opt"); if(li){ hot = +li.dataset.i; paint(); } });
    list.addEventListener("click", function(e){ var li = e.target.closest(".dd-opt"); if(li){ pick(+li.dataset.i); api.close(); btn.focus(); } });
    btn.addEventListener("keydown", function(e){
      var k = e.key;
      if(k === "ArrowDown" || k === "ArrowUp"){ e.preventDefault(); if(list.hidden){ show(); return; } hot = (hot + (k === "ArrowDown" ? 1 : -1) + opts.length) % opts.length; paint(); list.children[hot].scrollIntoView({block:"nearest"}); }
      else if(k === "Home" && !list.hidden){ e.preventDefault(); hot = 0; paint(); }
      else if(k === "End" && !list.hidden){ e.preventDefault(); hot = opts.length-1; paint(); }
      else if((k === "Enter" || k === " ") && !list.hidden){ e.preventDefault(); pick(hot); api.close(); }
      else if(k === "Tab"){ api.close(); }
    });
    paint();
  });
})();
