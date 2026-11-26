/* =============================================================
   MAXES NAVIGATION — drawers/employees/script.js
   Module: Employee Explorer (Using Engine v4.0 - Server Search)
   ============================================================= */

var MaxesEmployees = (function ($, Core) {

    // ── 1. Card Renderer (Tab List) ───────────────────────────
    function _renderEmployeeCard(e) {
        var sel = e.personid === Core.state.selectedId ? ' max-selected' : '';
        // Inisial dari Fullname (misal: "Andi Wijaya" -> "AW")
        var initials = e.fullname ? e.fullname.split(' ').map(function(n){ return n[0]; }).join('').substring(0,2).toUpperCase() : '??';
        
        return '<div class="max-list-record ' + sel + '" data-id="' + e.personid + '">' +
                Core.avatar(initials, 'max-avatar-blue', 'sm') +
                '<div class="max-record-info">' +
                    '<div class="max-record-id">' + e.personid + '</div>' +
                    '<div class="max-record-desc">' + (e.fullname || 'No Name') + '</div>' +
                    '<div class="max-record-loc">' + (e.title || '—') + ' | ' + (e.department || '—') + '</div>' +
                '</div>' +
                '<div class="max-record-meta">' +
                    '<span style="font-size: 10pt; color:var(--max-text-hint);">' + (e.section || '') + '</span>' +
                    Core.selectBtn(e.personid, 'sm') +
                '</div>' +
            '</div>';
    }

    // ── 2. Detail Renderer  ────────────────────────
    function _renderEmployeeDetail(res) {
        var h = res.header;
        var teams = res.teams ||[];
        var qualifications = res.qualifications || [];
        var planning = res.planning || [];
        var running = res.running ||[];
        var leaves = res.leaves ||[];

        // Generate Avatar Initials
        var initials = h.fullname ? h.fullname.split(' ').map(function(n){ return n[0]; }).join('').substring(0,2).toUpperCase() : h.personid.substring(0,2).toUpperCase();

        var html = '<button class="max-detail-back"> &#8592; Back to list</button>' +
            '<div class="max-detail-header">' +
                Core.avatar(initials, 'max-avatar-blue', 'lg') +
                '<div style="flex:1;min-width:0;">' +
                    '<div class="max-detail-title">' + h.fullname + '</div>' +
                    '<div class="max-detail-subtitle">' + (h.title || 'No Title') + '</div>' +
                    '<div class="max-detail-badges">' +
                        '<span class="max-badge max-badge-neutral">' + (h.department || 'No Dept') + '</span>' +
                        '<span class="max-badge max-badge-neutral">' + (h.section || 'No Section') + '</span>' +
                    '</div>' +
                '</div>' +
            '</div>'
            
             html += '<div style="display:flex; gap:10px; margin: 15px 0; width: 100%;">' +
            '<div style="flex:1;">' + 
                Core.selectBtn(h.personid, 'md') + 
            '</div>' +
            Core.gotoAppBtn(res.appUrl, res.applinksownernum) +
            '</div>' 


        // --- SECTION: LEAVE STATUS (Jika Sedang Cuti) ---
        var today = new Date().toISOString().split('T')[0];
        var currentLeave = leaves.filter(function(l) { return today >= l.startdate && today <= l.enddate; })[0];
        
        if (currentLeave) {
            html += '<div style="background:#FFF7E6; border:1px solid #FFE58F; padding:10px; border-radius:8px; margin-top:15px; color:#D48806; font-size: 10pt;">' +
                    '<b>Currently on Leave:</b> ' + currentLeave.leavetype + ' (' + currentLeave.description + ') ' +
                    'until ' + Core.formatDate(currentLeave.enddate) + '</div>';
        }

        // =============================================================
        // --- SECTION: PERSONAL & JOB INFO (WITH WA ICON) ---
        // =============================================================
        
        var rawPhone = h.primaryphone || '';
        var phoneHtml = '—'; 
        
        var waLogoUrl = 'https://static.vecteezy.com/system/resources/thumbnails/018/930/748/small_2x/whatsapp-logo-whatsapp-icon-whatsapp-transparent-free-png.png';

        if (rawPhone) {
            var cleanPhone = rawPhone.replace(/\D/g, '');
            
            if (cleanPhone.length > 5) {
                if (cleanPhone.startsWith("0")) {
                    cleanPhone = "62" + cleanPhone.slice(1);
                }
                
                var waLink = 'https://wa.me/' + cleanPhone;
                
                // Gunakan <img> untuk logo WhatsApp
                var waIcon = '<img src="' + waLogoUrl + '" style="width: 36px; height: 36px; vertical-align: middle; margin-right: 6px; margin-bottom: 2px;">';
                
                phoneHtml = '<a href="' + waLink + '" target="_blank" class="max-contact-link" title="Chat via WhatsApp" style="color:var(--max-primary); text-decoration:none; font-weight:600; display: inline-flex; align-items: center;">' + 
                            waIcon + rawPhone + '</a>';
            } else {
                phoneHtml = rawPhone;
            }
        }

        html += '<div class="max-section-label">Employment Information</div>' +
            '<div class="max-field-grid">' +
                '<div class="max-field-item"><div class="max-field-label">Person ID</div><div class="max-field-value max-mono">' + h.personid + '</div></div>' +
                '<div class="max-field-item"><div class="max-field-label">Hire Date</div><div class="max-field-value">' + Core.formatDate(h.hiredate) + '</div></div>' +
                '<div class="max-field-item"><div class="max-field-label">Supervisor</div><div class="max-field-value">' + (h.supervisor || '—') + '</div></div>' +
                '<div class="max-field-item"><div class="max-field-label">Contact</div><div class="max-field-value">' + phoneHtml + '</div></div>' +
                
                '<div class="max-field-item max-field-full"><div class="max-field-label">Email</div><div class="max-field-value">' + (h.primaryemail || '—') + '</div></div>' +
            '</div>';

        // --- SECTION: TEAM MEMBERSHIP ---
        html += '<div class="max-section-label">Team Membership</div>';
        if (teams.length > 0) {
            html += '<div class="max-team-container">';
            teams.forEach(function(t) {
                var leaderClass = (t.leader === 'Y') ? ' is-leader' : '';
                var leaderLabel = (t.leader === 'Y') ? ' (Leader)' : '';
                html += '<span class="max-team-chip' + leaderClass + '">' + t.teamnum + leaderLabel + '</span>';
            });
            html += '</div>';
        } else {
            html += '<div style="font-size: 10pt; color:var(--max-text-hint);">Not assigned to any team.</div>';
        }

        // --- SECTION: QUALIFICATIONS ---
        html += '<div class="max-section-label">Qualifications & Professional Skills</div>';
        if (qualifications && qualifications.length > 0) {
            html += '<div class="max-qual-container">';
            qualifications.forEach(function(q) {
                var skillLabel = q.skill ? q.skill : 'Qualified';
                html += '<div class="max-qual-card">' +
                            '<div class="max-qual-header">' +
                                '<div class="max-qual-name"><span>&#127891;</span> ' + q.qualification + '</div>' +
                                '<span class="max-qual-skill-tag">' + skillLabel + '</span>' +
                            '</div>' +
                            '<div class="max-qual-description">' + (q.description || 'No detailed description provided.') + '</div>';

                if (q.certificate) {
                    html += '<div class="max-qual-cert-badge"><span>&#9989;</span> Certified: ' + q.certificate + '</div>';
                } else {
                    html += '<div class="max-qual-no-cert">No physical certificate attached</div>';
                }
                html += '</div>'; 
            });
            html += '</div>';
        } else {
            html += '<div class="max-no-result" style="padding:10px 0;">No qualifications or skills found for this employee.</div>';
        }

        // --- SECTION: WORKLOAD - PLANNING ---
        html += '<div class="max-section-label">Work Planning (Allocated)</div>';
        if (planning.length > 0) {
            planning.forEach(function(p) {
                html += '<div class="max-list-record max-workload-planning" style="margin-bottom:6px; cursor:default;">' +
                        '<div class="max-record-info">' +
                            '<div class="max-record-id">' + p.wonum + '</div>' +
                            '<div class="max-record-desc">Schedule: ' + Core.formatDate(p.startdate) + ' - ' + Core.formatDate(p.enddate) + '</div>' +
                        '</div>' +
                        '<div class="max-record-meta">' +
                            '<span style="font-weight:700; color:var(--max-text-main);">' + p.reghours + 'h</span>' +
                            '<span style="font-size: 10pt;">' + Core.formatCurrency(p.prelinecost) + '</span>' +
                        '</div></div>';
            });
        } else {
            html += '<div style="font-size: 10pt; color:var(--max-text-hint);">No upcoming work planning.</div>';
        }

        // --- SECTION: WORKLOAD - RUNNING (HISTORICAL) ---
        html += '<div class="max-section-label">Recent Work Execution (Labor Trans)</div>';
        if (running.length > 0) {
            html += '<div style="display:flex; flex-direction:column; gap:8px;">';
            running.forEach(function(r) {
                html += '<div class="max-list-record max-workload-running" style="margin-bottom:0; cursor:default;">' +
                        '<div class="max-record-info">' +
                            '<div class="max-record-id">' + r.wonum + '</div>' +
                            '<div class="max-record-desc">' + Core.formatDate(r.startdate) + ' | ' + r.reghours + 'h (OT: ' + (r.extendedhours || 0) + 'h)</div>' +
                        '</div>' +
                        '<div class="max-record-meta" style="text-align:right;">' +
                            '<span style="color:var(--max-success); font-weight:700;">' + Core.formatCurrency(r.linecost) + '</span>' +
                            '<span style="font-size: 10pt; color:var(--max-text-hint);">Cost/h: ' + Core.formatCurrency(r.unitcost) + '</span>' +
                        '</div></div>';
            });
            html += '</div>';
        } else {
            html += '<div style="font-size: 10pt; color:var(--max-text-hint);">No recent labor transactions.</div>';
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
                name: 'Employee',
                // Daftarkan fungsi fetchList ke engine agar Debounce jalan
                onSearchFn: function(q) { self.fetchList(q); },
                renderCardFn: _renderEmployeeCard
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

            // GANTI Core.filter DENGAN Core.serverSearch
            $(document).off('input', '#max-emp-search').on('input', '#max-emp-search', function () {
                Core.serverSearch($(this).val());
            });
        },

        fetchList: function (searchQuery) {
            Core.showLoading('#max-records-container');
            
            // Sertakan parameter search ke server
            var payload = { 
                getDrawerEmployeeData: { 
                    action: 'list', 
                    search: searchQuery || ''
                } 
            };

            Core.request(payload, function (data) {
                // Set data dari server langsung, tidak ada filter lokal lagi
                Core.setListData(data);
            });
        },

        select: function (personid) {
            Core.state.selectedId = personid;
            Core.switchToTab('tab-detail');
            Core.showLoading('#max-detail-content');

            Core.request({ getDrawerEmployeeData: { action: 'detail', personid: personid } }, function (data) {
                _renderEmployeeDetail(data);
                Core.renderList(); // Render ulang agar highlight baris berubah
            });
        },

        loadByValue: function (val) {
            $('#max-emp-search').val(val || '');
            Core.switchToTab('tab-list');
            // Langsung hit server dengan value yang diberikan
            this.fetchList(val || '');
        }
    };

}(jQuery, window.MaxesDrawerCore));