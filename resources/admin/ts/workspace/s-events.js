/**
 * POS Enterprise MDI - s-event & s-mask handler
 * Enhanced replacement for s-event.js and s-mask.js with full MDI tab support.
 */

const PREFIX = "s";
const EVENT = [
  "click",
  "mouseover",
  "mouseout",
  "mousedown",
  "mouseup",
  "mousemove",
  "focus",
  "Keydown",
  "Keyup",
];
const TYPE = ["fn", "link", "open"];
let FULL_EVENT_ATTRIBUTES = [];
EVENT.forEach((event) => {
  TYPE.forEach((type) => {
    FULL_EVENT_ATTRIBUTES.push(`${PREFIX}-${event}-${type}`);
  });
});

export function initSEvents(container = document) {
  if (!container) return;
  const elements = container.querySelectorAll ? container.querySelectorAll("*") : [];
  
  elements.forEach((item) => {
    if (item._s_event_initialized) return;

    const attrNames = item.getAttributeNames ? item.getAttributeNames() : [];
    attrNames.forEach((attr) => {
      if (FULL_EVENT_ATTRIBUTES.includes(attr)) {
        item._s_event_initialized = true;
        const [prefix, event, type] = attr.split("-");
        const value = item.getAttribute(attr);

        item.addEventListener(event, (e) => {
          switch (type) {
            case "fn":
              if (value) {
                try {
                  Function(value)();
                } catch (err) {
                  console.error("s-event fn error:", err);
                }
              }
              break;

            case "link":
              if (value && value !== "#" && !value.startsWith("javascript:")) {
                // If MDI is active, route internal admin links into MDI tabs!
                try {
                  const url = new URL(value, window.location.origin);
                  if (
                    window.MDI &&
                    url.origin === window.location.origin &&
                    url.pathname.startsWith("/admin") &&
                    !url.pathname.includes("logout") &&
                    !url.pathname.includes("auth")
                  ) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Check if it's an export / download / destructive action
                    const lowerVal = value.toLowerCase();
                    if (
                      lowerVal.includes("/export") ||
                      lowerVal.includes("/download") ||
                      lowerVal.includes("/print") ||
                      lowerVal.endsWith(".xlsx") ||
                      lowerVal.endsWith(".pdf") ||
                      lowerVal.endsWith(".csv") ||
                      lowerVal.includes("delete") ||
                      lowerVal.includes("destroy") ||
                      item.classList.contains("delete") ||
                      item.classList.contains("text-danger")
                    ) {
                      window.location.href = value;
                      return;
                    }

                    const targetPath = url.pathname + url.search;
                    const currentTab = window.MDI.tabs.find(
                      (t) => t.key === window.MDI.activeTabKey
                    );

                    // If reload button on current active tab
                    if (
                      currentTab &&
                      (url.href === window.location.href ||
                        currentTab.url === targetPath)
                    ) {
                      window.MDI.refreshTab(window.MDI.activeTabKey);
                      return;
                    }

                    // Extract title
                    let title =
                      item.getAttribute("title") || item.innerText?.trim() || "";
                    if (!title) {
                      if (
                        item.classList.contains("head-icon") ||
                        item.querySelector('[data-feather="arrow-left"]') ||
                        item.getAttribute("data-feather") === "arrow-left"
                      ) {
                        title = "Back";
                      }
                    }

                    // Extract icon
                    let icon = "bx bx-file";
                    if (
                      item.classList.contains("btn-create") ||
                      lowerVal.includes("/create")
                    ) {
                      icon = "bx bx-plus-circle";
                    } else if (lowerVal.includes("/list")) {
                      icon = "bx bx-list-ul";
                    }

                    window.MDI.openUrlInTab(targetPath, title, icon);
                    return;
                  }
                } catch (err) {}

                // Default external or non-admin behavior
                window.location.href = value;
              }
              break;

            case "open":
              if (value) {
                window.open(value, "_blank");
              }
              break;
          }
        });
        // NOTE: We do NOT remove the attribute so inspection and delegation work reliably
      }
    });
  });
}

export function initSMask(container = document) {
  if (!container) return;
  const elements = container.querySelectorAll
    ? container.querySelectorAll("[s-mask]")
    : [];

  const maskConvert = (value, arg) => {
    if (value && value.length > 0 && isFinite(value)) {
      let mask = value.toString();
      let mask_result = "";
      let mask_index = 0;
      let arg_mask = arg.match(/#/gm);
      let mask_symbol =
        mask.length >= (arg_mask ? arg_mask.length : 0)
          ? mask.length
          : arg_mask.length;
      for (let i = 0; i < mask_symbol; i++) {
        if (arg[i]?.toLowerCase() === "#") {
          if (mask[i - mask_index]) {
            mask_result += mask[i - mask_index];
          }
        } else if (arg[i]) {
          mask_symbol++;
          mask_index++;
          mask_result += arg[i];
        }
      }
      return mask_result;
    }
    return value;
  };

  elements.forEach((el) => {
    const value = el.innerHTML;
    const mask = el.getAttribute("s-mask");
    if (mask) {
      el.innerHTML = maskConvert(value, mask);
    }
  });
}
