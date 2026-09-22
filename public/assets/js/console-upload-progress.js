/**
 * Admin console: upload progress for multipart forms (videos, images, CSV, etc.)
 */
(function () {
    'use strict';

    function formatMb(bytes) {
        if (bytes < 1024 * 1024) {
            return (bytes / 1024).toFixed(1) + ' KB';
        }

        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function collectFiles(form) {
        const files = [];
        form.querySelectorAll('input[type="file"]').forEach(function (input) {
            Array.from(input.files || []).forEach(function (file) {
                files.push(file);
            });
        });

        return files;
    }

    function ensureOverlay() {
        let overlay = document.getElementById('console-upload-overlay');
        if (overlay) {
            return overlay;
        }

        overlay = document.createElement('div');
        overlay.id = 'console-upload-overlay';
        overlay.className = 'console-upload-overlay';
        overlay.setAttribute('role', 'status');
        overlay.setAttribute('aria-live', 'polite');
        overlay.hidden = true;
        overlay.innerHTML =
            '<div class="console-upload-panel">' +
            '<div class="d-flex justify-content-between align-items-start gap-2 small mb-2">' +
            '<strong id="console-upload-status" class="console-upload-status">Uploading…</strong>' +
            '<span id="console-upload-bytes" class="text-nowrap">0 MB / 0 MB</span>' +
            '</div>' +
            '<div class="progress console-upload-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">' +
            '<div id="console-upload-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width:0%">0%</div>' +
            '</div>' +
            '<div id="console-upload-detail" class="small text-muted mt-2 mb-0"></div>' +
            '</div>';

        document.body.appendChild(overlay);

        return overlay;
    }

    function setOverlayState(overlay, percent, loaded, total, statusText, detailText, state) {
        const bar = overlay.querySelector('#console-upload-bar');
        const track = overlay.querySelector('.console-upload-progress-track');
        const statusEl = overlay.querySelector('#console-upload-status');
        const bytesEl = overlay.querySelector('#console-upload-bytes');
        const detailEl = overlay.querySelector('#console-upload-detail');
        const pct = Math.min(100, Math.max(0, Math.round(percent)));

        bar.style.width = pct + '%';
        bar.textContent = pct + '%';
        bar.setAttribute('aria-valuenow', String(pct));
        if (track) {
            track.setAttribute('aria-valuenow', String(pct));
        }

        statusEl.textContent = statusText;
        bytesEl.textContent = formatMb(loaded) + ' / ' + formatMb(total);
        detailEl.textContent = detailText || '';

        bar.classList.remove('bg-primary', 'bg-success', 'bg-danger');

        if (state === 'success') {
            bar.classList.remove('progress-bar-animated', 'progress-bar-striped');
            bar.classList.add('bg-success');
        } else if (state === 'error') {
            bar.classList.remove('progress-bar-animated', 'progress-bar-striped');
            bar.classList.add('bg-danger');
        } else {
            bar.classList.add('progress-bar-animated', 'progress-bar-striped', 'bg-primary');
        }
    }

    function showOverlay(overlay) {
        overlay.hidden = false;
        overlay.classList.add('is-active');
    }

    function hideOverlay(overlay) {
        overlay.hidden = true;
        overlay.classList.remove('is-active');
    }

    function describeFiles(files) {
        if (files.length === 0) {
            return { total: 0, detail: '' };
        }

        if (files.length === 1) {
            return { total: files[0].size, detail: files[0].name };
        }

        const total = files.reduce(function (sum, f) {
            return sum + f.size;
        }, 0);

        return {
            total: total,
            detail: files.length + ' files — ' + files.map(function (f) {
                return f.name;
            }).join(', '),
        };
    }

    function setFormDisabled(form, disabled) {
        form.querySelectorAll('button, input[type="submit"]').forEach(function (el) {
            el.disabled = disabled;
        });
    }

    function phpUploadMaxBytes() {
        const body = document.body;
        if (!body || !body.dataset.phpUploadMaxBytes) {
            return null;
        }

        const parsed = parseInt(body.dataset.phpUploadMaxBytes, 10);

        return Number.isFinite(parsed) && parsed > 0 ? parsed : null;
    }

    function phpUploadMaxLabel() {
        const body = document.body;

        return body && body.dataset.phpUploadMaxLabel ? body.dataset.phpUploadMaxLabel : 'the PHP limit';
    }

    function videoCompressAsync() {
        const body = document.body;

        return !!(body && body.dataset.videoCompressAsync === '1');
    }

    function uploadCompressionEnabled() {
        const body = document.body;

        return !!(body && body.dataset.uploadCompressionEnabled === '1');
    }

    function hasVideoFile(files) {
        return files.some(function (f) {
            return (f.type || '').indexOf('video/') === 0;
        });
    }

    function responseLooksLikeValidationError(html) {
        if (!html || typeof html !== 'string') {
            return false;
        }

        return /alert-danger|Upload too large|exceeds the PHP|dropped your upload|must be \d+ MB or smaller/i.test(html);
    }

    function firstValidationMessage(payload) {
        if (!payload || typeof payload !== 'object') {
            return null;
        }

        if (payload.errors && typeof payload.errors === 'object') {
            const keys = Object.keys(payload.errors);
            for (let i = 0; i < keys.length; i += 1) {
                const messages = payload.errors[keys[i]];
                if (Array.isArray(messages) && messages[0]) {
                    return messages[0];
                }
            }
        }

        return payload.message || null;
    }

    function uploadForm(form) {
        const files = collectFiles(form);
        const meta = describeFiles(files);
        const overlay = ensureOverlay();
        const xhr = new XMLHttpRequest();
        const phpMax = phpUploadMaxBytes();

        if (phpMax && meta.total > phpMax) {
            ensureOverlay();
            showOverlay(overlay);
            setOverlayState(
                overlay,
                100,
                meta.total,
                meta.total,
                'File too large for PHP',
                'Your files are ' + formatMb(meta.total) + ' but this server only allows ' + phpUploadMaxLabel()
                    + ' per request. Run: php artisan serve:large  (or .\\serve-large-uploads.bat)',
                'error'
            );
            return;
        }

        const formData = new FormData(form);
        const keyInput = form.querySelector('[name="section_key"]');
        const sectionKey = keyInput ? String(keyInput.value || '').trim() : '';

        if (sectionKey !== 'home_marquee') {
            [...formData.keys()].forEach(function (name) {
                if (name.indexOf('marquee_links') === 0 || name === 'text_color' || name === 'bg_color') {
                    formData.delete(name);
                }
            });
        }

        if (sectionKey !== 'factsheet_highlights') {
            [...formData.keys()].forEach(function (name) {
                if (name.indexOf('highlight_items') === 0) {
                    formData.delete(name);
                }
            });
        }

        showOverlay(overlay);
        setFormDisabled(form, true);
        setOverlayState(overlay, 0, 0, meta.total, 'Uploading…', meta.detail, 'active');

        xhr.upload.addEventListener('progress', function (ev) {
            if (!ev.lengthComputable) {
                setOverlayState(overlay, 0, 0, meta.total, 'Uploading…', meta.detail, 'active');
                return;
            }

            const pct = (ev.loaded / ev.total) * 100;
            let statusText = 'Uploading…';
            let detail = meta.detail;
            if (pct >= 100) {
                statusText = 'Saving…';
            }
            setOverlayState(overlay, pct, ev.loaded, ev.total, statusText, detail, 'active');
        });

        xhr.addEventListener('load', function () {
            const contentType = xhr.getResponseHeader('Content-Type') || '';

            if (contentType.indexOf('application/json') !== -1) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.redirect) {
                        setOverlayState(
                            overlay,
                            100,
                            meta.total,
                            meta.total,
                            'Done',
                            'Upload complete.',
                            'success'
                        );
                        window.setTimeout(function () {
                            window.location.href = data.redirect;
                        }, 400);
                        return;
                    }
                } catch (parseError) {
                    // Fall through to HTML handling.
                }
            }

            if (xhr.status === 413) {
                setOverlayState(
                    overlay,
                    100,
                    meta.total,
                    meta.total,
                    'Upload too large',
                    'File exceeds the server PHP upload limit. Use php artisan serve:large or raise post_max_size in php.ini.',
                    'error'
                );
                form.dataset.uploadInProgress = '0';
                setFormDisabled(form, false);
                return;
            }

            if (xhr.status >= 200 && xhr.status < 400) {
                if (responseLooksLikeValidationError(xhr.responseText)) {
                    setOverlayState(
                        overlay,
                        100,
                        meta.total,
                        meta.total,
                        'Upload not saved',
                        'The server rejected the file (often a PHP size limit). See the message on the page.',
                        'error'
                    );
                    form.dataset.uploadInProgress = '0';
                    setFormDisabled(form, false);
                    document.open();
                    document.write(xhr.responseText);
                    document.close();
                    return;
                }

                setOverlayState(
                    overlay,
                    100,
                    meta.total,
                    meta.total,
                    'Done',
                    'Upload complete.',
                    'success'
                );
                window.setTimeout(function () {
                    window.location.href = xhr.responseURL || window.location.href;
                }, 400);
                return;
            }

            if (xhr.status === 419) {
                setOverlayState(
                    overlay,
                    100,
                    meta.total,
                    meta.total,
                    'Session expired',
                    'Refresh the page and try again.',
                    'error'
                );
                form.dataset.uploadInProgress = '0';
                setFormDisabled(form, false);
                return;
            }

            if (xhr.status === 422) {
                let detail = 'The server rejected the form. Fix the issue below and try again.';
                try {
                    const data = JSON.parse(xhr.responseText);
                    const message = firstValidationMessage(data);
                    if (message) {
                        detail = message;
                    }
                } catch (parseError) {
                    if (responseLooksLikeValidationError(xhr.responseText)) {
                        document.open();
                        document.write(xhr.responseText);
                        document.close();
                        form.dataset.uploadInProgress = '0';
                        setFormDisabled(form, false);
                        hideOverlay(overlay);
                        return;
                    }
                }

                setOverlayState(
                    overlay,
                    100,
                    meta.total,
                    meta.total,
                    'Upload not saved',
                    detail,
                    'error'
                );
                form.dataset.uploadInProgress = '0';
                setFormDisabled(form, false);
                return;
            }

            setOverlayState(
                overlay,
                100,
                meta.total,
                meta.total,
                'Upload failed',
                'Server returned ' + xhr.status + '. Please try again.',
                'error'
            );
            form.dataset.uploadInProgress = '0';
            setFormDisabled(form, false);
        });

        xhr.addEventListener('error', function () {
            setOverlayState(
                overlay,
                100,
                meta.total,
                meta.total,
                'Upload failed',
                'Network error. Check your connection and try again.',
                'error'
            );
            form.dataset.uploadInProgress = '0';
            setFormDisabled(form, false);
        });

        xhr.addEventListener('abort', function () {
            hideOverlay(overlay);
            form.dataset.uploadInProgress = '0';
            setFormDisabled(form, false);
        });

        xhr.open(form.method || 'POST', form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json, text/html');
        xhr.send(formData);
    }

    function init() {
        const root = document.querySelector('.console-body') || document.body;

        root.querySelectorAll('form[enctype="multipart/form-data"]').forEach(function (form) {
            if (form.dataset.uploadProgress === 'off') {
                return;
            }

            if ((form.method || 'get').toLowerCase() === 'get') {
                return;
            }

            form.addEventListener('submit', function (e) {
                if (collectFiles(form).length === 0) {
                    return;
                }

                if (form.dataset.uploadInProgress === '1') {
                    e.preventDefault();
                    return;
                }

                e.preventDefault();
                form.dataset.uploadInProgress = '1';
                uploadForm(form);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
