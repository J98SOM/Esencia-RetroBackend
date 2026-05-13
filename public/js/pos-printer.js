(function(){
    // Simple POS invoice renderer for small/thermal printers and modal print views
    // Usage: fetch('/alquiler/12/json').then(r=>r.json()).then(data => {
    //   const html = window.renderPosInvoice(data);
    //   document.getElementById('modal-body').innerHTML = html;
    // });

    function formatMoney(v){
        return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP' }).format(v ?? 0);
    }

    window.renderPosInvoice = function(invoice){
        const itemsHtml = (invoice.items || []).map(i => {
            return `<tr><td style="padding:4px 0;">${escapeHtml(i.desc)}</td><td style="text-align:right;padding-left:8px;">${i.cant}</td><td style="text-align:right;padding-left:12px;">${formatMoney(i.precio)}</td></tr>`;
        }).join('');

        const pagosHtml = (invoice.pagos || []).map(p => `<div style="display:flex;justify-content:space-between;padding-top:6px"><div>${escapeHtml(p.metodo)}</div><div>${formatMoney(p.monto)}</div></div>`).join('');

        const company = invoice.company || {};
        const logo = company.logo || '/img/logo.png';
        const compName = company.name || 'ESENCIA RETRO';
        const compNit = company.nit || '';
        const compContact = (company.tel ? company.tel + ' · ' : '') + (company.email || '');

        const html = `
        <div class="pos-invoice" style="font-family: Arial, Helvetica, sans-serif; color:#000; background:#fff; padding:12px; max-width:480px;">
            <div style="text-align:center; margin-bottom:8px;"><img src="${logo}" alt="logo" style="max-width:120px;display:block;margin:0 auto 6px"/></div>
            <div style="text-align:center; font-weight:800; margin-bottom:6px;">${escapeHtml(compName)}</div>
            <div style="text-align:center; font-size:12px; margin-bottom:6px;">${escapeHtml(compNit)} ${escapeHtml(company.city ? '· ' + company.city : '')}</div>
            <div style="font-size:12px; display:flex;justify-content:space-between;">
                <div>Factura: ${escapeHtml(invoice.numero || ('#'+(invoice.id||'')))}</div>
                <div>${escapeHtml(invoice.fecha || '')}</div>
            </div>
            <hr style="margin:8px 0;border:none;border-top:1px dashed #666" />
            <table style="width:100%;font-size:13px; border-collapse:collapse;">
                ${itemsHtml}
            </table>
            <hr style="margin:8px 0;border:none;border-top:1px dashed #666" />
            <div style="display:flex;justify-content:space-between;font-weight:700;font-size:14px;">
                <div>Total</div>
                <div>${formatMoney(invoice.totales?.total ?? invoice.total ?? 0)}</div>
            </div>
            <div style="margin-top:8px;">${pagosHtml}</div>
            <div style="margin-top:12px;font-size:12px;text-align:center;">Gracias por su compra</div>
        </div>
        `;
        return html;
    };

    function escapeHtml(unsafe){
        if (unsafe === null || unsafe === undefined) return '';
        return String(unsafe).replace(/[&<>"'`]/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;', '`':'&#96;'}[m]; });
    }
})();
