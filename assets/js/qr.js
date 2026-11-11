/* Renders a QR code into every element with a data-qr attribute (used on the event pass). */
(function () {
  'use strict';

  function renderQRCodes(root) {
    if (typeof qrcode === 'undefined') return;
    var nodes = (root || document).querySelectorAll('[data-qr]:not([data-qr-done])');
    Array.prototype.forEach.call(nodes, function (el) {
      var qr = qrcode(0, 'M');
      qr.addData(el.getAttribute('data-qr'));
      qr.make();
      el.innerHTML = qr.createSvgTag({ cellSize: 4, margin: 0, scalable: true, alt: 'QR code' });
      el.setAttribute('data-qr-done', '1');
    });
  }

  window.renderQRCodes = renderQRCodes;
  document.addEventListener('DOMContentLoaded', function () { renderQRCodes(); });
})();
