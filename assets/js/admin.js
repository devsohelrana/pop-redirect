(function () {
  "use strict";

  function getSelectedMode() {
    var checked = document.querySelector(
      'input[name="aot_settings[mode]"]:checked',
    );
    return checked ? checked.value : "bar";
  }

  function toggleModeRows() {
    var mode = getSelectedMode();
    var rows = document.querySelectorAll("tr.aot-mode-row");

    rows.forEach(function (row) {
      var isBarRow = row.classList.contains("aot-mode-bar");
      var isAutoRow = row.classList.contains("aot-mode-auto");
      var shouldShow =
        (mode === "bar" && isBarRow) || (mode === "auto" && isAutoRow);

      row.classList.toggle("aot-hidden", !shouldShow);
    });
  }

  function initModeToggle() {
    var modeInputs = document.querySelectorAll(
      'input[name="aot_settings[mode]"]',
    );
    if (!modeInputs.length) return;

    modeInputs.forEach(function (input) {
      input.addEventListener("change", toggleModeRows);
    });

    toggleModeRows();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initModeToggle);
  } else {
    initModeToggle();
  }
})();
