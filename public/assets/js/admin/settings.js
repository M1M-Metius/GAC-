/**
 * GAC - Gestión de asuntos de email por plataforma
 */

(function() {
    'use strict';

    const panel = document.querySelector('.settings-panel');
    const addForm = document.getElementById('addSubjectForm');
    const tableBody = document.getElementById('subjectsTableBody');
    const platformToggle = document.getElementById('platformEnabledToggle');

    function getPlatform() {
        return panel?.dataset.platform || addForm?.querySelector('[name="platform"]')?.value || 'netflix';
    }

    function init() {
        if (addForm) {
            addForm.addEventListener('submit', handleAddSubject);
        }

        if (tableBody) {
            tableBody.addEventListener('click', handleTableClick);
        }

        if (platformToggle) {
            platformToggle.addEventListener('change', handleTogglePlatform);
        }
    }

    async function handleAddSubject(e) {
        e.preventDefault();

        const valueInput = document.getElementById('newSubjectValue');
        const errorEl = document.getElementById('newSubjectValueError');
        const value = valueInput?.value.trim() || '';

        if (!value) {
            showFieldError(errorEl, 'Ingresa el texto del asunto del correo');
            return;
        }

        clearFieldError(errorEl);
        setAddLoading(true);

        try {
            const response = await fetch('/admin/settings/subjects', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    platform: getPlatform(),
                    value: value
                })
            });

            const result = await response.json();

            if (result.success) {
                removeEmptyRow();
                appendSubjectRow(result.subject.name, result.subject.value);
                addForm.reset();
                await showSuccess('Asunto agregado. El cron usará este patrón en la próxima lectura.');
            } else {
                let msg = result.message || 'No se pudo agregar el asunto';
                if (result.existing?.name) {
                    msg += ` (${result.existing.name})`;
                }
                await showError(msg);
            }
        } catch (err) {
            console.error(err);
            await showError('Error de conexión. Intenta de nuevo.');
        } finally {
            setAddLoading(false);
        }
    }

    async function handleTableClick(e) {
        const saveBtn = e.target.closest('.btn-save-subject');
        const deleteBtn = e.target.closest('.btn-delete-subject');

        if (saveBtn) {
            const row = saveBtn.closest('tr');
            await saveSubjectRow(row);
        } else if (deleteBtn) {
            const row = deleteBtn.closest('tr');
            await deleteSubjectRow(row, deleteBtn.dataset.name);
        }
    }

    async function saveSubjectRow(row) {
        if (!row) return;

        const name = row.dataset.name;
        const input = row.querySelector('.subject-value-input');
        const value = input?.value.trim() || '';

        if (!value) {
            await showError('El asunto no puede estar vacío');
            return;
        }

        try {
            const response = await fetch('/admin/settings/subjects/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    platform: getPlatform(),
                    name: name,
                    value: value
                })
            });

            const result = await response.json();

            if (result.success) {
                await showSuccess('Asunto guardado');
            } else {
                await showError(result.message || 'No se pudo guardar');
            }
        } catch (err) {
            console.error(err);
            await showError('Error de conexión');
        }
    }

    async function deleteSubjectRow(row, name) {
        const confirmed = await window.GAC?.confirm(
            '¿Eliminar este asunto? Los correos con ese asunto dejarán de detectarse.',
            'Eliminar asunto'
        );

        if (!confirmed) return;

        try {
            const response = await fetch('/admin/settings/subjects/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    platform: getPlatform(),
                    name: name
                })
            });

            const result = await response.json();

            if (result.success) {
                row.remove();
                if (!tableBody.querySelector('tr[data-name]')) {
                    showEmptyRow();
                }
                await showSuccess('Asunto eliminado');
            } else {
                await showError(result.message || 'No se pudo eliminar');
            }
        } catch (err) {
            console.error(err);
            await showError('Error de conexión');
        }
    }

    async function handleTogglePlatform() {
        const enabled = platformToggle.checked;

        try {
            const response = await fetch('/admin/settings/platform/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    platform: getPlatform(),
                    enabled: enabled
                })
            });

            const result = await response.json();

            if (result.success) {
                const alert = document.querySelector('.settings-alert');
                if (enabled && alert) {
                    alert.remove();
                } else if (!enabled && !document.querySelector('.settings-alert')) {
                    const div = document.createElement('div');
                    div.className = 'settings-alert';
                    div.textContent = 'Esta plataforma está deshabilitada para lectura de correos.';
                    document.querySelector('.settings-panel-header')?.after(div);
                }
            } else {
                platformToggle.checked = !enabled;
                await showError(result.message || 'No se pudo cambiar el estado');
            }
        } catch (err) {
            platformToggle.checked = !enabled;
            console.error(err);
            await showError('Error de conexión');
        }
    }

    function appendSubjectRow(name, value) {
        const tr = document.createElement('tr');
        tr.dataset.name = name;
        tr.innerHTML = `
            <td><code>${escapeHtml(name)}</code></td>
            <td>
                <input type="text" class="form-input subject-value-input" value="${escapeAttr(value)}" maxlength="500">
            </td>
            <td class="actions-cell">
                <button type="button" class="btn btn-sm btn-secondary btn-save-subject">Guardar</button>
                <button type="button" class="btn-icon btn-delete-subject" data-name="${escapeAttr(name)}" title="Eliminar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            </td>
        `;
        tableBody.appendChild(tr);
    }

    function removeEmptyRow() {
        tableBody.querySelector('.empty-row')?.remove();
    }

    function showEmptyRow() {
        const tr = document.createElement('tr');
        tr.className = 'empty-row';
        tr.innerHTML = `
            <td colspan="3" class="text-center">
                <p class="empty-message">No hay asuntos configurados</p>
            </td>
        `;
        tableBody.appendChild(tr);
    }

    function setAddLoading(loading) {
        const btn = document.getElementById('addSubjectBtn');
        if (btn) {
            btn.disabled = loading;
            btn.textContent = loading ? 'Agregando...' : 'Agregar asunto';
        }
    }

    function showFieldError(el, message) {
        if (el) {
            el.textContent = message;
            el.style.display = 'block';
        }
    }

    function clearFieldError(el) {
        if (el) {
            el.textContent = '';
            el.style.display = 'none';
        }
    }

    async function showError(message) {
        if (window.GAC?.error) {
            await window.GAC.error(message, 'Error');
        } else {
            alert(message);
        }
    }

    async function showSuccess(message) {
        if (window.GAC?.success) {
            await window.GAC.success(message, 'Listo');
        }
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function escapeAttr(str) {
        return escapeHtml(str).replace(/"/g, '&quot;');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
