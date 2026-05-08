(function(){
    'use strict';

    function openInvoiceModal(id) {
        var modal = document.getElementById('invoice-modal');
        var body = document.getElementById('invoice-modal-body');
        var download = document.getElementById('invoice-modal-download');
        if (!modal || !body || !download) return console.warn('[Alquiler] modal elements missing');
        modal.classList.remove('hidden');
        body.innerHTML = '<div class="text-center py-12">Cargando...</div>';
        fetch('/alquiler/' + id + '/print?partial=1', { credentials: 'same-origin' })
            .then(function(r){ if (!r.ok) throw new Error('Error cargando factura'); return r.text(); })
            .then(function(html){ body.innerHTML = html; download.href = '/alquiler/' + id + '/pdf'; })
            .catch(function(err){ body.innerHTML = '<div class="text-center p-6 text-red-600">No se pudo cargar la factura.</div>'; console.error(err); });
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
        if (!body) return alert('No hay contenido para imprimir');
        var content = body.innerHTML;
        if (!content) return alert('No hay contenido para imprimir');
        var w = window.open('', '_blank', 'toolbar=0,location=0,menubar=0');
        if (!w) return alert('No se pudo abrir la ventana de impresión');
        var doc = w.document;
        doc.open();
        doc.write('<!doctype html><html><head><title>Imprimir factura</title>');
        doc.write('<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">');
        doc.write('<style>body{font-family:Arial,Helvetica,sans-serif;padding:12px;color:#111} .print-container{max-width:800px;margin:0 auto}</style>');
        doc.write('</head><body>');
        doc.write(content);
        doc.write('</body></html>');
        doc.close();
        w.focus();
        setTimeout(function(){ try { w.print(); w.close(); } catch(e) { console.error(e); } }, 300);
    }

    document.addEventListener('click', function(e){
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
