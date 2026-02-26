/**
 * Pop Redirect - Frontend Script
 *
 * MODE: "bar"  — sticky top/bottom bar with a button
 * MODE: "auto" — first user click opens target tab (no overlay)
 */
(function () {
  "use strict";

  if (typeof prConfig === "undefined") return;

  var url = prConfig.url || "";
  var delay = parseInt(prConfig.delay, 10) || 0;
  var onceSession = !!prConfig.onceSession;
  var mode = prConfig.mode === "auto" ? "auto" : "bar";
  var barText = prConfig.barText || "Visit our special offer";
  var barBtnText = prConfig.barBtnText || "Open Now";
  var barPos = prConfig.barPos || "bottom"; // 'top' or 'bottom'
  var barColor = prConfig.barColor || "#1a73e8";
  var autoText = prConfig.autoText || "Click anywhere to continue";
  var doneKey = "pr_done";

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
    bar.id = "pr-bar";

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
    var bar = document.getElementById("pr-bar");
    if (bar) {
      bar.style.opacity = "0";
      bar.style.transition = "opacity .25s";
      setTimeout(function () {
        bar && bar.parentNode && bar.parentNode.removeChild(bar);
      }, 300);
    }
  }

  /* ------------------------------------------------------------------ */
  /*  MODE: AUTO — first real click handler (no hidden/click-block layer) */
  /* ------------------------------------------------------------------ */
  function setupAutoMode() {
    var hint = null;

    if (autoText) {
      hint = document.createElement("div");
      hint.id = "pr-auto-hint";
      hint.textContent = autoText;
      hint.style.cssText = [
        "position:fixed",
        "bottom:20px",
        "left:50%",
        "transform:translateX(-50%)",
        "z-index:2147483646",
        "background:rgba(0,0,0,.55)",
        "color:#fff",
        'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif',
        "font-size:13px",
        "padding:6px 16px",
        "border-radius:20px",
        "white-space:nowrap",
        "pointer-events:none",
      ].join(";");
      document.body.appendChild(hint);
    }

    var onFirstClick = function () {
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
      }

      if (hint && hint.parentNode) {
        hint.parentNode.removeChild(hint);
      }

      document.removeEventListener("click", onFirstClick, true);
    };

    document.addEventListener("click", onFirstClick, true);
  }

  /* ------------------------------------------------------------------ */
  /*  Launch after delay                                                  */
  /* ------------------------------------------------------------------ */
  function launch() {
    if (mode === "auto") {
      setupAutoMode();
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
