(function () {
    document.documentElement.classList.add('js');

    function debounce(callback, wait) {
        var timeoutId;
        return function () {
            var args = arguments;
            var context = this;
            window.clearTimeout(timeoutId);
            timeoutId = window.setTimeout(function () {
                callback.apply(context, args);
            }, wait);
        };
    }

    function prefersReducedMotion() {
        return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    }

    function getSmoothScrollBehavior() {
        return prefersReducedMotion() ? 'auto' : 'smooth';
    }

    function normalizeSearchValue(value) {
        var text = (value || '').toString().toLowerCase();
        if (typeof text.normalize === 'function') {
            text = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }

        return text.replace(/\s+/g, ' ').trim();
    }

    function setElementVisible(element, visible) {
        if (!element) {
            return;
        }

        element.classList.toggle('is-hidden', !visible);
        element.classList.toggle('hidden', !visible);
        element.setAttribute('aria-hidden', visible ? 'false' : 'true');
    }

    function syncBodyModalState() {
        var openModalCount = document.querySelectorAll('.admin-form-editor-modal:not(.is-hidden), .admin-create-form-modal:not(.is-hidden)').length;
        document.body.classList.toggle('is-modal-open', openModalCount > 0);
    }

    function setupAutoDismissAlerts() {
        var alerts = document.querySelectorAll('[data-auto-dismiss]');
        alerts.forEach(function (alert) {
            var delay = Number(alert.getAttribute('data-auto-dismiss')) || 4000;
            window.setTimeout(function () {
                alert.classList.add('is-fading');
                window.setTimeout(function () {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 260);
            }, delay);
        });
    }

    function setupTableFilter() {
        var input = document.querySelector('[data-filter-input]');
        if (!input) {
            return;
        }

        var target = input.getAttribute('data-filter-target') || '';
        if (!target) {
            return;
        }

        var rows = Array.prototype.slice.call(document.querySelectorAll(target));
        var rowMeta = rows.map(function (row) {
            return {
                element: row,
                isPlaceholder: !!row.querySelector('td[colspan]'),
                searchText: normalizeSearchValue(row.textContent || ''),
            };
        });
        var empty = document.querySelector('[data-filter-empty]');
        var count = document.querySelector('[data-filter-count]');
        var purposeFilter = document.querySelector('[data-filter-purpose]');
        var linkScopeFilter = document.querySelector('[data-filter-link-scope]');
        var windowStatusFilter = document.querySelector('[data-filter-window-status]');
        var sortSelect = document.querySelector('[data-sort-select]');
        var resetButton = document.querySelector('[data-filter-reset]');
        var tbody = rows.length ? rows[0].parentNode : null;

        function applySort() {
            if (!sortSelect || !tbody || !rows.length) {
                return;
            }

            var mode = sortSelect.value || 'updated_desc';
            var sortableRows = rows.filter(function (row) {
                return row.hasAttribute('data-sort-title') || row.hasAttribute('data-sort-updated');
            });

            if (!sortableRows.length) {
                return;
            }

            sortableRows.sort(function (a, b) {
                var aTitle = (a.getAttribute('data-sort-title') || '').toLowerCase();
                var bTitle = (b.getAttribute('data-sort-title') || '').toLowerCase();
                var aUpdated = Number(a.getAttribute('data-sort-updated') || 0);
                var bUpdated = Number(b.getAttribute('data-sort-updated') || 0);

                if (mode === 'title_asc') {
                    return aTitle.localeCompare(bTitle);
                }

                if (mode === 'title_desc') {
                    return bTitle.localeCompare(aTitle);
                }

                if (mode === 'updated_asc') {
                    return aUpdated - bUpdated;
                }

                return bUpdated - aUpdated;
            });

            sortableRows.forEach(function (row) {
                tbody.appendChild(row);
            });
        }

        function applyFilter() {
            var keyword = normalizeSearchValue(input.value);
            var visibleCount = 0;
            var selectedPurpose = purposeFilter ? purposeFilter.value : '';
            var selectedLinkScope = linkScopeFilter ? linkScopeFilter.value : '';
            var selectedWindowStatus = windowStatusFilter ? windowStatusFilter.value : '';

            applySort();

            rowMeta.forEach(function (meta) {
                var row = meta.element;
                var keywordMatch = keyword === '' || meta.searchText.indexOf(keyword) !== -1;
                var purposeMatch = selectedPurpose === '' || (row.getAttribute('data-purpose') || '') === selectedPurpose;
                var linkScopeMatch = selectedLinkScope === '' || (row.getAttribute('data-link-scope') || '') === selectedLinkScope;
                var windowStatusMatch = selectedWindowStatus === '' || (row.getAttribute('data-window-status') || '') === selectedWindowStatus;

                var show;
                if (meta.isPlaceholder) {
                    show = keyword === '' && selectedPurpose === '' && selectedLinkScope === '' && selectedWindowStatus === '';
                } else {
                    show = keywordMatch && purposeMatch && linkScopeMatch && windowStatusMatch;
                }

                row.classList.toggle('is-hidden', !show);
                if (show && !meta.isPlaceholder) {
                    visibleCount += 1;
                }
            });

            setElementVisible(empty, visibleCount === 0);

            if (count) {
                count.textContent = visibleCount + ' entri ditampilkan';
            }
        }

        var applyFilterDebounced = debounce(applyFilter, 120);

        input.addEventListener('input', applyFilterDebounced);
        input.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            event.preventDefault();
            input.value = '';
            applyFilter();
        });

        if (purposeFilter) {
            purposeFilter.addEventListener('change', applyFilter);
        }

        if (linkScopeFilter) {
            linkScopeFilter.addEventListener('change', applyFilter);
        }

        if (windowStatusFilter) {
            windowStatusFilter.addEventListener('change', applyFilter);
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', applyFilter);
        }

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                input.value = '';
                if (purposeFilter) {
                    purposeFilter.value = '';
                }
                if (linkScopeFilter) {
                    linkScopeFilter.value = '';
                }
                if (windowStatusFilter) {
                    windowStatusFilter.value = '';
                }
                if (sortSelect) {
                    sortSelect.value = 'updated_desc';
                }

                applyFilter();
                input.focus();
            });
        }

        applyFilter();
    }

    function setupStickyOffsets() {
        var root = document.documentElement;
        var topbar = document.querySelector('.topbar');
        if (!topbar) {
            return;
        }

        var resizeTimer;

        function syncOffset() {
            var offset = Math.max(70, topbar.offsetHeight + 10);
            root.style.setProperty('--sticky-offset', offset + 'px');
        }

        function syncOffsetOnResize() {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(syncOffset, 80);
        }

        syncOffset();
        window.addEventListener('resize', syncOffsetOnResize);
    }

    function setupAdminSecondaryPanels() {
        var panel = document.querySelector('[data-admin-secondary-panels]');
        var toggle = document.querySelector('[data-toggle-admin-secondary]');
        if (!panel || !toggle) {
            return;
        }

        var compactMedia = window.matchMedia('(max-width: 900px)');

        function setExpandedState(expanded) {
            panel.classList.toggle('is-expanded', expanded);
            toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            toggle.textContent = expanded ? 'Sembunyikan Ringkasan' : 'Tampilkan Ringkasan';
        }

        function syncByViewport() {
            if (compactMedia.matches) {
                setExpandedState(false);
                return;
            }

            setExpandedState(true);
        }

        toggle.addEventListener('click', function () {
            setExpandedState(!panel.classList.contains('is-expanded'));
        });

        if (typeof compactMedia.addEventListener === 'function') {
            compactMedia.addEventListener('change', syncByViewport);
        } else if (typeof compactMedia.addListener === 'function') {
            compactMedia.addListener(syncByViewport);
        }

        syncByViewport();
    }

    function setupExclusiveDetails() {
        var details = document.querySelectorAll('details[data-exclusive-group]');
        details.forEach(function (currentDetail) {
            currentDetail.addEventListener('toggle', function () {
                if (!currentDetail.open) {
                    return;
                }

                var groupName = currentDetail.getAttribute('data-exclusive-group') || '';
                if (!groupName) {
                    return;
                }

                var groupSelector = 'details[data-exclusive-group="' + groupName + '"]';
                document.querySelectorAll(groupSelector).forEach(function (otherDetail) {
                    if (otherDetail !== currentDetail) {
                        otherDetail.open = false;
                    }
                });
            });
        });
    }

    function setupCreateFormModal() {
        var modal = document.querySelector('[data-create-form-modal]');
        var triggers = document.querySelectorAll('[data-open-create-form-modal]');
        if (!modal || !triggers.length) {
            return;
        }

        var closeButtons = modal.querySelectorAll('[data-close-create-form-modal]');
        var createForm = modal.querySelector('[data-create-form-modal-form]');
        var focusField = modal.querySelector('select[name="category_id"]');
        var purposeField = modal.querySelector('select[name="purpose"]');
        var linkScopeField = modal.querySelector('[data-form-link-scope]');
        var urlField = modal.querySelector('[data-form-url-input]');

        var lastTrigger = null;
        var modalKeyDownHandler = null;

        if (!modal.hasAttribute('role')) {
            modal.setAttribute('role', 'dialog');
        }

        if (!modal.hasAttribute('aria-modal')) {
            modal.setAttribute('aria-modal', 'true');
        }

        if (!modal.hasAttribute('tabindex')) {
            modal.setAttribute('tabindex', '-1');
        }

        closeButtons.forEach(function (btn) {
            if (!btn.hasAttribute('aria-label')) {
                btn.setAttribute('aria-label', 'Tutup');
            }
        });

        function trapTabKey(e) {
            if (e.key !== 'Tab') return;
            var focusable = modal.querySelectorAll('a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])');
            focusable = Array.prototype.filter.call(focusable, function (el) { return el.offsetParent !== null; });
            if (!focusable.length) return;
            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }

        function triggerFieldSync(field, eventName) {
            if (!field) {
                return;
            }

            field.dispatchEvent(new Event(eventName, { bubbles: true }));
        }

        function openModal() {
            if (createForm && typeof createForm.reset === 'function') {
                createForm.reset();
            }

            triggerFieldSync(purposeField, 'change');
            triggerFieldSync(linkScopeField, 'change');
            triggerFieldSync(urlField, 'input');

            modal.classList.remove('is-hidden');
            modal.setAttribute('aria-hidden', 'false');
            // remember trigger to restore focus
            try { lastTrigger && lastTrigger.focus && lastTrigger.focus(); } catch (e) {}
            // focus modal container for screen readers
            modal.focus();
            // trap TAB inside modal
            modalKeyDownHandler = function (ev) { trapTabKey(ev); };
            modal.addEventListener('keydown', modalKeyDownHandler);
            syncBodyModalState();

            window.setTimeout(function () {
                if (focusField) {
                    focusField.focus();
                }
            }, 90);
        }

        function closeModal() {
            modal.classList.add('is-hidden');
            modal.setAttribute('aria-hidden', 'true');
            // remove trap
            if (modalKeyDownHandler) {
                modal.removeEventListener('keydown', modalKeyDownHandler);
                modalKeyDownHandler = null;
            }
            syncBodyModalState();
            // restore focus to triggering element
            if (lastTrigger && typeof lastTrigger.focus === 'function') {
                lastTrigger.focus();
            }
            lastTrigger = null;
        }

        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (ev) {
                lastTrigger = ev.currentTarget || trigger;
                openModal();
            });
        });

        closeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal();
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            if (modal.classList.contains('is-hidden')) {
                return;
            }

            closeModal();
        });
    }

    function setupFormEditorQuickAccess() {
        var modal = document.querySelector('[data-form-editor-modal]');
        var triggers = document.querySelectorAll('[data-open-form-editor]');
        if (!modal || !triggers.length) {
            return;
        }

        var modalMeta = modal.querySelector('[data-form-editor-modal-meta]');
        var categoryLabel = modal.querySelector('[data-modal-field-category]');
        var idField = modal.querySelector('[data-modal-field-id]');
        var archiveIdField = modal.querySelector('[data-modal-archive-id]');
        var titleField = modal.querySelector('[data-modal-field-title]');
        var purposeField = modal.querySelector('[data-modal-field-purpose]');
        var linkScopeField = modal.querySelector('[data-modal-field-link-scope]');
        var urlField = modal.querySelector('[data-modal-field-url]');
        var startField = modal.querySelector('[data-modal-field-effective-start]');
        var endField = modal.querySelector('[data-modal-field-effective-end]');
        var notesField = modal.querySelector('[data-modal-field-notes]');
        var activeField = modal.querySelector('[data-modal-field-is-active]');
        var closeButtons = modal.querySelectorAll('[data-close-form-editor-modal]');

        var lastTrigger = null;
        var modalKeyDownHandler = null;

        if (!modal.hasAttribute('role')) {
            modal.setAttribute('role', 'dialog');
        }

        if (!modal.hasAttribute('aria-modal')) {
            modal.setAttribute('aria-modal', 'true');
        }

        if (!modal.hasAttribute('tabindex')) {
            modal.setAttribute('tabindex', '-1');
        }

        closeButtons.forEach(function (btn) {
            if (!btn.hasAttribute('aria-label')) {
                btn.setAttribute('aria-label', 'Tutup');
            }
        });

        function trapTabKey(e) {
            if (e.key !== 'Tab') return;
            var focusable = modal.querySelectorAll('a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])');
            focusable = Array.prototype.filter.call(focusable, function (el) { return el.offsetParent !== null; });
            if (!focusable.length) return;
            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }

        function triggerFieldSync(field, eventName) {
            if (!field) {
                return;
            }

            field.dispatchEvent(new Event(eventName, { bubbles: true }));
        }

        function openModal() {
            modal.classList.remove('is-hidden');
            modal.setAttribute('aria-hidden', 'false');
            // focus management
            modal.focus();
            modalKeyDownHandler = function (ev) { trapTabKey(ev); };
            modal.addEventListener('keydown', modalKeyDownHandler);
            syncBodyModalState();

            window.setTimeout(function () {
                if (!titleField) {
                    return;
                }

                titleField.focus();
                if (typeof titleField.select === 'function') {
                    titleField.select();
                }
            }, 90);
        }

        function closeModal() {
            modal.classList.add('is-hidden');
            modal.setAttribute('aria-hidden', 'true');
            if (modalKeyDownHandler) {
                modal.removeEventListener('keydown', modalKeyDownHandler);
                modalKeyDownHandler = null;
            }
            syncBodyModalState();
            if (lastTrigger && typeof lastTrigger.focus === 'function') {
                lastTrigger.focus();
            }
            lastTrigger = null;
        }

        function setInputValue(input, value) {
            if (!input) {
                return;
            }

            input.value = value || '';
        }

        function openEditorByTrigger(trigger) {
            if (!trigger) {
                return;
            }

            var formId = trigger.getAttribute('data-form-id') || trigger.getAttribute('data-open-form-editor-id') || '';
            var category = trigger.getAttribute('data-form-category') || '-';
            var title = trigger.getAttribute('data-form-title') || '';
            var purpose = trigger.getAttribute('data-form-purpose') || 'pengajuan';
            var linkScope = trigger.getAttribute('data-form-link-scope') || 'public';
            var formUrl = trigger.getAttribute('data-form-url') || '';
            var gdriveFolderId = trigger.getAttribute('data-form-gdrive-folder-id') || '';
            var effectiveStart = trigger.getAttribute('data-form-effective-start') || '';
            var effectiveEnd = trigger.getAttribute('data-form-effective-end') || '';
            var notes = trigger.getAttribute('data-form-notes') || '';
            var isActive = trigger.getAttribute('data-form-is-active') === '1';

            setInputValue(idField, formId);
            setInputValue(archiveIdField, formId);
            setInputValue(titleField, title);
            setInputValue(purposeField, purpose);
            setInputValue(linkScopeField, linkScope);
            setInputValue(urlField, formUrl);
            setInputValue(modal.querySelector('[data-modal-field-gdrive-folder-id]'), gdriveFolderId);
            setInputValue(startField, effectiveStart);
            setInputValue(endField, effectiveEnd);
            setInputValue(notesField, notes);

            if (activeField) {
                activeField.checked = isActive;
            }

            if (categoryLabel) {
                categoryLabel.textContent = category || '-';
            }

            if (modalMeta) {
                if (formId) {
                    modalMeta.textContent = 'Form ID #' + formId + ' - Kategori: ' + (category || '-');
                } else {
                    modalMeta.textContent = 'Siap mengubah formulir terpilih.';
                }
            }

            triggerFieldSync(purposeField, 'change');
            triggerFieldSync(linkScopeField, 'change');
            triggerFieldSync(startField, 'change');
            triggerFieldSync(endField, 'change');
            triggerFieldSync(urlField, 'input');

            openModal();
        }

        function openEditorById(formId) {
            if (!formId) {
                return;
            }

            var trigger = document.querySelector('[data-open-form-editor-id="' + formId + '"]');
            if (!trigger) {
                return;
            }

            openEditorByTrigger(trigger);
        }

        triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (ev) {
                lastTrigger = ev.currentTarget || trigger;
                openEditorByTrigger(trigger);
            });
        });

        closeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                closeModal();
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            if (modal.classList.contains('is-hidden')) {
                return;
            }

            closeModal();
        });

        var hash = (window.location.hash || '').trim();
        if (hash.indexOf('#edit-link-') === 0) {
            openEditorById(hash.replace('#edit-link-', ''));
        }
    }

    function setupAdminFormUrlActions() {
        var copyButtons = document.querySelectorAll('[data-copy-form-url]');
        var copyInputButtons = document.querySelectorAll('[data-copy-form-url-input]');
        var openInputButtons = document.querySelectorAll('[data-open-url-from-input]');

        if (!copyButtons.length && !copyInputButtons.length && !openInputButtons.length) {
            return;
        }

        function showButtonFeedback(button, success) {
            if (!button) {
                return;
            }

            var originalText = button.getAttribute('data-copy-original-label') || (button.textContent || '').trim();
            if (!button.getAttribute('data-copy-original-label')) {
                button.setAttribute('data-copy-original-label', originalText);
            }

            button.textContent = success ? 'Tersalin' : 'Gagal Salin';
            button.classList.toggle('is-success', success);
            button.classList.toggle('is-error', !success);

            window.setTimeout(function () {
                button.textContent = originalText;
                button.classList.remove('is-success', 'is-error');
            }, 1400);
        }

        function copyText(text, callback) {
            var value = (text || '').trim();
            if (!value) {
                callback(false);
                return;
            }

            if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
                navigator.clipboard.writeText(value).then(function () {
                    callback(true);
                }).catch(function () {
                    callback(false);
                });
                return;
            }

            var temp = document.createElement('textarea');
            temp.value = value;
            temp.setAttribute('readonly', 'readonly');
            temp.style.position = 'fixed';
            temp.style.left = '-9999px';
            document.body.appendChild(temp);
            temp.select();

            var copied = false;
            try {
                copied = document.execCommand('copy');
            } catch (error) {
                copied = false;
            }

            document.body.removeChild(temp);
            callback(copied);
        }

        copyButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var url = button.getAttribute('data-copy-form-url-value') || '';
                copyText(url, function (success) {
                    showButtonFeedback(button, success);
                });
            });
        });

        copyInputButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var wrapper = button.closest('.form-group') || button.closest('form');
                var input = wrapper ? wrapper.querySelector('[data-edit-url-input]') : null;
                var value = input ? input.value : '';

                copyText(value, function (success) {
                    showButtonFeedback(button, success);
                });
            });
        });

        openInputButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var wrapper = button.closest('.form-group') || button.closest('form');
                var input = wrapper ? wrapper.querySelector('[data-edit-url-input]') : null;
                var form = button.closest('form');
                var scopeSelect = form ? form.querySelector('[data-form-link-scope]') : null;
                var purposeSelect = form ? form.querySelector('select[name="purpose"]') : null;
                var scope = scopeSelect ? (scopeSelect.value || 'public') : 'public';
                var purpose = purposeSelect ? (purposeSelect.value || 'pengajuan') : 'pengajuan';
                var rawValue = input ? input.value : '';
                var normalized = normalizeGoogleFormUrl(rawValue);
                var candidate = normalized || rawValue;

                if (!isAllowedGoogleFormUrl(candidate, scope, purpose)) {
                    if (input) {
                        if (purpose === 'monitoring' && scope === 'private') {
                            input.setCustomValidity('URL monitoring private tidak valid. Gunakan docs.google.com ... /viewform, /viewanalytics, atau spreadsheet /edit berbasis HTTPS.');
                        } else if (scope === 'private') {
                            input.setCustomValidity('URL private tidak valid. Gunakan docs.google.com ... /viewform berbasis HTTPS.');
                        } else if (purpose === 'monitoring') {
                            input.setCustomValidity('URL monitoring tidak valid. Gunakan docs.google.com ... /viewform atau spreadsheet /edit berbasis HTTPS.');
                        } else {
                            input.setCustomValidity('URL formulir tidak valid. Gunakan link Google Form publik berbasis HTTPS.');
                        }

                        input.reportValidity();
                        window.setTimeout(function () {
                            input.setCustomValidity('');
                        }, 1200);
                    }
                    return;
                }

                if (input && normalized) {
                    input.value = normalized;
                }

                window.open(normalized || candidate, '_blank', 'noopener');
            });
        });
    }

    function setupSavedFormSpotlight() {
        var alert = document.querySelector('[data-saved-form-id]');
        if (!alert) {
            return;
        }

        var savedFormId = (alert.getAttribute('data-saved-form-id') || '').trim();
        if (!savedFormId || savedFormId === '0') {
            return;
        }

        var row = document.querySelector('#form-row-' + savedFormId);
        if (!row) {
            return;
        }

        row.classList.add('form-row-updated');
        row.scrollIntoView({ behavior: getSmoothScrollBehavior(), block: 'center' });
    }

    function setupConfirmButtons() {
        var forms = document.querySelectorAll('[data-confirm]');
        forms.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                var message = form.getAttribute('data-confirm') || 'Lanjutkan proses ini?';
                if (!window.confirm(message)) {
                    event.preventDefault();
                }
            });
        });
    }

    function setupSearchShortcut() {
        var input = document.querySelector('[data-filter-input]') || document.querySelector('[data-portal-filter-input]');
        if (!input) {
            return;
        }

        document.addEventListener('keydown', function (event) {
            if (event.key !== '/' || event.ctrlKey || event.metaKey || event.altKey) {
                return;
            }

            var activeElement = document.activeElement;
            if (!activeElement) {
                return;
            }

            var tagName = activeElement.tagName;
            var isTypingContext = tagName === 'INPUT' || tagName === 'TEXTAREA' || activeElement.isContentEditable;
            if (isTypingContext) {
                return;
            }

            event.preventDefault();
            input.focus();
            if (typeof input.select === 'function') {
                input.select();
            }
        });
    }

    function setupSectionNavigation() {
        var nav = document.querySelector('[data-section-nav]');
        if (!nav) {
            return;
        }

        var links = nav.querySelectorAll('a[href^="#"]');
        if (!links.length) {
            return;
        }

        var targetMap = new Map();
        links.forEach(function (link) {
            var href = link.getAttribute('href') || '';
            var target = document.querySelector(href);
            if (target) {
                targetMap.set(target, link);
            }

            link.addEventListener('click', function () {
                links.forEach(function (item) {
                    item.classList.remove('is-active');
                });
                link.classList.add('is-active');
            });
        });

        if (!targetMap.size || !('IntersectionObserver' in window)) {
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                var activeLink = targetMap.get(entry.target);
                if (!activeLink) {
                    return;
                }

                links.forEach(function (item) {
                    item.classList.remove('is-active');
                });
                activeLink.classList.add('is-active');
            });
        }, {
            rootMargin: '-42% 0px -48% 0px',
            threshold: 0.1,
        });

        targetMap.forEach(function (link, target) {
            observer.observe(target);
        });
    }

    function setupHeroCarousel() {
        var carousels = document.querySelectorAll('[data-hero-carousel]');
        if (!carousels.length) {
            return;
        }

        carousels.forEach(function (carousel) {
            var track = carousel.querySelector('[data-carousel-track]');
            if (!track) {
                return;
            }

            var slides = Array.prototype.slice.call(track.querySelectorAll('[data-carousel-slide]'));
            if (slides.length <= 1) {
                return;
            }

            var prevButton = carousel.querySelector('[data-carousel-prev]');
            var nextButton = carousel.querySelector('[data-carousel-next]');
            var dots = Array.prototype.slice.call(carousel.querySelectorAll('[data-carousel-dot]'));
            var autoplayDelay = 5400;
            var activeIndex = 0;
            var autoplayId = null;
            var hasReducedMotion = prefersReducedMotion();
            var isVisibleInViewport = true;

            function goToSlide(index) {
                if (!slides.length) {
                    return;
                }

                var maxIndex = slides.length - 1;
                if (index < 0) {
                    activeIndex = maxIndex;
                } else if (index > maxIndex) {
                    activeIndex = 0;
                } else {
                    activeIndex = index;
                }

                track.style.transform = 'translateX(' + (-activeIndex * 100) + '%)';

                slides.forEach(function (slide, slideIndex) {
                    var isActive = slideIndex === activeIndex;
                    slide.classList.toggle('is-active', isActive);
                    slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                });

                dots.forEach(function (dot, dotIndex) {
                    var isActiveDot = dotIndex === activeIndex;
                    dot.classList.toggle('is-active', isActiveDot);
                    dot.setAttribute('aria-selected', isActiveDot ? 'true' : 'false');
                });
            }

            function stopAutoplay() {
                if (autoplayId) {
                    window.clearInterval(autoplayId);
                    autoplayId = null;
                }
            }

            function startAutoplay() {
                if (hasReducedMotion || document.hidden || !isVisibleInViewport) {
                    return;
                }

                stopAutoplay();
                autoplayId = window.setInterval(function () {
                    goToSlide(activeIndex + 1);
                }, autoplayDelay);
            }

            if (prevButton) {
                prevButton.addEventListener('click', function () {
                    goToSlide(activeIndex - 1);
                    startAutoplay();
                });
            }

            if (nextButton) {
                nextButton.addEventListener('click', function () {
                    goToSlide(activeIndex + 1);
                    startAutoplay();
                });
            }

            dots.forEach(function (dot) {
                dot.addEventListener('click', function () {
                    var index = Number(dot.getAttribute('data-slide-index'));
                    if (Number.isNaN(index)) {
                        return;
                    }

                    goToSlide(index);
                    startAutoplay();
                });
            });

            carousel.addEventListener('mouseenter', stopAutoplay);
            carousel.addEventListener('mouseleave', startAutoplay);
            carousel.addEventListener('focusin', stopAutoplay);
            carousel.addEventListener('focusout', function (event) {
                var nextTarget = event.relatedTarget;
                if (nextTarget && carousel.contains(nextTarget)) {
                    return;
                }

                startAutoplay();
            });
            carousel.addEventListener('keydown', function (event) {
                if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    goToSlide(activeIndex - 1);
                    startAutoplay();
                }

                if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    goToSlide(activeIndex + 1);
                    startAutoplay();
                }
            });

            if ('IntersectionObserver' in window) {
                var visibilityObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        isVisibleInViewport = entry.isIntersecting;
                        if (!isVisibleInViewport) {
                            stopAutoplay();
                        } else {
                            startAutoplay();
                        }
                    });
                }, {
                    threshold: 0.15,
                });

                visibilityObserver.observe(carousel);
            }

            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    stopAutoplay();
                    return;
                }

                startAutoplay();
            });

            window.addEventListener('pagehide', stopAutoplay);

            goToSlide(0);
            startAutoplay();
        });
    }

    function setupPortalFormGate() {
        var gate = document.querySelector('[data-form-gate]');
        if (!gate) {
            return;
        }

        var showTriggers = document.querySelectorAll('[data-show-forms]');
        var hideTriggers = document.querySelectorAll('[data-hide-forms]');

        function openGate(event) {
            if (event) {
                event.preventDefault();
            }

            gate.classList.remove('is-collapsed');
            gate.setAttribute('aria-hidden', 'false');

            var target = gate.querySelector('#kategori-form');
            if (target) {
                target.scrollIntoView({ behavior: getSmoothScrollBehavior(), block: 'start' });
            }
        }

        function closeGate(event) {
            if (event) {
                event.preventDefault();
            }

            gate.classList.add('is-collapsed');
            gate.setAttribute('aria-hidden', 'true');

            var topSection = document.querySelector('.lcm-gateway');
            if (topSection) {
                topSection.scrollIntoView({ behavior: getSmoothScrollBehavior(), block: 'start' });
            }
        }

        showTriggers.forEach(function (trigger) {
            trigger.addEventListener('click', openGate);
        });

        hideTriggers.forEach(function (trigger) {
            trigger.addEventListener('click', closeGate);
        });

        if (window.location.hash) {
            var hashTarget = gate.querySelector(window.location.hash);
            if (hashTarget) {
                gate.classList.remove('is-collapsed');
                gate.setAttribute('aria-hidden', 'false');
            }
        }
    }

    function coerceGoogleFormUrl(rawValue) {
        var value = (rawValue || '').trim();
        if (!value) {
            return '';
        }

        value = value.replace(/&amp;/gi, '&');
        value = value.replace(/[\u200B-\u200D\uFEFF\u2060]/g, '');
        value = value.replace(/[\r\n\t]+/g, '');

        if (value.indexOf('//') === 0) {
            value = 'https:' + value;
        }

        var hasScheme = /^[a-z][a-z0-9+\-.]*:\/\//i.test(value);
        if (!hasScheme) {
            var lowerValue = value.toLowerCase();
            var knownPrefixes = [
                'docs.google.com/',
                'www.docs.google.com/',
                'forms.google.com/',
                'www.forms.google.com/',
                'forms.gle/',
                'www.forms.gle/',
            ];

            var shouldPrefixHttps = knownPrefixes.some(function (prefix) {
                return lowerValue.indexOf(prefix) === 0;
            });

            if (shouldPrefixHttps) {
                value = 'https://' + value.replace(/^\/+/, '');
            }
        }

        return value;
    }

    function isSupportedGoogleFormHost(host) {
        return host === 'docs.google.com'
            || host.endsWith('.docs.google.com')
            || host === 'forms.gle'
            || host.endsWith('.forms.gle');
    }

    function normalizeGoogleFormUrl(rawValue) {
        var value = coerceGoogleFormUrl(rawValue);
        if (!value) {
            return '';
        }

        var url;
        try {
            url = new URL(value);
        } catch (error) {
            return value;
        }

        var host = (url.hostname || '').toLowerCase();
        if (host.indexOf('www.') === 0) {
            host = host.slice(4);
        }

        if (host === 'forms.google.com') {
            host = 'docs.google.com';
        }

        url.hostname = host;

        if (!isSupportedGoogleFormHost(host)) {
            return value;
        }

        if (url.protocol === 'http:') {
            url.protocol = 'https:';
        }

        if (url.protocol !== 'https:') {
            return value;
        }

        if (host.indexOf('docs.google.com') !== -1) {
            var normalizedPath = (url.pathname || '').replace(/\/+/g, '/');
            var endpointMatch = normalizedPath.match(/^(\/forms(?:\/u\/\d+)?\/d(?:\/e)?\/[^/]+)\/(viewform|formresponse|edit|prefill)(?:\/.*)?$/i);
            var baseMatch = normalizedPath.match(/^(\/forms(?:\/u\/\d+)?\/d(?:\/e)?\/[^/]+)\/?$/i);

            if (endpointMatch) {
                url.pathname = endpointMatch[1] + '/viewform';
            } else if (baseMatch) {
                url.pathname = baseMatch[1] + '/viewform';
            }
        }

        url.hash = '';

        var toDelete = [];
        url.searchParams.forEach(function (paramValue, key) {
            var lowerKey = key.toLowerCase();
            if (
                lowerKey.indexOf('utm_') === 0
                || lowerKey === 'fbclid'
                || lowerKey === 'gclid'
                || lowerKey === 'igshid'
                || lowerKey === 'source'
                || lowerKey === 'src'
            ) {
                toDelete.push(key);
            }
        });

        toDelete.forEach(function (key) {
            url.searchParams.delete(key);
        });

        return url.toString();
    }

    function isAllowedGoogleFormUrl(rawValue, linkScope, purpose) {
        var normalizedValue = normalizeGoogleFormUrl(rawValue);
        if (!normalizedValue) {
            return false;
        }

        var url;
        try {
            url = new URL(normalizedValue);
        } catch (error) {
            return false;
        }

        if (url.protocol !== 'https:') {
            return false;
        }

        var host = (url.hostname || '').toLowerCase();
        if (!isSupportedGoogleFormHost(host)) {
            return false;
        }

        var normalizedScope = (linkScope || 'public');
        var normalizedPurpose = (purpose || 'pengajuan').toLowerCase();

        if (host.indexOf('docs.google.com') !== -1) {
            var path = (url.pathname || '').replace(/\/+/g, '/').toLowerCase();

            if (path.indexOf('/settings') !== -1) {
                return false;
            }

            // Monitoring may use Google Sheets tracker links.
            var isMonitoringSpreadsheetPath = normalizedPurpose === 'monitoring'
                && /^\/spreadsheets\/d\/[^/]+(?:\/(edit|view))?\/?$/.test(path);
            if (isMonitoringSpreadsheetPath) {
                return true;
            }

            if (normalizedPurpose === 'monitoring' && normalizedScope === 'private') {
                var isMonitoringPrivatePath = /^\/forms(?:\/u\/\d+)?\/d(?:\/e)?\/[^/]+\/(viewform|viewanalytics|edit)\/?$/.test(path);
                if (!isMonitoringPrivatePath) {
                    return false;
                }
            } else {
                var isRespondentPath = /^\/forms(?:\/u\/\d+)?\/d(?:\/e)?\/[^/]+\/viewform\/?$/.test(path);
                if (!isRespondentPath) {
                    return false;
                }
            }
        }

        if (normalizedScope === 'private' && host.indexOf('forms.gle') !== -1) {
            return false;
        }

        return true;
    }

    function setupGFormAssistant() {
        var forms = document.querySelectorAll('form[data-gform-form]');
        if (!forms.length) {
            return;
        }

        forms.forEach(function (form) {
            var urlInput = form.querySelector('[data-form-url-input]');
            if (!urlInput) {
                return;
            }

            var urlPreview = form.querySelector('[data-form-url-preview]');
            var urlHint = form.querySelector('[data-form-url-hint]');
            var startInput = form.querySelector('input[name="effective_start"]');
            var endInput = form.querySelector('input[name="effective_end"]');
            var scopeSelect = form.querySelector('[data-form-link-scope]');
            var purposeSelect = form.querySelector('select[name="purpose"]');

            function currentScope() {
                return scopeSelect ? (scopeSelect.value || 'public') : 'public';
            }

            function currentPurpose() {
                return purposeSelect ? (purposeSelect.value || 'pengajuan') : 'pengajuan';
            }

            function syncDateBounds() {
                if (!startInput || !endInput) {
                    return;
                }

                if (startInput.value) {
                    endInput.min = startInput.value;
                } else {
                    endInput.removeAttribute('min');
                }

                if (endInput.value) {
                    startInput.max = endInput.value;
                } else {
                    startInput.removeAttribute('max');
                }
            }

            function syncUrlState() {
                var value = urlInput.value.trim();
                var scope = currentScope();
                var purpose = currentPurpose();
                if (!value) {
                    if (urlPreview) {
                        urlPreview.classList.add('is-hidden');
                        urlPreview.setAttribute('href', '#');
                        urlPreview.setAttribute('aria-disabled', 'true');
                    }
                    if (urlHint) {
                        urlHint.classList.remove('is-error', 'is-success');
                        if (purpose === 'monitoring' && scope === 'private') {
                            urlHint.textContent = 'Monitoring private: gunakan docs.google.com ... /viewform, /viewanalytics, atau spreadsheet /edit (forms.gle tidak didukung).';
                        } else if (purpose === 'monitoring') {
                            urlHint.textContent = 'Monitoring: gunakan docs.google.com ... /viewform atau spreadsheet /edit berbasis HTTPS.';
                        } else if (scope === 'private') {
                            urlHint.textContent = 'Scope private: gunakan docs.google.com ... /viewform (forms.gle tidak disarankan).';
                        } else {
                            urlHint.textContent = 'Scope public: domain yang diizinkan docs.google.com dan forms.gle dengan link publik viewform.';
                        }
                    }
                    urlInput.setCustomValidity('');
                    return;
                }

                var normalizedValue = normalizeGoogleFormUrl(value);
                var candidateUrl = normalizedValue || value;
                var allowed = isAllowedGoogleFormUrl(candidateUrl, scope, purpose);
                var wasNormalized = normalizedValue !== '' && normalizedValue !== value;

                if (urlPreview) {
                    if (allowed) {
                        urlPreview.classList.remove('is-hidden');
                        urlPreview.setAttribute('href', candidateUrl);
                        urlPreview.removeAttribute('aria-disabled');
                    } else {
                        urlPreview.classList.add('is-hidden');
                        urlPreview.setAttribute('href', '#');
                        urlPreview.setAttribute('aria-disabled', 'true');
                    }
                }

                if (allowed) {
                    urlInput.setCustomValidity('');
                    if (urlHint) {
                        urlHint.classList.remove('is-error');
                        urlHint.classList.add('is-success');
                        if (purpose === 'monitoring' && scope === 'private') {
                            if (wasNormalized) {
                                urlHint.textContent = 'URL monitoring valid. Link telah dinormalisasi ke format docs.google.com yang aman.';
                            } else {
                                urlHint.textContent = 'URL monitoring valid untuk scope private (mendukung viewform, viewanalytics, atau spreadsheet).';
                            }
                        } else if (purpose === 'monitoring') {
                            if (wasNormalized) {
                                urlHint.textContent = 'URL monitoring valid. Link dinormalisasi agar aman.';
                            } else {
                                urlHint.textContent = 'URL monitoring valid (mendukung Google Form atau Google Spreadsheet).';
                            }
                        } else if (scope === 'private') {
                            if (wasNormalized) {
                                urlHint.textContent = 'URL valid. Sistem menormalisasi link private ke format docs.google.com/viewform.';
                            } else {
                                urlHint.textContent = 'URL valid untuk scope private (akses akun terautorisasi).';
                            }
                        } else {
                            if (wasNormalized) {
                                urlHint.textContent = 'URL valid. Sistem menormalisasi link ke format publik viewform.';
                            } else {
                                urlHint.textContent = 'URL valid dan siap digunakan sebagai tautan publik formulir.';
                            }
                        }
                    }
                } else {
                    if (purpose === 'monitoring' && scope === 'private') {
                        urlInput.setCustomValidity('URL monitoring private tidak valid. Gunakan docs.google.com ... /viewform, /viewanalytics, atau spreadsheet /edit berbasis HTTPS.');
                    } else if (purpose === 'monitoring') {
                        urlInput.setCustomValidity('URL monitoring tidak valid. Gunakan docs.google.com ... /viewform atau spreadsheet /edit berbasis HTTPS.');
                    } else if (scope === 'private') {
                        urlInput.setCustomValidity('URL private tidak valid. Gunakan docs.google.com ... /viewform berbasis HTTPS.');
                    } else {
                        urlInput.setCustomValidity('URL formulir tidak valid. Gunakan link Google Form publik berbasis HTTPS.');
                    }
                    if (urlHint) {
                        urlHint.classList.remove('is-success');
                        urlHint.classList.add('is-error');
                        if (purpose === 'monitoring' && scope === 'private') {
                            urlHint.textContent = 'URL monitoring private tidak valid. Gunakan docs.google.com ... /viewform, /viewanalytics, atau spreadsheet /edit, bukan domain lain.';
                        } else if (purpose === 'monitoring') {
                            urlHint.textContent = 'URL monitoring tidak valid. Gunakan docs.google.com ... /viewform atau spreadsheet /edit.';
                        } else if (scope === 'private') {
                            urlHint.textContent = 'URL private tidak valid. Gunakan link docs.google.com ... /viewform, bukan forms.gle atau link edit.';
                        } else {
                            urlHint.textContent = 'URL tidak valid. Gunakan link publik Google Form, bukan link edit atau analytics.';
                        }
                    }
                }
            }

            urlInput.addEventListener('input', syncUrlState);
            urlInput.addEventListener('blur', function () {
                var normalized = normalizeGoogleFormUrl(urlInput.value);
                if (normalized) {
                    urlInput.value = normalized;
                }
                syncUrlState();
            });

            if (startInput && endInput) {
                startInput.addEventListener('change', syncDateBounds);
                endInput.addEventListener('change', syncDateBounds);
                syncDateBounds();
            }

            if (scopeSelect) {
                scopeSelect.addEventListener('change', syncUrlState);
            }

            if (purposeSelect) {
                purposeSelect.addEventListener('change', syncUrlState);
            }

            form.addEventListener('submit', function (event) {
                var normalized = normalizeGoogleFormUrl(urlInput.value);
                if (normalized) {
                    urlInput.value = normalized;
                }

                syncDateBounds();
                syncUrlState();

                if (!form.checkValidity()) {
                    event.preventDefault();
                    form.reportValidity();
                    return;
                }
            });

            syncUrlState();
        });
    }

    function setupPortalCategoryFilter() {
        var input = document.querySelector('[data-portal-filter-input]');
        if (!input) {
            return;
        }

        var target = input.getAttribute('data-portal-filter-target') || '#portalCategoryGrid .category-card';
        if (!target) {
            return;
        }

        var cards = Array.prototype.slice.call(document.querySelectorAll(target));
        if (!cards.length) {
            return;
        }

        var indexedCards = cards.map(function (card) {
            return {
                element: card,
                searchText: normalizeSearchValue(card.textContent || ''),
            };
        });
        var count = document.querySelector('[data-portal-filter-count]');
        var empty = document.querySelector('[data-portal-filter-empty]');
        var resetButton = document.querySelector('[data-portal-filter-reset]');

        function applyFilter() {
            var keyword = normalizeSearchValue(input.value);
            var visible = 0;

            indexedCards.forEach(function (entry) {
                var card = entry.element;
                var show = keyword === '' || entry.searchText.indexOf(keyword) !== -1;
                card.classList.toggle('is-hidden', !show);
                if (show) {
                    card.removeAttribute('inert');
                } else {
                    card.setAttribute('inert', '');
                }

                if (show) {
                    visible += 1;
                }
            });

            if (count) {
                count.textContent = visible + ' kategori ditampilkan';
            }

            setElementVisible(empty, visible === 0);
        }

        var applyFilterDebounced = debounce(applyFilter, 120);

        input.addEventListener('input', applyFilterDebounced);
        input.addEventListener('keydown', function (event) {
            if (event.key !== 'Escape') {
                return;
            }

            event.preventDefault();
            input.value = '';
            applyFilter();
        });

        if (resetButton) {
            resetButton.addEventListener('click', function () {
                input.value = '';
                applyFilter();
                input.focus();
            });
        }

        applyFilter();
    }

    function setupDocumentUploadEnhancements() {
        var form = document.querySelector('[data-document-upload-form]');
        if (!form) {
            return;
        }

        var dropzone = form.querySelector('[data-doc-dropzone]');
        var fileInput = form.querySelector('[data-doc-file-input]');
        var selectedFile = form.querySelector('[data-doc-selected-file]');
        var progressWrap = form.querySelector('[data-upload-progress]');
        var progressBar = form.querySelector('[data-upload-progress-bar]');
        var progressLabel = form.querySelector('[data-upload-progress-label]');

        if (!dropzone || !fileInput) {
            return;
        }

        if (!dropzone.hasAttribute('tabindex')) {
            dropzone.setAttribute('tabindex', '0');
        }
        if (!dropzone.hasAttribute('role')) {
            dropzone.setAttribute('role', 'button');
        }

        var maxBytes = 20 * 1024 * 1024;

        function normalizeFile(file) {
            if (!file) {
                return null;
            }

            var fileName = (file.name || '').trim();
            var fileType = (file.type || '').toLowerCase();
            var isPdf = fileType === 'application/pdf' || /\.pdf$/i.test(fileName);
            var isWithinSize = Number(file.size || 0) > 0 && Number(file.size || 0) <= maxBytes;

            return {
                file: file,
                fileName: fileName || 'file.pdf',
                isPdf: isPdf,
                isWithinSize: isWithinSize,
            };
        }

        function updateSelectedFileText(fileInfo) {
            if (!selectedFile) {
                return;
            }

            if (!fileInfo) {
                selectedFile.textContent = 'Belum ada file dipilih.';
                selectedFile.classList.remove('is-error', 'is-success');
                return;
            }

            if (!fileInfo.isPdf) {
                selectedFile.textContent = 'File harus berformat PDF.';
                selectedFile.classList.remove('is-success');
                selectedFile.classList.add('is-error');
                return;
            }

            if (!fileInfo.isWithinSize) {
                selectedFile.textContent = 'Ukuran file melebihi 20 MB.';
                selectedFile.classList.remove('is-success');
                selectedFile.classList.add('is-error');
                return;
            }

            selectedFile.textContent = 'File dipilih: ' + fileInfo.fileName;
            selectedFile.classList.remove('is-error');
            selectedFile.classList.add('is-success');
        }

        function setInputValidity(fileInfo) {
            if (!fileInfo) {
                fileInput.setCustomValidity('Silakan pilih file PDF.');
                return;
            }

            if (!fileInfo.isPdf) {
                fileInput.setCustomValidity('File harus berformat PDF.');
                return;
            }

            if (!fileInfo.isWithinSize) {
                fileInput.setCustomValidity('Ukuran file maksimal 20 MB.');
                return;
            }

            fileInput.setCustomValidity('');
        }

        function applyDroppedFile(fileList) {
            if (!fileList || !fileList.length) {
                return;
            }

            var file = fileList[0];
            var fileInfo = normalizeFile(file);

            try {
                var dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
            } catch (error) {
                // Fallback: file assignment may be blocked on older browsers.
            }

            updateSelectedFileText(fileInfo);
            setInputValidity(fileInfo);
        }

        dropzone.addEventListener('click', function (event) {
            if (event.target === fileInput) {
                return;
            }

            fileInput.click();
        });

        dropzone.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            event.preventDefault();
            fileInput.click();
        });

        dropzone.addEventListener('dragover', function (event) {
            event.preventDefault();
            dropzone.classList.add('is-dragover');
        });

        dropzone.addEventListener('dragleave', function () {
            dropzone.classList.remove('is-dragover');
        });

        dropzone.addEventListener('drop', function (event) {
            event.preventDefault();
            dropzone.classList.remove('is-dragover');
            applyDroppedFile(event.dataTransfer ? event.dataTransfer.files : null);
        });

        fileInput.addEventListener('change', function () {
            var file = fileInput.files && fileInput.files.length ? fileInput.files[0] : null;
            var fileInfo = normalizeFile(file);
            updateSelectedFileText(fileInfo);
            setInputValidity(fileInfo);
        });

        form.addEventListener('submit', function (event) {
            var file = fileInput.files && fileInput.files.length ? fileInput.files[0] : null;
            var fileInfo = normalizeFile(file);
            setInputValidity(fileInfo);
            updateSelectedFileText(fileInfo);

            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
                return;
            }

            if (progressWrap) {
                progressWrap.classList.remove('is-hidden');
            }

            if (progressBar) {
                progressBar.classList.add('is-running');
            }

            if (progressLabel) {
                progressLabel.textContent = 'Upload sedang berjalan. Jangan tutup halaman ini...';
            }
        });
    }

    function setupScrollableTables() {
        var wraps = document.querySelectorAll('.table-wrap');
        if (!wraps.length) {
            return;
        }

        wraps.forEach(function (wrap) {
            var isTicking = false;

            function syncShadow() {
                var canScroll = wrap.scrollWidth > wrap.clientWidth + 2;
                wrap.classList.toggle('is-scrollable', canScroll);

                if (!canScroll) {
                    wrap.classList.remove('has-overflow-left', 'has-overflow-right');
                    return;
                }

                wrap.classList.toggle('has-overflow-left', wrap.scrollLeft > 2);
                wrap.classList.toggle('has-overflow-right', wrap.scrollLeft + wrap.clientWidth < wrap.scrollWidth - 2);
            }

            function onScroll() {
                if (isTicking) {
                    return;
                }

                isTicking = true;
                window.requestAnimationFrame(function () {
                    syncShadow();
                    isTicking = false;
                });
            }

            var resizeTimer;
            function syncShadowOnResize() {
                window.clearTimeout(resizeTimer);
                resizeTimer = window.setTimeout(syncShadow, 90);
            }

            syncShadow();

            if ('ResizeObserver' in window) {
                var resizeObserver = new ResizeObserver(syncShadow);
                resizeObserver.observe(wrap);
            } else {
                window.addEventListener('resize', syncShadowOnResize);
            }

            wrap.addEventListener('scroll', onScroll, { passive: true });
        });
    }

    function setupProgressiveReveal() {
        var revealItems = document.querySelectorAll('[data-ui-reveal]');
        if (!revealItems.length) {
            return;
        }

        var hasReducedMotion = prefersReducedMotion();
        if (hasReducedMotion || !('IntersectionObserver' in window)) {
            revealItems.forEach(function (item) {
                item.classList.add('is-visible');
            });
            return;
        }

        var observer = new IntersectionObserver(function (entries, currentObserver) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                currentObserver.unobserve(entry.target);
            });
        }, {
            rootMargin: '0px 0px -10% 0px',
            threshold: 0.12,
        });

        revealItems.forEach(function (item, index) {
            item.style.setProperty('--reveal-delay', Math.min(index * 35, 280) + 'ms');
            observer.observe(item);
        });
    }

    function setupAuthLoginEnhancements() {
        var form = document.querySelector('[data-auth-login-form]');
        if (!form) {
            return;
        }

        var emailInput = form.querySelector('[data-auth-email]');
        var passwordInput = form.querySelector('[data-auth-password]');
        var togglePasswordButton = form.querySelector('[data-auth-toggle-password]');
        var capsHint = form.querySelector('[data-auth-caps]');
        var rememberEmail = form.querySelector('[data-auth-remember-email]');
        var storageKey = 'lcm_admin_email_hint';

        function syncCapsLock(event) {
            if (!capsHint || !event || typeof event.getModifierState !== 'function') {
                return;
            }

            var isCapsLock = event.getModifierState('CapsLock');
            capsHint.classList.toggle('is-hidden', !isCapsLock);
        }

        if (togglePasswordButton && passwordInput) {
            togglePasswordButton.addEventListener('click', function () {
                var shouldShow = passwordInput.type === 'password';
                passwordInput.type = shouldShow ? 'text' : 'password';
                togglePasswordButton.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
                togglePasswordButton.setAttribute('title', shouldShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');

                var eye = togglePasswordButton.querySelector('.icon-eye');
                var eyeSlash = togglePasswordButton.querySelector('.icon-eye-slash');
                if (eye && eyeSlash) {
                    eye.classList.toggle('is-hidden', shouldShow);
                    eyeSlash.classList.toggle('is-hidden', !shouldShow);
                }

                passwordInput.focus();
            });
        }

        if (passwordInput) {
            passwordInput.addEventListener('keydown', syncCapsLock);
            passwordInput.addEventListener('keyup', syncCapsLock);
            passwordInput.addEventListener('focus', syncCapsLock);
            passwordInput.addEventListener('blur', function () {
                if (capsHint) {
                    capsHint.classList.add('is-hidden');
                }
            });
        }

        if (emailInput && rememberEmail) {
            try {
                var savedEmail = window.localStorage.getItem(storageKey) || '';
                if (savedEmail && !emailInput.value) {
                    emailInput.value = savedEmail;
                }
            } catch (error) {
                // Ignore browser storage issues.
            }

            form.addEventListener('submit', function () {
                try {
                    if (rememberEmail.checked && emailInput.value.trim()) {
                        window.localStorage.setItem(storageKey, emailInput.value.trim());
                    } else {
                        window.localStorage.removeItem(storageKey);
                    }
                } catch (error) {
                    // Ignore browser storage issues.
                }
            });
        }
    }

    function setupFormSubmitState() {
        var forms = document.querySelectorAll('form[method="post"], form[method="POST"]');
        if (!forms.length) {
            return;
        }

        forms.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (event.defaultPrevented) {
                    return;
                }

                if (form.getAttribute('data-submitting') === '1') {
                    event.preventDefault();
                    return;
                }

                form.setAttribute('data-submitting', '1');
                form.setAttribute('aria-busy', 'true');
                form.classList.add('is-submitting');

                var submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                submitButtons.forEach(function (button) {
                    var tag = (button.tagName || '').toLowerCase();
                    var currentLabel = tag === 'input' ? (button.value || '') : (button.textContent || '');

                    if (!button.getAttribute('data-original-label')) {
                        button.setAttribute('data-original-label', currentLabel.trim());
                    }

                    if (tag === 'input') {
                        button.value = 'Memproses...';
                    } else {
                        button.textContent = 'Memproses...';
                        button.classList.add('is-loading');
                    }

                    button.disabled = true;
                    button.setAttribute('aria-disabled', 'true');
                });
            });
        });
    }

    setupAutoDismissAlerts();
    setupAdminSecondaryPanels();
    setupTableFilter();
    setupExclusiveDetails();
    setupCreateFormModal();
    setupFormEditorQuickAccess();
    setupAdminFormUrlActions();
    setupSavedFormSpotlight();
    setupConfirmButtons();
    setupStickyOffsets();
    setupSearchShortcut();
    setupSectionNavigation();
    setupHeroCarousel();
    setupPortalFormGate();
    setupGFormAssistant();
    setupPortalCategoryFilter();
    setupDocumentUploadEnhancements();
    setupScrollableTables();
    setupFormSubmitState();
    setupAuthLoginEnhancements();
    setupProgressiveReveal();
})();




