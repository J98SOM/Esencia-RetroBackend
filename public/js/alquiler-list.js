(function(){
    'use strict';

    function openInvoiceModal(id) {
        var modal = document.getElementById('invoice-modal');
        var body = document.getElementById('invoice-modal-body');
        var download = document.getElementById('invoice-modal-download');
        if (!modal || !body || !download) return console.warn('[Alquiler] modal elements missing');
        modal.classList.remove('hidden');
        body.innerHTML = '<div class="text-center py-12">Cargando...</div>';
        // First try JSON endpoint to determine tipo and allow client-side POS rendering
        fetch('/alquiler/' + id + '/json', { credentials: 'same-origin' })
            .then(function(r){ if (!r.ok) throw r; return r.json(); })
            .then(function(data){
                var tipo = (data.tipo || '').toLowerCase();
                if (tipo.includes('pos') || tipo.includes('venta') || tipo.includes('caja')) {
                    // Use client-side POS renderer if available
                    if (window.renderPosInvoice && typeof window.renderPosInvoice === 'function') {
                        try {
                            body.innerHTML = window.renderPosInvoice(data);
                            // point download/print to server print view so printed export matches Caja template
                            download.href = '/alquiler/' + id + '/print?autoprint=1';
                            return;
                        } catch (e) {
                            console.error('POS renderer failed, falling back to server partial', e);
                        }
                    }
                }
                // Fallback: fetch server-rendered partial HTML
                return fetch('/alquiler/' + id + '/print?partial=1', { credentials: 'same-origin' })
                    .then(function(r){ if (!r.ok) throw new Error('Error cargando factura'); return r.text(); })
                    .then(function(html){ body.innerHTML = html; download.href = '/alquiler/' + id + '/print?autoprint=1'; });
            })
            .catch(function(err){
                // If JSON failed, fallback to server partial as before
                console.warn('JSON load failed, fetching partial', err);
                fetch('/alquiler/' + id + '/print?partial=1', { credentials: 'same-origin' })
                    .then(function(r){ if (!r.ok) throw new Error('Error cargando factura'); return r.text(); })
                    .then(function(html){ body.innerHTML = html; download.href = '/alquiler/' + id + '/print?autoprint=1'; })
                    .catch(function(err2){ body.innerHTML = '<div class="text-center p-6 text-red-600">No se pudo cargar la factura.</div>'; console.error(err2); });
            });
    }

    function closeInvoiceModal() {
        var modal = document.getElementById('invoice-modal');
        var body = document.getElementById('invoice-modal-body');
        if (!modal) return;
        modal.classList.add('hidden');
        if (body) body.innerHTML = '';
    }

    function printInvoiceModal() {
        var body = document.getElementById('invoice-modal-body');
        var download = document.getElementById('invoice-modal-download');
        if (!body) return alert('No hay contenido para imprimir');
        var content = body.innerHTML;
        if (!content) return alert('No hay contenido para imprimir');
        // If modal's download link points to server print view, open that to ensure printed output matches Caja template
        try {
            if (download && download.href && download.href.indexOf('/print') !== -1) {
                window.open(download.href, '_blank');
                return;
            }
        } catch(e) {}

        var w = window.open('', '_blank', 'toolbar=0,location=0,menubar=0');
        if (!w) return alert('No se pudo abrir la ventana de impresión');
        var doc = w.document;
        // Build a full document that includes the same stylesheet and style tags from the parent
        var headHtml = '';
        try {
            var nodes = document.querySelectorAll('link[rel="stylesheet"], style');
            nodes.forEach(function(n){ headHtml += n.outerHTML; });
        } catch(e) {
            // ignore, fallback to minimal style
        }

        doc.open();
        doc.write('<!doctype html><html><head><title>Imprimir factura</title>');
        doc.write('<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">');
        // include collected head styles (links and inline styles)
        if (headHtml && headHtml.length) {
            doc.write(headHtml);
        } else {
            doc.write('<style>body{font-family:Arial,Helvetica,sans-serif;padding:12px;color:#111} .print-container{max-width:800px;margin:0 auto}</style>');
        }
        doc.write('</head><body>');
        doc.write(content);
        doc.write('</body></html>');
        doc.close();
        w.focus();
        setTimeout(function(){ try { w.print(); w.close(); } catch(e) { console.error(e); } }, 300);
    }

    document.addEventListener('click', function(e){
        // Intercept clicks on print links in list to use JS POS renderer when available
        var listPrint = e.target.closest && e.target.closest('.js-new-print');
        if (listPrint) {
            e.preventDefault();
            var href = listPrint.href || listPrint.getAttribute('href');
            // For printing, always open the server-side print view so it uses the caja.print template (matches Caja view exactly)
            window.open(href, '_blank');
            return;
        }
        var openBtn = e.target.closest && e.target.closest('.js-open-invoice');
        if (openBtn){
            var id = openBtn.dataset ? openBtn.dataset.id : openBtn.getAttribute('data-id');
            console.debug('[Alquiler] openInvoice click detected, id=', id, openBtn);
            if (!id) console.warn('[Alquiler] openInvoice: no data-id found');
            openInvoiceModal(id);
            return;
        }
        var closeBtn = e.target.closest && e.target.closest('.js-close-invoice');
        if (closeBtn){ console.debug('[Alquiler] closeInvoice click detected', closeBtn); closeInvoiceModal(); return; }
        // overlay click: if clicked directly on the overlay (id=invoice-modal) close
        if (e.target && e.target.id === 'invoice-modal') { console.debug('[Alquiler] overlay click detected'); closeInvoiceModal(); return; }
        var printBtn = e.target.closest && e.target.closest('.js-print-invoice');
        if (printBtn){ console.debug('[Alquiler] printInvoice click detected', printBtn); printInvoiceModal(); return; }
    });

    // expose for debugging if needed
    window.__alquiler = { openInvoiceModal: openInvoiceModal, closeInvoiceModal: closeInvoiceModal, printInvoiceModal: printInvoiceModal };
})();
