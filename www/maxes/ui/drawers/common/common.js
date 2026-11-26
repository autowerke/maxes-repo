/* =============================================================
   MAXES NAVIGATION — common.js (ENGINE VERSION 4.0 - SERVER SEARCH)
   Global core logic: Otomatisasi UI, State, & Data Lifecycle.
   ============================================================= */

// Variabel global untuk Debounce timer
var _maxSearchTimer = null;

window.MaxesDrawerCore = {
    // ── Internal Engine State ────────────────────────────────────
    state: {
        selectable: true,
        pageSize: 20,
        currentPage: 1,
        rawData:[], // Berisi data hasil dari server (sudah terfilter dari DB jika ada query)
        moduleName: '',
        renderCardFn: null,
        onSearchFn: null, // Fungsi yang akan dipanggil saat user mengetik (nge-hit server)
        selectedId: null,
        activeModule: ''
    },
    gotoAppBtn: function (url, applinksowner) {
        if (!url) return '';
        // Ganti <a> jadi <button> untuk mematikan navigasi otomatis browser
        return '<button type="button" ' +
               'data-app-url="' + url + '" ' +
               'data-applinksownernum="' + applinksowner + '" ' +
               'class="max-select-btn max-app-link-trigger" ' +
               '<span>&#128279;</span> Go To Application</button>';
    },
    /**
     * Inisialisasi Engine. Dipanggil oleh modul saat init().
     * @param {Object} cfg - { name, renderCardFn, onSearchFn }
     */
    setup: function(cfg) {
        this.state.moduleName = cfg.name;
        this.state.renderCardFn = cfg.renderCardFn;
        this.state.onSearchFn = cfg.onSearchFn;
        this.state.currentPage = 1;
        this.state.rawData =[];
        this.state.selectedId = null;
        this.state.activeModule = cfg.activeModule;
       
        
        $('#max-records-container').html('');
        $('#max-list-count').text('');
        if($('#max-pagination-container').length) $('#max-pagination-container').html('');
    },

    // ── UI Feedback Helpers ─────────────────────────────────────
    showLoading: function(selector) {
        $(selector).html('<div class="max-placeholder"><div class="max-placeholder-icon" style="animation: spin 1s linear infinite;">&#8635;</div><div class="max-placeholder-title">Loading data...</div></div>');
    },

    showError: function(selector, msg) {
        $(selector).html('<div class="max-placeholder"><div class="max-placeholder-icon" style="color:var(--max-error)">&#9888;</div><div class="max-placeholder-title">Error Occurred</div><div class="max-page-info">'+msg+'</div></div>');
    },

    // ── AJAX Wrapper ────────────────────────────────────────────
    request: function(payload, successCallback) {
        var self = this;
        if (typeof xhr === 'undefined') {
            console.error("[Maxes] Helper xhr.js tidak ditemukan!");
            return;
        }
        xhr.commonRun(null, "json", true, payload).response(function(res) {
            if (res.status === "success") {
                successCallback(res.data);
            } else {
                self.showError('#max-records-container', res.message || 'Server error');
                self.showError('#max-detail-content', res.message || 'Server error');
            }
        });
    },

    // ── SERVER SEARCH DEBOUNCE ──────────────────────────────────
    serverSearch: function(query) {
        var self = this;
        // Bersihkan timer jika user masih mengetik
        clearTimeout(_maxSearchTimer);

        // Pasang timer baru (500ms)
        _maxSearchTimer = setTimeout(function() {
            if (typeof self.state.onSearchFn === 'function') {
                // Tampilkan loading di list
                self.showLoading('#max-records-container');
                // Panggil fungsi fetch list milik modul dengan mengirimkan query
                self.state.onSearchFn(query);
            }
        }, 500);
    },

    // ── Data Handling ───────────────────────────────────────────
    setListData: function(data) {
        // Data dari server langsung di-set sebagai rawData
        this.state.rawData = data ||[];
        this.state.currentPage = 1; // Selalu mulai dari halaman 1 untuk hasil fetch baru
        this.renderList();
    },

    // ── Rendering Engine ────────────────────────────────────────
    renderList: function() {
        var self = this;
        var $container = $('#max-records-container');
        var $countInfo = $('#max-list-count');
        if (!$container.length) return;

        var total = this.state.rawData.length;
        
        // Update count text
        var suffix = (total !== 1 ? 's' : '') + (total >= 500 ? ' (Max limit reached)' : '');
        $countInfo.text(total + ' ' + this.state.moduleName + suffix + ' found');

        if (total === 0) {
            $container.html('<div class="max-no-result">No records found.</div>');
            this.updatePaginationUI(0);
            return;
        }

        // Local Pagination Logic (membagi data yang sudah ditarik dari server)
        var start = (this.state.currentPage - 1) * this.state.pageSize;
        var end   = start + this.state.pageSize;
        var pagedData = this.state.rawData.slice(start, end);

        var html = pagedData.map(function(item) {
            return self.state.renderCardFn(item);
        }).join('');

        $container.html(html);
        this.updatePaginationUI(Math.ceil(total / this.state.pageSize));
    },

    updatePaginationUI: function(totalPages) {
        var $pageCont = $('#max-pagination-container');
        if (!$pageCont.length) {
            $('#max-records-container').after('<div id="max-pagination-container" class="max-pagination"></div>');
            $pageCont = $('#max-pagination-container');
        }

        if (totalPages <= 1) {
            $pageCont.hide().html('');
            return;
        }

        var curr = this.state.currentPage;
        $pageCont.show().html(
            '<button class="max-page-btn" data-page="' + (curr - 1) + '" ' + (curr === 1 ? 'disabled' : '') + '>&#10094;</button>' +
            '<span class="max-page-info">' + curr + ' / ' + totalPages + '</span>' +
            '<button class="max-page-btn" data-page="' + (curr + 1) + '" ' + (curr === totalPages ? 'disabled' : '') + '>&#10095;</button>'
        );
    },

    // ── Navigation & Utils ──────────────────────────────────────
    switchToTab: function (tabId) {
        var $tabBtn = $('.max-tab-btn[data-tab="' + tabId + '"]');
        if ($tabBtn.length) {
            $tabBtn.trigger('click');
        } else {
            console.warn("[Maxes] Tab button not found:", tabId);
        }
    },

    setSelectableMode: function (canSelect) {
        this.state.selectable = canSelect;
        this.refreshSelectButtons();
    },

    refreshSelectButtons: function() {
        var $btns = $('.max-select-btn');
        if (this.state.selectable) {
            $btns.removeClass('max-select-btn--disabled').prop('disabled', false);
        } else {
            $btns.addClass('max-select-btn--disabled').prop('disabled', true);
        }
    },

    selectValue: function (value) {
        if (!this.state.selectable) return;
        if (window.MaxesNavigation && typeof window.MaxesNavigation.applyValue === 'function') {
            window.MaxesNavigation.applyValue(value);
        }
    },

    loadSpecificRecord: function (drawerName, value) {
        // Mapping folder ke Nama Object
        var targetObject = moduleMapping[drawerName];
        // HANYA panggil loadByValue milik modul yang sesuai dengan drawer yang dibuka
        if (targetObject && window[targetObject] && typeof window[targetObject].loadByValue === 'function') {
            window[targetObject].loadByValue(value);
        } else {
            console.warn("[Maxes] No active module object found for:", drawerName);
        }
    },

    // ── Data Formatters ──────────────────────────────────────────
    selectBtn: function (value, size) {
        var disabled = !this.state.selectable;
        var cls = 'max-select-btn max-select-btn--' + (size || 'sm') + (disabled ? ' max-select-btn--disabled' : '');
        var attr = disabled ? ' disabled' : '';
        var label = (size === 'md') ? '&#10003; Select this value' : '&#10003;';
        return '<button class="' + cls + '" data-select-value="' + value + '" ' + attr + '>' + label + '</button>';
    },

    formatCurrency: function (n) {
        if (!n || isNaN(n)) return 'Rp 0';
        if (n >= 1000000000000) return 'Rp ' + (n / 1000000000000).toFixed(2) + ' T';
        if (n >= 1000000000)    return 'Rp ' + (n / 1000000000).toFixed(1) + ' B';
        if (n >= 1000000)       return 'Rp ' + (n / 1000000).toFixed(1) + ' M';
        return 'Rp ' + Math.round(n).toLocaleString('id-ID');
    },

    formatDate: function (isoStr) {
        if (!isoStr) return '—';
        var dateOnly = isoStr.split(' ')[0];
        var p = dateOnly.split('-');
        return p.length !== 3 ? isoStr : p[2] + '/' + p[1] + '/' + p[0];
    },

    statusBadge: function (status) {
        var map = { 'Active':'max-badge-active', 'Inactive':'max-badge-inactive', 'Operational':'max-badge-op', 'Decommissioned':'max-badge-decom' };
        return '<span class="max-badge ' + (map[status] || 'max-badge-neutral') + '">' + status + '</span>';
    },

    avatar: function (initials, avatarClass, size) {
        var sz = (size === 'lg') ? '44px' : '36px';
        var fs = (size === 'lg') ? '14px' : '11px';
        return '<div class="max-record-avatar ' + avatarClass + '" style="width:' + sz + '; height:' + sz + '; font-size:' + fs + '; line-height:' + sz + '; text-align:center; display:flex;">' + initials + '</div>';
    }
};

// ── Global Event Handlers ────────────────────────────────────────

// 1. Klik Tombol Select
$(document).off('click', '.max-select-btn').on('click', '.max-select-btn', function (e) {
    var val = $(this).attr('data-select-value');
    if (typeof val === 'undefined' || val === false) {
        return; 
    }
    e.preventDefault();
    e.stopPropagation();
    if ($(this).hasClass('max-select-btn--disabled') || $(this).prop('disabled')) return;
    window.MaxesDrawerCore.selectValue($(this).attr('data-select-value'));
});

// 2. Klik Pagination
$(document).off('click', '.max-page-btn').on('click', '.max-page-btn', function() {
    if($(this).is(':disabled')) return;
    window.MaxesDrawerCore.state.currentPage = parseInt($(this).data('page'));
    window.MaxesDrawerCore.renderList();
    $('.max-drawer-body').animate({ scrollTop: 0 }, 'fast');
});

// 3. Klik Tab
$(document).off('click', '.max-tab-btn').on('click', '.max-tab-btn', function () {
    var targetTabId = $(this).attr('data-tab');
    var $container = $(this).closest('.max-drawer-container');
    
    $container.find('.max-tab-btn').removeClass('active');
    $container.find('.max-tab-content').removeClass('active');
    
    $(this).addClass('active');
    var $content = $container.find('#' + targetTabId);
    if($content.length === 0) $content = $('#' + targetTabId);
    $content.addClass('active');
});

// 4. Klik Back to List
$(document).off('click', '.max-detail-back').on('click', '.max-detail-back', function (e) {
    e.preventDefault();
    window.MaxesDrawerCore.switchToTab('tab-list');
});

// Animasi Spin untuk Loading
if ($('style#max-spin-anim').length === 0) {
    $("<style id='max-spin-anim'>").text("@keyframes spin { 100% { transform:rotate(360deg); } }").appendTo("head");
}



/* === Update bagian Event Handlers di paling bawah common.js === */

// 1. Khusus Handler "Go To Application" (Context Aware Navigation)
$(document).off('click', '.max-app-link-trigger').on('click', '.max-app-link-trigger', function (e) {
    e.preventDefault();
    e.stopPropagation();

    var $btn = $(this);
    var targetUrl = $btn.attr('data-app-url');
    var ownerNum  = $btn.attr('data-applinksownernum');

    // URUTAN EKSEKUSI YANG AMAN (Anti-Race Condition):
    
    // A. Set Cookie dulu (Synchronous)
    if (typeof setCookie === 'function' && ownerNum) {
        setCookie("applinksownernum", ownerNum);
        console.log("[Maxes Engine] Context Locked:", ownerNum);
    }

    // B. Beri jeda micro-second (opsional) atau langsung buka window
    // Menggunakan window.open setelah cookie di-set menjamin tab baru akan membaca cookie yang terbaru
    if (targetUrl) {
        window.open(targetUrl, '_blank');
    }
});

// 2. Khusus Handler "Select Value" (Tombol Centang Biru)
$(document).off('click', '.max-select-btn:not(.max-app-link-trigger)').on('click', '.max-select-btn:not(.max-app-link-trigger)', function (e) {
    var $el = $(this);
    var val = $el.attr('data-select-value');

    if (typeof val === 'undefined' || val === false) return;

    e.preventDefault();
    e.stopPropagation();

    if ($el.hasClass('max-select-btn--disabled') || $el.prop('disabled')) return;
    
    window.MaxesDrawerCore.selectValue(val);
});