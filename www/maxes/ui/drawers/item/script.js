/* =============================================================
   MAXES NAVIGATION — drawers/item/script.js
   ============================================================= */

var MaxesItem = (function ($, Core) {

    function _renderItemCard(i) {
        var sel = i.itemnum === Core.state.selectedId ? ' max-selected' : '';
        var initials = i.itemnum ? i.itemnum.substring(0, 2).toUpperCase() : 'IT';
        
        return '<div class="max-list-record ' + sel + '" data-id="' + i.itemnum + '">' +
                Core.avatar(initials, 'max-avatar-blue', 'sm') +
                '<div class="max-record-info">' +
                    '<div class="max-record-id">' + i.itemnum + '</div>' +
                    '<div class="max-record-desc">' + (i.description || 'No description') + '</div>' +
                    '<div class="max-record-loc">' + (i.brand || 'No Brand') + ' | ' + (i.manufacturer || '—') + '</div>' +
                '</div>' +
                '<div class="max-record-meta">' +
                    '<span class="max-badge max-badge-neutral">' + (i.stocktype || 'NON-STOCK') + '</span>' +
                    Core.selectBtn(i.itemnum, 'sm') +
                '</div>' +
            '</div>';
    }

    function _renderItemDetail(res) {
        var h = res.header;
        var stock = res.stock || [];
        var vendors = res.vendors || [];
        var assembly = res.assembly || [];
        var classifications = res.classifications || [];
        var production = res.production || []; 
        
        function getRankClass(val) {
            if (val === 'A' || val === '1') return 'rank-a';
            if (val === 'B' || val === '2') return 'rank-b';
            return 'rank-c';
        }

        var html = '<button class="max-detail-back"> &#8592; Back to list</button>' +
            '<div class="max-detail-header">' +
                Core.avatar(h.itemnum.substring(0,2), 'max-avatar-blue', 'lg') +
                '<div style="flex:1;min-width:0;">' +
                    '<div class="max-detail-title">' + h.itemnum + '</div>' +
                    '<div class="max-detail-subtitle">' + (h.description || '') + '</div>' +
                    '<div class="max-badge max-badge-neutral">' + h.itemgroup + '</div>' +
                '</div>' +
            '</div>'
            
           
            html += '<div style="display:flex; gap:10px; margin: 15px 0; width: 100%;">' +
            '<div style="flex:1;">' + 
                Core.selectBtn(h.itemnum, 'md') + 
            '</div>' +
            Core.gotoAppBtn(res.appUrl, res.applinksownernum) +
        '</div>';
       
        // --- SECTION: ABC RANKING (Hanya tampil jika tidak null) ---
        if (h.usageranking || h.criticalityranking || h.marketrarityranking) {
            html += '<div class="max-section-label">Inventory Analytics (ABC)</div>' +
                '<div class="max-rank-container">' +
                    '<div class="max-rank-box"><span class="max-rank-label">Usage</span><span class="max-rank-value">' + (h.usageranking || 'N/A') + '</span></div>' +
                    '<div class="max-rank-box"><span class="max-rank-label">Critical</span><span class="max-rank-value">' + (h.criticalityranking || 'N/A') + '</span></div>' +
                    '<div class="max-rank-box"><span class="max-rank-label">Rarity</span><span class="max-rank-value">' + (h.marketrarityranking || 'N/A') + '</span></div>' +
                '</div>';
        }

        // --- SECTION: LOGISTICS & ATTRIBUTES ---
        html += '<div class="max-section-label">Logistics & Attributes</div>' +
            '<div class="max-field-grid">' +
                '<div class="max-field-item"><div class="max-field-label">Manufacturer</div><div class="max-field-value">' + (h.manufacturer || '—') + '</div></div>' +
                '<div class="max-field-item"><div class="max-field-label">Brand</div><div class="max-field-value">' + (h.brand || '—') + '</div></div>' +
                '<div class="max-field-item"><div class="max-field-label">Item Group</div><div class="max-field-value">' + (h.itemgroup || '—') + '</div></div>' +
                '<div class="max-field-item"><div class="max-field-label">Stock Type</div><div class="max-field-value">' + (h.stocktype || '—') + '</div></div>' +
            '</div>';

        // Unit Conversion Card
        html += '<div class="max-unit-card">' +
                '<div class="max-unit-item"><span class="max-unit-label">Issue Unit</span><span class="max-unit-value">' + h.issueunit + '</span></div>' +
                '<div class="max-unit-item"><span class="max-unit-label">Order Unit</span><span class="max-unit-value">' + h.orderunit + '</span></div>' +
            '</div>';

        // Staff Notes Memo
        if (h.notes) {
            html += '<div class="max-staff-notes"><b>Staff Notice:</b><br>' + h.notes + '</div>';
        }


        // --- SECTION: TECHNICAL CLASSIFICATION (Grid Style) ---
        html += '<div class="max-section-label">Technical Specification</div>';
        if (classifications.length > 0) {
            html += '<div class="max-field-grid">';
            classifications.forEach(function(c) {
                html += '<div class="max-field-item">' +
                            '<div class="max-field-label">' + c.attributename + '</div>' +
                            '<div class="max-field-value" style="color:var(--max-primary)">' + c.value + ' <small>' + (c.unit || '') + '</small></div>' +
                        '</div>';
            });
            html += '</div>';
        } else {
            html += '<div class="max-no-result" style="padding:10px 0;">No technical specs defined.</div>';
        }

        // =============================================================
        // SECTION: ITEM ASSEMBLY STRUCTURE (BOM)
        // =============================================================
        html += '<div class="max-section-label">Item Assembly Structure (BOM)</div>';
        
        if (assembly && assembly.length > 0) {
            html += '<div class="max-assembly-container">';
            
            assembly.forEach(function(item) {
                var qty = parseFloat(item.quantity) || 0;
                var uCost = parseFloat(item.unitcost) || 0;
                var lCost = parseFloat(item.linecost) || 0;

                html += '<div class="max-assembly-item">' +
                            '<div class="max-assembly-info">' +
                                '<div style="display:flex; align-items:center;">' +
                                    '<span class="max-assembly-tree-icon">└─</span>' +
                                    '<span class="max-assembly-id">' + item.subitemnum + '</span>' +
                                '</div>' +
                                '<div class="max-assembly-qty-tag" style="margin-left:22px;">' +
                                    'Required: ' + qty.toLocaleString() + ' ' + (h.issueunit || 'pcs') +
                                '</div>' +
                            '</div>' +

                            '<div class="max-assembly-finance">' +
                                '<span class="max-assembly-linecost">' + Core.formatCurrency(lCost) + '</span>' +
                                '<span class="max-assembly-unitcost">@' + Core.formatCurrency(uCost) + '</span>' +
                            '</div>' +
                        '</div>';
            });
            
            html += '</div>';
            
            // Info ringkasan assembly
            var totalAssemblyCost = assembly.reduce(function(sum, item) { 
                return sum + (parseFloat(item.linecost) || 0); 
            }, 0);
            
            html += '<div style="text-align:right; padding:8px 5px; color:var(--max-text-muted);">' +
                        'Estimated Assembly Cost: <b>' + Core.formatCurrency(totalAssemblyCost) + '</b>' +
                    '</div>';
        } else {
            html += '<div class="max-no-result" style="padding:10px 0;">This item has no sub-assembly components.</div>';
        }

        // SECTION: WAREHOUSE STOCK & BATCH ANALYSIS (REWRITTEN)
        // =============================================================
        html += '<div class="max-section-label">Warehouse Stock & Batch Analysis</div>';
        
        if (stock && stock.length > 0) {
            html += '<div class="max-stock-container">';
            
            stock.forEach(function(s) {
                // 1. Parsing Data Numerik
                var qty     = parseFloat(s.quantity) || 0;
                var safety  = parseFloat(s.safetystock) || 0;
                var reorder = parseFloat(s.reorderqty) || 0;
                var uCost   = parseFloat(s.unitcost) || 0;
                var lCost   = parseFloat(s.linecost) || 0;
                
                // 2. Tentukan Status Kesehatan Stok (Visual Indicator)
                var healthColor = '#0f6e56'; // Default: Safe (Hijau)
                var statusText  = 'HEALTHY';
                
                if (qty <= 0) {
                    healthColor = '#db3737'; // Out of Stock (Merah)
                    statusText  = 'OUT OF STOCK';
                } else if (qty <= safety) {
                    healthColor = '#db3737'; // Critical (Merah)
                    statusText  = 'CRITICAL';
                } else if (qty <= (safety + reorder)) {
                    healthColor = '#d48806'; // Warning (Oranye)
                    statusText  = 'REORDER POINT';
                }

                // 3. Kalkulasi Persentase Progress Bar (Max 100%)
                var barPercent = (qty / (safety + reorder + 1)) * 100;
                if (barPercent > 100) barPercent = 100;

                // 4. Mulai Build HTML Card
                html += '<div class="max-stock-item">';
                
                // Badge Tipe Pengeluaran (FIFO/LIFO)
                html += '<div class="max-stock-type-tag">' + (s.issuetype || 'FIFO') + '</div>';
                
                // Header: Lokasi & Rack
                html += '<div class="max-stock-wh-header">' +
                            '<span class="max-stock-wh-name">📍 ' + s.warehouse + '</span>' +
                            '<span class="max-stock-rack">' + (s.racknum || 'UNASSIGNED') + '</span>' +
                        '</div>';

                // Grid Utama: Qty & Financials
                html += '<div class="max-stock-main-grid">' +
                            '<div class="max-qty-display">' +
                                '<div class="max-qty-value">' + qty.toLocaleString() + '<span class="max-qty-unit">' + (h.issueunit || '') + '</span></div>' +
                                '<div style="font-weight:700; color:' + healthColor + '; margin-top:4px;">● ' + statusText + '</div>' +
                                '<div class="max-stock-bar-bg">' +
                                    '<div class="max-stock-bar-fill" style="width:' + barPercent + '%; background:' + healthColor + ';"></div>' +
                                '</div>' +
                            '</div>' +

                            '<div class="max-stock-finance">' +
                                '<span class="max-stat-label">Batch Value</span>' +
                                '<div class="max-stat-value" style="color:var(--max-success);">' + Core.formatCurrency(lCost) + '</div>' +
                                '<div style="color:var(--max-text-hint);">@' + Core.formatCurrency(uCost) + '</div>' +
                            '</div>' +
                        '</div>';

                // Row: Supply Thresholds (Safety & Reorder)
                html += '<div class="max-stock-supply-row">' +
                            '<div class="max-supply-pill">' +
                                '<span class="max-supply-label">Safety Stock</span>' +
                                '<span class="max-supply-value">' + safety.toLocaleString() + '</span>' +
                            '</div>' +
                            '<div class="max-supply-pill">' +
                                '<span class="max-supply-label">Reorder Qty</span>' +
                                '<span class="max-supply-value">' + reorder.toLocaleString() + '</span>' +
                            '</div>' +
                            '<div class="max-supply-pill">' +
                                '<span class="max-supply-label">Condition</span>' +
                                '<span class="max-supply-value">' + (s.conditiontype || 'NEW') + '</span>' +
                            '</div>' +
                        '</div>';

                // Section: Expiry Alert (Jika ada expdate)
                if (s.expdate) {
                    var expDateObj = new Date(s.expdate);
                    var todayObj   = new Date();
                    var diffDays   = Math.ceil((expDateObj - todayObj) / (1000 * 60 * 60 * 24));
                    
                    var expClass = (diffDays < 30) ? '' : 'safe';
                    var expIcon  = (diffDays < 30) ? '⚠️' : '✅';
                    var dayLabel = (diffDays > 0) ? diffDays + ' days left' : 'EXPIRED';

                    html += '<div class="max-expiry-alert ' + expClass + '">' +
                                '<span>' + expIcon + '</span>' +
                                'Expires: ' + Core.formatDate(s.expdate) + ' (' + dayLabel + ')' +
                            '</div>';
                }

                // Footer: Batch ID & Cost Date
                html += '<div class="max-stock-batch-info">' +
                            '<span><b>Batch:</b> ' + (s.batchnum || '—') + '</span>' +
                            '<span><b>Cost Date:</b> ' + Core.formatDate(s.costdate) + '</span>' +
                        '</div>';

                html += '</div>'; // End max-stock-item
            });
            
            html += '</div>'; // End max-stock-container
        } else {
            html += '<div class="max-no-result" style="color:var(--max-error)">🚨 NO PHYSICAL STOCK IN WAREHOUSE</div>';
        }

        // --- SECTION: PURCHASING HISTORY ---
       html += '<div class="max-section-label">Purchasing History & Vendor Performance</div>';
        
        if (vendors && vendors.length > 0) {
            html += '<div class="max-vendor-container">';
            
            vendors.forEach(function(v) {
                var lastPrice = parseFloat(v.lastprice) || 0;
                var totalLT = parseFloat(v.leadtime) || 0;
                
                html += '<div class="max-vendor-card">' +
                            '<div class="max-vendor-header">' +
                                '<div>' +
                                    '<span class="max-vendor-name">' + (v.vendorname || 'Unknown Vendor') + '</span>' +
                                    '<div class="max-vendor-contact">' +
                                        '<span>👤 ' + (v.contact || 'No contact') + '</span>' +
                                        '<span>📍 ' + (v.city || '—') + '</span>' +
                                        '<span>📞 ' + (v.phone || '—') + '</span>' +
                                    '</div>' +
                                '</div>' +
                                '<span class="max-badge max-badge-neutral">' + v.vendorcode + '</span>' +
                            '</div>' +

                            '<div class="max-vendor-info-row">' +
                                // Zona Leadtime
                                '<div class="max-lt-box">' +
                                    '<span class="max-lt-title">Supply Leadtime</span>' +
                                    '<div style="display:flex; align-items:baseline; gap:5px;">' +
                                        '<span class="max-lt-total">' + totalLT + '</span>' +
                                        '<span style="color:var(--max-text-hint);">Days Total</span>' +
                                    '</div>' +
                                    '<div class="max-lt-grid" style="margin-top:5px; border-top:1px solid #eee; padding-top:5px;">' +
                                        '<span>Int: ' + parseFloat(v.intleadtime) + 'd</span>' +
                                        '<span>Ext: ' + parseFloat(v.extleadtime) + 'd</span>' +
                                    '</div>' +
                                '</div>' +

                                // Zona Harga & PO Terakhir
                                '<div class="max-history-box">' +
                                    '<span class="max-lt-title">Last Purchase</span>' +
                                    '<div class="max-last-price">' + (lastPrice > 0 ? Core.formatCurrency(lastPrice) : 'No Price Data') + '</div>' +
                                    '<div>' +
                                        (v.lastorderdate ? '<span style="color:var(--max-text-hint);">' + Core.formatDate(v.lastorderdate) + '</span>' : '') +
                                    '</div>' +
                                    '<div>' +
                                        (v.lastponum ? '<span class="max-last-po">' + v.lastponum + '</span>' : '<small style="color:#ccc;">No PO History</small>') +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</div>';
            });
            
            html += '</div>';
        } else {
            html += '<div class="max-no-result">No vendor or purchasing history found for this item.</div>';
        }


        // =============================================================
        // SECTION: PRODUCTION ACTIVITY LOG (RE-IMPROVED)
        // =============================================================
        html += '<div class="max-section-label">Production & Work Order Usage</div>';
        
        if (production && production.length > 0) {
            html += '<div class="max-prod-container">';
            
            production.slice(0, 5).forEach(function(p) {
                var qty = parseFloat(p.quantity) || 0;
                var unitCost = parseFloat(p.unitcost) || 0;
                var lineCost = parseFloat(p.linecost) || 0;

                html += '<div class="max-prod-card">' +
                            // Baris 1: Header (WO & Date)
                            '<div class="max-prod-card-header">' +
                                '<div class="max-prod-wo-badge">' + (p.wonum || 'N/A') + '</div>' +
                                '<div class="max-prod-date-info">' + 
                                    Core.formatDate(p.productiondate) + 
                                    '<span class="max-prod-batch-tag">Batch: ' + (p.batchnum || '—') + '</span>' +
                                '</div>' +
                            '</div>' +
                            
                            // Baris 2: Qty (Angka Utama)
                            '<div class="max-prod-qty-area">' +
                                '<span class="max-prod-qty-val">' + qty.toLocaleString() + ' <small style="font-weight:500;">' + (p.issueunit || '') + '</small></span>' +
                                '<span class="max-prod-qty-label">Usage Output</span>' +
                            '</div>' +
                            
                            // Baris 3: Footer (Financials)
                            '<div class="max-prod-footer">' +
                                '<div class="max-prod-stat">' +
                                    '<span class="max-prod-stat-label">Unit Cost</span>' +
                                    '<span class="max-prod-stat-value">' + Core.formatCurrency(unitCost) + '</span>' +
                                '</div>' +
                                '<div class="max-prod-stat align-right">' +
                                    '<span class="max-prod-stat-label">Total Impact</span>' +
                                    '<span class="max-prod-stat-value total">' + Core.formatCurrency(lineCost) + '</span>' +
                                '</div>' +
                            '</div>' +
                        '</div>';
            });
            
            html += '</div>';
        } else {
            html += '<div class="max-no-result">No activity recorded.</div>';
        }



        html += '<div style="height:40px;"></div>';
        $('#max-detail-content').html(html);
        Core.refreshSelectButtons();
    }

    return {
        init: function () {
            var self = this;
            Core.setup({
                name: 'Item',
                onSearchFn: function(q) { self.fetchList(q); },
                renderCardFn: _renderItemCard
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

            $(document).off('input', '#max-item-search').on('input', '#max-item-search', function () {
                Core.serverSearch($(this).val());
            });
        },
        fetchList: function (searchQuery) {
            Core.showLoading('#max-records-container');
            
            var payload = { 
                getDrawerItemData: { 
                    action: 'list', 
                    search: searchQuery || '' 
                } 
            };

            Core.request(payload, function (data) {
                // Set data dari server langsung, tidak ada filter lokal lagi
                Core.setListData(data);
            });
        },
        select: function (id) {
            Core.state.selectedId = id;
            Core.switchToTab('tab-detail');
            Core.showLoading('#max-detail-content');
            Core.request({ getDrawerItemData: { action: 'detail', itemnum: id } }, function (data) {
                _renderItemDetail(data);
                Core.renderList(); // Render ulang agar highlight baris berubah
            });
        },
        loadByValue: function (val) {
            $('#max-item-search').val(val || '');
            Core.switchToTab('tab-list');
            // Langsung hit server dengan value yang diberikan
            this.fetchList(val || '');
        }
    };
}(jQuery, window.MaxesDrawerCore));

//MaxesItem.init();