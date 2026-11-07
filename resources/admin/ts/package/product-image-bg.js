/**
 * Product Image Background Color Detection
 *
 * Uses Canvas to sample edge pixels of each product image and applies the
 * detected background color to its wrapper via a CSS custom property.
 *
 * Only processes wrappers that carry the attribute `data-detect-bg="1"`.
 * When an image fails to load (onerror swaps to default), the attribute is
 * removed so no detection runs for placeholder images.
 */
(function () {
    'use strict';

    // In-memory cache: image src → { left: 'rgb(…)', right: 'rgb(…)' }
    var colorCache = {};

    // Shared off-screen canvas (reused for every image)
    var canvas = null;
    var ctx = null;

    function getCanvas() {
        if (!canvas) {
            canvas = document.createElement('canvas');
            ctx = canvas.getContext('2d', { willReadFrequently: true });
        }
        return { canvas: canvas, ctx: ctx };
    }

    /**
     * Sample edge pixels and return left/right median colors.
     *
     * @param {HTMLImageElement} img - A fully-loaded <img> element
     * @returns {{ left: string, right: string }} RGB color strings
     */
    function detectEdgeColors(img) {
        var c = getCanvas();
        var w = img.naturalWidth;
        var h = img.naturalHeight;

        if (!w || !h) return null;

        // Draw at a small scale (max 80px tall) for performance
        var scale = Math.min(1, 80 / h);
        var sw = Math.round(w * scale);
        var sh = Math.round(h * scale);

        c.canvas.width = sw;
        c.canvas.height = sh;

        try {
            c.ctx.drawImage(img, 0, 0, sw, sh);
        } catch (e) {
            // SecurityError when cross-origin tainting prevents getImageData
            return null;
        }

        // Determine how many pixels to sample from each edge
        // Sample a strip ~5% of the width on each side, minimum 1px, max 4px
        var stripW = Math.max(1, Math.min(4, Math.round(sw * 0.05)));

        // Skip top/bottom ~10% to avoid corner artifacts
        var yStart = Math.max(0, Math.round(sh * 0.1));
        var yEnd = Math.max(yStart + 1, Math.round(sh * 0.9));

        var leftPixels = [];
        var rightPixels = [];

        try {
            // Left edge strip
            var leftData = c.ctx.getImageData(0, yStart, stripW, yEnd - yStart);
            for (var i = 0; i < leftData.data.length; i += 4) {
                var a = leftData.data[i + 3];
                if (a < 128) continue; // skip transparent
                leftPixels.push([leftData.data[i], leftData.data[i + 1], leftData.data[i + 2]]);
            }

            // Right edge strip
            var rightData = c.ctx.getImageData(sw - stripW, yStart, stripW, yEnd - yStart);
            for (var j = 0; j < rightData.data.length; j += 4) {
                var a2 = rightData.data[j + 3];
                if (a2 < 128) continue;
                rightPixels.push([rightData.data[j], rightData.data[j + 1], rightData.data[j + 2]]);
            }
        } catch (e) {
            // SecurityError: tainted canvas
            return null;
        }

        if (!leftPixels.length && !rightPixels.length) return null;

        var leftColor = leftPixels.length ? medianColor(leftPixels) : null;
        var rightColor = rightPixels.length ? medianColor(rightPixels) : null;

        // Fall back to whichever side we have
        if (!leftColor) leftColor = rightColor;
        if (!rightColor) rightColor = leftColor;

        return { left: leftColor, right: rightColor };
    }

    /**
     * Compute the median color from an array of [r, g, b] values.
     * Median is more robust than average against outlier product pixels.
     */
    function medianColor(pixels) {
        if (!pixels.length) return 'rgb(241,245,249)';

        var rs = [], gs = [], bs = [];
        for (var i = 0; i < pixels.length; i++) {
            rs.push(pixels[i][0]);
            gs.push(pixels[i][1]);
            bs.push(pixels[i][2]);
        }

        rs.sort(numSort);
        gs.sort(numSort);
        bs.sort(numSort);

        var mid = Math.floor(pixels.length / 2);
        return 'rgb(' + rs[mid] + ',' + gs[mid] + ',' + bs[mid] + ')';
    }

    function numSort(a, b) { return a - b; }

    /**
     * Check if two RGB color strings are "close enough" to use a single solid
     * color rather than a gradient.
     */
    function colorsAreSimilar(c1, c2) {
        if (c1 === c2) return true;
        var p1 = parseRgb(c1);
        var p2 = parseRgb(c2);
        if (!p1 || !p2) return false;
        var diff = Math.abs(p1[0] - p2[0]) + Math.abs(p1[1] - p2[1]) + Math.abs(p1[2] - p2[2]);
        return diff < 30; // threshold: combined channel difference < 30
    }

    function parseRgb(s) {
        var m = s.match(/(\d+)/g);
        return m ? [parseInt(m[0]), parseInt(m[1]), parseInt(m[2])] : null;
    }

    /**
     * Apply detected colors to a single .product-image-wrapper element.
     */
    function applyBg(wrapper, colors) {
        if (!colors) return;

        if (colorsAreSimilar(colors.left, colors.right)) {
            wrapper.style.setProperty('--product-bg', colors.left);
            wrapper.style.background = 'var(--product-bg)';
        } else {
            // Left-to-right gradient for different edge colors
            var grad = 'linear-gradient(to right, ' + colors.left + ' 0%, ' + colors.left + ' 15%, ' + colors.right + ' 85%, ' + colors.right + ' 100%)';
            wrapper.style.background = grad;
        }
    }

    /**
     * Process a single <img> inside a wrapper.
     */
    function processImage(img) {
        var wrapper = img.closest('.product-image-wrapper');
        if (!wrapper || !wrapper.hasAttribute('data-detect-bg')) return;

        var src = img.src;
        if (!src) return;

        // Check cache first
        if (colorCache[src]) {
            applyBg(wrapper, colorCache[src]);
            return;
        }

        // Detect colors
        var colors = detectEdgeColors(img);
        if (colors) {
            colorCache[src] = colors;
            applyBg(wrapper, colors);
        }
    }

    /**
     * Scan a root element for all product image wrappers and process them.
     * Handles both already-loaded and not-yet-loaded images.
     */
    function scanImages(root) {
        if (!root || !root.querySelectorAll) return;

        var wrappers = root.querySelectorAll('.product-image-wrapper[data-detect-bg]');
        for (var i = 0; i < wrappers.length; i++) {
            var img = wrappers[i].querySelector('img.product-image-main');
            if (!img) continue;

            if (img.complete && img.naturalWidth > 0) {
                processImage(img);
            } else {
                // Wait for load
                (function (imgEl) {
                    imgEl.addEventListener('load', function onLoad() {
                        imgEl.removeEventListener('load', onLoad);
                        processImage(imgEl);
                    });
                })(img);
            }
        }
    }

    // --- Initialization ---

    // Process images already in the DOM
    function init() {
        scanImages(document);
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // Already loaded (script loaded defer or at end of body)
        setTimeout(init, 0);
    }

    // Observe DOM mutations (MDI tab workspace loads content via AJAX)
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                var added = mutations[i].addedNodes;
                for (var j = 0; j < added.length; j++) {
                    var node = added[j];
                    if (node.nodeType !== 1) continue;
                    // If the added node itself is a wrapper
                    if (node.classList && node.classList.contains('product-image-wrapper') && node.hasAttribute('data-detect-bg')) {
                        var img = node.querySelector('img.product-image-main');
                        if (img) {
                            if (img.complete && img.naturalWidth > 0) {
                                processImage(img);
                            } else {
                                (function (imgEl) {
                                    imgEl.addEventListener('load', function onLoad() {
                                        imgEl.removeEventListener('load', onLoad);
                                        processImage(imgEl);
                                    });
                                })(img);
                            }
                        }
                    }
                    // Or scan descendants
                    scanImages(node);
                }
            }
        });
        observer.observe(document.body || document.documentElement, { childList: true, subtree: true });
    }

    // Expose for manual invocation (e.g., after AJAX pagination reload)
    window.$detectProductImageBg = scanImages;
})();

