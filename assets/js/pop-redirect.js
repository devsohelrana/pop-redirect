/**
 * Pop Redirect - Frontend Script v2.0
 *
 * THE REAL PROBLEM:
 * Browsers (Chrome, Firefox, Safari) block window.open() unless called
 * synchronously inside a direct user click event on a real DOM element.
 * No timeout, no scroll, no indirect trigger works reliably.
 *
 * THE SOLUTION:
 * Show a sticky notification bar with a real <a> link (target="_blank").
 * When the visitor clicks it, the browser opens the tab — guaranteed.
 * This is exactly how all major ad networks and traffic exchanges work.
 *
 * MODE: "auto" — page itself acts as a clickable layer (opens on any click)
 * MODE: "bar"  — sticky top/bottom bar with a button
 */
(function () {
  "use strict";

  if (typeof aotConfig === "undefined") return;

  var url = aotConfig.url || "";
  var delay = parseInt(aotConfig.delay, 10) || 0;
  var onceSession = !!aotConfig.onceSession;
  var mode = aotConfig.mode === "auto" ? "auto" : "bar";
  var barText = aotConfig.barText || "Visit our special offer";
  var barBtnText = aotConfig.barBtnText || "Open Now";
  var barPos = aotConfig.barPos || "bottom"; // 'top' or 'bottom'
  var barColor = aotConfig.barColor || "#1a73e8";
  var autoText = aotConfig.autoText || "Click anywhere to continue";
  var doneKey = "aot_done";

  if (!url) return;

  // Session guard
  if (hasDone()) return;

  /* ------------------------------------------------------------------ */
  /*  HELPER: mark done                                                   */
  /* ------------------------------------------------------------------ */
  function hasDone() {
    if (!onceSession) return false;

    try {
      if (window.sessionStorage && sessionStorage.getItem(doneKey) === "1") {
        return true;
      }
    } catch (e) {}

    return new RegExp("(?:^|; )" + doneKey + "=1(?:;|$)").test(
      document.cookie || "",
    );
  }

  function markDone() {
    if (!onceSession) return;

    try {
      if (window.sessionStorage) {
        sessionStorage.setItem(doneKey, "1");
      }
    } catch (e) {}

    document.cookie = doneKey + "=1; path=/; SameSite=Lax";
  }

  /* ------------------------------------------------------------------ */
  /*  MODE: BAR — sticky notification bar with a real <a> button         */
  /* ------------------------------------------------------------------ */
  function showBar() {
    markDone();

    var bar = document.createElement("div");
    bar.id = "aot-bar";

    var isTop = barPos === "top";

    bar.style.cssText = [
      "position:fixed",
      isTop ? "top:0" : "bottom:0",
      "left:0",
      "right:0",
      "z-index:2147483647",
      "background:" + barColor,
      "color:#fff",
      'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif',
      "font-size:15px",
      "padding:12px 16px",
      "display:flex",
      "align-items:center",
      "justify-content:center",
      "gap:14px",
      "box-shadow:0 " + (isTop ? "2px" : "-2px") + " 10px rgba(0,0,0,.25)",
      "box-sizing:border-box",
    ].join(";");

    // Message text
    var msg = document.createElement("span");
    msg.textContent = barText;
    msg.style.cssText = "flex:1;text-align:center;font-weight:500;";

    // Open button — real <a> tag so browser allows target="_blank"
    var btn = document.createElement("a");
    btn.href = url;
    btn.target = "_blank";
    btn.rel = "noopener noreferrer";
    btn.textContent = barBtnText;
    btn.style.cssText = [
      "background:#fff",
      "color:" + barColor,
      "border:none",
      "border-radius:4px",
      "padding:7px 18px",
      "font-size:14px",
      "font-weight:700",
      "cursor:pointer",
      "text-decoration:none",
      "white-space:nowrap",
      "flex-shrink:0",
    ].join(";");
    btn.addEventListener("click", function () {
      markDone();
      setTimeout(function () {
        removeBar();
      }, 300);
    });

    // Close button
    var close = document.createElement("button");
    close.textContent = "✕";
    close.setAttribute("aria-label", "Close");
    close.style.cssText = [
      "background:none",
      "border:none",
      "color:rgba(255,255,255,.8)",
      "font-size:18px",
      "cursor:pointer",
      "padding:0 4px",
      "line-height:1",
      "flex-shrink:0",
    ].join(";");
    close.addEventListener("click", function () {
      markDone();
      removeBar();
    });

    bar.appendChild(msg);
    bar.appendChild(btn);
    bar.appendChild(close);
    document.body.appendChild(bar);

    // Nudge page content so bar doesn't cover content
    if (isTop) {
      document.body.style.marginTop =
        (parseInt(document.body.style.marginTop, 10) || 0) +
        bar.offsetHeight +
        "px";
    }
  }

  function removeBar() {
    var bar = document.getElementById("aot-bar");
    if (bar) {
      bar.style.opacity = "0";
      bar.style.transition = "opacity .25s";
      setTimeout(function () {
        bar && bar.parentNode && bar.parentNode.removeChild(bar);
      }, 300);
    }
  }

  /* ------------------------------------------------------------------ */
  /*  MODE: AUTO — invisible full-page overlay, opens on first click     */
  /*  Still requires a real click but needs zero UI friction.            */
  /* ------------------------------------------------------------------ */
  function showAutoOverlay() {
    markDone();

    // Transparent clickable <a> covering the whole viewport
    var overlay = document.createElement("a");
    overlay.href = url;
    overlay.target = "_blank";
    overlay.rel = "noopener noreferrer";
    overlay.style.cssText = [
      "position:fixed",
      "top:0",
      "left:0",
      "right:0",
      "bottom:0",
      "z-index:2147483646",
      "cursor:pointer",
      "display:block",
      "background:transparent",
    ].join(";");

    // Optional hint label at bottom center
    if (autoText) {
      var hint = document.createElement("div");
      hint.textContent = autoText;
      hint.style.cssText = [
        "position:absolute",
        "bottom:20px",
        "left:50%",
        "transform:translateX(-50%)",
        "background:rgba(0,0,0,.55)",
        "color:#fff",
        'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif',
        "font-size:13px",
        "padding:6px 16px",
        "border-radius:20px",
        "white-space:nowrap",
        "pointer-events:none",
      ].join(";");
      overlay.appendChild(hint);
    }

    overlay.addEventListener("click", function () {
      markDone();
      // Remove overlay so normal page links work again
      setTimeout(function () {
        overlay &&
          overlay.parentNode &&
          overlay.parentNode.removeChild(overlay);
      }, 200);
    });

    document.body.appendChild(overlay);
  }

  /* ------------------------------------------------------------------ */
  /*  Launch after delay                                                  */
  /* ------------------------------------------------------------------ */
  function launch() {
    var win = null;

    try {
      win = window.open(url, "_blank");

      if (win) {
        try {
          win.opener = null;
        } catch (e) {}
      }
    } catch (e) {
      win = null;
    }

    if (win && !win.closed) {
      markDone();
      return;
    }

    if (mode === "auto") {
      showAutoOverlay();
      return;
    }

    showBar();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      setTimeout(launch, delay);
    });
  } else {
    setTimeout(launch, delay);
  }
})();
