/* =============================================================
   MAXES NAVIGATION — drawers/vendor/script.js
   Module: Vendor Explorer (Using Engine v4.0 - Server Search)
   ============================================================= */

var MaxesVendor = (function ($, Core) {

    // ── 1. Card Renderer (Tab List) ───────────────────────────
    function _renderVendorCard(v) {
        var sel = v.vendorcode === Core.state.selectedId ? ' max-selected' : '';
        // Inisial dari nama vendor (misal: "PT Sukses Makmur" -> "PS")
        var initials = v.name ? v.name.split(' ').map(function(n){ return n[0]; }).join('').substring(0,2).toUpperCase() : 'VN';
        
        return '<div class="max-list-record ' + sel + '" data-id="' + v.vendorcode + '">' +
                Core.avatar(initials, 'max-avatar-teal', 'sm') +
                '<div class="max-record-info">' +
                    '<div class="max-record-id">' + v.name + '</div>' +
                    '<div class="max-record-desc">' + v.vendorcode + '</div>' +
                    '<div class="max-record-loc">📍 ' + (v.city || '—') + (v.stateprovince ? ', ' + v.stateprovince : '') + '</div>' +
                '</div>' +
                '<div class="max-record-meta">' +
                    (v.website ? '<span class="max-badge max-badge-neutral">🌐 Web</span>' : '') +
                    Core.selectBtn(v.vendorcode, 'sm') +
                '</div>' +
            '</div>';
    }

    // ── 2. Detail Renderer (Tab Detail) ───────────────────────
    function _renderVendorDetail(res) {
        var h = res.header;
        var contacts = res.contacts || [];
        var services = res.services ||[];
        var items = res.items ||[];

        // Generate Avatar Initials
        var initials = h.name ? h.name.split(' ').map(function(n){ return n[0]; }).join('').substring(0,2).toUpperCase() : h.vendorcode.substring(0,2).toUpperCase();

        // --- HEADER ---
        var html = '<button class="max-detail-back"> &#8592; Back to list</button>' +
            '<div class="max-detail-header">' +
                Core.avatar(initials, 'max-avatar-teal', 'lg') +
                '<div style="flex:1;min-width:0;">' +
                    '<div class="max-detail-title">' + h.name + '</div>' +
                    '<div class="max-detail-subtitle">' + h.vendorcode + '</div>' +
                    '<div class="max-detail-badges">' +
                        (h.website ? '<a href="http://'+h.website.replace('http://','').replace('https://','')+'" target="_blank" class="max-badge max-badge-active" style="text-decoration:none;">🌐 ' + h.website + '</a>' : '') +
                    '</div>' +
                '</div>' +
            '</div>' 
            
            html += '<div style="display:flex; gap:10px; margin: 15px 0; width: 100%;">' +
            '<div style="flex:1;">' + 
                Core.selectBtn(h.itemnum, 'md') + 
            '</div>' +
            Core.gotoAppBtn(res.appUrl, res.applinksownernum) +
        '</div>';


        // --- SECTION: GENERAL INFO ---
        html += '<div class="max-section-label">Company Information</div>' +
            '<div class="max-field-grid">' +
                '<div class="max-field-item max-field-full"><div class="max-field-label">Head Office Address</div><div class="max-field-value">' + (h.address || '—') + '</div></div>' +
                '<div class="max-field-item"><div class="max-field-label">City</div><div class="max-field-value">' + (h.city || '—') + '</div></div>' +
                '<div class="max-field-item"><div class="max-field-label">State / Province</div><div class="max-field-value">' + (h.stateprovince || '—') + '</div></div>' +
                '<div class="max-field-item"><div class="max-field-label">Postal Code</div><div class="max-field-value max-mono">' + (h.zippostalcode || '—') + '</div></div>' +
                '<div class="max-field-item"><div class="max-field-label">Fax Number</div><div class="max-field-value">' + (h.vendorfax || '—') + '</div></div>' +
            '</div>';

        // --- SECTION: FINANCIAL / BANK INFO ---
        if (h.bankname1 || h.bankaccount1) {
            html += '<div class="max-section-label" style="margin-top:20px;">Financial Details</div>' +
                '<div class="max-bank-card">' +
                    '<div class="max-bank-title">💳 Primary Bank Account</div>' +
                    '<div class="max-bank-grid">' +
                        '<div class="max-bank-item"><span class="max-bank-label">Bank Name</span><span class="max-bank-value">' + (h.bankname1 || '—') + '</span></div>' +
                        '<div class="max-bank-item"><span class="max-bank-label">Account No.</span><span class="max-bank-value">' + (h.bankaccount1 || '—') + '</span></div>' +
                        '<div class="max-bank-item"><span class="max-bank-label">A/C Holder Name</span><span class="max-bank-value" style="font-family:inherit;">' + (h.accountholder || '—') + '</span></div>' +
                        '<div class="max-bank-item"><span class="max-bank-label">Bank Branch/Address</span><span class="max-bank-value" style="font-family:inherit; font-weight:500;">' + (h.bankaddress || '—') + '</span></div>' +
                    '</div>' +
                '</div>';
        }

        // --- SECTION: CONTACT PERSONS ---
        // --- SECTION: CONTACT PERSONS ---
        html += '<div class="max-section-label" style="margin-top:20px;">Contact Persons</div>';
        
        if (contacts.length > 0) {
            html += '<div class="max-contact-grid">';
            
            // NAH, DI SINI DEKLARASI 'c'-NYA BRO (Sebagai parameter setiap item kontak)
            contacts.forEach(function(c) {
                
                var cInitial = c.contact ? c.contact.substring(0,1).toUpperCase() : '👤';
                
                // 1. Logic WhatsApp (Phone)
                var rawPhone = c.phone || c.officephone || '';
                var phoneHtml = '<span style="color:var(--max-text-hint);">📞 No Phone</span>';
                
                if (rawPhone) {
                    // Bersihkan karakter non-angka (spasi, strip, kurung)
                    var cleanPhone = rawPhone.replace(/\D/g, '');
                    
                    // Validasi panjang nomor
                    if (cleanPhone.length > 5) {
                        // Ubah 0 awalan jadi 62
                        if (cleanPhone.startsWith("0")) {
                            cleanPhone = "62" + cleanPhone.slice(1);
                        }
                        
                        var waLink = 'https://wa.me/' + cleanPhone;
                        phoneHtml = '<a href="' + waLink + '" target="_blank" class="max-contact-link" title="Chat via WhatsApp">📞 ' + rawPhone + '</a>';
                    } else {
                        // Teks biasa kalau format aneh (misal: "Ext 123")
                        phoneHtml = '<span style="color:var(--max-text-main);" title="Invalid phone format">📞 ' + rawPhone + '</span>';
                    }
                }

                // 2. Logic Email
                var rawEmail = c.email || '';
                var emailHtml = '<span style="color:var(--max-text-hint);">✉️ No Email</span>';
                
                if (rawEmail) {
                    var mailtoLink = 'mailto:' + rawEmail;
                    emailHtml = '<a href="' + mailtoLink + '" class="max-contact-link" title="Send Email">✉️ ' + rawEmail + '</a>';
                }

                // 3. Gabungkan ke dalam Card HTML
                html += '<div class="max-contact-card">' +
                            '<div class="max-contact-avatar">' + cInitial + '</div>' +
                            '<div class="max-contact-info">' +
                                '<div class="max-contact-name">' + (c.contact || 'No Name') + '</div>' +
                                '<div class="max-contact-pos">' + (c.position || 'Staff') + '</div>' +
                                '<div class="max-contact-detail" style="margin-top:4px;">' + phoneHtml + '</div>' +
                                '<div class="max-contact-detail" style="margin-top:2px;">' + emailHtml + '</div>' +
                            '</div>' +
                        '</div>';
            }); // <-- Batas akhir loop 'c'
            
            html += '</div>';
        } else {
            html += '<div style="font-size: 10pt; color:var(--max-text-hint);">No contact persons registered.</div>';
        }

        // --- SECTION: SERVICE PERFORMANCE (VARIANCE) ---
        html += '<div class="max-section-label" style="margin-top:25px;">Service Supply Performance</div>';
        if (services.length > 0) {
            html += '<div class="max-history-container">';
            services.forEach(function(s) {
                var stdCost = parseFloat(s.stdcost) || 0;
                var avgCost = parseFloat(s.avg_actualcost) || 0;
                var variance = parseFloat(s.varianceunitcost) || 0;
                
                // LOGIC VARIANCE COLOR (Finance Perspective)
                // Variance Negatif = Lebih murah = GOOD (Hijau)
                // Variance Positif = Lebih mahal = BAD (Merah)
                var varClass = 'max-variance-neutral';
                var varIcon = '⏸️';
                var varText = 'On Budget';

                if (variance < -0.01) { // -0.01 untuk toleransi floating point
                    varClass = 'max-variance-good';
                    varIcon = '📉';
                    varText = 'Saved ' + Core.formatCurrency(Math.abs(variance));
                } else if (variance > 0.01) {
                    varClass = 'max-variance-bad';
                    varIcon = '📈';
                    varText = 'Overspend ' + Core.formatCurrency(variance);
                }

                html += '<div class="max-history-card">' +
                            '<div class="max-history-header">' +
                                '<div>' +
                                    '<span class="max-history-id">' + s.serviceitemnum + '</span>' +
                                    '<div class="max-history-desc">' + (s.description || 'Unknown Service') + '</div>' +
                                '</div>' +
                                '<div class="max-variance-pill ' + varClass + '" title="Variance vs Standard Cost">' + varIcon + ' ' + varText + '</div>' +
                            '</div>' +
                            
                            '<div class="max-compare-grid">' +
                                '<div class="max-compare-item">' +
                                    '<span class="max-compare-label">Standard Cost</span>' +
                                    '<span class="max-compare-val" style="color:var(--max-text-muted);">' + Core.formatCurrency(stdCost) + '</span>' +
                                '</div>' +
                                '<div class="max-compare-item align-right">' +
                                    '<span class="max-compare-label">Avg Actual Cost</span>' +
                                    '<span class="max-compare-val">' + Core.formatCurrency(avgCost) + '</span>' +
                                '</div>' +
                            '</div>' +
                        '</div>';
            });
            html += '</div>';
        } else {
            html += '<div style="font-size: 10pt; color:var(--max-text-hint);">No historical service data.</div>';
        }

        // --- SECTION: ITEM SUPPLY HISTORY ---
        html += '<div class="max-section-label" style="margin-top:25px;">Item Supply History</div>';
        if (items.length > 0) {
            html += '<div class="max-history-container">';
            // Batasi tampilan hanya 10 item terbaru/teratas agar tidak terlalu panjang
            items.slice(0, 10).forEach(function(i) {
                var avgCost = parseFloat(i.avg_unitcost) || 0;
                
                html += '<div class="max-history-card" style="padding:10px 12px;">' +
                            '<div style="display:flex; justify-content:space-between; align-items:center;">' +
                                '<div style="flex:1; min-width:0; padding-right:10px;">' +
                                    '<div class="max-history-id" style="font-size: 10pt;">' + i.itemnum + '</div>' +
                                    '<div class="max-history-desc" style="margin-top:2px; font-size: 10pt; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">' + (i.description || 'Unknown Item') + '</div>' +
                                '</div>' +
                                '<div style="text-align:right; flex-shrink:0;">' +
                                    '<div style="font-size: 10pt; color:var(--max-text-hint); text-transform:uppercase; font-weight:700; margin-bottom:2px;">Avg Price</div>' +
                                    '<div style="font-size: 11pt; font-weight:700; color:var(--max-success);">' + Core.formatCurrency(avgCost) + '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>';
            });
            html += '</div>';
        } else {
            html += '<div style="font-size: 10pt; color:var(--max-text-hint);">No historical item data.</div>';
        }

        html += '<div style="height:40px;"></div>';
        $('#max-detail-content').html(html);
        Core.refreshSelectButtons();
    }

    // ── 3. Public API (ENGINE v4.0 READY) ─────────────────────
    return {
        init: function () {
            var self = this;
            Core.setup({
                name: 'Vendor',
                onSearchFn: function(q) { self.fetchList(q); },
                renderCardFn: _renderVendorCard
            });

            this._bindEvents();
        },

        _bindEvents: function () {
            var self = this;
            $(document).off('click', '.max-list-record').on('click', '.max-list-record', function (e) {
                if ($(e.target).closest('.max-select-btn').length) return;
                var id = $(this).attr('data-id');
                if (id) self.select(id);
            });

            $(document).off('input', '#max-vendor-search').on('input', '#max-vendor-search', function () {
                Core.serverSearch($(this).val());
            });
        },

        fetchList: function (searchQuery) {
            Core.showLoading('#max-records-container');
            
            var payload = { 
                getDrawerVendorData: { 
                    action: 'list', 
                    search: searchQuery || ''
                } 
            };

            Core.request(payload, function (data) {
                Core.setListData(data);
            });
        },

        select: function (vendorcode) {
            Core.state.selectedId = vendorcode;
            Core.switchToTab('tab-detail');
            Core.showLoading('#max-detail-content');

            Core.request({ getDrawerVendorData: { action: 'detail', vendorcode: vendorcode } }, function (data) {
                _renderVendorDetail(data);
                Core.renderList(); 
            });
        },

        loadByValue: function (val) {
            $('#max-vendor-search').val(val || '');
            Core.switchToTab('tab-list');
            this.fetchList(val || '');
        }
    };

}(jQuery, window.MaxesDrawerCore));