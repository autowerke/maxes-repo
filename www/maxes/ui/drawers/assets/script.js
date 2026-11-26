/* =============================================================
   MAXES NAVIGATION — drawers/assets/script.js
   Module: Assets Explorer (Using Engine v3.1 - Standard JS)
   ============================================================= */

var MaxesAssets = (function ($, Core) {

    function _getAvatar(type, status) {
        // 1. Tentukan Inisial (Ambil 2 huruf pertama)
        var initials = 'AS';
        if (type) {
            initials = type.substring(0, 2).toUpperCase();
        }

        // 2. Tentukan Warna berdasarkan Status
        var colorClass = 'max-avatar-gray'; // Default jika status kosong atau tak dikenal
        
        if (status) {
            var s = status.toUpperCase();
            if (s === 'ACTIVE' || s === 'OPERATING' || s === 'IN-SERVICE') {
                colorClass = 'max-avatar-blue';
            } else if (s === 'READY' || s === 'OPERATIONAL') {
                colorClass = 'max-avatar-teal';
            } else if (s === 'BROKEN' || s === 'DECOMMISSIONED' || s === 'SCRAPPED') {
                colorClass = 'max-avatar-red';
            } else if (s === 'LIMITED' || s === 'WARNING') {
                colorClass = 'max-avatar-orange';
            } else{
                colorClass = 'max-avatar-gray';
            }
        }

        return Core.avatar(initials, colorClass, 'sm');
    }

    function _renderAssetCard(a) {
        // Akses state via Core.state
        var sel = a.assetnum === Core.state.selectedId ? ' max-selected' : '';
        return '<div class="max-list-record ' + sel + '" data-id="' + a.assetnum + '">' +
                _getAvatar(a.assettype, a.status) +
                '<div class="max-record-info">' +
                    '<div class="max-record-id">' + a.assetnum + '</div>' +
                    '<div class="max-record-desc">' + (a.description || 'No description') + '</div>' +
                    '<div class="max-record-loc">Loc: ' + (a.locnum || '—') + '</div>' +
                '</div>' +
                '<div class="max-record-meta">' +
                    Core.statusBadge(a.status) +
                    '<span style="font-size: 10pt;color:var(--max-text-hint);">' + (a.assettype || '') + '</span>' +
                    Core.selectBtn(a.assetnum, 'sm') +
                '</div>' +
            '</div>';
    }

    function _renderAssetDetail(res) {
        var a = res.header;
        var classifications = res.classifications || [];
        var maintenance = res.maintenance || [];
        var production = res.production || [];
        var meters = res.meters || [];

        var html = '<button class="max-detail-back"> &#8592; Back to list</button>' +
            '<div class="max-detail-header">' +
                Core.avatar(a.assettype ? a.assettype.substring(0,2).toUpperCase() : 'AS', 'max-avatar-blue', 'lg') +
                '<div style="flex:1;min-width:0;">' +
                    '<div class="max-detail-title">' + a.assetnum + '</div>' +
                    '<div class="max-detail-subtitle">' + (a.description || '') + '</div>' +
                    '<div class="max-detail-badges">' +
                        Core.statusBadge(a.status) +
                        '<span class="max-badge max-badge-neutral">' + a.assettype + '</span>' +
                    '</div>' +
                '</div>' +
            '</div>'
            
            html += '<div style="display:flex; gap:10px; margin: 15px 0; width: 100%;">' +
            '<div style="flex:1;">' + 
                Core.selectBtn(a.assetnum, 'md') + 
            '</div>' +
            Core.gotoAppBtn(res.appUrl, res.applinksownernum) +
        '</div>' + 

            '<div class="max-section-label">General Information</div>' +
            '<div class="max-field-grid">' +
                '<div class="max-field-item"><div class="max-field-label">Location</div><div class="max-field-value">' + (a.locnum || '—') + '</div></div>' +
                '<div class="max-field-item"><div class="max-field-label">Parent</div><div class="max-field-value">' + (a.parent || '—') + '</div></div>' +
                '<div class="max-field-item"><div class="max-field-label">Manufacturer</div><div class="max-field-value">' + (a.manufacturer || '—') + '</div></div>' +
                '<div class="max-field-item"><div class="max-field-label">Model</div><div class="max-field-value">' + (a.model || '—') + '</div></div>' +
            '</div>';
        
        // --- Classification ---   
        html += '<div class="max-section-label">Technical Classification</div>';
        if (classifications.length > 0) {
            html += '<div class="max-spec-grid">';
            classifications.forEach(function (c) {
                html += '<div class="max-spec-item" title="' + (c.description || '') + '">' +
                            '<span class="max-spec-label">' + c.attributename + '</span>' +
                            '<div class="max-spec-value">' + (c.value || '—') + 
                                '<span class="max-spec-unit">' + (c.unit || '') + '</span>' +
                            '</div>' +
                        '</div>';
            });
            html += '</div>';
        } else {
            html += '<div class="max-no-result" style="padding:10px 0;">No technical specs.</div>';
        }

        // --- Maintenance ---
        html += '<div class="max-section-label">Maintenance Performance (Last 5)</div>';
        
        if (maintenance && maintenance.length > 0) {
            html += '<div class="max-wo-container">';
            
            maintenance.slice(0, 5).forEach(function (wo) {
                var actCost = parseFloat(wo.actualcost) || 0;
                var planCost = parseFloat(wo.plannedcost) || 0;

                html += '<div class="max-wo-card">' +
                    '<div class="max-wo-header">' +
                        '<div class="max-wo-num">' + wo.wonum + '</div>' +
                        '<div class="max-wo-people">' +
                            '<span>Own: <b>' + (wo.owner || '—') + '</b></span>' +
                            '<span>Lead: <b>' + (wo.leader || '—') + '</b></span>' +
                        '</div>' +
                    '</div>' +
                    
                    '<div class="max-wo-grid">' +
                        // Kolom 1: Timelines
                        '<div class="max-wo-sub-section">' +
                            '<span class="max-wo-label-tiny">Schedule & Execution</span>' +
                            '<div class="max-wo-date-row">' +
                                '<span class="text-target">Targ Start:</span>' +
                                '<span class="text-actual">' + Core.formatDate(wo.targstartdate) + '</span>' +
                            '</div>' +
                            '<div class="max-wo-date-row">' +
                                '<span class="text-target">Act Start:</span>' +
                                '<span class="text-actual">' + Core.formatDate(wo.actstartdate) + '</span>' +
                            '</div>' +
                            '<div style="margin-top:2px; border-top:1px solid #f8f9fa; padding-top:2px;"></div>' +
                            '<div class="max-wo-date-row">' +
                                '<span class="text-target">Targ Comp:</span>' +
                                '<span class="text-actual">' + Core.formatDate(wo.targcompdate) + '</span>' +
                            '</div>' +
                            '<div class="max-wo-date-row">' +
                                '<span class="text-target">Act Finish:</span>' +
                                '<span class="text-actual">' + Core.formatDate(wo.actfinishdate) + '</span>' +
                            '</div>' +
                        '</div>' +

                        // Kolom 2: Costs
                        '<div class="max-wo-sub-section">' +
                            '<span class="max-wo-label-tiny">Cost Comparison</span>' +
                            '<div class="max-wo-cost-box">' +
                                '<div class="max-wo-cost-item">' +
                                    '<span style="color:var(--max-text-hint)">Planned:</span>' +
                                    '<span class="cost-planned">' + Core.formatCurrency(planCost) + '</span>' +
                                '</div>' +
                                '<div class="max-wo-cost-item">' +
                                    '<span style="color:var(--max-text-hint)">Actual:</span>' +
                                    '<span class="cost-actual">' + Core.formatCurrency(actCost) + '</span>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' + // End Grid
                '</div>'; // End Card
            });
            
            html += '</div>';
        } else {
            html += '<div class="max-no-result" style="padding:10px 0;">No maintenance records found.</div>';
        }

        // =============================================================
        // 4. Section: Production Logs (BAGIAN YANG HILANG)
        // =============================================================
         html += '<div class="max-section-label">Production Activity Log</div>';
        if (production && production.length > 0) {
            html += '<div class="max-prod-container">';
            production.slice(0, 5).forEach(function (p) {
                var qty = parseFloat(p.quantity);
                var lineCost = parseFloat(p.linecost);
                
                html += '<div class="max-prod-card">' +
                    '<div class="max-prod-header">' +
                        '<div>' +
                            '<div class="max-prod-itemname">' + p.itemnum + '</div>' +
                            '<div class="max-prod-meta-info">' + Core.formatDate(p.productiondate) + ' | ' + (p.outputtype || 'ITEM') + '</div>' +
                        '</div>' +
                        '<div style="text-align:right">' +
                            '<div class="max-prod-qty">' + (isNaN(qty) ? '0' : qty.toLocaleString()) + ' <small style="font-size: 10pt; font-weight:400; color:var(--max-text-muted);">' + (p.issueunit || '') + '</small></div>' +
                            '<div class="max-prod-meta-info">' + (p.batchnum ? 'Batch: ' + p.batchnum : 'No Batch') + '</div>' +
                        '</div>' +
                    '</div>' +
                    
                    '<div class="max-prod-cost-row">' +
                        '<div class="max-stat-item">' +
                            '<span class="max-stat-label">Unit Cost</span>' +
                            '<span class="max-stat-value" style="color:var(--max-text-main);">' + Core.formatCurrency(p.unitcost) + '</span>' +
                        '</div>' +
                        '<div class="max-stat-item" style="text-align:right;">' +
                            '<span class="max-stat-label">Total Line Cost</span>' +
                            '<span class="max-stat-value" style="color:var(--max-success); font-size: 12pt;">' + Core.formatCurrency(lineCost) + '</span>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            });
            html += '</div>';
        } else {
            html += '<div class="max-no-result" style="padding:10px 0;">No production history.</div>';
        }


        // --- Meters ---
        html += '<div class="max-section-label">Asset Meter Readings & Analytics</div>';
        
        if (meters && meters.length > 0) {
            html += '<div class="max-meter-grid">';
            
            meters.forEach(function (m) {
                // Parsing Data
                var lastReading = parseFloat(m.lastreading);
                var displayReading = isNaN(lastReading) ? '0' : lastReading.toLocaleString();
                var lastDate = Core.formatDate(m.lastreadingdate);
                
                var avgUsage = parseFloat(m.avgdailyusage) || 0;
                var unitsToGo = parseFloat(m.unitstogo);
                var daysUntil = parseInt(m.daysuntildue);
                
                // Logic warna untuk "Days Until Due"
                var dueClass = (daysUntil < 7) ? 'urgent' : '';

                html += '<div class="max-meter-card">' +
                    '<div class="max-meter-header">' +
                        '<div>' +
                            '<div class="max-meter-title">' + m.meternum + '</div>' +
                            '<span class="max-meter-desc">' + (m.description || '') + '</span>' +
                        '</div>' +
                        '<span class="max-meter-type-badge">' + (m.metertype || 'Unknown') + '</span>' +
                    '</div>' +
                    
                    '<div class="max-meter-body">' +
                        '<div class="max-meter-reading-group">' +
                            '<div class="max-meter-reading">' + displayReading + 
                                '<span class="max-meter-unit">' + (m.measureunit || '') + '</span>' +
                            '</div>' +
                            '<div class="max-meter-date">Last Reading: ' + lastDate + '</div>' +
                        '</div>' +

                        '<div class="max-meter-stats">' +
                            '<div class="max-stat-item">' +
                                '<span class="max-stat-label">Daily Avg</span>' +
                                '<span class="max-stat-value">' + avgUsage.toFixed(1) + '</span>' +
                            '</div>';
                
                // Hanya tampilkan 'Remaining' jika ada datanya
                if (!isNaN(unitsToGo)) {
                    html += '<div class="max-stat-item">' +
                                '<span class="max-stat-label">Remaining</span>' +
                                '<span class="max-stat-value ' + dueClass + '">' + Math.round(unitsToGo).toLocaleString() + '</span>' +
                            '</div>';
                }

                if (!isNaN(daysUntil)) {
                    html += '<div class="max-stat-item">' +
                                '<span class="max-stat-label">Due In</span>' +
                                '<span class="max-stat-value ' + dueClass + '">' + daysUntil + ' d</span>' +
                            '</div>';
                }

                html += '</div></div></div>'; // End Card
            });
            
            html += '</div>'; // End Grid
        } else {
            html += '<div class="max-no-result" style="padding:10px 0;">No meters assigned.</div>';
        }
        
        
        html += '</table><div style="height:40px;"></div>';
        
        $('#max-detail-content').html(html);
        Core.refreshSelectButtons();
    }

    return {
        init: function () {
            var self = this;
            Core.setup({
                name: 'Asset',
                onSearchFn: function(q) { self.fetchList(q); },
                renderCardFn: _renderAssetCard
            });
            this._bindEvents();
        },
        _bindEvents: function () {
            var self = this;
            
            // Klik Record Row
            $(document).off('click', '.max-list-record').on('click', '.max-list-record', function (e) {
                if ($(e.target).closest('.max-select-btn').length) return;
                var id = $(this).attr('data-id'); 
                if (id) self.select(id);
            });

            // Input Search
            /*
            $(document).off('input', '#max-asset-search').on('input', '#max-asset-search', function () {
                Core.filter($(this).val());
            });
            */
           $(document).off('input', '#max-asset-search').on('input', '#max-asset-search', function () {
                Core.serverSearch($(this).val());
            });
        },
        fetchList: function (q) {
            var self = this;
            Core.showLoading('#max-records-container');
            
            // Kirim payload search ke server
            var payload = { 
                getDrawerAssetData: { 
                    action: 'list', 
                    search: q || '' 
                } 
            };

            Core.request(payload, function (data) {
                // Langsung masukkan data ke engine, tidak perlu request lagi di dalam sini
                Core.setListData(data);
            });
        },


        select: function (assetnum) {
            // 1. Simpan ID ke state global agar renderCard tahu mana yang di-highlight
            Core.state.selectedId = assetnum; 
            
            // 2. Pindah Tab
            Core.switchToTab('tab-detail');
            
            // 3. Tampilkan Loading di Tab Detail
            Core.showLoading('#max-detail-content');

            // 4. Request Data Detail
            Core.request({ 
                getDrawerAssetData: { 
                    action: 'detail', 
                    assetnum: assetnum 
                } 
            }, function (data) {
                // Render detail setelah data tiba
                _renderAssetDetail(data);
                // Render ulang list (tanpa fetch ulang) agar baris yang dipilih berwarna biru
                Core.renderList(); 
            });
        },
        /**
         */
        loadByValue: function (val) {
            // 1. Masukkan nilai (baik ada isinya atau kosong) ke search box drawer
            $('#max-asset-search').val(val || '');

            // 2. Pastikan kita berada di Tab List
            Core.switchToTab('tab-list');
            this.fetchList(val || '');

            /*
            // 3. Jika data sudah terlanjur di-load (cached), langsung jalankan filter.
            // Jika data belum datang (masih request), fetchList akan menghandle-nya di atas.
            if (Core.state.rawData && Core.state.rawData.length > 0) {
                Core.filter(val || '');
            }
            */
        } 
    };

}(jQuery, window.MaxesDrawerCore));

// Jalankan init
//MaxesAssets.init();