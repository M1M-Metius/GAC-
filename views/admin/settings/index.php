<?php
/**
 * GAC - Gestión de asuntos de email por plataforma
 */

$content = ob_start();

$platformLabels = [];
foreach ($platforms as $p) {
    $platformLabels[$p['name']] = $p['display_name'];
}
$currentLabel = $platformLabels[$current_platform] ?? ucfirst($current_platform);
?>

<div class="admin-container">
    <div class="admin-header">
        <h1 class="admin-title">Asuntos de Email</h1>
        <a href="/admin/dashboard" class="btn btn-secondary">Volver al Dashboard</a>
    </div>

    <div class="admin-content settings-content">
        <p class="settings-help">
            Los asuntos configurados aquí se usan para detectar correos de cada plataforma (por ejemplo Netflix).
            Si un cliente no recibe códigos, verifica que el asunto del correo real coincida con alguno de esta lista.
        </p>

        <div class="platform-tabs" role="tablist">
            <?php foreach ($platforms as $p): ?>
                <a href="/admin/settings?platform=<?= urlencode($p['name']) ?>"
                   class="platform-tab <?= $p['name'] === $current_platform ? 'active' : '' ?>">
                    <?= htmlspecialchars($p['display_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="settings-panel" data-platform="<?= htmlspecialchars($current_platform) ?>">
            <div class="settings-panel-header">
                <h2><?= htmlspecialchars($currentLabel) ?></h2>
                <label class="toggle-platform">
                    <input type="checkbox"
                           id="platformEnabledToggle"
                           <?= $filter_enabled ? 'checked' : '' ?>>
                    <span>Lectura de correos activa</span>
                </label>
            </div>

            <?php if (!$filter_enabled): ?>
                <div class="settings-alert">
                    Esta plataforma está deshabilitada para lectura de correos. Los asuntos no se usarán hasta que la actives.
                </div>
            <?php endif; ?>

            <form id="addSubjectForm" class="add-subject-form">
                <input type="hidden" name="platform" value="<?= htmlspecialchars($current_platform) ?>">
                <div class="form-group form-group-grow">
                    <label for="newSubjectValue" class="form-label">Nuevo asunto</label>
                    <input type="text"
                           id="newSubjectValue"
                           name="value"
                           class="form-input"
                           placeholder="Ej: Tu código de acceso temporal de Netflix"
                           maxlength="500"
                           required>
                    <span class="form-error" id="newSubjectValueError"></span>
                </div>
                <button type="submit" class="btn btn-primary" id="addSubjectBtn">
                    Agregar asunto
                </button>
            </form>

            <div class="table-container">
                <table class="admin-table" id="subjectsTable">
                    <thead>
                        <tr>
                            <th style="width: 120px">Clave</th>
                            <th>Asunto (texto del correo)</th>
                            <th style="width: 140px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="subjectsTableBody">
                        <?php if (empty($subjects)): ?>
                            <tr class="empty-row">
                                <td colspan="3" class="text-center">
                                    <p class="empty-message">No hay asuntos configurados para <?= htmlspecialchars($currentLabel) ?></p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($subjects as $subject): ?>
                                <tr data-name="<?= htmlspecialchars($subject['name']) ?>">
                                    <td><code><?= htmlspecialchars($subject['name']) ?></code></td>
                                    <td>
                                        <input type="text"
                                               class="form-input subject-value-input"
                                               value="<?= htmlspecialchars($subject['value']) ?>"
                                               maxlength="500">
                                    </td>
                                    <td class="actions-cell">
                                        <button type="button"
                                                class="btn btn-sm btn-secondary btn-save-subject"
                                                title="Guardar cambios">
                                            Guardar
                                        </button>
                                        <button type="button"
                                                class="btn-icon btn-delete-subject"
                                                title="Eliminar"
                                                data-name="<?= htmlspecialchars($subject['name']) ?>">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

$title = $title ?? 'Asuntos de Email';
$show_nav = true;
$footer_text = '';
$footer_whatsapp = false;
$additional_css = ['/assets/css/admin/main.css', '/assets/css/admin/settings.css'];
$additional_js = ['/assets/js/admin/settings.js'];

require base_path('views/layouts/main.php');
?>
