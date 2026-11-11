/* Gadgero hero: approved scroll story with the 3D foldable phone (ported unchanged from the static site, apart from
   reading the campaign word, model URL and step panels from WordPress). Needs three.js r147 + GLTFLoader. */
(function(){
  var CFG = window.GADGERO_HERO || {};
  var story = document.getElementById("story");
  if(!story) return;
  var stage = document.getElementById("stage"), canvas = document.getElementById("gl");
  var wordEl = document.getElementById("word"), ringEl = document.getElementById("ring"), introEl = document.getElementById("intro"), hintEl = document.getElementById("hint");
  var stepsEl = document.getElementById("steps"), curEl = document.getElementById("cur"), progEl = document.getElementById("prog");
  var reduce = window.matchMedia && matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* giant lettering: one span per character, then fitted to the stage width so longer campaign names still fit */
  (CFG.word || "").split("").forEach(function(ch,j){ var s = document.createElement("span"); s.style.setProperty("--i", j); if(ch === " "){ s.className = "sp"; s.textContent = " "; } else s.textContent = ch; wordEl.appendChild(s); });
  /* longer names: shrink to fit on one line; if that would make it small (narrow screens), break into lines per word instead */
  function fitWord(){
    wordEl.style.fontSize = ""; wordEl.style.whiteSpace = ""; wordEl.classList.remove("multi");
    var max = stage.clientWidth * .94, w = wordEl.scrollWidth, fs = parseFloat(getComputedStyle(wordEl).fontSize);
    if(w <= max) return;
    var one = fs * max / w;
    if(one >= 64 || (CFG.word || "").indexOf(" ") < 0){ wordEl.style.fontSize = Math.max(40, one) + "px"; return; }
    wordEl.classList.add("multi");
    var longest = Math.max.apply(null, (CFG.word || "").split(" ").map(function(s){ return s.length; }));
    var lw = w / (CFG.word || "").length * longest;   /* approx. width of the longest word at the current size */
    wordEl.style.fontSize = Math.min(fs, fs * max / lw) + "px";
  }
  fitWord(); window.addEventListener("resize", fitWord);
  if(document.fonts && document.fonts.ready) document.fonts.ready.then(fitWord);
  requestAnimationFrame(function(){ requestAnimationFrame(function(){ story.classList.add("ready"); }); });

  /* step panels are rendered by WordPress; their scroll ranges come from data-from / data-to */
  var STEPS = Array.prototype.map.call(stepsEl.querySelectorAll(".step"), function(el){ return {from:parseFloat(el.dataset.from), to:parseFloat(el.dataset.to), el:el}; });

  /* phone pose per scroll position. rY/rX rotation, x = side shift (0..1 of the free space), y/ox/oy world offsets, s scale, m = 1 when a text panel sits under the phone on small screens */
  var PI = Math.PI;
  var KEYS = [
    {t:0,    rY:PI-.55,rX:.1,  x:0, y:-1.2, ox:0, oy:0, s:.9,  m:.8},
    {t:.14,  rY:1.3,   rX:.06, x:0, y:-.4,  ox:0, oy:0, s:.95, m:.8},
    {t:.28,  rY:0,     rX:0,   x:0, y:0,    ox:0, oy:0, s:1,   m:.8},
    {t:.38,  rY:.35,   rX:.04, x:1, y:0,    ox:0, oy:0, s:1,   m:1},
    {t:.62,  rY:.7,    rX:.05, x:1, y:0,    ox:0, oy:0, s:1.04,m:1},
    {t:.82,  rY:-.1,   rX:0,   x:1, y:0,    ox:0, oy:0, s:1.06,m:1},
    {t:1,    rY:.25,   rX:.02, x:1, y:0,    ox:0, oy:0, s:1.1, m:1}
  ];
  var FIELDS = ["rY","rX","x","y","ox","oy","s","m"];
  function ease(u){ return u*u*(3-2*u); }
  function pose(p){
    var i = 0; while(i < KEYS.length-2 && p > KEYS[i+1].t) i++;
    var a = KEYS[i], b = KEYS[i+1], u = Math.min(1, Math.max(0, (p-a.t)/(b.t-a.t))); u = ease(u);
    var o = {}; FIELDS.forEach(function(f){ o[f] = a[f] + (b[f]-a[f])*u; }); return o;
  }
  function clamp(v,a,b){ return Math.min(b, Math.max(a, v)); }

  /* ---- Three.js scene ---- */
  var R = null;
  function build(){
    var T = window.THREE; if(!T) throw new Error("three missing");
    var renderer = new T.WebGLRenderer({canvas:canvas, antialias:true, alpha:true, powerPreference:"high-performance"});
    renderer.setClearColor(0x000000, 0);
    renderer.outputEncoding = T.sRGBEncoding;
    renderer.toneMapping = T.ACESFilmicToneMapping; renderer.toneMappingExposure = 1.3;
    var scene = new T.Scene();
    var camera = new T.PerspectiveCamera(30, 1, .1, 100); camera.position.set(0,0,16);

    /* studio reflections for the metal and glass */
    var envScene = new T.Scene();
    envScene.add(new T.Mesh(new T.BoxGeometry(40,40,40), new T.MeshBasicMaterial({color:0x0b1636, side:T.BackSide})));
    function panel(w,h,pos,col,k){
      var m = new T.Mesh(new T.PlaneGeometry(w,h), new T.MeshBasicMaterial({color:new T.Color(col).multiplyScalar(k), side:T.DoubleSide}));
      m.position.set(pos[0],pos[1],pos[2]); m.lookAt(0,0,0); envScene.add(m);
    }
    panel(14,8,[-9,9,9],0xffffff,8); panel(4,18,[12,0,5],0x8fdcff,8); panel(18,3,[0,-10,7],0xffffff,5); panel(10,10,[0,8,-14],0x4a9cff,8); panel(4,16,[-13,-2,-2],0x33d0ff,5); panel(10,10,[0,0,14],0xffffff,3);
    var pmrem = new T.PMREMGenerator(renderer);
    scene.environment = pmrem.fromScene(envScene, .02).texture;

    /* drifting light particles behind and around the phone */
    var N = 420, ppos = new Float32Array(N*3), pbase = new Float32Array(N*3), pspd = new Float32Array(N);
    for(var i=0;i<N;i++){ pbase[i*3] = (Math.random()-.5)*34; pbase[i*3+1] = Math.random()*20; pbase[i*3+2] = -14 + Math.random()*13; pspd[i] = .35 + Math.random()*.9; }
    var pg = new T.BufferGeometry(); pg.setAttribute("position", new T.BufferAttribute(ppos,3));
    var sc = document.createElement("canvas"); sc.width = sc.height = 64; var sx = sc.getContext("2d");
    var sg = sx.createRadialGradient(32,32,0,32,32,32); sg.addColorStop(0,"rgba(255,255,255,1)"); sg.addColorStop(.25,"rgba(160,230,255,.8)"); sg.addColorStop(1,"rgba(60,160,255,0)"); sx.fillStyle = sg; sx.fillRect(0,0,64,64);
    var points = new T.Points(pg, new T.PointsMaterial({size:.26, map:new T.CanvasTexture(sc), transparent:true, opacity:.8, depthWrite:false, blending:T.AdditiveBlending, toneMapped:false, sizeAttenuation:true}));
    points.frustumCulled = false; scene.add(points);
    function stepParticles(time){
      for(var i=0;i<N;i++){
        var y = (pbase[i*3+1] + time*.0007*pspd[i]) % 20;
        ppos[i*3] = pbase[i*3] + Math.sin(time*.0004*pspd[i] + i)*.5;
        ppos[i*3+1] = y - 10;
        ppos[i*3+2] = pbase[i*3+2];
      }
      pg.attributes.position.needsUpdate = true;
    }
    stepParticles(0);

    var key = new T.DirectionalLight(0xffffff, 1.7); key.position.set(-6,8,10); scene.add(key);
    var rim = new T.DirectionalLight(0x5aa8ff, 3.0); rim.position.set(9,2,-6); scene.add(rim);
    var rim2 = new T.DirectionalLight(0x33d0ff, 2.6); rim2.position.set(-9,-3,-6); scene.add(rim2);
    var fill = new T.DirectionalLight(0xffffff, .6); fill.position.set(6,-2,12); scene.add(fill);
    var rearLight = new T.DirectionalLight(0xdfe9ff, .5); rearLight.position.set(0,4,-12); scene.add(rearLight);   /* lights the rear glass when the phone turns round */
    scene.add(new T.AmbientLight(0x8fb0f0, .4));

    /* helpers */
    function rr(w,h,r){
      var s = new T.Shape(), x = -w/2, y = -h/2;
      s.moveTo(x+r,y); s.lineTo(x+w-r,y); s.absarc(x+w-r,y+r,r,-PI/2,0,false);
      s.lineTo(x+w,y+h-r); s.absarc(x+w-r,y+h-r,r,0,PI/2,false);
      s.lineTo(x+r,y+h); s.absarc(x+r,y+h-r,r,PI/2,PI,false);
      s.lineTo(x,y+r); s.absarc(x+r,y+r,r,PI,PI*1.5,false); return s;
    }
    function flat(w,h,r){
      var g = new T.ShapeGeometry(rr(w,h,r), 24), pos = g.attributes.position, uv = [];
      for(var i=0;i<pos.count;i++) uv.push((pos.getX(i)+w/2)/w, (pos.getY(i)+h/2)/h);
      g.setAttribute("uv", new T.Float32BufferAttribute(uv,2)); return g;
    }
    function slab(w,h,r,depth,bev){
      var g = new T.ExtrudeGeometry(rr(w-2*bev,h-2*bev,Math.max(.01,r-bev)), {depth:depth, bevelEnabled:true, bevelThickness:bev, bevelSize:bev, bevelSegments:6, curveSegments:28});
      g.translate(0,0,-depth/2); return g;
    }

    var W = 3.0, H = 6.2, RAD = .5, D = .24, BV = .06;   /* body size; front face sits at z = D/2 + BV */
    var FZ = D/2 + BV;
    var root = new T.Group(); scene.add(root);
    var phone = new T.Group(); phone.visible = false; root.add(phone);   /* hand-built phone, used only if the GLB cannot load */
    var frameMat = new T.MeshStandardMaterial({color:0xaab9d4, metalness:1, roughness:.26, envMapIntensity:1.35});
    phone.add(new T.Mesh(slab(W,H,RAD,D,BV), frameMat));

    /* front: black bezel, live screen, thin glass */
    var bez = new T.Mesh(flat(W-.09,H-.09,RAD-.045), new T.MeshBasicMaterial({color:0x020308})); bez.position.z = FZ+.0008; phone.add(bez);
    var tex = screenTexture(); 
    var scr = new T.Mesh(flat(W-.17,H-.17,RAD-.085), new T.MeshBasicMaterial({map:tex, toneMapped:false})); scr.position.z = FZ+.0018; phone.add(scr);
    var glass = new T.Mesh(flat(W-.09,H-.09,RAD-.045), new T.MeshStandardMaterial({color:0xffffff, transparent:true, opacity:.07, roughness:.02, metalness:0, envMapIntensity:2.2, depthWrite:false})); glass.position.z = FZ+.003; phone.add(glass);

    /* back: frosted glass + camera plateau (built as seen from behind) */
    var back = new T.Group(); back.rotation.y = PI; back.position.z = -FZ-.0008; phone.add(back);
    back.add(new T.Mesh(flat(W-.09,H-.09,RAD-.045), new T.MeshPhysicalMaterial({color:0x1856e8, roughness:.62, metalness:.05, clearcoat:.25, clearcoatRoughness:.45, envMapIntensity:.5})));
    var PW = 1.55, plate = new T.Mesh(slab(PW,PW,.38,.05,.018), new T.MeshPhysicalMaterial({color:0x2f72f5, roughness:.25, metalness:.1, clearcoat:1, clearcoatRoughness:.08, envMapIntensity:.9}));
    var px = -(W/2 - .2 - PW/2), py = H/2 - .2 - PW/2;
    plate.position.set(px,py,.045); back.add(plate);
    var ringMat = new T.MeshStandardMaterial({color:0xc6d2ea, metalness:1, roughness:.2, envMapIntensity:1.5});
    var lensMat = new T.MeshPhysicalMaterial({color:0x04060b, metalness:.4, roughness:.05, clearcoat:1, clearcoatRoughness:0, envMapIntensity:2.2});
    var coreMat = new T.MeshBasicMaterial({color:0x1b4dd8, toneMapped:false});
    [[-.38,.38],[-.38,-.38],[.38,0]].forEach(function(c){
      var g = new T.Group(); g.position.set(px+c[0], py+c[1], .1);
      var ring = new T.Mesh(new T.CylinderGeometry(.27,.29,.09,56), ringMat); ring.rotation.x = PI/2; g.add(ring);
      var lens = new T.Mesh(new T.CylinderGeometry(.21,.21,.1,56), lensMat); lens.rotation.x = PI/2; lens.position.z = .012; g.add(lens);
      var core = new T.Mesh(new T.TorusGeometry(.105,.012,12,48), coreMat); core.position.z = .066; g.add(core);
      var dot = new T.Mesh(new T.CircleGeometry(.04,24), new T.MeshBasicMaterial({color:0x8fd0ff, toneMapped:false, transparent:true, opacity:.7})); dot.position.set(-.06,.07,.067); g.add(dot);
      back.add(g);
    });
    var flash = new T.Mesh(new T.CylinderGeometry(.085,.085,.05,32), new T.MeshStandardMaterial({color:0xfff2d6, emissive:0xffe2a8, emissiveIntensity:.5, roughness:.4})); flash.rotation.x = PI/2; flash.position.set(px+.43,py+.62,.08); back.add(flash);
    var mic = new T.Mesh(new T.CylinderGeometry(.03,.03,.04,16), new T.MeshBasicMaterial({color:0x05070c})); mic.rotation.x = PI/2; mic.position.set(px+.12,py+.62,.08); back.add(mic);

    /* side buttons */
    function btn(x,y,h){ var m = new T.Mesh(new T.BoxGeometry(.06,h,.12), frameMat); m.position.set(x, y, 0); phone.add(m); }
    btn(-W/2-.005, 1.95, .34); btn(-W/2-.005, 1.2, .62); btn(-W/2-.005, .35, .62); btn(W/2+.005, 1.35, 1.0);
    /* simple bottom port row */
    var port = new T.Mesh(new T.BoxGeometry(.5,.05,.12), new T.MeshBasicMaterial({color:0x0a0d14})); port.position.set(0,-H/2-.0, 0); phone.add(port);

    R = {renderer:renderer, scene:scene, camera:camera, phone:root, tex:tex, model:null, points:points, step:stepParticles};
    sizeGL();

    /* real model: the GLB, shipped as model.wasm because that is a binary type the host serves (front faces -z in the file, so it is turned round, scaled to the same 6.2 unit height and centred) */
    function showProc(){ if(!R.model) phone.visible = true; }
    if(T.GLTFLoader){
      fetch(CFG.model).then(function(r){ if(!r.ok) throw new Error("model"); return r.arrayBuffer(); }).then(function(buf){ new T.GLTFLoader().parse(buf, "", onModel, showProc); }).catch(showProc);
      function onModel(g){
        /* the Duo opens at about 0.166 wide x 0.118 high (file units); it is scaled so the open width is 5 world units, and its fold animation is driven by scroll */
        var m = g.scene, wrap = new T.Group(), OW = .166, OH = .118; wrap.add(m);
        m.position.set(0, -OH/2, 0); wrap.scale.setScalar(5/OW);
        m.traverse(function(o){
          if(!o.isMesh) return;
          o.frustumCulled = false;
          var mt = o.material; if(!mt) return;
          if(/^C_NS_(Side|Backpanel|Antenna|Side_Matte|Plastic|ButtonCap|Apple)/.test(mt.name) || mt.name === "BASE_Grey" || mt.name === "BASE_Hinge"){ mt.envMapIntensity = 2.6; mt.needsUpdate = true; }
          if(/^C_NS_Screen/.test(mt.name)){ mt.emissiveIntensity = 1.75; mt.needsUpdate = true; }
          if(mt.name === "BASE_Glass" || mt.name === "C_NS_CamGloss"){ mt.envMapIntensity = 1.4; mt.needsUpdate = true; }
          if(/^BASE_Cam_Lens/.test(mt.name)){ mt.envMapIntensity = .5; mt.needsUpdate = true; }
        });
        var mixer = new T.AnimationMixer(m); mixer.clipAction(g.animations[0]).play(); mixer.setTime(1);
        R.mixer = mixer; R.fold = m;
        root.add(wrap); phone.visible = false; R.model = wrap; story.classList.add("glb");
      }
      setTimeout(showProc, 7000);
    } else showProc();
  }

  function screenTexture(){
    var c = document.createElement("canvas"); c.width = 708; c.height = 1500; var x = c.getContext("2d");
    function draw(){
      var w = c.width, h = c.height;
      var g = x.createLinearGradient(0,0,w,h); g.addColorStop(0,"#37c8ff"); g.addColorStop(.45,"#0a6cff"); g.addColorStop(1,"#0a1d55");
      x.fillStyle = g; x.fillRect(0,0,w,h);
      /* ribbon shapes echoing the logo mark */
      x.save(); x.lineCap = "round";
      var rg = x.createLinearGradient(0,h*.4,w,h); rg.addColorStop(0,"rgba(255,255,255,.28)"); rg.addColorStop(1,"rgba(255,255,255,.02)");
      x.strokeStyle = rg; x.lineWidth = 170; x.beginPath(); x.arc(w*.62,h*.66,w*.52,Math.PI*.95,Math.PI*2.05); x.stroke();
      x.strokeStyle = "rgba(4,16,60,.38)"; x.lineWidth = 120; x.beginPath(); x.arc(w*.5,h*.7,w*.62,Math.PI*.1,Math.PI*.95); x.stroke();
      x.restore();
      x.fillStyle = "#000"; x.beginPath(); x.roundRect ? x.roundRect(w/2-74,34,148,44,22) : x.rect(w/2-74,34,148,44); x.fill();
      x.fillStyle = "#fff"; x.textAlign = "center";
      x.font = "600 30px Sora, Arial, sans-serif"; x.globalAlpha = .9; x.fillText("Tuesday, 20 October", w/2, 230); x.globalAlpha = 1;
      x.font = "700 210px Sora, Arial, sans-serif"; x.fillText("9:41", w/2, 430);
      x.textAlign = "left"; x.font = "600 28px Sora, Arial, sans-serif"; x.fillText("9:41", 58, 70);
      /* widgets */
      function card(px,py,pw,ph){ x.fillStyle = "rgba(255,255,255,.18)"; x.beginPath(); x.roundRect ? x.roundRect(px,py,pw,ph,40) : x.rect(px,py,pw,ph); x.fill(); }
      card(58,560,282,282); card(368,560,282,282);
      x.fillStyle = "#fff"; x.font = "800 34px Sora, Arial, sans-serif"; x.fillText("Gadgero", 90, 640);
      x.font = "500 24px Sora, Arial, sans-serif"; x.globalAlpha = .85; x.fillText("New + refurbished", 90, 682); x.globalAlpha = 1;
      x.font = "800 90px Sora, Arial, sans-serif"; x.fillText("94%", 396, 720);
      x.font = "500 24px Sora, Arial, sans-serif"; x.globalAlpha = .85; x.fillText("Battery health", 396, 780); x.globalAlpha = 1;
      /* dock */
      [0,1,2,3].forEach(function(i){ var gx = 58+i*158; var gg = x.createLinearGradient(gx,0,gx+130,0); gg.addColorStop(0,"rgba(255,255,255,.34)"); gg.addColorStop(1,"rgba(255,255,255,.16)"); x.fillStyle = gg; x.beginPath(); x.roundRect ? x.roundRect(gx,1180,130,130,34) : x.rect(gx,1180,130,130); x.fill(); });
      x.fillStyle = "rgba(255,255,255,.9)"; x.beginPath(); x.roundRect ? x.roundRect(w/2-90,1450,180,9,5) : x.rect(w/2-90,1450,180,9); x.fill();
    }
    draw();
    var t = new THREE.CanvasTexture(c); t.encoding = THREE.sRGBEncoding; t.anisotropy = 8;
    if(document.fonts && document.fonts.ready) document.fonts.ready.then(function(){ draw(); t.needsUpdate = true; screenClones.forEach(function(c){ c.needsUpdate = true; }); });
    return t;
  }

  var screenClones = [];
  var vw = 1, vh = 1;
  function sizeGL(){
    vw = stage.clientWidth; vh = stage.clientHeight;
    if(!R) return;
    R.renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    R.renderer.setSize(vw, vh, false);
    R.camera.aspect = vw/vh; R.camera.updateProjectionMatrix();
  }
  window.addEventListener("resize", sizeGL);

  try { build(); } catch(e){ R = null; story.classList.add("nogl"); }

  /* ---- scroll progress, smoothed ---- */
  var target = 0, cur = 0, mx = 0, my = 0, smx = 0, smy = 0, running = false;
  function readProgress(){
    var r = story.getBoundingClientRect(), hh = stage.getBoundingClientRect().top; /* stage top is pinned under the header */
    var total = story.offsetHeight - stage.offsetHeight;
    var offset = r.top - parseFloat(getComputedStyle(story).marginTop || 0) * 0; /* story top relative to viewport */
    var pinTop = parseFloat(getComputedStyle(stage).top) || 0;
    return clamp((pinTop - r.top) / total, 0, 1);
  }
  window.addEventListener("pointermove", function(e){ mx = (e.clientX/innerWidth - .5)*2; my = (e.clientY/innerHeight - .5)*2; }, {passive:true});

  function apply(p, time){
    var o = pose(p), desk = vw/vh > 1.05;
    /* step panels + counter */
    var idx = -1; STEPS.forEach(function(s,i){ if(p >= s.from && p < s.to) idx = i; });
    STEPS.forEach(function(s,i){ s.el.classList.toggle("on", i===idx); });
    var n = idx >= 0 ? idx+2 : (p < .34 ? 1 : (STEPS.filter(function(s){ return p >= s.to; }).length + 1));
    curEl.textContent = "0" + clamp(n,1,2);
    progEl.style.height = (p*100) + "%";

    /* intro copy, word, hint fade out as the phone moves off-centre */
    var fade = clamp((p-.02)/.08, 0, 1);
    introEl.style.opacity = 1 - fade; introEl.style.transform = "translateY(" + (fade*30) + "px)"; introEl.style.pointerEvents = fade > .8 ? "none" : "auto";
    hintEl.style.opacity = 1 - clamp(p/.03,0,1);
    var wf = clamp((p-.06)/.12, 0, 1);
    wordEl.style.opacity = 1 - wf; wordEl.style.transform = "scale(" + (1 + wf*.18) + ") translateY(" + (-wf*40) + "px)";

    /* background light follows the phone */
    stage.style.setProperty("--gx", (68 - o.x*18 + Math.sin(p*7)*6) + "%");
    stage.style.setProperty("--gy", (32 + Math.cos(p*5)*10) + "%");

    var cam = R ? R.camera : null, dist = 16;
    var visH = 2*Math.tan((30*PI/180)/2)*dist, visW = visH*(vw/vh);
    var closed = clamp(1 - (p-.03)/.22, 0, 1); closed = closed*closed*(3-2*closed);
    var sc = o.s*(desk ? 1 : .7)*(R && R.mixer ? 1 + (desk ? .45 : .28)*closed : 1), shiftX = desk ? o.x*visW*.2 : 0;
    var px = shiftX + o.ox*(desk?1:.7)*sc/o.s*1, py = o.y + o.oy*(desk?1:.75) + (desk ? 0 : o.m*1.7);
    if(!desk) px = o.ox*.5;
    var bob = Math.sin(time*.0012)*.07;
    smx += (mx - smx)*.06; smy += (my - smy)*.06;

    if(R){
      R.phone.rotation.y = o.rY + smx*.14; R.phone.rotation.x = o.rX + smy*.08;
      R.phone.position.set(px, py + bob, 0); R.phone.scale.setScalar(sc);
      if(R.mixer){ R.mixer.setTime(1 - (1-closed)); R.fold.position.x = -.04*closed; }
      if(time) R.step(time);
      R.points.position.set(smx*-.6, p*-4, 0); R.points.rotation.z = p*.25;   /* depth parallax with scroll and pointer */
      R.renderer.render(R.scene, R.camera);
    } else {
      var fb = document.getElementById("fallback");
      fb.style.transform = "translate(" + (shiftX/visW*vw + (o.ox*vw/visW)) + "px," + (-py/visH*vh) + "px) scale(" + sc + ")";
    }

    /* ring follows the phone and softens when the details take over */
    ringEl.style.setProperty("--rx", (50 + (px/visW)*100) + "%");
    ringEl.style.setProperty("--ry", (50 - ((py+bob)/visH)*100) + "%");
    ringEl.style.setProperty("--rs", (vh*.86*sc) + "px");
    ringEl.style.setProperty("--ro", String(clamp(1 - clamp((p-.34)/.2,0,1)*.7 - (p<.05 ? (.05-p)*10 : 0), .15, 1)));
  }

  var last = 0;
  function frame(time){
    if(!running) return;
    var dt = last ? Math.min(time - last, 250) : 16; last = time;
    target = readProgress();
    cur += (target - cur)*(reduce ? 1 : 1 - Math.exp(-dt/130));
    if(Math.abs(target-cur) < .0002) cur = target;
    apply(cur, reduce ? 0 : time);
    requestAnimationFrame(frame);
  }
  if("IntersectionObserver" in window){
    new IntersectionObserver(function(es){
      var on = es[0].isIntersecting; if(on && !running){ running = true; requestAnimationFrame(frame); } else if(!on){ running = false; }
    }, {rootMargin:"200px"}).observe(story);
  } else { running = true; requestAnimationFrame(frame); }
  document.addEventListener("visibilitychange", function(){ last = 0; });
  cur = target = readProgress(); apply(cur, 0);
})();
