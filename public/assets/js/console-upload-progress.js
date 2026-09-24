/**
 * Admin console: upload progress for multipart forms (videos, images, CSV, etc.)
 * Large image sets are sent in automatic batches (500+ supported in one submit).
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

    function collectImageFiles(form) {
        const input = form.querySelector('input[type="file"][name="images[]"]');

        return input ? Array.from(input.files || []) : [];
    }

    function collectVideoFiles(form) {
        const input = form.querySelector('input[type="file"][name="videos[]"]');

        return input ? Array.from(input.files || []) : [];
    }

    function chunkArray(items, size) {
        const chunks = [];
        for (let i = 0; i < items.length; i += size) {
            chunks.push(items.slice(i, i + size));
        }

        return chunks;
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

    function imageUploadBatchSize() {
        const body = document.body;
        const parsed = body && body.dataset.phpImageBatchSize
            ? parseInt(body.dataset.phpImageBatchSize, 10)
            : 20;

        return Number.isFinite(parsed) && parsed > 0 ? Math.min(25, parsed) : 20;
    }

    function imageUploadBatchMaxBytes() {
        const body = document.body;
        const parsed = body && body.dataset.phpImageBatchMaxMb
            ? parseFloat(body.dataset.phpImageBatchMaxMb, 10)
            : 150;

        return Number.isFinite(parsed) && parsed > 0 ? parsed * 1024 * 1024 : 150 * 1024 * 1024;
    }

    function makeImageBatches(files, maxCount, maxBytes) {
        const batches = [];
        let current = [];
        let currentBytes = 0;

        files.forEach(function (file) {
            const size = file.size || 0;
            if (current.length > 0 && (current.length >= maxCount || currentBytes + size > maxBytes)) {
                batches.push(current);
                current = [];
                currentBytes = 0;
            }
            current.push(file);
            currentBytes += size;
        });

        if (current.length > 0) {
            batches.push(current);
        }

        return batches;
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

    function stripIrrelevantFields(formData, sectionKey) {
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
    }

    function buildFormDataForBatch(form, imageBatch, batchIndex, sectionKey) {
        const formData = new FormData(form);

        formData.delete('images[]');
        imageBatch.forEach(function (file) {
            formData.append('images[]', file, file.name);
        });

        if (batchIndex > 0) {
            ['image', 'videos[]', 'pdfs[]', 'audios[]'].forEach(function (name) {
                formData.delete(name);
            });
            form.querySelectorAll('input[type="file"]').forEach(function (input) {
                if (input.name !== 'images[]') {
                    formData.delete(input.name);
                }
            });
        }

        stripIrrelevantFields(formData, sectionKey);

        return formData;
    }

    function sendFormData(form, formData, options) {
        options = options || {};

        return new Promise(function (resolve, reject) {
            const xhr = new XMLHttpRequest();

            xhr.addEventListener('load', function () {
                resolve({
                    status: xhr.status,
                    responseText: xhr.responseText,
                    contentType: xhr.getResponseHeader('Content-Type') || '',
                    responseURL: xhr.responseURL,
                });
            });

            xhr.addEventListener('error', function () {
                reject(new Error('Network error'));
            });

            xhr.addEventListener('abort', function () {
                reject(new Error('Upload aborted'));
            });

            xhr.open(form.method || 'POST', form.action, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json, text/html');
            if (typeof options.batchIndex === 'number') {
                xhr.setRequestHeader('X-Image-Batch-Index', String(options.batchIndex));
            }
            if (typeof options.videoBatchIndex === 'number') {
                xhr.setRequestHeader('X-Video-Batch-Index', String(options.videoBatchIndex));
            }
            xhr.send(formData);
        });
    }

    function buildFormDataForVideoBatch(form, videoFile, batchIndex, sectionKey) {
        const formData = new FormData(form);

        formData.delete('videos[]');
        formData.append('videos[]', videoFile, videoFile.name);

        if (batchIndex > 0) {
            ['image', 'images[]', 'pdfs[]', 'audios[]'].forEach(function (name) {
                formData.delete(name);
            });
            form.querySelectorAll('input[type="file"]').forEach(function (input) {
                if (input.name !== 'videos[]') {
                    formData.delete(input.name);
                }
            });
        }

        stripIrrelevantFields(formData, sectionKey);

        return formData;
    }

    function handleUploadResponse(form, overlay, meta, response) {
        const contentType = response.contentType;

        if (contentType.indexOf('application/json') !== -1) {
            try {
                const data = JSON.parse(response.responseText);
                if (data.redirect) {
                    return { ok: true, redirect: data.redirect };
                }
            } catch (parseError) {
                // Fall through.
            }
        }

        if (response.status === 413) {
            return {
                ok: false,
                title: 'Upload too large',
                detail: 'File exceeds the server upload limit (' + phpUploadMaxLabel() + '). Upload one large file at a time, or ask hosting to raise PHP/nginx limits.',
            };
        }

        if (response.status === 419) {
            return {
                ok: false,
                title: 'Session expired',
                detail: 'Refresh the page and try again.',
            };
        }

        if (response.status === 422) {
            let detail = 'The server rejected the form. Fix the issue below and try again.';
            try {
                const data = JSON.parse(response.responseText);
                const message = firstValidationMessage(data);
                if (message) {
                    detail = message;
                }
            } catch (parseError) {
                if (responseLooksLikeValidationError(response.responseText)) {
                    document.open();
                    document.write(response.responseText);
                    document.close();
                    return { ok: false, handled: true };
                }
            }

            return { ok: false, title: 'Upload not saved', detail: detail };
        }

        if (response.status >= 200 && response.status < 400) {
            if (responseLooksLikeValidationError(response.responseText)) {
                document.open();
                document.write(response.responseText);
                document.close();
                return { ok: false, handled: true };
            }

            return {
                ok: true,
                redirect: response.responseURL || window.location.href,
            };
        }

        var failDetail = 'Server returned ' + response.status + '. Please try again.';
        try {
            var failData = JSON.parse(response.responseText);
            if (failData && failData.message) {
                failDetail = failData.message;
            }
        } catch (parseError) {
            // Keep generic message.
        }

        return {
            ok: false,
            title: 'Upload failed',
            detail: failDetail,
        };
    }

    function uploadSingleRequest(form, files, overlay) {
        const meta = describeFiles(files);
        const formData = new FormData(form);
        const keyInput = form.querySelector('[name="section_key"]');
        const sectionKey = keyInput ? String(keyInput.value || '').trim() : '';

        stripIrrelevantFields(formData, sectionKey);

        const xhr = new XMLHttpRequest();

        return new Promise(function (resolve, reject) {
            xhr.upload.addEventListener('progress', function (ev) {
                if (!ev.lengthComputable) {
                    setOverlayState(overlay, 0, 0, meta.total, 'Uploading…', meta.detail, 'active');
                    return;
                }

                const pct = (ev.loaded / ev.total) * 100;
                let statusText = 'Uploading…';
                let detail = meta.detail;
                if (pct >= 100) {
                    statusText = 'Processing on server…';
                    if (files.length > 1) {
                        detail = 'Upload finished. Saving ' + files.length + ' files on the server…';
                    }
                }
                setOverlayState(overlay, pct, ev.loaded, ev.total, statusText, detail, 'active');
            });

            xhr.addEventListener('load', function () {
                resolve({
                    status: xhr.status,
                    responseText: xhr.responseText,
                    contentType: xhr.getResponseHeader('Content-Type') || '',
                    responseURL: xhr.responseURL,
                });
            });

            xhr.addEventListener('error', function () {
                reject(new Error('Network error'));
            });

            xhr.open(form.method || 'POST', form.action, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json, text/html');
            xhr.send(formData);
        }).then(function (response) {
            return handleUploadResponse(form, overlay, meta, response);
        });
    }

    async function uploadBatchedImages(form, imageFiles, overlay) {
        const batchSize = imageUploadBatchSize();
        const batches = makeImageBatches(imageFiles, batchSize, imageUploadBatchMaxBytes());
        const totalBytes = imageFiles.reduce(function (sum, f) {
            return sum + f.size;
        }, 0);
        const keyInput = form.querySelector('[name="section_key"]');
        const sectionKey = keyInput ? String(keyInput.value || '').trim() : '';
        let uploadedBytes = 0;
        let lastRedirect = null;

        for (let i = 0; i < batches.length; i += 1) {
            const batch = batches[i];
            const batchBytes = batch.reduce(function (sum, f) {
                return sum + f.size;
            }, 0);
            const batchLabel = 'Batch ' + (i + 1) + ' of ' + batches.length + ' (' + batch.length + ' images)';

            setOverlayState(
                overlay,
                totalBytes > 0 ? (uploadedBytes / totalBytes) * 100 : 0,
                uploadedBytes,
                totalBytes,
                'Uploading ' + batchLabel + '…',
                batch.map(function (f) {
                    return f.name;
                }).join(', '),
                'active'
            );

            const formData = buildFormDataForBatch(form, batch, i, sectionKey);
            const response = await sendFormData(form, formData, { batchIndex: i });
            const result = handleUploadResponse(form, overlay, { total: totalBytes }, response);

            if (!result.ok) {
                if (result.handled) {
                    form.dataset.uploadInProgress = '0';
                    setFormDisabled(form, false);
                    hideOverlay(overlay);
                    return;
                }

                setOverlayState(
                    overlay,
                    100,
                    uploadedBytes,
                    totalBytes,
                    result.title || 'Upload failed',
                    (result.detail || '') + ' Failed during ' + batchLabel + '.',
                    'error'
                );
                form.dataset.uploadInProgress = '0';
                setFormDisabled(form, false);
                return;
            }

            uploadedBytes += batchBytes;
            lastRedirect = result.redirect || lastRedirect;

            setOverlayState(
                overlay,
                totalBytes > 0 ? (uploadedBytes / totalBytes) * 100 : 100,
                uploadedBytes,
                totalBytes,
                'Saved ' + batchLabel,
                'Uploaded ' + imageFiles.length + ' images in ' + batches.length + ' batches.',
                'active'
            );
        }

        setOverlayState(
            overlay,
            100,
            totalBytes,
            totalBytes,
            'Done',
            'All ' + imageFiles.length + ' images uploaded.',
            'success'
        );

        window.setTimeout(function () {
            window.location.href = lastRedirect || window.location.href;
        }, 400);
    }

    async function uploadBatchedVideos(form, videoFiles, overlay) {
        const totalBytes = videoFiles.reduce(function (sum, f) {
            return sum + f.size;
        }, 0);
        const keyInput = form.querySelector('[name="section_key"]');
        const sectionKey = keyInput ? String(keyInput.value || '').trim() : '';
        let uploadedBytes = 0;
        let lastRedirect = null;

        for (let i = 0; i < videoFiles.length; i += 1) {
            const video = videoFiles[i];
            const batchLabel = 'Video ' + (i + 1) + ' of ' + videoFiles.length;

            setOverlayState(
                overlay,
                totalBytes > 0 ? (uploadedBytes / totalBytes) * 100 : 0,
                uploadedBytes,
                totalBytes,
                'Uploading ' + batchLabel + '…',
                video.name + ' (' + formatMb(video.size) + ')',
                'active'
            );

            const formData = buildFormDataForVideoBatch(form, video, i, sectionKey);
            const response = await sendFormData(form, formData, { videoBatchIndex: i });
            const result = handleUploadResponse(form, overlay, { total: totalBytes }, response);

            if (!result.ok) {
                if (result.handled) {
                    form.dataset.uploadInProgress = '0';
                    setFormDisabled(form, false);
                    hideOverlay(overlay);
                    return;
                }

                setOverlayState(
                    overlay,
                    100,
                    uploadedBytes,
                    totalBytes,
                    result.title || 'Upload failed',
                    (result.detail || '') + ' Failed during ' + batchLabel + '.',
                    'error'
                );
                form.dataset.uploadInProgress = '0';
                setFormDisabled(form, false);
                return;
            }

            uploadedBytes += video.size || 0;
            lastRedirect = result.redirect || lastRedirect;
        }

        setOverlayState(
            overlay,
            100,
            totalBytes,
            totalBytes,
            'Done',
            'All ' + videoFiles.length + ' videos uploaded.',
            'success'
        );

        window.setTimeout(function () {
            window.location.href = lastRedirect || window.location.href;
        }, 400);
    }

    function uploadForm(form) {
        const files = collectFiles(form);
        const imageFiles = collectImageFiles(form);
        const videoFiles = collectVideoFiles(form);
        const meta = describeFiles(files);
        const overlay = ensureOverlay();
        const phpMax = phpUploadMaxBytes();
        const batchSize = imageUploadBatchSize();
        const needsImageBatching = imageFiles.length > batchSize || imageFiles.length > 15;
        const needsVideoBatching = videoFiles.length > 1;

        if (phpMax && meta.total > phpMax && !needsImageBatching && !needsVideoBatching) {
            showOverlay(overlay);
            setOverlayState(
                overlay,
                100,
                meta.total,
                meta.total,
                'File too large for PHP',
                'Your files are ' + formatMb(meta.total) + ' but this server only allows ' + phpUploadMaxLabel() + ' per request.',
                'error'
            );
            form.dataset.uploadInProgress = '0';
            return;
        }

        showOverlay(overlay);
        setFormDisabled(form, true);

        if (needsVideoBatching) {
            uploadBatchedVideos(form, videoFiles, overlay).catch(function (error) {
                setOverlayState(
                    overlay,
                    100,
                    0,
                    meta.total,
                    'Upload failed',
                    error && error.message ? error.message : 'Network error.',
                    'error'
                );
                form.dataset.uploadInProgress = '0';
                setFormDisabled(form, false);
            });
            return;
        }

        if (needsImageBatching) {
            uploadBatchedImages(form, imageFiles, overlay).catch(function (error) {
                setOverlayState(
                    overlay,
                    100,
                    0,
                    meta.total,
                    'Upload failed',
                    error && error.message ? error.message : 'Network error.',
                    'error'
                );
                form.dataset.uploadInProgress = '0';
                setFormDisabled(form, false);
            });
            return;
        }

        uploadSingleRequest(form, files, overlay)
            .then(function (result) {
                if (!result.ok) {
                    if (result.handled) {
                        return;
                    }

                    setOverlayState(
                        overlay,
                        100,
                        meta.total,
                        meta.total,
                        result.title || 'Upload failed',
                        result.detail || '',
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
                    'Done',
                    'Upload complete.',
                    'success'
                );
                window.setTimeout(function () {
                    window.location.href = result.redirect || window.location.href;
                }, 400);
            })
            .catch(function () {
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
