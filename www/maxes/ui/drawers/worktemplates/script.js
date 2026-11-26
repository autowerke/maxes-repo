/* =============================================================
   MAXES NAVIGATION — drawers/worktemplates/script.js
   Module: Work Templates Explorer (v4.2 - Multi-Pagination Detail)
   ============================================================= */

window.MaxesWorkTemplates = (function ($, Core) {

    // ── Internal Detail State ──
    var _currentDetailRaw = null;
    var _detailFilterQuery = "";
    var _detailPages = {
        materials: 1, services: 1, tools: 1, labor: 1, tasks: 1 
    };
    var _pageSize = 10; // Maksimal 10 baris sesuai permintaan

    // ── Helper: Filter Data Local ──
    function _getFilteredData(data, query, searchCols) {
        if (!query) return data;
        var q = query.toLowerCase();
        return data.filter(function(item) {
            return searchCols.some(function(col) {
                return String(item[col] || '').toLowerCase().indexOf(q) > -1;
            });
        });
    }

    // ── Render Card List Utama (Tetap) ──
    function _renderWTCard(w) {
        var sel = w.wtnum === Core.state.selectedId ? ' max-selected' : '';
        return '<div class="max-list-record ' + sel + '" data-id="' + w.wtnum + '">' +
                Core.avatar(w.worktype ? w.worktype.substring(0, 2).toUpperCase() : 'WT', 'max-avatar-blue', 'sm') +
                '<div class="max-record-info">' +
                    '<div class="max-record-id">' + w.wtnum + '</div>' +
                    '<div class="max-record-desc">' + (w.description || 'No description') + '</div>' +
                '</div>' +
                '<div class="max-record-meta">' +
                    '<span class="max-badge max-badge-neutral">' + (w.workclass || 'JOB') + '</span>' +
                    Core.selectBtn(w.wtnum, 'sm') +
                '</div></div>';
    }

    // ── MAIN DETAIL RENDERER ──
    function _renderWTDetail() {
        var res = _currentDetailRaw;
        var h = res.header;
        $('#detail-search-container').show();

        var html = '<button class="max-detail-back"> &#8592; Back to list</button>' +
            '<div class="max-detail-header">' +
                Core.avatar(h.wtnum.substring(0,2), 'max-avatar-blue', 'lg') +
                '<div style="flex:1;min-width:0;">' +
                    '<div class="max-detail-title">' + h.wtnum + '</div>' +
                    '<div class="max-detail-subtitle">' + (h.description || '') + '</div>' +
                '</div>' +
            '</div>';

        html += '<div style="display:flex; gap:10px; margin: 15pt 0;">' +
                    '<div style="flex:1;">' + Core.selectBtn(h.wtnum, 'md') + '</div>' +
                    Core.gotoAppBtn(res.appUrl, res.applinksownernum) +
                '</div>';


        html += _renderSection('tasks', 'Work Tasks (Job Steps)', ['description', 'tasklinenum'], res.plans.tasks, function(t) {
            return '<div class="max-task-row">' +
                        '<div class="max-task-number">' + t.tasklinenum + '</div>' +
                        '<div class="max-task-desc">' + t.description + '</div>' +
                   '</div>';
        }, ''); 

        // --- SECTION: MATERIALS (WITH PAGINATION & FILTER) ---

        html += _renderSection('materials', 'Planned Materials & Inventory Status', ['itemnum', 'warehouse'], res.plans.materials, function(m) {
            var master = m.master || {};
            var specs = m.specs || [];
            var stocks = m.avail_stock || [];
            var plannedQty = parseFloat(m.quantity) || 0;

            // 1. Hitung Total Quantity Available
            var totalAvail = 0;
            stocks.forEach(function(st) {
                totalAvail += parseFloat(st.quantity) || 0;
            });

            // 2. Tentukan Flag/Alert
            var isInsufficient = totalAvail < plannedQty;
            var bannerClass = isInsufficient ? 'insufficient' : '';
            var statusIcon = isInsufficient ? '🚨' : '✅';
            var statusText = isInsufficient ? 'INSUFFICIENT STOCK' : 'STOCK AVAILABLE';

            var rowHtml = '<tr><td colspan="3" style="padding: 12pt;">' +
                '<div style="display:flex; justify-content:space-between;">' +
                    '<div>' +
                        '<span class="max-plan-id">' + m.itemnum + '</span>' +
                        '<div style="font-size:11pt; font-weight:700; margin-top:2pt;">' + (master.description || 'No Description') + '</div>' +
                        '<div class="max-mat-master-info">' + (master.brand || '—') + ' | ' + (master.manufacturer || '—') + '</div>' +
                    '</div>' +
                    '<div style="text-align:right;">' +
                        '<div class="max-field-label">PLANNED QTY</div>' +
                        '<div style="font-size:14pt; font-weight:800;">' + plannedQty.toLocaleString() + '</div>' +
                    '</div>' +
                '</div>';

            // Technical Specs (Tags)
            if (specs.length > 0) {
                rowHtml += '<div class="max-mat-specs-container">';
                specs.forEach(function(s) {
                    rowHtml += '<span class="max-mat-spec-tag"><b>' + (s.attr_desc || s.attributename) + ':</b> ' + s.value + '</span>';
                });
                rowHtml += '</div>';
            }

            // --- ENRICHED STOCK SECTION ---
            if (stocks.length > 0) {
                rowHtml += '<div class="max-mat-stock-banner ' + bannerClass + '">' +
                        '<span class="max-mat-stock-title">' + statusIcon + ' ' + statusText + '</span>' +
                        '<table class="max-mat-stock-table">' +
                                '<thead><tr>' +
                                    '<th>Wh/Rack</th><th>Batch</th><th>Cost Date</th><th>Exp. Date</th><th align="right">Qty</th>' +
                                '</tr></thead>' +
                                '<tbody>';
                
                stocks.forEach(function(st) {
                    var expDate = st.expdate ? Core.formatDate(st.expdate) : '<span style="color:#ccc">—</span>';
                    rowHtml += '<tr>' +
                                '<td><b>' + st.warehouse + '</b><br>' + (st.racknum || '—') + '</td>' +
                                '<td>' + (st.batchnum || '—') + '</td>' +
                                '<td>' + Core.formatDate(st.costdate) + '</td>' +
                                '<td>' + expDate + '</td>' +
                                '<td align="right"><b>' + parseFloat(st.quantity).toLocaleString() + '</b></td>' +
                            '</tr>';
                });

                rowHtml += '</tbody></table>' +
                        '<div class="max-total-avail">' +
                                '<span>TOTAL AVAILABLE IN ALL RACKS:</span>' +
                                '<span>' + totalAvail.toLocaleString() + '</span>' +
                        '</div>' +
                        '</div>';
            } else {
                rowHtml += '<div style="margin-top:8pt; font-size:10pt; color:#cf1322; font-weight:800; background:#fff1f0; padding:8pt; border-radius:4px; border:1pt solid #ffa39e;">' +
                        '🚨 OUT OF STOCK: No records found in any warehouse.' +
                        '</div>';
            }

            rowHtml += '</td></tr>';
            return rowHtml;
        }, '<thead><tr><th colspan="3">Material Planning & Inventory Intelligence</th></tr></thead>');

        // --- SECTION: SERVICES ---
        html += _renderSection('services', 'Planned Services', ['serviceitemnum', 'description'], res.plans.services, function(s) {
            return '<tr><td><span class="max-plan-id">' + s.serviceitemnum + '</span><br>' + s.description + '</td>' +
                   '<td align="right"><b>' + parseFloat(s.quantity).toLocaleString() + '</b></td>' +
                   '<td align="right">' + Core.formatCurrency(s.linecost) + '</td></tr>';
        }, '<thead><tr><th>Service</th><th align="right">Qty</th><th align="right">Cost</th></tr></thead>');

        // --- SECTION: TOOLS ---
        /* === Update di script.js, ganti logika render tools di dalam _renderWTDetail === */

        html += _renderSection('tools', 'Planned Tools & Readiness', ['toolnum', 'description'], res.plans.tools, function(t) {
            var m = t.master || {};
            var rawStock = t.stock_info;
            var stocks = [];
            if (rawStock) {
                stocks = Array.isArray(rawStock) ? rawStock : [rawStock];
            }
            var plannedQty = parseFloat(t.quantity) || 0;
            var uCost = parseFloat(t.unitcost) || 0;
            var lCost = parseFloat(t.linecost) || 0;

            // 1. Kalkulasi Total dari semua lokasi
            var totalInStock = 0;
            var totalAvail = 0;
            stocks.forEach(function(s) {
                totalInStock += parseFloat(s.curbal) || 0;
                totalAvail += parseFloat(s.qtyavailable) || 0;
            });

            // 2. Logic Status (Berdasarkan TOTAL ketersediaan)
            var isOutOfStock = totalAvail < plannedQty;
            var availClass = isOutOfStock ? 'out' : '';
            var availIcon = isOutOfStock ? '❌' : '✅';
            var availText = isOutOfStock ? 'UNAVAILABLE IN POOL' : 'AVAILABLE TO BORROW';

            // 3. Logic Kalibrasi
            var today = new Date();
            var nextCal = m.nextcalibrationdate ? new Date(m.nextcalibrationdate) : null;
            var isCalExpired = nextCal && nextCal < today;

            var rowHtml = '<tr><td colspan="3" style="padding: 12pt;">' +
                '<div style="display:flex; justify-content:space-between;">' +
                    '<div>' +
                        '<span class="max-plan-id">' + t.toolnum + '</span>' +
                        '<div style="font-size:11pt; font-weight:700; margin-top:2pt;">' + (m.name || t.description || 'Unknown Tool') + '</div>' +
                        '<div class="max-tool-spec">' + 
                            (m.brand || '—') + ' | SN: ' + (m.serialnumber || '—') + 
                        '</div>' +
                    '</div>' +
                    '<div style="text-align:right;">' +
                        '<div class="max-field-label">REQ QTY</div>' +
                        '<div style="font-size:14pt; font-weight:800;">' + plannedQty.toLocaleString() + '</div>' +
                        '<div style="font-size:10pt; color:var(--max-text-muted); margin-top:2pt;">' + Core.formatCurrency(lCost) + '</div>' +
                    '</div>' +
                '</div>';

            // Calibration Info
            if (m.lastcalibrationdate || m.nextcalibrationdate) {
                rowHtml += '<div class="max-calibration-banner">' +
                    '<div class="max-cal-item"><span class="max-cal-label">Last Cal</span><span class="max-cal-value">' + Core.formatDate(m.lastcalibrationdate) + '</span></div>' +
                    '<div class="max-cal-item"><span class="max-cal-label">Next Cal</span><span class="max-cal-value ' + (isCalExpired ? 'expired' : '') + '">' + Core.formatDate(m.nextcalibrationdate) + '</span></div>' +
                '</div>';
            }

            // --- AVAILABILITY BOX (MULTIPLE LOCATIONS) ---
            rowHtml += '<div class="max-tool-availability ' + availClass + '">' +
                        '<div style="width:100%">' +
                            '<span style="font-size:10pt; font-weight:800;">' + availIcon + ' ' + availText + '</span>' +
                            '<div class="max-tool-loc-list">';
            
            if (stocks.length > 0) {
                stocks.forEach(function(s) {
                    rowHtml += '<div class="max-tool-loc-row">' +
                                '<span>📍 Location: <b>' + (s.locnum || '-') + '</b></span>' +
                                '<span>In Stock / Avail: <b>' + (parseFloat(s.curbal) || 0) + ' / ' + (parseFloat(s.qtyavailable) || 0) + '</b></span>' +
                            '</div>';
                });
                
                // Menambahkan Garis Total
                rowHtml += '<div class="max-tool-total-row">' +
                            '<span>TOTAL TOOLS</span>' +
                            '<span>' + totalInStock + ' / ' + totalAvail + '</span>' +
                        '</div>';
            } else {
                rowHtml += '<div style="font-size:9.5pt; color:#cf1322;">No stock records found for this tool type.</div>';
            }

            rowHtml += '</div></div></div>'; // End Box

            // Unit Cost Info (Kecil di bawah)
            rowHtml += '<div style="margin-top:6pt; font-size:9pt; color:var(--max-text-hint); text-align:right;">' +
                    'Estimation Unit Cost: ' + Core.formatCurrency(uCost) +
                    '</div>';

            rowHtml += '</td></tr>';
            return rowHtml;
        }, '<thead><tr><th colspan="3">Tool Planning & Multi-Location Availability</th></tr></thead>');

        // =============================================================
        // SECTION: PLANNED LABOR & TEAM BREAKDOWN (IMPROVED JS LOGIC)
        // =============================================================

        html += _renderSection('labor', 'Planned Labor & Team Allocation', ['teamnum', 'personid'], res.plans.labor, function(l) {
            var type = l.type;
            var d = l.detail || {};
            
            // --- FIX BUG KALKULASI TOTAL ---
            var hours = parseFloat(l.reghours) || 0;
            var rate  = parseFloat(l.unitcost) || 0;
            
            // Jika linecost dari database 0 atau null, hitung manual
            var lCost = (parseFloat(l.linecost) > 0) ? parseFloat(l.linecost) : (hours * rate);
            // -------------------------------

            var rowHtml = '<tr><td colspan="3" style="padding: 12pt; border-bottom: 1pt solid #eee;">';

            // 1. HEADER
            rowHtml += '<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10pt;">' +
                            '<div>' +
                                '<span class="max-badge ' + (type === 'TEAM' ? 'max-badge-op' : 'max-badge-active') + '" style="margin-bottom:5pt;">' + type + ' ASSIGNMENT</span>' +
                                '<div class="max-plan-id" style="font-size:12pt;">' + (l.teamnum || l.personid) + '</div>' +
                                '<div style="font-size:11pt; font-weight:700;">' + (type === 'TEAM' ? (d.description || 'No Description') : d.fullname) + '</div>' +
                            '</div>' +
                            '<div style="text-align:right;">' +
                                '<div class="max-field-label">PLANNED HOURS</div>' +
                                '<div style="font-size:15pt; font-weight:800; color:var(--max-text-main);">' + hours + ' <small style="font-size:10pt; font-weight:400;">Hrs</small></div>' +
                            '</div>' +
                    '</div>';

            // 2. BODY (Breakdown)
            rowHtml += '<div class="max-labor-breakdown-container">';
            if (type === 'TEAM' && d.members && d.members.length > 0) {
                rowHtml += '<div style="font-size:9pt; font-weight:700; color:var(--max-text-hint); text-transform:uppercase; margin-bottom:8pt; letter-spacing:0.5px;">Team Members Breakdown:</div>';
                d.members.forEach(function(m) {
                    rowHtml += _buildPersonRow(m);
                });
            } else if (type === 'INDIVIDUAL' && d.personid) {
                rowHtml += _buildPersonRow(d);
            } else {
                rowHtml += '<div style="font-size:10pt; color:var(--max-text-hint); font-style:italic;">No personnel data available.</div>';
            }
            rowHtml += '</div>'; 

            // 3. FOOTER (Gunakan lCost yang sudah diperbaiki)
            rowHtml += '<div class="max-labor-footer">' +
                            '<span class="max-est-rate">Est. Resource Rate: <b>' + Core.formatCurrency(rate) + '/h</b></span>' +
                            '<span class="max-total-labor-cost">Total: ' + Core.formatCurrency(lCost) + '</span>' +
                    '</div>';

            rowHtml += '</td></tr>';
            return rowHtml;
        }, '<thead><tr><th colspan="3">Labor Resources & Skill Breakdown</th></tr></thead>');
        

        // --- HELPER INTERNAL JS: Membangun Baris Personil (Actionable) ---
        function _buildPersonRow(p) {
            // Logic Actionable WhatsApp
            var phone = p.primaryphone || '';
            var waLink = '';
            if (phone) {
                var cleanPhone = phone.replace(/\D/g, '');
                if (cleanPhone.startsWith("0")) cleanPhone = "62" + cleanPhone.slice(1);
                waLink = 'https://wa.me/' + cleanPhone;
            }
            var waLogoUrl = 'https://static.vecteezy.com/system/resources/thumbnails/018/930/748/small_2x/whatsapp-logo-whatsapp-icon-whatsapp-transparent-free-png.png';
            var waIcon = '<img src="' + waLogoUrl + '" style="width: 36px; height: 36px; vertical-align: middle; margin-right: 6px; margin-bottom: 2px;">';

            return '<div style="display:flex; align-items:center; gap:10pt; padding:6pt 0; border-bottom:1pt solid #f0f0f0; last-child { border-bottom:none; }">' +
                        // Avatar inisial kecil
                        Core.avatar(p.fullname ? p.fullname.substring(0,1) : '?', 'max-avatar-gray', 'sm') +
                        
                        '<div style="flex:1; min-width:0;">' +
                            '<div style="font-size:10.5pt; font-weight:700; color:var(--max-text-main);">' + p.fullname + 
                                (p.priority ? ' <small style="font-weight:400; color:var(--max-text-hint);">[P' + p.priority + ']</small>' : '') + 
                            '</div>' +
                            '<div style="font-size:9.5pt; color:var(--max-text-muted);">' + (p.department || 'No Dept') + ' | ' + (p.skilllevel || 'General') + '</div>' +
                        '</div>' +

                        '<div style="display:flex; gap:8pt; align-items:center;">' +
                            (waLink ? '<a href="' + waLink + '" target="_blank" style="text-decoration:none; font-size:14pt;" title="WhatsApp ' + p.fullname + '">'+waIcon+'</a>' : '') +
                            (p.primaryemail ? '<a href="mailto:' + p.primaryemail + '" style="text-decoration:none; font-size:14pt;" title="Email ' + p.fullname + '">📧</a>' : '') +
                        '</div>' +
                   '</div>';
        }


        $('#max-detail-content').html(html);
        Core.refreshSelectButtons();
    }

    // ── Generator Section Tabel ──
    function _renderSection(type, title, searchCols, rawData, rowFn, thead) {
        var filtered = _getFilteredData(rawData, _detailFilterQuery, searchCols);
        var total = filtered.length;
        var page = _detailPages[type];
        var totalPages = Math.ceil(total / _pageSize);
        
        var start = (page - 1) * _pageSize;
        var pagedData = filtered.slice(start, start + _pageSize);

        var sectionHtml = '<div class="max-section-label">' + title + ' (' + total + ')</div>';
        
        if (total > 0) {
            sectionHtml += '<table class="max-plan-table">' + thead + '<tbody>' +
                           pagedData.map(rowFn).join('') + '</tbody></table>';
            
            if (totalPages > 1) {
                sectionHtml += '<div class="max-mini-pagination">' +
                    '<button class="max-mini-page-btn" data-type="'+type+'" data-page="'+(page-1)+'" '+(page===1?'disabled':'')+'>&#10094;</button>' +
                    '<span class="max-mini-page-info">'+page+' / '+totalPages+'</span>' +
                    '<button class="max-mini-page-btn" data-type="'+type+'" data-page="'+(page+1)+'" '+(page===totalPages?'disabled':'')+'>&#10095;</button>' +
                '</div>';
            }
        } else {
            sectionHtml += '<div class="max-no-result" style="padding:10pt 0;">No matching items.</div>';
        }
        return sectionHtml;
    }

    return {
        init: function () {
            var self = this;
            Core.setup({
                name: 'Work Template',
                onSearchFn: function(q) { self.fetchList(q); },
                renderCardFn: _renderWTCard
            });
            this._bindEvents();
        },

        _bindEvents: function () {
            var self = this;
            $(document).off('click', '.max-list-record').on('click', '.max-list-record', function () {
                var id = $(this).attr('data-id');
                if (id) self.select(id);
            });

            $(document).off('input', '#max-wt-search').on('input', '#max-wt-search', function () {
                Core.serverSearch($(this).val());
            });

            // --- EVENT BARU: Detail Search ---
            $(document).off('input', '#max-wt-detail-search').on('input', '#max-wt-detail-search', function () {
                _detailFilterQuery = $(this).val();
                // Reset semua page ke 1 saat searching
                _detailPages = { materials: 1, services: 1, tools: 1, labor: 1, tasks: 1 }; 
                _renderWTDetail();
            });

            // --- EVENT BARU: Mini Pagination Detail ---
            $(document).off('click', '.max-mini-page-btn').on('click', '.max-mini-page-btn', function () {
                var type = $(this).data('type');
                var page = parseInt($(this).data('page'));
                _detailPages[type] = page;
                _renderWTDetail();
            });
        },

        fetchList: function (q) {
            Core.showLoading('#max-records-container');
            Core.request({ getDrawerWorkTemplateData: { action: 'list', search: q || '' } }, function (data) {
                Core.setListData(data);
            });
        },

        select: function (wtnum) {
            Core.state.selectedId = wtnum;
            Core.switchToTab('tab-detail');
            Core.showLoading('#max-detail-content');
            $('#detail-search-container').hide(); // Sembunyikan search detail saat loading
            
            Core.request({ getDrawerWorkTemplateData: { action: 'detail', wtnum: wtnum } }, function (data) {
                _currentDetailRaw = data;
                _detailFilterQuery = ""; // Reset filter
                _detailPages = { materials: 1, services: 1, tools: 1, labor: 1, tasks: 1  }; // Reset pages
                $('#max-wt-detail-search').val(''); 
                _renderWTDetail();
                Core.renderList(); 
            });
        },

        loadByValue: function (val) {
            $('#max-wt-search').val(val || '');
            Core.switchToTab('tab-list');
            this.fetchList(val || '');
        }
    };

}(jQuery, window.MaxesDrawerCore));